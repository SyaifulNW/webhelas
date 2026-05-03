<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixSalesplansIdTruly extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Disable strict mode temporarily
        DB::statement("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");

        // 2. Deduplicate IDs in salesplans just in case
        $duplicates = DB::select('SELECT id, COUNT(*) as c FROM salesplans GROUP BY id HAVING c > 1');
        foreach ($duplicates as $dup) {
            $id = $dup->id;
            $count = $dup->c;
            for ($i = 0; $i < ($count - 1); $i++) {
                 $maxId = DB::table('salesplans')->max('id');
                 $newId = $maxId + 1;
                 DB::update("UPDATE salesplans SET id = ? WHERE id = ? LIMIT 1", [$newId, $id]);
            }
        }

        // 3. Drop Primary Key if exists to ensure clean state (Warning: this might fail if no PK, so we wrap in try)
        // But MySQL doesn't have "DROP PRIMARY KEY IF EXISTS". 
        // We will try to just MODIFY first.
        
        try {
            // Try modifying to Auto Increment. This works if it's already a Key.
            DB::statement("ALTER TABLE salesplans MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        } catch (\Exception $e) {
            // If it failed, maybe it's not a key yet?
            // "Incorrect table definition; there can be only one auto column and it must be defined as a key"
            try {
                DB::statement("ALTER TABLE salesplans MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
            } catch (\Exception $ex) {
                // If this *also* fails, we are in trouble. Rethrow to see error.
                throw $ex;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
