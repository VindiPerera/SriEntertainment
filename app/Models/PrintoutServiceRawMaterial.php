<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintoutServiceRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'printout_service_id',
        'product_id',
    ];

    public function printoutService()
    {
        return $this->belongsTo(PrintoutService::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}