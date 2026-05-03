<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFieldsToAdsPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ads_performances', function (Blueprint $table) {
            $table->decimal('budget_iklan', 15, 2)->nullable()->after('cpa');
            $table->integer('total_leads')->nullable()->after('budget_iklan');
            $table->integer('jumlah_closing')->nullable()->after('total_leads');
            $table->decimal('omset', 15, 2)->nullable()->after('jumlah_closing');
            $table->decimal('roas', 15, 2)->nullable()->after('omset');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ads_performances', function (Blueprint $table) {
            $table->dropColumn(['budget_iklan', 'total_leads', 'jumlah_closing', 'omset', 'roas']);
        });
    }
}
