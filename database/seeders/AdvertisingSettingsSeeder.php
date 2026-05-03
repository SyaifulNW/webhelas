<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertisingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cek apakah setting biaya_iklan sudah ada
        $existingSetting = DB::table('settings')->where('key', 'biaya_iklan')->first();

        if (!$existingSetting) {
            DB::table('settings')->insert([
                'key' => 'biaya_iklan',
                'value' => '5000000', // Default 5 juta
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo "✅ Setting 'biaya_iklan' berhasil ditambahkan!\n";
        } else {
            echo "ℹ️ Setting 'biaya_iklan' sudah ada dalam database.\n";
        }
    }
}
