<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SalesPlan;

$ids = [972, 973, 978, 1301, 1397, 1445, 1541];
foreach($ids as $id) {
    $sp = SalesPlan::with('kelas')->find($id);
    if ($sp) {
        $kName = $sp->kelas->nama_kelas ?? 'N/A';
        $kId = $sp->kelas_id ?? 'N/A';
        echo "SP ID:{$id} | Kelas:{$kName} (ID:{$kId})" . PHP_EOL;
    } else {
        echo "SP ID:{$id} | NOT FOUND" . PHP_EOL;
    }
}
