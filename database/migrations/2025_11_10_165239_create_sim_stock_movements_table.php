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
        Schema::create('sim_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_stock_id')->constrained('sim_stocks')->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'adjustment']); // in=purchase/restock, out=sale, adjustment=manual correction
            $table->integer('quantity'); // positive for in, negative for out
            $table->integer('previous_stock');
            $table->integer('current_stock');
            $table->string('reference_type')->nullable(); // e.g., 'purchase', 'sale', 'adjustment'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record (sale_id, etc.)
            $table->text('notes')->nullable();
            $table->timestamp('movement_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sim_stock_movements');
    }
};
