<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Activity;
use App\Models\Category;

// 1. Delete Activities
$toDelete = [
    'Dokumentasi progres event',
    'Evaluasi event minggu lalu'
];

Activity::whereIn('nama', $toDelete)->delete();
echo "Deleted activities.\n";

// 2. Recalculate Weights

// DAILY INTAKE (Cat ID 8 likely, check DB or previous script output)
// Remaining: 'Update database leads', 'Update peserta EF', 'Update peserta uprev/visit' (3 items)
// Target weight total: 100% (or distributed?)
// Previous weight was 25% each (total 100 for 4 items). Now 3 items.
// 100 / 3 = 33.33
$dailyCat = Category::where('nama', 'DAILY INTAKE')->first();
if ($dailyCat) {
    $acts = Activity::where('categories_id', $dailyCat->id)->get();
    $count = $acts->count();
    if ($count > 0) {
        $base = floor(100 / $count);
        $rem = 100 % $count;
        foreach ($acts as $i => $a) {
            $w = $base + ($i < $rem ? 1 : 0);
            $a->update(['bobot' => $w]);
        }
    }
}

// WEEKLY INTAKE
// Remaining: 'Rekap leads mingguan', 'Evaluasi event dan perbaikan' (2 items)
// Target: 100% / 2 = 50%
$weeklyCat = Category::where('nama', 'WEEKLY INTAKE')->first();
if ($weeklyCat) {
    $acts = Activity::where('categories_id', $weeklyCat->id)->get();
    $count = $acts->count();
    if ($count > 0) {
        $base = floor(100 / $count);
        $rem = 100 % $count;
        foreach ($acts as $i => $a) {
            $w = $base + ($i < $rem ? 1 : 0);
            $a->update(['bobot' => $w]);
        }
    }
}

echo "Weights updated.\n";
