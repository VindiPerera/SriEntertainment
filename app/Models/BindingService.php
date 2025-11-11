<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BindingService extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_id',
        'name',
        'binding_type',
        'pages',
        'price',
        'service_charge',
        'totalprice'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bindingService) {
            $bindingService->totalprice = $bindingService->price + $bindingService->service_charge;
        });

        static::updating(function ($bindingService) {
            $bindingService->totalprice = $bindingService->price + $bindingService->service_charge;
        });
    }

    /**
     * Calculate the total price
     *
     * @return float
     */
    public function calculateTotalPrice()
    {
        return $this->price + $this->service_charge;
    }

    /**
     * Get the raw materials for this binding service.
     */
    public function rawMaterials()
    {
        return $this->hasMany(BindingServiceRawMaterial::class);
    }
}