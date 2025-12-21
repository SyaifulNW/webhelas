<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixSalesplansSchemaFinal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Fix ID Auto Increment
        try {
            DB::statement("ALTER TABLE salesplans MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        } catch (\Exception $e) {
            // Should fail if PK exists, try just Auto Increment
             try {
                DB::statement("ALTER TABLE salesplans MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
             } catch (\Exception $ex) {
                 // ignore
             }
        }

        // 2. Fix alumni_id nullability (as it was likely intended to be nullable for non-alumni leads)
        try {
             DB::statement("ALTER TABLE salesplans MODIFY alumni_id BIGINT UNSIGNED NULL");
        } catch (\Exception $e) {}
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
