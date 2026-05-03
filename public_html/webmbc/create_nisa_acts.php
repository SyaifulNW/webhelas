<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Activity;

// Ensure category exists
$cat = Category::find(11);
if (!$cat) {
    $cat = Category::create(['id' => 11, 'nama' => 'C. Aktivitas Bulanan']);
    echo "Category created.\n";
} else {
    echo "Category exists.\n";
}

$items = [
    ['nama' => 'Reach naik 10% per bulan', 'target' => 10],
    ['nama' => 'Impressions naik 10% per bulan', 'target' => 10],
    ['nama' => 'Enggagement rate 20%', 'target' => 20],
    ['nama' => 'Growth followers 30% per bulan', 'target' => 30],
    ['nama' => 'View video (retention rate) 10% per bulan', 'target' => 10]
];

foreach ($items as $item) {
    Activity::updateOrCreate(
        ['nama' => $item['nama'], 'categories_id' => 11],
        [
            'role' => 'marketing',
            'target_daily' => 0,
            'target_bulanan' => $item['target'],
            'bobot' => 20
        ]
    );
    echo "Created/Updated: " . $item['nama'] . "\n";
}
echo "Done.\n";
