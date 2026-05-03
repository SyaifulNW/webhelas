<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketing_participants', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('no_wa')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->string('provinsi_nama')->nullable();
            $table->unsignedBigInteger('kota_id')->nullable();
            $table->string('kota_nama')->nullable();
            $table->string('nama_bisnis')->nullable();
            $table->string('jenis_bisnis')->nullable();
            $table->string('potensi')->nullable(); // MBC, SMI
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_transferred')->default(false); // New column for DB CS transfer tracking
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
        Schema::dropIfExists('marketing_participants');
    }
}
