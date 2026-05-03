<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSppCustomScheduleToPesertaSmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->json('spp_custom_schedule')->nullable()->after('sales_plan_id');
        });
    }

    public function down()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->dropColumn('spp_custom_schedule');
        });
    }
}
