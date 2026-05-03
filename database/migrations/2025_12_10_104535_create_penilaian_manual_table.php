<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenilaianManualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penilaian_manual', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('bulan'); // Bulan penilaian (format: YYYY-MM-01)
            $table->decimal('nilai', 5, 2)->default(0); // Nilai penilaian (0-100)
            $table->text('catatan')->nullable(); // Catatan dari atasan
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index untuk pencarian cepat
            $table->index(['user_id', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penilaian_manual');
    }
}
