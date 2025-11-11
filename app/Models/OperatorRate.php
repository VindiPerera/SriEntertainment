<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'user_id',
        'employee_id',
        'rate_type', // 'deposit_bonus' or 'sale_commission'
        'percentage',
        'effective_from',
        'effective_to',
        'is_active'
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the operator
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get the user (if seller-specific rate)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee (if seller-specific rate)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if rate is currently effective
     */
    public function isEffective()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->effective_from > $now) {
            return false;
        }

        if ($this->effective_to && $this->effective_to < $now) {
            return false;
        }

        return true;
    }

    /**
     * Scope for active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for currently effective rates
     */
    public function scopeEffective($query)
    {
        return $query->where('is_active', true)
                    ->where('effective_from', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', now());
                    });
    }

    /**
     * Scope for seller-specific rates
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for employee-specific rates
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for seller (user or employee)
     */
    public function scopeForSeller($query, $sellerId, $sellerType = 'user')
    {
        if ($sellerType === 'user') {
            return $query->where('user_id', $sellerId);
        }
        return $query->where('employee_id', $sellerId);
    }

    /**
     * Scope for global rates
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id')->whereNull('employee_id');
    }
}
