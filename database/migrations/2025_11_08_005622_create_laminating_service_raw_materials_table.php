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
        Schema::create('laminating_service_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laminating_service_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('laminating_service_id')->references('id')->on('laminating_services')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laminating_service_raw_materials');
    }
};
