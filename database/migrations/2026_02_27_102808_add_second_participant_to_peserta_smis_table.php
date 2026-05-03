<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecondParticipantToPesertaSmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->string('nama_2')->nullable()->after('nama_asli');
            $table->string('nama_asli_2')->nullable()->after('nama_2');
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
            $table->dropColumn(['nama_2', 'nama_asli_2']);
        });
    }
}
