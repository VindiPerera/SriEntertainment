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
        Schema::create('sim_activation_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            
            // References
            $table->foreignId('sim_stock_id')->nullable()->constrained('sim_stocks')->onDelete('set null');
            $table->foreignId('reload_package_id')->nullable()->constrained('reload_packages')->onDelete('set null');
            $table->foreignId('operator_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict'); // Seller/cashier
            $table->foreignId('wallet_account_id')->nullable()->constrained('wallet_accounts')->onDelete('set null');
            $table->foreignId('pricing_rule_id')->nullable()->constrained('operator_pricing_rules')->onDelete('set null');
            
            // Transaction details
            $table->enum('transaction_type', ['sim_activation', 'reload'])->default('reload');
            $table->string('mobile_number', 15)->nullable();
            
            // Package/reload financials
            $table->decimal('package_face_value', 10, 2)->default(0);
            $table->decimal('package_revenue', 10, 2)->default(0); // What customer pays
            $table->decimal('package_cost', 10, 2)->default(0); // Actual cost
            $table->decimal('package_profit', 10, 2)->default(0);
            
            // SIM financials (if applicable)
            $table->decimal('sim_cost', 10, 2)->default(0);
            $table->decimal('sim_revenue', 10, 2)->default(0);
            $table->decimal('sim_profit', 10, 2)->default(0);
            
            // Seller benefits
            $table->decimal('seller_discount_total', 10, 2)->default(0);
            $table->decimal('seller_extra_benefit', 10, 2)->default(0);
            
            // Wallet accounting
            $table->decimal('wallet_change', 10, 2)->default(0); // Positive = credit, Negative = debit
            $table->decimal('wallet_balance_before', 10, 2)->default(0);
            $table->decimal('wallet_balance_after', 10, 2)->default(0);
            
            // Totals
            $table->decimal('total_profit', 10, 2)->default(0); // package_profit + sim_profit
            $table->decimal('total_revenue', 10, 2)->default(0); // package_revenue + sim_revenue
            $table->decimal('total_cost', 10, 2)->default(0); // package_cost + sim_cost
            
            // Metadata
            $table->string('matched_rule_description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['operator_id', 'transaction_date']);
            $table->index(['user_id', 'transaction_date']);
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sim_activation_transactions');
    }
};
