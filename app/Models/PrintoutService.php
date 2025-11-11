<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintoutService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'size',
        'side',
        'pages',
        'color',
        'price',
        'service_charge',
        'totalprice',
        'service_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($photocopyService) {
            $photocopyService->totalprice = $photocopyService->price + $photocopyService->service_charge;
        });

        static::updating(function ($photocopyService) {
            $photocopyService->totalprice = $photocopyService->price + $photocopyService->service_charge;
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
     * Get the raw materials for this printout service
     */
    public function rawMaterials()
    {
        return $this->hasMany(PrintoutServiceRawMaterial::class);
    }

    /**
     * Get the service this printout service belongs to
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}