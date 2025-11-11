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
        Schema::table('photocopy_service_raw_materials', function (Blueprint $table) {
            // Drop the quantity column
            $table->dropColumn('quantity');
            
            // Drop the foreign key constraint first, then the column
            $table->dropForeign(['photocopy_service_id']);
            $table->dropColumn('photocopy_service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photocopy_service_raw_materials', function (Blueprint $table) {
            // Re-add the quantity column
            $table->integer('quantity');
            
            // Re-add the photocopy_service_id column and foreign key
            $table->foreignId('photocopy_service_id')->constrained('photocopy_services')->onDelete('cascade');
        });
    }
};
