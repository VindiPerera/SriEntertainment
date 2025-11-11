<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printout_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('size');
            $table->string('side');
            $table->string('pages');
            $table->string('color');
            $table->decimal('price', 8, 2);
            $table->decimal('service_charge', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printout_services');
    }
};