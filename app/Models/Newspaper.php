<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newspaper extends Model
{
    use HasFactory, GeneratesUniqueCode;

    protected $fillable = [
        'name',
        'productcode',
        'barcode',
        'batch_no',
        'supplier',
        'duration',
        'publication_date',
        'expire_date',
        'language',
        'stock_quantity',
        'cost_price',
        'selling_price',
        'discount',
        'discount_price',
        'return',
    ];

    // default attributes
    protected $attributes = [
        'stock_quantity' => 0,
    ];

    // ensure numeric type for stock
    protected $casts = [
        'publication_date' => 'date', // Cast publication_date as a date
        'stock_quantity' => 'integer',
    ];

    /**
     * Scope to select only id, name and stock_quantity.
     * Usage: Newspaper::selectNameAndStock()->get();
     */
    public function scopeSelectNameAndStock($query)
    {
        return $query->select('id', 'name', 'stock_quantity');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}