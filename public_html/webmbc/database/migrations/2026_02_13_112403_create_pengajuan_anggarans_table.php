<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengajuanAnggaransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengajuan_anggarans', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal_pengajuan');
            $table->string('nama_pengajuan');
            $table->decimal('jumlah_biaya', 15, 2);
            $table->unsignedBigInteger('user_id');
            $table->string('diajukan_oleh');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengajuan_anggarans');
    }
}
