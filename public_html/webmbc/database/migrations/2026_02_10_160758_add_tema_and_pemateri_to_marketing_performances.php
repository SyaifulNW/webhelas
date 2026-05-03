<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemaAndPemateriToMarketingPerformances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_performances', function (Blueprint $table) {
            $table->string('tema')->nullable()->after('event_name');
            $table->string('pemateri')->nullable()->after('tema');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_performances', function (Blueprint $table) {
            $table->dropColumn(['tema', 'pemateri']);
        });
    }
}
