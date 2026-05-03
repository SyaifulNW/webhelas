<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuDoneToSalesplans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            for ($i = 1; $i <= 12; $i++) {
                $table->boolean("fu{$i}_done")->default(false)->after("fu{$i}_rtl_at");
            }
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
            for ($i = 1; $i <= 12; $i++) {
                $table->dropColumn("fu{$i}_done");
            }
        });
    }
}
