<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLevelToPesertaSmis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->string('level')->nullable()->after('sales_plan_id');
        });
    }

    public function down()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
}
