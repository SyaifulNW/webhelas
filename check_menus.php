<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== MENU LIST ===\n";
$menus = DB::table('menus')->get();
echo "Total menus: " . $menus->count() . "\n\n";

foreach ($menus as $menu) {
    $status = $menu->is_active ? '✓ Active' : '✗ Inactive';
    echo sprintf("%-30s | %-40s | %s\n", $menu->name, $menu->label, $status);
}

echo "\n=== SETTINGS ===\n";
$settings = DB::table('settings')->get();
foreach ($settings as $setting) {
    echo sprintf("%-20s : %s\n", $setting->key, $setting->value);
}
