<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_account_id',
        'transaction_type', // 'deposit', 'sale', 'adjustment', 'refund'
        'face_amount',
        'percentage_applied',
        'bonus_amount',
        'commission_amount',
        'debit',
        'credit',
        'balance_before',
        'balance_after',
        'reference',
        'notes',
        'performed_by',
        'related_sale_id',
        'reload_sale_id',
        'transaction_date',
    ];

    protected $casts = [
        'face_amount' => 'decimal:2',
        'percentage_applied' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the wallet account
     */
    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    /**
     * Get the user who performed the transaction
     */
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get related sale (if any)
     */
    public function relatedSale()
    {
        return $this->belongsTo(Sale::class, 'related_sale_id');
    }

    /**
     * Get related reload sale (if any)
     */
    public function reloadSale()
    {
        return $this->belongsTo(ReloadSale::class, 'reload_sale_id');
    }

    /**
     * Scope for deposits
     */
    public function scopeDeposits($query)
    {
        return $query->where('transaction_type', 'deposit');
    }

    /**
     * Scope for sales
     */
    public function scopeSales($query)
    {
        return $query->where('transaction_type', 'sale');
    }

    /**
     * Scope for adjustments
     */
    public function scopeAdjustments($query)
    {
        return $query->where('transaction_type', 'adjustment');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    /**
     * Get net amount (credit - debit)
     */
    public function getNetAmountAttribute()
    {
        return $this->credit - $this->debit;
    }
}
