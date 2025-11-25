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
        Schema::table('refill_binding', function (Blueprint $table) {
            if (!Schema::hasColumn('refill_binding', 'reason')) {
                $table->string('reason')->default('Added')->after('total_stock');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refill_binding', function (Blueprint $table) {
            if (Schema::hasColumn('refill_binding', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};
