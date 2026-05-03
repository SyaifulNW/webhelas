<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            for ($i = 6; $i <= 10; $i++) {
                $table->string("fu{$i}")->nullable();
                $table->boolean("fu{$i}_wa")->default(0);
                $table->boolean("fu{$i}_telp")->default(0);
                $table->timestamp("fu{$i}_at")->nullable();
                $table->text("fu{$i}_hasil")->nullable();
                $table->text("fu{$i}_tindak_lanjut")->nullable();
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
            for ($i = 6; $i <= 10; $i++) {
                $table->dropColumn(["fu{$i}", "fu{$i}_wa", "fu{$i}_telp", "fu{$i}_at", "fu{$i}_hasil", "fu{$i}_tindak_lanjut"]);
            }
        });
    }
};
