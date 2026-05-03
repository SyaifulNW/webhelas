<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFuDetailColumnsToDataTable extends Migration
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
                $table->text("fu{$i}_hasil")->nullable()->after("fu{$i}");
                $table->text("fu{$i}_tindak_lanjut")->nullable()->after("fu{$i}_hasil");
            }
        });

        // Migrate existing data from fuX to fuX_hasil
        for ($i = 1; $i <= 5; $i++) {
            DB::table('data')->update([
                "fu{$i}_hasil" => DB::raw("fu{$i}")
            ]);
        }
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
                $table->dropColumn(["fu{$i}_hasil", "fu{$i}_tindak_lanjut"]);
            }
        });
    }
}
