<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumniTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_id')->nullable();
            $table->string('nama'); // Name of the alumni
            $table->enum('leads', ['Iklan', 'Instagram', 'Facebook', 'Tiktok', 'Lain-Lain'])->default('Iklan');
            $table->string('leads_custom')->nullable(); // Custom field for leads
            $table->unsignedBigInteger('provinsi_id'); // Foreign key for province
            $table->string('provinsi_nama'); // Name of the province
            $table->unsignedBigInteger('kota_id'); // Foreign key for city
            $table->string('kota_nama'); // Name of the city
            $table->string('jenis_bisnis');
            $table->string('nama_bisnis');
            $table->string('no_wa');
            $table->string('kendala')->nullable();
            $table->boolean('ikut_kelas')->default(false); // Indicates if the alumni participated in a class
            $table->unsignedBigInteger('kelas_id')->nullable(); // Foreign key for class
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('set null');
            // Additional fields for alumni
            $table->json('sudah_pernah_ikut_kelas_apa_saja')->nullable();
            $table->json('kelas_yang_belum_diikuti_apa_saja')->nullable();
            $table->timestamps();
            $table->foreign('data_id')->references('id')->on('data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alumni');
    }
}
