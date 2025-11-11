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
            // Add back photocopy_service_id column with foreign key
            $table->foreignId('photocopy_service_id')->constrained('photocopy_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photocopy_service_raw_materials', function (Blueprint $table) {
            // Remove the foreign key and column
            $table->dropForeign(['photocopy_service_id']);
            $table->dropColumn('photocopy_service_id');
        });
    }
};
