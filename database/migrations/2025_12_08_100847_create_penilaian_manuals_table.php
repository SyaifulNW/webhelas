<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenilaianManualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penilaian_manuals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id'); // User CS yang dinilai
            $table->integer('bulan');
            $table->integer('tahun');
            
            // Aspek Penilaian (Skala 0-100 atau sesuai kebutuhan)
            $table->integer('kerajinan')->default(0);
            $table->integer('kerjasama')->default(0);
            $table->integer('tanggung_jawab')->default(0);
            $table->integer('inisiatif')->default(0);
            $table->integer('komunikasi')->default(0);

            // Total Nilai (rata-rata atau sum)
            $table->integer('total_nilai')->default(0);
            
            $table->text('catatan')->nullable();

            // Atasan yang menilai
            $table->unsignedBigInteger('created_by')->nullable();
            
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
        Schema::dropIfExists('penilaian_manuals');
    }
}
