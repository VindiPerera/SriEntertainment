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
        Schema::table('reload_sales', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cash')->after('notes');
            $table->decimal('card_surcharge', 10, 2)->default(0)->after('payment_method');
            $table->decimal('cash_received', 10, 2)->default(0)->after('card_surcharge');
            $table->decimal('change_amount', 10, 2)->default(0)->after('cash_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reload_sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'card_surcharge', 'cash_received', 'change_amount']);
        });
    }
};
