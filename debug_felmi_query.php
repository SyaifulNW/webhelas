<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Activity;
use App\Models\Category;

$user = User::where('name', 'Felmi')->first();
if (!$user) {
    echo "Felmi not found\n";
    exit;
}

$userRole = strtolower($user->role);
$activityRole = ($userRole === 'marketing') ? 'marketing' : 'cs';
$userName = trim($user->name);

echo "User: [{$userName}] | Role: [{$userRole}] | Activity Role: [{$activityRole}]\n";

$categoryNames = ['DAILY INTAKE', 'WEEKLY INTAKE', 'MONTHLY INTAKE'];

echo "\nCategories Search:\n";
foreach ($categoryNames as $name) {
    $found = Category::where('nama', 'LIKE', '%' . trim($name) . '%')->get();
    echo "Search '%" . trim($name) . "%' found " . $found->count() . " matches:\n";
    foreach ($found as $f) {
        echo "  - ID: {$f->id} | Name: [{$f->nama}]\n";
    }
}

$query = Activity::where('role', $activityRole);
$query->whereHas('kategori', function($q) use ($categoryNames) {
    $q->where(function($sub) use ($categoryNames) {
        foreach ($categoryNames as $name) {
            $sub->orWhere('nama', 'LIKE', '%' . trim($name) . '%');
        }
    });
});

$activities = $query->with('kategori')->get();
echo "\nFinal Activities Count: " . $activities->count() . "\n";
foreach ($activities as $act) {
    echo "ID: {$act->id} | Name: [{$act->nama}] | Cat: [{$act->kategori->nama}]\n";
}
