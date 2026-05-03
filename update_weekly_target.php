<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Activity;
use App\Models\Category;

$weeklyCat = Category::where('nama', 'WEEKLY INTAKE')->first();

if ($weeklyCat) {
    // Update target_bulanan to 4 for all weekly activities
    Activity::where('categories_id', $weeklyCat->id)
            ->update(['target_bulanan' => 4]);
    
    echo "Updated Weekly Intake target_bulanan to 4.\n";
} else {
    echo "Category WEEKLY INTAKE not found.\n";
}
