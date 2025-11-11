<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WalletAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'operator_id',
        'balance',
        'total_deposits',
        'total_sales',
        'total_commissions',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_deposits' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_commissions' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the wallet
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the operator for this wallet
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get transactions for this wallet
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get reload sales from this wallet
     */
    public function reloadSales()
    {
        return $this->hasMany(ReloadSale::class);
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance($amount)
    {
        return $this->balance >= $amount;
    }

    /**
     * Scope to get active wallets only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get wallets by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Deposit money with optional bonus (for Airtel/Hutch)
     */
    public function deposit($amount, $userId, $notes = null)
    {
        return DB::transaction(function () use ($amount, $userId, $notes) {
            // Lock the wallet for update to prevent race conditions
            $this->lockForUpdate()->find($this->id);

            $operator = $this->operator;
            $balanceBefore = $this->balance;
            
            // Calculate bonus if applicable
            $bonusAmount = 0;
            $percentage = 0;
            $creditAmount = $amount;

            if ($operator->usesDepositBonus()) {
                $percentage = $operator->getEffectiveRate($this->user_id, 'deposit_bonus');
                $bonusAmount = ($amount * $percentage) / 100;
                $creditAmount = $amount + $bonusAmount;
            }

            // Update wallet balance
            $this->balance += $creditAmount;
            $this->last_transaction_at = now();
            $this->save();

            // Create deposit transaction
            $depositTx = $this->transactions()->create([
                'type' => 'deposit',
                'face_amount' => $amount,
                'percentage_applied' => $percentage,
                'bonus_amount' => 0,
                'debit' => 0,
                'credit' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $amount,
                'reference' => 'DEP-' . strtoupper(uniqid()),
                'performed_by' => $userId,
                'notes' => $notes,
                'metadata' => json_encode([
                    'operator' => $operator->name,
                    'deposit_type' => 'manual'
                ])
            ]);

            // If there's a bonus, create bonus transaction
            if ($bonusAmount > 0) {
                $this->transactions()->create([
                    'type' => 'bonus',
                    'face_amount' => $amount,
                    'percentage_applied' => $percentage,
                    'bonus_amount' => $bonusAmount,
                    'debit' => 0,
                    'credit' => $bonusAmount,
                    'balance_before' => $balanceBefore + $amount,
                    'balance_after' => $balanceBefore + $creditAmount,
                    'reference' => 'BON-' . strtoupper(uniqid()),
                    'performed_by' => $userId,
                    'notes' => "Deposit bonus ({$percentage}%)",
                    'metadata' => json_encode([
                        'operator' => $operator->name,
                        'related_transaction' => $depositTx->reference
                    ])
                ]);
            }

            return [
                'success' => true,
                'deposit_amount' => $amount,
                'bonus_amount' => $bonusAmount,
                'total_credited' => $creditAmount,
                'new_balance' => $this->balance,
                'transaction_reference' => $depositTx->reference
            ];
        });
    }

    /**
     * Sell reload and deduct from wallet
     */
    public function sellReload($faceValue, $msisdn, $userId, $reloadPackageId = null, $notes = null)
    {
        return DB::transaction(function () use ($faceValue, $msisdn, $userId, $reloadPackageId, $notes) {
            // Lock the wallet for update
            $this->lockForUpdate()->find($this->id);

            $operator = $this->operator;
            $balanceBefore = $this->balance;
            
            // Calculate commission and net cost
            $commissionPercent = 0;
            $commissionAmount = 0;
            $netCost = $faceValue;

            if ($operator->usesSaleCommission()) {
                $commissionPercent = $operator->getEffectiveRate($this->user_id, 'sale_commission');
                $commissionAmount = ($faceValue * $commissionPercent) / 100;
                $netCost = $faceValue - $commissionAmount;
            }

            // Check if sufficient balance
            if ($this->balance < $netCost) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Update wallet balance
            $this->balance -= $netCost;
            $this->last_transaction_at = now();
            $this->save();

            // Create sale transaction
            $txReference = 'SALE-' . strtoupper(uniqid());
            $saleTx = $this->transactions()->create([
                'type' => 'sale',
                'face_amount' => $faceValue,
                'percentage_applied' => $commissionPercent,
                'bonus_amount' => 0,
                'debit' => $netCost,
                'credit' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->balance,
                'reference' => $txReference,
                'performed_by' => $userId,
                'notes' => $notes,
                'metadata' => json_encode([
                    'operator' => $operator->name,
                    'msisdn' => $msisdn,
                    'face_value' => $faceValue,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commissionAmount
                ])
            ]);

            // Create reload sale record
            $reloadSale = ReloadSale::create([
                'user_id' => $this->user_id,
                'operator_id' => $this->operator_id,
                'reload_package_id' => $reloadPackageId,
                'msisdn' => $msisdn,
                'face_value' => $faceValue,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
                'net_cost' => $netCost,
                'transaction_reference' => $txReference,
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => $notes
            ]);

            return [
                'success' => true,
                'face_value' => $faceValue,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
                'net_cost' => $netCost,
                'new_balance' => $this->balance,
                'transaction_reference' => $txReference,
                'reload_sale_id' => $reloadSale->id
            ];
        });
    }

    /**
     * Get quote for reload sale without executing
     */
    public function getReloadQuote($faceValue)
    {
        $operator = $this->operator;
        $commissionPercent = 0;
        $commissionAmount = 0;
        $netCost = $faceValue;

        if ($operator->usesSaleCommission()) {
            $commissionPercent = $operator->getEffectiveRate($this->user_id, 'sale_commission');
            $commissionAmount = ($faceValue * $commissionPercent) / 100;
            $netCost = $faceValue - $commissionAmount;
        }

        return [
            'face_value' => $faceValue,
            'commission_percent' => $commissionPercent,
            'commission_amount' => $commissionAmount,
            'net_cost' => $netCost,
            'current_balance' => $this->balance,
            'balance_after_sale' => $this->balance - $netCost,
            'sufficient_balance' => $this->balance >= $netCost
        ];
    }
}
