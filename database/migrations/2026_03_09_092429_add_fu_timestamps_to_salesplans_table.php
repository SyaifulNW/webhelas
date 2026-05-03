<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuTimestampsToSalesplansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
             for ($i = 1; $i <= 8; $i++) {
                $table->timestamp("fu{$i}_at")->nullable()->after("fu{$i}_tindak_lanjut");
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
             for ($i = 1; $i <= 8; $i++) {
                $table->dropColumn("fu{$i}_at");
            }
        });
    }
}
