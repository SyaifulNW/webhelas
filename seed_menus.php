<?php
use App\Models\Menu;
use App\Models\RoleMenu;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$menus = [
    ['name' => 'dashboard_admin', 'label' => 'DASHBOARD ADMIN'],
    ['name' => 'penjualan', 'label' => 'Penjualan'],
    ['name' => 'hr', 'label' => 'HRD'],
    ['name' => 'marketing', 'label' => 'Marketing'],
    ['name' => 'operasional', 'label' => 'Operasional'],
    ['name' => 'keuangan', 'label' => 'Keuangan'],
    
    ['name' => 'dashboard_marketing', 'label' => 'DASHBOARD MARKETING'],
    ['name' => 'dashboard_manager', 'label' => 'DASHBOARD MANAGER'],
    ['name' => 'dashboard_hr', 'label' => 'DASHBOARD HR'],
    ['name' => 'dashboard_advertising', 'label' => 'DASHBOARD ADVERTISING'],
    ['name' => 'dashboard_general', 'label' => 'DASHBOARD (General)'],
    
    ['name' => 'program_kerja', 'label' => 'Program Kerja'],
    ['name' => 'ganchart', 'label' => 'Ganchart'],
    
    ['name' => 'data_lead', 'label' => 'Data Lead / Prospek'],
    ['name' => 'penilaian_kinerja_saya', 'label' => 'Penilaian Kinerja Saya'],
    
    ['name' => 'data_calon_peserta', 'label' => 'DATA CALON PESERTA'],
    ['name' => 'daily_activity', 'label' => 'DAILY ACTIVITY'],
    
    ['name' => 'sales_plan', 'label' => 'SALES PLAN'],
    ['name' => 'data_peserta_smi', 'label' => 'Data Peserta SMI'],
    
    ['name' => 'database_cs', 'label' => 'DATABASE CS'],
    ['name' => 'jadwal_kelas', 'label' => 'JADWAL KELAS'],
    ['name' => 'activity_cs', 'label' => 'ACTIVITY CS'],
    ['name' => 'penilaian_karyawan', 'label' => 'PENILAIAN KARYAWAN'],
    ['name' => 'settings', 'label' => 'SETTING'],
];

echo "Seeding Menus...\n";
foreach ($menus as $m) {
    Menu::firstOrCreate(
        ['name' => $m['name']],
        ['label' => $m['label'], 'is_active' => true]
    );
}

echo "Seeding RoleMenus...\n";
$roles = User::distinct('role')->pluck('role')->filter()->map(function($r){ return strtolower(trim($r)); })->unique();
$allMenus = Menu::all();

foreach ($roles as $role) {
    foreach ($allMenus as $menu) {
        RoleMenu::firstOrCreate(
            ['role' => $role, 'menu_id' => $menu->id],
            ['can_access' => true]
        );
    }
}

echo "Done.\n";
