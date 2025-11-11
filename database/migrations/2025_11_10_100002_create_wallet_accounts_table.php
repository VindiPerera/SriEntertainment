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
        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('total_deposits', 15, 2)->default(0.00);
            $table->decimal('total_sales', 15, 2)->default(0.00);
            $table->decimal('total_commissions', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Ensure one wallet per operator per seller
            $table->unique(['user_id', 'operator_id']);
            $table->unique(['employee_id', 'operator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_accounts');
    }
};
