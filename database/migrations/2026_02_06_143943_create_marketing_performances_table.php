<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('event_name');
            $table->date('tanggal')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('jenis_event')->nullable();
            $table->integer('target_peserta')->default(0);
            $table->integer('peserta_hadir')->default(0);
            $table->integer('target_closing')->default(0);
            $table->integer('real_closing')->default(0);
            $table->integer('selisih')->default(0);
            $table->string('status')->default('Terlaksana'); // Terlaksana, Ditunda, Dibatalkan
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
        Schema::dropIfExists('marketing_performances');
    }
}
