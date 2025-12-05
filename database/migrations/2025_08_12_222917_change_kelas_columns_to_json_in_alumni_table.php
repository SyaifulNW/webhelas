<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeKelasColumnsToJsonInAlumniTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alumni', function (Blueprint $table) {
             $table->json('sudah_pernah_ikut_kelas_apa_saja')->nullable()->change();
            $table->json('kelas_yang_belum_diikuti_apa_saja')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alumni', function (Blueprint $table) {
             $table->text('sudah_pernah_ikut_kelas_apa_saja')->nullable()->change();
            $table->text('kelas_yang_belum_diikuti_apa_saja')->nullable()->change();
        });
    }
}
