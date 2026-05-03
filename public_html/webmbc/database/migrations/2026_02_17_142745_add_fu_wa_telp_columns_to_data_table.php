<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuWaTelpColumnsToDataTable extends Migration
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
                $table->boolean("fu{$i}_wa")->default(false)->after("fu{$i}");
                $table->boolean("fu{$i}_telp")->default(false)->after("fu{$i}_wa");
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
                $table->dropColumn(["fu{$i}_wa", "fu{$i}_telp"]);
            }
        });
    }
}
