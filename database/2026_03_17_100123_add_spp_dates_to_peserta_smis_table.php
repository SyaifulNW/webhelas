<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSppDatesToPesertaSmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            for ($i = 1; $i <= 12; $i++) {
                $table->date('tanggal_spp_' . $i)->nullable()->after('spp_' . $i);
            }
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
            for ($i = 1; $i <= 12; $i++) {
                $table->dropColumn('tanggal_spp_' . $i);
            }
        });
    }
}
