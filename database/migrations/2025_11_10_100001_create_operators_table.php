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
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Mobitel, Dialog, Airtel, Hutch
            $table->string('code')->unique(); // MOB, DIA, AIR, HUT
            $table->enum('business_model', ['deposit_bonus', 'sale_commission'])->default('sale_commission');
            $table->decimal('default_percentage', 5, 2)->default(0.00); // Default percentage (4.00, 6.00)
            $table->boolean('is_active')->default(true);
            $table->string('logo_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
