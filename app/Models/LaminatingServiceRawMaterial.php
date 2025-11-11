<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaminatingServiceRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'laminating_service_id',
        'product_id',
    ];

    public function laminatingService()
    {
        return $this->belongsTo(LaminatingService::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
