<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentKeteranganToLabaRugisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('laba_rugis', function (Blueprint $table) {
            $table->string('parent_keterangan')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('laba_rugis', function (Blueprint $table) {
            $table->dropColumn('parent_keterangan');
        });
    }
}
