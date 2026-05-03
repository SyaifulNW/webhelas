<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSelectedMonthsToSalesplansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            $table->json('selected_months')->nullable()->after('fu_history');
        });
    }

    public function down()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            $table->dropColumn('selected_months');
        });
    }
}
