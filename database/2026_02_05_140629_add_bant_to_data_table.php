<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBantToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->boolean('bant')->default(false)->after('ikut_zoom');
        });
    }

    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropColumn('bant');
        });
    }
}
