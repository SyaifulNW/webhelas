<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToSalesplansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
           Schema::table('salesplans', function (Blueprint $table) {
        $table->softDeletes(); // Ini akan menambahkan kolom 'deleted_at'
    });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salesplans', function (Blueprint $table) {
             Schema::table('salesplans', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
        });
    }
}
