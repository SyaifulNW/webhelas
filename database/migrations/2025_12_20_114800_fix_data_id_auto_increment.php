<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixDataIdAutoIncrement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable strict mode
        DB::statement("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");

        // 1. Ensure status_peserta is clean (DDL implicit commit, so this might already be done)
        try {
            DB::statement("ALTER TABLE data MODIFY status_peserta VARCHAR(50) NOT NULL DEFAULT 'peserta_baru'");
            DB::statement("UPDATE data SET status_peserta = 'peserta_baru' WHERE status_peserta = '' OR status_peserta IS NULL");
        } catch (\Exception $e) {
            // Ignore
        }

        // 2. De-duplicate IDs
        // Calculate duplicates
        $duplicates = DB::select('SELECT id, COUNT(*) as c FROM data GROUP BY id HAVING c > 1');
        
        foreach ($duplicates as $dup) {
            $id = $dup->id;
            $count = $dup->c;
            
            // We have $count rows with same ID. We need to re-assign ID for ($count - 1) of them.
            // We will loop $count-1 times
            for ($i = 0; $i < ($count - 1); $i++) {
                 $maxId = DB::table('data')->max('id');
                 $newId = $maxId + 1;
                 
                 // Update one of the duplicates to the new ID
                 DB::update("UPDATE data SET id = ? WHERE id = ? LIMIT 1", [$newId, $id]);
            }
        }

        // 3. Fix ID to be AUTO_INCREMENT PRIMARY KEY
        try {
             DB::statement("ALTER TABLE data MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
        } catch (\Exception $e) {
             // If Primary key already exists but not auto_increment, try dropping first?
             // But usually modifying works.
             // If we still get error, we might need manual intervention.
             throw $e;
        }

        // 4. Restore ENUM
        DB::statement("ALTER TABLE data MODIFY status_peserta ENUM('alumni', 'peserta_baru') NOT NULL DEFAULT 'peserta_baru'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE data MODIFY id BIGINT UNSIGNED NOT NULL');
    }
}
