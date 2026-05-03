<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Activity;
use App\Models\Category;

$felmi = User::where('name', 'Felmi')->first();
if ($felmi) {
    echo "User Felmi found:\n";
    echo "ID: {$felmi->id} | Name: [{$felmi->name}] | Role: [{$felmi->role}]\n";
} else {
    echo "User Felmi NOT found.\n";
    echo "All users:\n";
    foreach (User::all() as $u) {
        echo "ID: {$u->id} | Name: '{$u->name}'\n";
    }
}

echo "\nChecking Categories for Felmi names...\n";
$targetCats = ['DAILY INTAKE', 'WEEKLY INTAKE', 'MONTHLY INTAKE'];
foreach ($targetCats as $catName) {
    $cat = Category::where('nama', 'LIKE', '%' . trim($catName) . '%')->first();
    if ($cat) {
        echo "Category '{$catName}' found as ID: {$cat->id} | Name: '{$cat->nama}'\n";
        $activities = Activity::where('categories_id', $cat->id)->get();
        echo "  Activities count: " . $activities->count() . "\n";
        foreach ($activities as $act) {
            echo "    - ID: {$act->id} | Name: '{$act->nama}' | Role: '{$act->role}'\n";
        }
    } else {
        echo "Category '{$catName}' NOT found.\n";
    }
}
