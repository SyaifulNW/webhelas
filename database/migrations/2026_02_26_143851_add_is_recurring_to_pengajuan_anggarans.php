<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRecurringToPengajuanAnggarans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengajuan_anggarans', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengajuan_anggarans', function (Blueprint $table) {
            $table->dropColumn('is_recurring');
        });
    }
}
