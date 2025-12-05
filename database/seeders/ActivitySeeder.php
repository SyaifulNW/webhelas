<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        // ========================
        // 1. Aktivitas Pribadi
        // ========================
        $pribadi = Category::create(['nama' => 'Aktivitas Pribadi']);

        Activity::create([
            'categories_id' => $pribadi->id,
            'nama' => 'Niat & Doa Pagi',
            'target_daily' => 1,
            'target_bulanan' => 26,
            'bobot' => 30,
        ]);
        Activity::create([
            'categories_id' => $pribadi->id,
            'nama' => 'Review Target Harian',
            'target_daily' => 1,
            'target_bulanan' => 26,
            'bobot' => 20,
        ]);
        Activity::create([
            'categories_id' => $pribadi->id,
            'nama' => 'Belajar dan Catat (Sharing Day)',
            'target_daily' => 1,
            'target_bulanan' => 26,
            'bobot' => 50,
        ]);

        // ========================
        // 2. Aktivitas Mencari Leads
        // ========================
        $leads = Category::create(['nama' => 'Aktivitas Mencari Leads']);

        Activity::create([
            'categories_id' => $leads->id,
            'nama' => 'Share Group WA',
            'target_daily' => 1,
            'target_bulanan' => 26,
            'bobot' => 10,
        ]);
        Activity::create([
            'categories_id' => $leads->id,
            'nama' => 'List Building / Database',
            'target_daily' => 2,
            'target_bulanan' => 52,
            'bobot' => 50,
        ]);
        Activity::create([
            'categories_id' => $leads->id,
            'nama' => 'Interaksi Manual - Komentar Positif',
            'target_daily' => 10,
            'target_bulanan' => 260,
            'bobot' => 10,
        ]);
        Activity::create([
            'categories_id' => $leads->id,
            'nama' => 'Interaksi Manual - Follow akun prospek',
            'target_daily' => 100,
            'target_bulanan' => 2600,
            'bobot' => 10,
        ]);
        Activity::create([
            'categories_id' => $leads->id,
            'nama' => 'Interaksi Manual - Like (100)',
            'target_daily' => 100,
            'target_bulanan' => 2600,
            'bobot' => 10,
        ]);
        Activity::create([
            'categories_id' => $leads->id,
            'nama' => 'Posting Story (WA/IG)',
            'target_daily' => 4,
            'target_bulanan' => 104,
            'bobot' => 10,
        ]);

        // ========================
        // 3. Aktivitas Memprospek
        // ========================
        $prospek = Category::create(['nama' => 'Aktivitas Memprospek']);

        Activity::create([
            'categories_id' => $prospek->id,
            'nama' => 'ZOOM OHC',
            'target_daily' => 4,
            'target_bulanan' => 60,
            'bobot' => 20,
        ]);
        Activity::create([
            'categories_id' => $prospek->id,
            'nama' => 'Edukasi & Membangun Hubungan',
            'target_daily' => 5,
            'target_bulanan' => 130,
            'bobot' => 60,
        ]);
        Activity::create([
            'categories_id' => $prospek->id,
            'nama' => 'Kirim Penawaran',
            'target_daily' => 2,
            'target_bulanan' => 52,
            'bobot' => 20,
        ]);

        // ========================
        // 4. Aktivitas Closing
        // ========================
        $closing = Category::create(['nama' => 'Aktivitas Closing']);

        Activity::create([
            'categories_id' => $closing->id,
            'nama' => 'Negosiasi',
            'target_daily' => 5,
            'target_bulanan' => 130,
            'bobot' => 30,
        ]);
        Activity::create([
            'categories_id' => $closing->id,
            'nama' => 'Daftar Isi Form',
            'target_daily' => 2,
            'target_bulanan' => 52,
            'bobot' => 30,
        ]);
        Activity::create([
            'categories_id' => $closing->id,
            'nama' => 'Transfer Masuk',
            'target_daily' => 2500000,
            'target_bulanan' => 5000000,
            'bobot' => 40,
        ]);

        // ========================
        // 5. Aktivitas Merawat Customer
        // ========================
        $care = Category::create(['nama' => 'Aktivitas Merawat Customer']);

        Activity::create([
            'categories_id' => $care->id,
            'nama' => 'Membangun Hubungan Alumni',
            'target_daily' => 1,
            'target_bulanan' => 1,
            'bobot' => 20,
        ]);
        Activity::create([
            'categories_id' => $care->id,
            'nama' => 'Minta Testimoni',
            'target_daily' => 2,
            'target_bulanan' => 4,
            'bobot' => 20,
        ]);
        Activity::create([
            'categories_id' => $care->id,
            'nama' => 'Program Referral',
            'target_daily' => 1,
            'target_bulanan' => 2,
            'bobot' => 20,
        ]);
        Activity::create([
            'categories_id' => $care->id,
            'nama' => 'Mengajak Bisnis Visit',
            'target_daily' => 5,
            'target_bulanan' => 5,
            'bobot' => 20,
        ]);
        Activity::create([
            'categories_id' => $care->id,
            'nama' => 'Mengajak Upref',
            'target_daily' => 5,
            'target_bulanan' => 5,
            'bobot' => 20,
        ]);
    }
}
