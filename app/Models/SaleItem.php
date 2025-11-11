<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'newspaper_id',
        'photocopy_id',
        'printout_id',
        'laminating_id',
        'binding_id',
        'quantity',
        'unit_price',
        'total_price',
    ];


    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id','id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','id');
    }
    public function newspaper()
    {
        return $this->belongsTo(Newspaper::class);
    }

    public function photocopyService()
    {
        return $this->belongsTo(PhotocopyService::class, 'photocopy_id', 'id');
    }

    public function printoutService()
    {
        return $this->belongsTo(PrintoutService::class, 'printout_id', 'id');
    }

    public function laminatingService()
    {
        return $this->belongsTo(LaminatingService::class, 'laminating_id', 'id');
    }

    public function bindingService()
    {
        return $this->belongsTo(BindingService::class, 'binding_id', 'id');
    }
}
