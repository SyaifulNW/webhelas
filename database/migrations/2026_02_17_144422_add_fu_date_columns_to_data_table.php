<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuDateColumnsToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            for ($i = 1; $i <= 5; $i++) {
                $table->timestamp("fu{$i}_at")->nullable()->after("fu{$i}_telp");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
            for ($i = 1; $i <= 5; $i++) {
                $table->dropColumn("fu{$i}_at");
            }
        });
    }
}
