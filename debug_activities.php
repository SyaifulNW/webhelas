<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Activity;

echo "Checking Activities...\n";
$activities = Activity::all();

foreach ($activities as $act) {
    echo "ID: {$act->id} | Name: {$act->nama} | Category ID: {$act->categories_id} | Role: {$act->role}\n";
}

echo "\nCategories:\n";
$cats = \Illuminate\Support\Facades\DB::table('categories')->get();
foreach ($cats as $c) {
    echo "ID: {$c->id} | Name: {$c->nama}\n";
}
