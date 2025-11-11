<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotocopyService extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'size',
        'side',
        'pages',
        'color',
        'price',
        'service_charge',
        'totalprice',
        'service_id',
    ];

    /**
     * The "booting" method of the model.
     */
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
     * Get the raw materials for the photocopy service.
     */
    public function rawMaterials()
    {
        return $this->hasMany(PhotocopyServiceRawMaterial::class);
    }
}
