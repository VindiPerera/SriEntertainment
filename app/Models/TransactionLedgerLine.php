<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLedgerLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_activation_transaction_id',
        'line_type',
        'description',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the transaction
     */
    public function transaction()
    {
        return $this->belongsTo(SimActivationTransaction::class, 'sim_activation_transaction_id');
    }
}
