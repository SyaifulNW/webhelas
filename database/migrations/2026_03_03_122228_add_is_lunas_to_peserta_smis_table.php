<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLunasToPesertaSmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->boolean('is_lunas')->default(0)->after('cs_name');
        });
    }

    public function down()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->dropColumn('is_lunas');
        });
    }
}
