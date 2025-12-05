<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesplansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesplans', function (Blueprint $table) {
            $table->id();
        
            // Relasi ke tabel data
            $table->unsignedBigInteger('alumni_id'); // jika relasi ke alumni
            $table->unsignedBigInteger('data_id')->nullable();
            $table->foreign('data_id')->references('id')->on('data')->onDelete('cascade');

             // FU 1 s/d FU 8, masing-masing ada hasil dan tindak lanjut
              for ($i = 1; $i <= 8; $i++) {
                $table->text("fu{$i}_hasil")->nullable();         // a. Apa hasil follow up?
                $table->text("fu{$i}_tindak_lanjut")->nullable(); // b. Apa rencana tindak lanjut?
            }
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->enum('status', ['cold', 'warm','hot','no' ])->default('cold'); // Status penjualan

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
        Schema::dropIfExists('salesplans');
    }
}
