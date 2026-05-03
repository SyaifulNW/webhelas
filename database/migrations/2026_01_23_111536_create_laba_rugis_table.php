<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabaRugisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('laba_rugis', function (Blueprint $table) {
            $table->id();
            $table->string('bulan');
            $table->integer('tahun');
            $table->enum('type', ['pendapatan', 'biaya']);
            $table->string('keterangan');
            $table->decimal('jumlah', 15, 2);
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
        Schema::dropIfExists('laba_rugis');
    }
}
