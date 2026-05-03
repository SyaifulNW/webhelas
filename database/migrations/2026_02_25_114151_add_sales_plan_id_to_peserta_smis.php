<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesPlanIdToPesertaSmis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_plan_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->dropColumn('sales_plan_id');
        });
    }
}
