<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisBisnis;
use Illuminate\Support\Facades\DB;

class JenisBisnisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jenisBisnis = [
            'Bisnis Properti',
            'Bisnis Manufaktur',
            'Bisnis F&B (Food & Beverage)',
            'Bisnis Jasa',
            'Bisnis Digital',
            'Bisnis Online',
            'Bisnis Franchise',
            'Bisnis Edukasi & Pelatihan',
            'Bisnis Kreatif',
            'Bisnis Agribisnis',
            'Bisnis Kesehatan & Kecantikan',
            'Bisnis Keuangan',
            'Bisnis Transportasi & Logistik',
            'Bisnis Pariwisata & Hospitality',
            'Bisnis Sosial (Social Enterprise)',
            'Bisnis Retail',
            'Bisnis Teknologi Informasi',
            'Bisnis Energi & Lingkungan',
            'Bisnis Otomotif',
            'Bisnis Telekomunikasi',
        ];
         foreach ($jenisBisnis as $jenis) {
            DB::table('jenis_bisnis')->insert([
                'nama' => $jenis,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
}

