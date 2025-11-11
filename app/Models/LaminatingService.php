<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaminatingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pouch_size',
        'price',
        'service_amount',
        'service_id',
    ];

    public function rawMaterials()
    {
        return $this->hasMany(LaminatingServiceRawMaterial::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}