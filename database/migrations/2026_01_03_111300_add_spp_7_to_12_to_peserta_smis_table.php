<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpp7To12ToPesertaSmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->boolean('spp_7')->default(0);
            $table->boolean('spp_8')->default(0);
            $table->boolean('spp_9')->default(0);
            $table->boolean('spp_10')->default(0);
            $table->boolean('spp_11')->default(0);
            $table->boolean('spp_12')->default(0);
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
            //
        });
    }
}
