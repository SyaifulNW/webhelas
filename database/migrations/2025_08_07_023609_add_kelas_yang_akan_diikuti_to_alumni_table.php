<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKelasYangAkanDiikutiToAlumniTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->string('kelas_yang_akan_diikuti')->nullable()->after('nama'); // atau sesuaikan dengan kolom yang relevan
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
            Schema::table('alumni', function (Blueprint $table) {
                $table->dropColumn('kelas_yang_akan_diikuti');
            });
        });
    }
}
