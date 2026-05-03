<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFu678RtlTimestampsToSalesplansTable extends Migration
{
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            for ($i = 6; $i <= 8; $i++) {
                $table->timestamp("fu{$i}_rtl_at")->nullable()->after("fu{$i}_at");
            }
        });
    }

    public function down()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            for ($i = 6; $i <= 8; $i++) {
                $table->dropColumn("fu{$i}_rtl_at");
            }
        });
    }
}
