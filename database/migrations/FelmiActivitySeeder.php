<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Activity;

class FelmiActivitySeeder extends Seeder
{
    public function run()
    {
        // 1. DAILY INTAKE
        $daily = Category::create(['nama' => 'DAILY INTAKE']);
        
        $dailyActivities = [
            ['nama' => 'Update database leads', 'standar' => 'Setiap hari', 'bobot' => 34],
            ['nama' => 'Update peserta EF', 'standar' => 'Min 50/bln', 'bobot' => 33],
            ['nama' => 'Update peserta uprev/visit', 'standar' => 'Min 50/bln', 'bobot' => 33],
        ];

        foreach ($dailyActivities as $act) {
            Activity::create([
                'categories_id' => $daily->id,
                'nama' => $act['nama'],
                'standar' => $act['standar'],
                'target_daily' => 1, 
                'target_bulanan' => 26,
                'bobot' => $act['bobot'],
                'role' => 'marketing'
            ]);
        }

        // 2. WEEKLY INTAKE
        $weekly = Category::create(['nama' => 'WEEKLY INTAKE']);
        
        $weeklyActivities = [
            ['nama' => 'Rekap leads mingguan', 'standar' => 'Lengkap', 'bobot' => 50],
            ['nama' => 'Evaluasi event dan perbaikan', 'standar' => 'Rutin', 'bobot' => 50],
        ];

        foreach ($weeklyActivities as $act) {
            Activity::create([
                'categories_id' => $weekly->id,
                'nama' => $act['nama'],
                'standar' => $act['standar'],
                'target_daily' => null,
                'target_bulanan' => 1,
                'bobot' => $act['bobot'],
                'role' => 'marketing'
            ]);
        }

        // 3. MONTHLY INTAKE
        $monthly = Category::create(['nama' => 'MONTHLY INTAKE']);
        
        $monthlyActivities = [
            ['nama' => 'Kalender event bulan depan', 'standar' => 'Selesai sebelum bulan berjalan', 'bobot' => 20],
            ['nama' => 'Laporan performa semua event', 'standar' => 'Lengkap', 'bobot' => 25],
            ['nama' => 'Analisa CPL & ROI event', 'standar' => 'Ada data', 'bobot' => 15],
            ['nama' => 'Evaluasi kualitas leads dengan sales', 'standar' => 'Ada hasil', 'bobot' => 20],
            ['nama' => 'Ide konsep event baru', 'standar' => 'Min. 1 ide', 'bobot' => 20],
        ];

        foreach ($monthlyActivities as $act) {
            Activity::create([
                'categories_id' => $monthly->id,
                'nama' => $act['nama'],
                'standar' => $act['standar'],
                'target_daily' => null,
                'target_bulanan' => 1,
                'bobot' => $act['bobot'],
                'role' => 'marketing'
            ]);
        }
    }
}
