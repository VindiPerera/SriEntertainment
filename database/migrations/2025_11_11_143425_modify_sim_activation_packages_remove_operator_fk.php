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
        Schema::table('sim_activation_packages', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['operator_id']);
            
            // Drop the index
            $table->dropIndex(['operator_id', 'is_active']);
            
            // Drop operator_id column
            $table->dropColumn('operator_id');
            
            // Add operator_name as string
            $table->string('operator_name')->after('id');
            
            // Add new index
            $table->index(['operator_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sim_activation_packages', function (Blueprint $table) {
            // Drop the new index
            $table->dropIndex(['operator_name', 'is_active']);
            
            // Drop operator_name
            $table->dropColumn('operator_name');
            
            // Re-add operator_id
            $table->foreignId('operator_id')->after('id')->constrained('operators')->onDelete('cascade');
            
            // Re-add index
            $table->index(['operator_id', 'is_active']);
        });
    }
};
