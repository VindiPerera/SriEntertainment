<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillBinding extends Model
{
    use HasFactory;

    protected $table = 'refill_binding';

    protected $fillable = [
        'product_id',
        'product_code',
        'product_name',
        'quantity',
        'total_stock',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
