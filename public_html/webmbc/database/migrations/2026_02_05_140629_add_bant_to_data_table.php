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
            $table->boolean('bant_budget')->default(false)->after('ikut_zoom');
            $table->boolean('bant_authority')->default(false)->after('bant_budget');
            $table->boolean('bant_time')->default(false)->after('bant_authority');
        });
    }

    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropColumn(['bant_budget', 'bant_authority', 'bant_time']);
        });
    }
}
