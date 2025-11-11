<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimReload extends Model
{
    use HasFactory;

    protected $table = 'reload_sales';

    protected $fillable = [
        'user_id',
        'employee_id',
        'operator_id',
        'wallet_account_id',
        'reload_package_id',
        'msisdn',
        'face_value',
        'commission_percent',
        'commission_amount',
        'net_cost',
        'wallet_balance_before',
        'wallet_balance_after',
        'status',
        'transaction_reference',
        'sale_id',
        'notes',
        'sale_date',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_cost' => 'decimal:2',
        'wallet_balance_before' => 'decimal:2',
        'wallet_balance_after' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    /**
     * Get the operator
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the user who made the sale
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee who made the sale
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the wallet account used
     */
    public function walletAccount()
    {
        return $this->belongsTo(WalletAccount::class);
    }

    /**
     * Get the reload package
     */
    public function reloadPackage()
    {
        return $this->belongsTo(ReloadPackage::class);
    }

    /**
     * Get the related POS sale
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get wallet transaction
     */
    public function walletTransaction()
    {
        return $this->hasOne(WalletTransaction::class);
    }

    /**
     * Scope for completed sales
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending sales
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed sales
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('sale_date', [$from, $to]);
    }

    /**
     * Scope by operator
     */
    public function scopeByOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    /**
     * Get formatted MSISDN
     */
    public function getFormattedMsisdnAttribute()
    {
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $this->msisdn);
    }
}

