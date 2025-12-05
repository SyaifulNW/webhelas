<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\kelas;

class kelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kelas = [
            ['nama_kelas' =>  'Sistemasi Bisnis', 'deskripsi' => 'Deskripsi untuk Kelas A'],
            ['nama_kelas' => 'Great Manager', 'deskripsi' => 'Deskripsi untuk Kelas B'],
            ['nama_kelas' =>   'Scale-Up 10X', 'deskripsi' => 'Deskripsi untuk Kelas C'],
            ['nama_kelas' =>  'Leadership', 'deskripsi' => 'Deskripsi untuk Kelas C'],
            ['nama_kelas' =>   'CS dan Sales Jago Closing', 'deskripsi' => 'Deskripsi untuk Kelas C'],
            ['nama_kelas' =>   'Repeat Order','deskripsi' => 'Deskripsi untuk Kelas C'],
            ['nama_kelas' =>  'Keuangan', 'deskripsi' => 'Deskripsi untuk Kelas C'],
            ['nama_kelas' =>   'HRD Mastery', 'deskripsi' => 'Deskripsi untuk Kelas C'],
            ['nama_kelas' =>   'Public Speaking', 'deskripsi' => 'Deskripsi untuk Kelas C'],
        ];
        foreach ($kelas as $k) {
            kelas::create($k);
        }
    }
}
