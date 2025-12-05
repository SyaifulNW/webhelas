<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyActivitisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_activitis', function (Blueprint $table) {
            $table->id();
           $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
        $table->date('tanggal');
        $table->decimal('realisasi', 15, 2)->default(0);
        $table->timestamps();

        $table->unique(['user_id','activity_id','tanggal']); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_activitis');
    }
}
