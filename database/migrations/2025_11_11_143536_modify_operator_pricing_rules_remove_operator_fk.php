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
        Schema::table('operator_pricing_rules', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['operator_id']);
            
            // Drop the index
            $table->dropIndex('opr_op_fv_type_idx');
            
            // Drop operator_id column
            $table->dropColumn('operator_id');
            
            // Add operator_name as string
            $table->string('operator_name')->after('id');
            
            // Add new index
            $table->index(['operator_name', 'face_value', 'transaction_type'], 'opr_name_fv_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_pricing_rules', function (Blueprint $table) {
            // Drop the new index
            $table->dropIndex('opr_name_fv_type_idx');
            
            // Drop operator_name
            $table->dropColumn('operator_name');
            
            // Re-add operator_id
            $table->foreignId('operator_id')->after('id')->constrained()->onDelete('cascade');
            
            // Re-add index
            $table->index(['operator_id', 'face_value', 'transaction_type'], 'opr_op_fv_type_idx');
        });
    }
};
