<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillLaminating extends Model
{
    use HasFactory;

    protected $table = 'refill_laminating'; // Correct table name

    protected $fillable = [
        'product_id',
        'product_code',
        'product_name',
        'quantity',
        'total_stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}