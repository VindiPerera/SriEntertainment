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
        Schema::create('reload_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade');
            $table->string('name'); // e.g., "Rs. 50 Package", "Rs. 100 Package"
            $table->string('code')->unique(); // e.g., "MOB-50", "DIA-100"
            $table->decimal('face_value', 10, 2); // Rs. 50, 100, 500, 1000
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['operator_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reload_packages');
    }
};
