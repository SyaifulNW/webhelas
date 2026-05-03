<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiSosmedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_sosmeds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('bulan');
            $table->integer('tahun');
            
            // Indikator
            $table->decimal('followers_real', 10, 2)->default(0);
            $table->decimal('followers_skor', 10, 2)->default(0);
            
            $table->string('respons_dm_real')->nullable(); // String for flexible input
            $table->decimal('respons_dm_skor', 10, 2)->default(0);
            
            $table->integer('dm_masuk_real')->default(0);
            $table->decimal('dm_masuk_skor', 10, 2)->default(0);
            
            $table->integer('link_wa_real')->default(0);
            $table->decimal('link_wa_skor', 10, 2)->default(0);
            
            $table->integer('zoom_real')->default(0);
            $table->decimal('zoom_skor', 10, 2)->default(0);
            
            $table->decimal('skor_disiplin', 10, 2)->default(0);
            $table->decimal('nilai_akhir', 10, 2)->default(0);
            
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
        Schema::dropIfExists('kpi_sosmeds');
    }
}
