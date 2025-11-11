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
        Schema::create('reload_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade');
            $table->foreignId('wallet_account_id')->constrained('wallet_accounts')->onDelete('cascade');
            $table->foreignId('reload_package_id')->constrained('reload_packages')->onDelete('cascade');
            $table->string('msisdn', 15); // Mobile number
            $table->decimal('face_value', 10, 2); // Rs. 50, 100, 500, 1000
            $table->decimal('commission_percent', 5, 2)->default(0.00);
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->decimal('net_cost', 10, 2); // Amount debited from wallet
            $table->decimal('wallet_balance_before', 15, 2);
            $table->decimal('wallet_balance_after', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->string('transaction_reference')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null'); // Link to POS sale
            $table->text('notes')->nullable();
            $table->timestamp('sale_date')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index(['operator_id', 'sale_date']);
            $table->index(['msisdn', 'sale_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reload_sales');
    }
};
