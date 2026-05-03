<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPengajuanBudgetToAdsPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ads_performances', function (Blueprint $table) {
            $table->decimal('pengajuan_budget', 15, 2)->nullable()->after('budget_iklan');
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
            $table->dropColumn('pengajuan_budget');
        });
    }
}
