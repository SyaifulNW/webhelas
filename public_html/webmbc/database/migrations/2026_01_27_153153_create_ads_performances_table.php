<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdsPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->integer('bulan');
            $table->integer('tahun');
            $table->date('tanggal_kelas')->nullable();
            $table->date('tanggal_set')->nullable();
            $table->decimal('ctr', 15, 2)->nullable();
            $table->decimal('cpl', 15, 2)->nullable();
            $table->decimal('conv_rate', 15, 2)->nullable();
            $table->decimal('cpa', 15, 2)->nullable();
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
        Schema::dropIfExists('ads_performances');
    }
}
