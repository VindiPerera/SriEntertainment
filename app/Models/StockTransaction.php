<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'newspaper_id',
        'photocopy_service_id',
        'printout_service_id',
        'laminating_service_id',
        'binding_service_id',
        'transaction_type',
        'quantity',
        'transaction_date',
        'supplier_id',
        'reason',
        'notes',
    ];

     // Relationships
     public function product()
     {
        //  return $this->belongsTo(Product::class, 'product_id','id');
         return $this->belongsTo(Product::class)->withTrashed();
     }

     public function supplier()
     {

        return $this->belongsTo(Supplier::class)->withTrashed();

     }
        public function newspaper()
        {
            return $this->belongsTo(Newspaper::class);
        }

        public function photocopyService()
        {
            return $this->belongsTo(PhotocopyService::class);
        }

        public function printoutService()
        {
            return $this->belongsTo(PrintoutService::class);
        }

        public function laminatingService()
        {
            return $this->belongsTo(LaminatingService::class);
        }

        public function bindingService()
        {
            return $this->belongsTo(BindingService::class);
        }
}
