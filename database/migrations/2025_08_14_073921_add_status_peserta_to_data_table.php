<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusPesertaToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
             $table->enum('status_peserta', ['alumni', 'peserta_baru'])
                  ->default('peserta_baru')
                  ->after('id'); // ubah 'id' sesuai kolom yang ingin didahului
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
                 $table->dropColumn('status_peserta');
        });
    }
}
