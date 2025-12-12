<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Daftar tabel yang sudah ada
$existingTables = [
    'data',
    'alumni', 
    'salesplans',
    'daily_activities',
    'daily_activityitems',
    'categories',
    'activities',
    'daily_activitis',
    'notifikasis',
    'chat_messages',
    'program_kerjas',
    'penilaian_manuals'
];

$batch = DB::table('migrations')->max('batch') + 1;

foreach ($existingTables as $table) {
    if (Schema::hasTable($table)) {
        echo "✓ Table '$table' exists\n";
        
        // Cari migration file yang sesuai
        $migrationFiles = glob(__DIR__ . '/database/migrations/*_create_' . $table . '_table.php');
        
        if (!empty($migrationFiles)) {
            $migrationFile = basename($migrationFiles[0], '.php');
            
            // Check if already in migrations table
            $exists = DB::table('migrations')->where('migration', $migrationFile)->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migrationFile,
                    'batch' => $batch
                ]);
                echo "  → Added to migrations table: $migrationFile\n";
            } else {
                echo "  → Already in migrations table\n";
            }
        }
    } else {
        echo "✗ Table '$table' does not exist\n";
    }
}

echo "\nDone!\n";
