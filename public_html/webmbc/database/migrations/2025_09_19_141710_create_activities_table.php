<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
    $table->foreignId('categories_id')->constrained('categories')->cascadeOnDelete();
        $table->string('nama');
        $table->decimal('target_daily', 15, 2)->nullable();   // null kalau tidak wajib harian
        $table->decimal('target_bulanan', 15, 2)->nullable();
        $table->integer('bobot')->default(0); // persentase bobot
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
        Schema::dropIfExists('activities');
    }
}
