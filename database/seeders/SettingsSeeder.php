<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Settings
        \App\Models\Setting::updateOrCreate(
            ['key' => 'target_omset'],
            ['value' => '50000000']
        );

        // 2. Menus
        $menus = [
            // Administrator Menus
            ['name' => 'dashboard_admin', 'label' => 'DASHBOARD ADMIN'],
            ['name' => 'database_cs', 'label' => 'DATABASE CS'],
            ['name' => 'jadwal_kelas', 'label' => 'JADWAL KELAS'],
            ['name' => 'activity_cs', 'label' => 'ACTIVITY CS'],
            ['name' => 'penilaian_karyawan', 'label' => 'PENILAIAN KARYAWAN'],
            ['name' => 'penjualan', 'label' => 'PENJUALAN'],
            
            // Common Menus
            ['name' => 'dashboard_marketing', 'label' => 'DASHBOARD MARKETING'],
            ['name' => 'dashboard_manager', 'label' => 'DASHBOARD MANAGER'],
            ['name' => 'dashboard_hr', 'label' => 'DASHBOARD HR'],
            ['name' => 'dashboard_general', 'label' => 'DASHBOARD (General)'],

            ['name' => 'program_kerja', 'label' => 'Program Kerja'],
            ['name' => 'ganchart', 'label' => 'Ganchart'],
            ['name' => 'data_lead', 'label' => 'Data Lead / Prospek'],
            
            // CS / Other Roles
            ['name' => 'data_calon_peserta', 'label' => 'DATA CALON PESERTA'],
            ['name' => 'daily_activity', 'label' => 'DAILY ACTIVITY'],
            ['name' => 'penilaian_kinerja_saya', 'label' => 'PENILAIAN KINERJA SAYA'],
            ['name' => 'sales_plan', 'label' => 'SALES PLAN'],
            
            // HRD specific
            ['name' => 'menu_hrd', 'label' => 'MENU HRD (Section)'],
        ];

        foreach ($menus as $m) {
            \App\Models\Menu::updateOrCreate(
                ['name' => $m['name']],
                ['label' => $m['label'], 'is_active' => true]
            );
        }
    }
}
