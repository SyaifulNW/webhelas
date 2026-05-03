<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to fix 'id' column in 'data' table...\n";
    
    // Check if Primary Key exists
    $pk = Illuminate\Support\Facades\DB::select("SHOW KEYS FROM data WHERE Key_name = 'PRIMARY'");
    if (count($pk) > 0) {
        echo "Primary Key already exists. Checking Extra...\n";
        // Just modify to auto_increment
        Illuminate\Support\Facades\DB::statement('ALTER TABLE data MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
    } else {
        echo "No Primary Key found. Setting id as PRIMARY KEY and AUTO_INCREMENT...\n";
        Illuminate\Support\Facades\DB::statement('ALTER TABLE data MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
    }
    
    echo "Database fixed successfully.\n";
    
    // Verify
    $results = Illuminate\Support\Facades\DB::select('DESCRIBE data');
    foreach ($results as $row) {
        if ($row->Field === 'id') {
            echo "Field: " . $row->Field . ", Extra: " . $row->Extra . ", Key: " . $row->Key . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
