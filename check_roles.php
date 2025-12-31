<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::whereIn('name', ['Felmi', 'Eko Sulis', 'Arifa', 'Nisa'])->get();
foreach ($users as $u) {
    echo $u->name . ": " . $u->role . "\n";
}
