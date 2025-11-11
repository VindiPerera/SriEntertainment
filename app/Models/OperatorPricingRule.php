<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_name',
        'face_value',
        'rule_type',
        'transaction_type',
        'seller_discount_flat',
        'seller_discount_percent',
        'extra_benefit',
        'wallet_credit',
        'package_cost_override',
        'priority',
        'is_active',
        'rule_description',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'seller_discount_flat' => 'decimal:2',
        'seller_discount_percent' => 'decimal:2',
        'extra_benefit' => 'decimal:2',
        'wallet_credit' => 'decimal:2',
        'package_cost_override' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get transactions using this rule
     */
    public function transactions()
    {
        return $this->hasMany(SimActivationTransaction::class, 'pricing_rule_id');
    }

    /**
     * Scope to get active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
