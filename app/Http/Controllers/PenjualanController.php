<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Kelas;

class PenjualanController extends Controller
{
    /**
     * Tampilkan halaman dashboard penjualan.
     */
    public function index()
    {
        // ======================================================
        // 📊 1. Total Penjualan Bulanan & Tahunan (Dummy Data)
        // ======================================================
        $totalBulanan = 10_680_200;
        $totalTahunan = 12_680_200;

        // ======================================================
        // 📈 2. Target vs Realisasi (Performance)
        // ======================================================
        $targetBulanan = 100_000_000;
        $realisasi = $totalBulanan;
        $persentaseCapaian = round(($realisasi / $targetBulanan) * 100, 1);

        // ======================================================
        // 📅 3. Rata-rata Penjualan per Hari
        // ======================================================
        $hariIni = max(Carbon::now()->day, 1);
        $rataHarian = round($realisasi / $hariIni, 0);

        // ======================================================
        // 👥 4. Data Kelas dari Database + Status Penjualan Dummy
        // ======================================================
        $dummyPenjualan = [28, 21, 17, 25, 9, 15, 12, 8, 5, 14, 19, 23, 11, 30]; // sesuai jumlah kelas di DB
        $index = 0;

        $kelas = Kelas::select('nama_kelas')->get()->map(function ($item) use (&$index, $dummyPenjualan) {
            $penjualan = $dummyPenjualan[$index++] ?? rand(5, 30);

            if ($penjualan >= 25) {
                $status = 'Laris';
            } elseif ($penjualan >= 10) {
                $status = 'Sedang';
            } else {
                $status = 'Kurang Laris';
            }

            return [
                'nama' => $item->nama_kelas,
                'penjualan' => $penjualan,
                'status' => $status,
            ];
        });

        // ======================================================
        // 🧭 5. Kontribusi Sumber Database (Statis)
        // ======================================================
        $kontribusiDatabase = [
            'Instagram Ads' => 40,
            'Facebook Ads' => 25,
            'Alumni' => 15,
            'Marketing' => 10,
            'Lain-Lain' => 10,
            'Iklan'=>5
        ];

        // ======================================================
        // 🧑‍💼 6. Penjualan Per Sales & Conversion Rate
        // ======================================================
        $salesData = [
            [
                'nama' => 'Qiyya',
                'penjualan' => 20,
                'target' => 50000000,
                'realisasi' => round((20 / 25) * 100),
                'conversion_rate' => 58,
                'komisi' => 1_500_000,
                'bonus' => 400_000,
            ],
            [
                'nama' => 'Yasmin',
                'penjualan' => 18,
                'target' => 5000000,
                'realisasi' => round((18 / 20) * 100),
                'conversion_rate' => 62,
                'komisi' => 1_400_000,
                'bonus' => 350_000,
            ],
            [
                'nama' => 'Linda',
                'penjualan' => 15,
                'target' => 50000000,
                'realisasi' => round((15 / 18) * 100),
                'conversion_rate' => 55,
                'komisi' => 1_200_000,
                'bonus' => 300_000,
            ],
            [
                'nama' => 'Tursia',
                'penjualan' => 12,
                'target' => 50000000,
                'realisasi' => round((12 / 16) * 100),
                'conversion_rate' => 48,
                'komisi' => 950_000,
                'bonus' => 200_000,
            ],
            [
                'nama' => 'Latifah',
                'penjualan' => 10,
                'target' => 50000000,
                'realisasi' => round((10 / 15) * 100),
                'conversion_rate' => 42,
                'komisi' => 850_000,
                'bonus' => 180_000,
            ],
        ];

        // ======================================================
        // 💵 7. Total Komisi & Bonus
        // ======================================================
        $totalKomisi = array_sum(array_column($salesData, 'komisi'));
        $totalBonus = array_sum(array_column($salesData, 'bonus'));

        // ======================================================
        // 👨‍👩‍👧 8. Data Pelanggan
        // ======================================================
        $totalPelangganAktif = 102;
        $pelangganBaru = 55;
        $pelangganLama = 47;
        $repeatOrderRate = 45; // (%)
        $ltv = 3_650_000; // Lifetime value rata-rata pelanggan

        // ======================================================
        // 📊 9. Grafik Pertumbuhan Penjualan Bulanan
        // ======================================================
        $penjualanBulanan = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data' => [25, 28, 30, 38, 42, 45, 48, 52, 55, 60, 65, 70],
        ];

        // ======================================================
        // 🔔 10. Notifikasi Target Belum Tercapai (lebih dinamis)
        // ======================================================
        if ($persentaseCapaian >= 100) {
            $notifikasi = '🎉 Target penjualan bulan ini sudah tercapai! Pertahankan performa tim.';
        } elseif ($persentaseCapaian >= 80) {
            $notifikasi = '💪 Hampir tercapai! Tingkatkan follow-up untuk menembus target bulan ini.';
        } else {
            $notifikasi = '⚠️ Target penjualan bulan ini belum tercapai. Segera perkuat strategi closing!';
        }

        // ======================================================
        // 📤 11. Kirim Semua Data ke View
        // ======================================================
        return view('penjualan.index', [
            'totalBulanan' => $totalBulanan,
            'totalTahunan' => $totalTahunan,
            'targetBulanan' => $targetBulanan,
            'realisasi' => $realisasi,
            'persentaseCapaian' => $persentaseCapaian,
            'rataHarian' => $rataHarian,
            'kelas' => $kelas,
            'kontribusiDatabase' => $kontribusiDatabase,
            'salesData' => $salesData,
            'totalKomisi' => $totalKomisi,
            'totalBonus' => $totalBonus,
            'totalPelangganAktif' => $totalPelangganAktif,
            'pelangganBaru' => $pelangganBaru,
            'pelangganLama' => $pelangganLama,
            'repeatOrderRate' => $repeatOrderRate,
            'ltv' => $ltv,
            'penjualanBulanan' => $penjualanBulanan,
            'notifikasi' => $notifikasi,
        ]);
    }
}
