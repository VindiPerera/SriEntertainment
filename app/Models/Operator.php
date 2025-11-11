<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'business_model', // 'deposit_bonus' or 'sale_commission'
        'default_percentage',
        'is_active',
        'logo_url',
        'description'
    ];

    protected $casts = [
        'default_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get wallet accounts for this operator
     */
    public function walletAccounts()
    {
        return $this->hasMany(WalletAccount::class);
    }

    /**
     * Get operator rates
     */
    public function rates()
    {
        return $this->hasMany(OperatorRate::class);
    }

    /**
     * Get reload packages for this operator
     */
    public function reloadPackages()
    {
        return $this->hasMany(ReloadPackage::class);
    }

    /**
     * Get reload sales for this operator
     */
    public function reloadSales()
    {
        return $this->hasMany(ReloadSale::class);
    }

    /**
     * Check if operator uses deposit bonus model (Airtel/Hutch)
     */
    public function usesDepositBonus()
    {
        return $this->business_model === 'deposit_bonus';
    }

    /**
     * Check if operator uses sale commission model (Mobitel/Dialog)
     */
    public function usesSaleCommission()
    {
        return $this->business_model === 'sale_commission';
    }

    /**
     * Get effective rate for a user and rate type
     */
    public function getEffectiveRate($userId = null, $rateType = null)
    {
        // If rate type not specified, use operator's business model
        if (!$rateType) {
            $rateType = $this->business_model;
        }

        // Try to get user-specific rate first
        if ($userId) {
            $userRate = $this->rates()
                ->where('user_id', $userId)
                ->where('rate_type', $rateType)
                ->where('is_active', true)
                ->where('effective_from', '<=', now())
                ->where(function($query) {
                    $query->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', now());
                })
                ->orderBy('effective_from', 'desc')
                ->first();

            if ($userRate) {
                return $userRate->percentage;
            }
        }

        // Fall back to global operator rate
        $globalRate = $this->rates()
            ->whereNull('user_id')
            ->where('rate_type', $rateType)
            ->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function($query) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', now());
            })
            ->orderBy('effective_from', 'desc')
            ->first();

        return $globalRate ? $globalRate->percentage : $this->default_percentage;
    }

    /**
     * Scope to get active operators only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
