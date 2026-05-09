<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PesertaSmi;

$peserta = PesertaSmi::select('id', 'nama', 'tanggal_masuk', 'tanggal_selesai', 'status')->get();
foreach ($peserta as $p) {
    echo "ID:{$p->id} | Name:{$p->nama} | Masuk:{$p->tanggal_masuk} | Selesai:{$p->tanggal_selesai} | Status:{$p->status}" . PHP_EOL;
}
