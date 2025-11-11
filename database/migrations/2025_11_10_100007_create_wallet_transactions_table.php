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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_account_id')->constrained('wallet_accounts')->onDelete('cascade');
            $table->enum('transaction_type', ['deposit', 'sale', 'adjustment', 'refund']);
            $table->decimal('face_amount', 15, 2); // The actual amount (e.g., reload face value or deposit amount)
            $table->decimal('percentage_applied', 5, 2)->default(0.00); // Bonus or commission %
            $table->decimal('bonus_amount', 15, 2)->default(0.00); // Bonus on deposit (Airtel/Hutch)
            $table->decimal('commission_amount', 15, 2)->default(0.00); // Commission on sale (Mobitel/Dialog)
            $table->decimal('debit', 15, 2)->default(0.00); // Amount debited from wallet
            $table->decimal('credit', 15, 2)->default(0.00); // Amount credited to wallet
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference')->nullable(); // MSISDN, invoice number, etc.
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('related_sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->unsignedBigInteger('reload_sale_id')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
            
            // Indexes for reporting
            $table->index(['wallet_account_id', 'transaction_date']);
            $table->index(['transaction_type', 'transaction_date']);
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
