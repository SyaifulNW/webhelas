<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBiayaDisetujuiToPengajuanAnggaransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengajuan_anggarans', function (Blueprint $table) {
            $table->decimal('biaya_disetujui', 15, 2)->default(0)->after('jumlah_biaya');
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
            $table->dropColumn('biaya_disetujui');
        });
    }
}
