<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualNameToAdsPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ads_performances', function (Blueprint $table) {
            $table->string('manual_name')->nullable()->after('kelas_id');
        });
    }

    public function down()
    {
        Schema::table('ads_performances', function (Blueprint $table) {
            $table->dropColumn('manual_name');
        });
    }
}
