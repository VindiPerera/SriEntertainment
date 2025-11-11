<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewspaperIdToStockTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('newspaper_id')->nullable()->after('product_id');
            
            $table->foreign('newspaper_id')
                  ->references('id')
                  ->on('newspapers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['newspaper_id']);
            $table->dropColumn('newspaper_id');
        });
    }
}