<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKebutuhanToSalesplansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesplans', function (Blueprint $table) {
            $table->text('kebutuhan')->nullable()->after('nominal'); // Adjust position if needed, or just append
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
            $table->dropColumn('kebutuhan');
        });
    }
}
