<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $results = Illuminate\Support\Facades\DB::select('DESCRIBE data');
    echo "Schema of 'data' table:\n";
    foreach ($results as $row) {
        if ($row->Field === 'id') {
            echo "Field: " . $row->Field . "\n";
            echo "Type: " . $row->Type . "\n";
            echo "Null: " . $row->Null . "\n";
            echo "Key: " . $row->Key . "\n";
            echo "Default: " . $row->Default . "\n";
            echo "Extra: " . $row->Extra . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
