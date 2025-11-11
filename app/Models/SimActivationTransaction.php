<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimActivationTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'operator_name',
        'sim_stock_id',
        'user_id',
        'wallet_account_id',
        'pricing_rule_id',
        'transaction_type',
        'mobile_number',
        'package_face_value',
        'package_revenue',
        'package_cost',
        'package_profit',
        'sim_cost',
        'sim_revenue',
        'sim_profit',
        'seller_discount_total',
        'seller_extra_benefit',
        'wallet_change',
        'wallet_balance_before',
        'wallet_balance_after',
        'total_profit',
        'total_revenue',
        'total_cost',
        'matched_rule_description',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'package_face_value' => 'decimal:2',
        'package_revenue' => 'decimal:2',
        'package_cost' => 'decimal:2',
        'package_profit' => 'decimal:2',
        'sim_cost' => 'decimal:2',
        'sim_revenue' => 'decimal:2',
        'sim_profit' => 'decimal:2',
        'seller_discount_total' => 'decimal:2',
        'seller_extra_benefit' => 'decimal:2',
        'wallet_change' => 'decimal:2',
        'wallet_balance_before' => 'decimal:2',
        'wallet_balance_after' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function simStock()
    {
        return $this->belongsTo(SimStock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    public function pricingRule()
    {
        return $this->belongsTo(OperatorPricingRule::class);
    }

    public function ledgerLines()
    {
        return $this->hasMany(TransactionLedgerLine::class)->orderBy('sort_order');
    }

    /**
     * Generate transaction number
     */
    public static function generateTransactionNumber()
    {
        return 'SIM-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
}
