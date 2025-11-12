<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimActivationPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_name',
        'package_name',
        'face_value',
        'selling_price',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'face_value' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the transactions using this package
     */
    public function transactions()
    {
        return $this->hasMany(SimActivationTransaction::class, 'sim_activation_package_id');
    }
}
