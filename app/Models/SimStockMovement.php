<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_stock_id',
        'movement_type',
        'quantity',
        'previous_stock',
        'current_stock',
        'reference_type',
        'reference_id',
        'notes',
        'movement_date'
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    /**
     * Get the SIM stock that owns the movement
     */
    public function simStock()
    {
        return $this->belongsTo(SimStock::class);
    }
}
