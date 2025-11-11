<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BindingServiceRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'binding_service_id',
        'product_id',
    ];

    public function bindingService()
    {
        return $this->belongsTo(BindingService::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
