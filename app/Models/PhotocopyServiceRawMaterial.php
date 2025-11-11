<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotocopyServiceRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'photocopy_service_id',
        'product_id',
    ];

    public function photocopyService()
    {
        return $this->belongsTo(PhotocopyService::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}