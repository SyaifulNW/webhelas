<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesertaSmisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('peserta_smis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('one_on_one_coaching')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('biaya_pendaftaran', 15, 0)->nullable();
            $table->boolean('spp_1')->default(0);
            $table->boolean('spp_2')->default(0);
            $table->boolean('spp_3')->default(0);
            $table->boolean('spp_4')->default(0);
            $table->boolean('spp_5')->default(0);
            $table->boolean('spp_6')->default(0);
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
        Schema::dropIfExists('peserta_smis');
    }
}
