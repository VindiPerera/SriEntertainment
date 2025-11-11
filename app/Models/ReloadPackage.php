<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReloadPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_id',
        'name',
        'code',
        'face_value',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the operator
     */
    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * Get reload sales for this package
     */
    public function reloadSales()
    {
        return $this->hasMany(ReloadSale::class);
    }

    /**
     * Scope to get active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get packages for an operator
     */
    public function scopeForOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('face_value');
    }
}

