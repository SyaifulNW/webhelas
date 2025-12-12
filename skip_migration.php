<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$batch = DB::table('migrations')->max('batch') + 1;

DB::table('migrations')->insert([
    'migration' => '2025_08_07_025350_add_data_id_to_alumni_table',
    'batch' => $batch
]);

echo "Migration marked as run\n";
