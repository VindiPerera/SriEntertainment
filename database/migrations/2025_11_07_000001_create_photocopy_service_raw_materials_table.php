<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotocopyServiceRawMaterialsTable extends Migration
{
    public function up()
    {
        Schema::create('photocopy_service_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photocopy_service_id')->constrained('photocopy_services')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('photocopy_service_raw_materials');
    }
}