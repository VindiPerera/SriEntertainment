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
        Schema::create('operator_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained()->onDelete('cascade');
            $table->decimal('face_value', 10, 2)->nullable(); // NULL means "any/default"
            $table->string('rule_type')->default('default'); // 'exact', 'percentage', 'default'
            $table->enum('transaction_type', ['sim_activation', 'package', 'reload'])->default('package');
            
            // Discount configuration
            $table->decimal('seller_discount_flat', 10, 2)->default(0); // Fixed discount amount
            $table->decimal('seller_discount_percent', 5, 2)->default(0); // Percentage discount
            $table->decimal('extra_benefit', 10, 2)->default(0); // Extra benefit beyond discount
            $table->decimal('wallet_credit', 10, 2)->default(0); // Direct wallet credit (can be positive)
            
            // Cost override
            $table->decimal('package_cost_override', 10, 2)->nullable(); // Override package cost
            
            $table->integer('priority')->default(0); // Higher priority rules match first
            $table->boolean('is_active')->default(true);
            $table->text('rule_description')->nullable(); // Human-readable description
            
            $table->timestamps();
            
            // Indexes
            $table->index(['operator_id', 'face_value', 'transaction_type'], 'opr_op_fv_type_idx');
            $table->index(['is_active', 'priority'], 'opr_active_priority_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_pricing_rules');
    }
};
