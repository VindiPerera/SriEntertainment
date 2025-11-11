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
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('laminating_service_id')->nullable()->after('printout_service_id');
            $table->foreign('laminating_service_id')->references('id')->on('laminating_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['laminating_service_id']);
            $table->dropColumn('laminating_service_id');
        });
    }
};
