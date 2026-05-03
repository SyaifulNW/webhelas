<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Activity;
use App\Models\Category;

$nisa = User::where('name', 'Nisa')->first();
if ($nisa) {
    echo "User Nisa found:\n";
    echo "ID: {$nisa->id} | Name: {$nisa->name} | Role: {$nisa->role}\n";
} else {
    echo "User Nisa NOT found.\n";
}

$nisa = User::where('name', 'Nisa')->first();
echo "Nisa Role: " . ($nisa->role ?? 'NULL') . "\n";

$cat = Category::find(11);
echo "Category 11: " . ($cat->nama ?? 'NOT FOUND') . "\n";

$acts = Activity::where('categories_id', 11)->get();
echo "Activities count: " . $acts->count() . "\n";
foreach($acts as $a) {
    echo "- " . $a->nama . " (Role: " . $a->role . ")\n";
}




echo "\nChecking Categories for Nisa...\n";
$targetCats = ['A. Aktivitas Harian (NON-NEGOTIABLE)', 'B. Aktivitas Mingguan'];
foreach ($targetCats as $catName) {
    $cat = Category::where('nama', 'LIKE', '%' . trim($catName) . '%')->first();
    if ($cat) {
        echo "Category '{$catName}' found as ID: {$cat->id} | Name: '{$cat->nama}'\n";
        $activities = Activity::where('categories_id', $cat->id)->get();
        echo "  Activities count: " . $activities->count() . "\n";
        foreach ($activities as $act) {
            echo "    - ID: {$act->id} | Name: {$act->nama} | Role: {$act->role}\n";
        }
    } else {
        echo "Category '{$catName}' NOT found.\n";
    }
}
