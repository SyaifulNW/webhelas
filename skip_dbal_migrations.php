<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$batch = DB::table('migrations')->max('batch') + 1;

// Skip migrasi yang memerlukan doctrine/dbal
$migrationsToSkip = [
    '2025_08_11_073533_change_created_by_column_type_in_data_table',
    '2025_08_12_222917_change_kelas_columns_to_json_in_alumni_table'
];

foreach ($migrationsToSkip as $migration) {
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "✓ Marked as run: $migration\n";
    } else {
        echo "→ Already exists: $migration\n";
    }
}

echo "\nDone!\n";
