<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyActivityitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_activityitems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_activity_id')->constrained()->onDelete('cascade');
            $table->string('kategori'); // e.g., 'pribadi', 'mencari_leads'
            $table->string('aktivitas')->nullable();
            $table->string('deskripsi')->nullable();
            $table->integer('target')->nullable();
            $table->integer('real')->nullable();
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
        Schema::dropIfExists('daily_activityitems');
    }
}
