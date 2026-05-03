<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data', function (Blueprint $table) {
            $table->id();
             $table->string('nama');
            $table->enum('leads', ['Iklan', 'Marketing', 'Alumni', 'Lain-lain'])->default('Iklan');
            $table->string('leads_custom')->nullable();
            //  Tambahkan field untuk wilayah:
            $table->string('provinsi_id');
            $table->string('provinsi_nama');
            $table->string('kota_id');
            $table->string('kota_nama');
            $table->string('jenis_bisnis');
            $table->string('nama_bisnis');
            $table->string('no_wa');
            $table->string('situasi_bisnis')->nullable();
            $table->string('kendala')->nullable();
            $table->boolean('ikut_kelas')->default(false); 
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data');
    }
}
