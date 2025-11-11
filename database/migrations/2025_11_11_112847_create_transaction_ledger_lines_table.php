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
        Schema::create('transaction_ledger_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_activation_transaction_id')->constrained()->onDelete('cascade');
            $table->string('line_type'); // 'wallet_change', 'discount', 'profit', 'info'
            $table->text('description'); // Human-readable ledger line
            $table->decimal('amount', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('sim_activation_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_ledger_lines');
    }
};
