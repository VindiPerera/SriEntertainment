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
        Schema::table('sim_activation_transactions', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['operator_id']);
            $table->dropForeign(['reload_package_id']);
            
            // Drop the old columns
            $table->dropColumn(['operator_id', 'reload_package_id']);
            
            // Add new operator_name column
            $table->string('operator_name')->after('transaction_number');
            
            // Update index
            $table->dropIndex(['operator_id', 'transaction_date']);
            $table->index(['operator_name', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sim_activation_transactions', function (Blueprint $table) {
            // Drop new index and column
            $table->dropIndex(['operator_name', 'transaction_date']);
            $table->dropColumn('operator_name');
            
            // Restore old columns
            $table->foreignId('operator_id')->after('transaction_number')->constrained()->onDelete('restrict');
            $table->foreignId('reload_package_id')->after('sim_stock_id')->constrained()->onDelete('restrict');
            
            // Restore old index
            $table->index(['operator_id', 'transaction_date']);
        });
    }
};
