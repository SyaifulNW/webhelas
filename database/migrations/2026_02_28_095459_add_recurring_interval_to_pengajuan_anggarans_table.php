<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecurringIntervalToPengajuanAnggaransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengajuan_anggarans', function (Blueprint $table) {
            $table->string('recurring_interval')->nullable()->after('is_recurring');
        });

        // Migrate existing recurring items to 'monthly'
        DB::table('pengajuan_anggarans')
            ->where('is_recurring', true)
            ->update(['recurring_interval' => 'monthly']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengajuan_anggarans', function (Blueprint $table) {
            $table->dropColumn('recurring_interval');
        });
    }
}
