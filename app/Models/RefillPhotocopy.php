<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillPhotocopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_code',
        'product_name',
        'quantity',
        'stock',
    ];
       
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}