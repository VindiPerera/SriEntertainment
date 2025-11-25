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
        Schema::table('refill_photocopies', function (Blueprint $table) {
            $table->string('reason')->default('Added')->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refill_photocopies', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
