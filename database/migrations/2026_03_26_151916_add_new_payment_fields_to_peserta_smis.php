<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewPaymentFieldsToPesertaSmis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->decimal('pembayaran_spp', 15, 0)->nullable()->after('spp_awal');
            $table->decimal('total_pembayaran', 15, 0)->nullable()->after('pembayaran_spp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            //
        });
    }
}
