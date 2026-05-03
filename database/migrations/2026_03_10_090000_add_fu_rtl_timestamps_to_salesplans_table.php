<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuRtlTimestampsToSalesplansTable extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom timestamp terpisah untuk RTL (fu1_rtl_at s/d fu5_rtl_at)
     * agar Hasil FU dan RTL bisa punya tanggal masing-masing.
     */
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            for ($i = 1; $i <= 5; $i++) {
                $table->timestamp("fu{$i}_rtl_at")->nullable()->after("fu{$i}_at");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            for ($i = 1; $i <= 5; $i++) {
                $table->dropColumn("fu{$i}_rtl_at");
            }
        });
    }
}
