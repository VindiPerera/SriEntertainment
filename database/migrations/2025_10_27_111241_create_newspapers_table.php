<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('newspapers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('productcode');
            $table->string('barcode')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('supplier')->nullable();
            $table->enum('duration', ['monthly', 'weekly']);
            $table->date('publication_date');
            $table->date('expire_date');
            $table->string('language')->nullable();
            $table->integer('stock_quantity');
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('discount', 5, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newspapers');
    }
};
