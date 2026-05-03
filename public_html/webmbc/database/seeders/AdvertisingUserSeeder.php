<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdvertisingUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cek apakah user sudah ada
        $existingUser = DB::table('users')->where('email', 'eko.sulis@webhelas.com')->first();

        if (!$existingUser) {
            DB::table('users')->insert([
                'name' => 'Eko Sulis',
                'email' => 'eko.sulis@webhelas.com',
                'password' => Hash::make('password123'), // Ganti dengan password yang diinginkan
                'role' => 'advertising',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            echo "✅ User 'Eko Sulis' dengan role 'advertising' berhasil dibuat!\n";
        } else {
            echo "ℹ️ User 'Eko Sulis' sudah ada dalam database.\n";
        }
    }
}
