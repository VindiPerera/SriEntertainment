<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_name',
        'batch_number',
        'cost_price',
        'selling_price',
        'stock',
        'purchase_date'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    /**
     * Get the stock movements for this SIM stock
     */
    public function movements()
    {
        return $this->hasMany(SimStockMovement::class);
    }
}
