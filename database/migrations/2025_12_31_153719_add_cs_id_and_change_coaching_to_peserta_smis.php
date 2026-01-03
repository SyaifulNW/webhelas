<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCsIdAndChangeCoachingToPesertaSmis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->string('cs_name')->nullable()->after('biaya_pendaftaran'); 
            $table->unsignedBigInteger('closing_cs_id')->nullable()->after('biaya_pendaftaran');
        });
        
        // Use raw statement to avoid doctrine/dbal dependency
        DB::statement('ALTER TABLE peserta_smis MODIFY one_on_one_coaching DATETIME NULL');
    }

    public function down()
    {
        Schema::table('peserta_smis', function (Blueprint $table) {
            $table->dropColumn('cs_name');
            $table->dropColumn('closing_cs_id');
        });
        
        DB::statement('ALTER TABLE peserta_smis MODIFY one_on_one_coaching TEXT NULL');
    }
}
