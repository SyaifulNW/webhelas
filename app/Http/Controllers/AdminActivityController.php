<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyActiviti;
use App\Models\Activity;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $csId = $request->input('cs_id');
        $user = auth()->user();

        // ==============================
        // 🔹 1. Tentukan daftar CS yang bisa dilihat
        // ==============================
        $csQuery = User::query();

        $userName = trim($user->name);

        if ($user->role === 'administrator' || in_array($userName, ['Linda', 'Yasmin'])) {
             // Admin, Linda & Yasmin bisa lihat CS MBC + Team Mereka (Arifa, Felmi, Nisa, Eko Sulis, dll)
             $csQuery->where(function($q) {
                 $q->where('role', 'cs-mbc')
                   ->orWhereIn('name', ['Arifa', 'Felmi', 'Nisa', 'Eko Sulis', 'Shafa', 'Qiyya']);
             });
        } elseif (in_array($userName, ['Agus', 'Agus Setyo'])) {
            $csQuery->whereIn('name', ['Puput']);
        } else {
            // CS biasa hanya bisa melihat dirinya sendiri
            $csQuery->where('id', $user->id);
        }

        $csList = $csQuery->orderBy('name')->get();
        if (!$csId && $csList->isNotEmpty()) {
             $csId = $csList->first()->id;
        }

        // ==============================
        // 🔹 2. Ambil Master Activity & Realisasi Harian
        // ==============================
        
        // Ambil master aktivitas
        $activities = Activity::with('kategori')->orderBy('categories_id')->get()->groupBy('categories_id');

        // Ambil realisasi user untuk TANGGAL yang dipilih
        $daily = [];
        if ($csId) {
            $daily = DailyActiviti::where('user_id', $csId)
                ->whereDate('tanggal', $tanggal)
                ->pluck('realisasi', 'activity_id');
        }

        // ==============================
        // 🔹 3. Hitung KPI Bulanan (Optional, untuk kelengkapan data view)
        // ==============================
        $carbon = Carbon::parse($tanggal);
        $bulan   = $carbon->month;
        $tahun   = $carbon->year;
        
        $kpiData = [];
        $totalNilai = 0;
        $totalBobot = 0;

        if ($csId) {
             // Hari kerja
            $daysInMonth = $carbon->daysInMonth;
            $hariKerja = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = Carbon::create($tahun, $bulan, $d);
                if ($day->dayOfWeek != Carbon::SUNDAY) {
                    $hariKerja++;
                }
            }

            $categoryKpiWeights = [
                'Aktivitas Pribadi' => 10,
                'Aktivitas Mencari Leads' => 20,
                'Aktivitas Memprospek' => 20,
                'Aktivitas Closing' => 40,
                'Aktivitas Merawat Customer' => 10,
            ];

            foreach ($activities as $kategoriId => $list) {
                $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
                $activityPercents = [];

                foreach ($list as $act) {
                    $targetDaily = (float) ($act->target_daily ?? 0);
                    $targetBulanan = $targetDaily * $hariKerja;

                    $totalRealisasi = (float) DailyActiviti::where('user_id', $csId)
                        ->where('activity_id', $act->id)
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->sum('realisasi');

                    $percent = 0;
                    if ($targetBulanan > 0) {
                        $percent = ($totalRealisasi / $targetBulanan) * 100;
                        if ($percent > 100) $percent = 100;
                    }
                    $activityPercents[] = $percent;
                }

                $skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
                $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
                $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

                $kpiData[] = [
                    'categories_id' => $kategoriId,
                    'nama'        => $categoryName,
                    'target'      => '100%',
                    'bobot'       => $bobotKategori,
                    'persentase'  => round($skorKategori, 2),
                    'nilai'       => round($nilaiKategori, 2),
                ];

                $totalNilai += $nilaiKategori;
                $totalBobot += $bobotKategori;
            }
        }

        return view('admin.activity-cs.index', compact(
            'csList', 'csId', 'tanggal', 
            'activities', 'daily', 
            'kpiData', 'totalNilai', 'totalBobot'
        ));
    }

    public function viewPdfBulanan(Request $request)
    {
        // dd('Reached Controller: ' . auth()->user()->name);
        $bulan = $request->input('bulan');
        $csId = $request->input('cs_id');
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $jumlahHari = $carbonBulan->daysInMonth;

        $cs = User::findOrFail($csId);

        // Ambil aktivitas hanya untuk CS dan bulan tersebut
        $activities = DailyActiviti::with('activity')
            ->where('user_id', $csId)
            ->whereMonth('tanggal', $carbonBulan->month)
            ->whereYear('tanggal', $carbonBulan->year)
            ->get();

        $allActivities = Activity::all();
        $categories = [];
        $total = [];

        foreach ($allActivities as $act) {
            $kategori = $act->kategori->nama ?? 'Tanpa Kategori';
            if (!isset($categories[$kategori])) {
                $categories[$kategori] = [];
                $total[$kategori] = [
                    'target_daily' => 0,
                    'target_bulanan' => 0,
                    'bobot' => 0,
                    'real' => 0,
                    'nilai' => 0,
                    'harian' => []
                ];
            }

            $totalRealisasi = $activities->where('activity_id', $act->id)->sum('realisasi');
            $persentase = $act->target_bulanan > 0
                ? min(100, ($totalRealisasi / $act->target_bulanan) * 100)
                : 0;
            $nilai = ($persentase / 100) * $act->bobot;

            $harian = [];
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $harian[$d] = $activities
                    ->where('activity_id', $act->id)
                    ->where('tanggal', $carbonBulan->format("Y-m-") . str_pad($d, 2, '0', STR_PAD_LEFT))
                    ->sum('realisasi');
            }

            $categories[$kategori][] = [
                'nama' => $act->nama,
                'target_daily' => $act->target_daily,
                'target_bulanan' => $act->target_bulanan,
                'bobot' => $act->bobot,
                'real' => $totalRealisasi,
                'nilai' => round($nilai, 2),
                'harian' => $harian
            ];

            $total[$kategori]['target_daily'] += $act->target_daily;
            $total[$kategori]['target_bulanan'] += $act->target_bulanan;
            $total[$kategori]['bobot'] += $act->bobot;
            $total[$kategori]['real'] += $totalRealisasi;
            $total[$kategori]['nilai'] += $nilai;
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $total[$kategori]['harian'][$d] = ($total[$kategori]['harian'][$d] ?? 0) + $harian[$d];
            }
        }

        $pdf = Pdf::loadView('admin.dailyactivity.pdf', [
            'categories' => $categories,
            'total' => $total,
            'jumlahHari' => $jumlahHari,
            'bulan' => $carbonBulan->translatedFormat('F Y'),
            'csName' => $cs->name,
            'downloadDate' => now()->translatedFormat('d F Y H:i')
        ])->setPaper('F4', 'landscape');

        // Stream PDF agar langsung tampil di browser
        return $pdf->stream("Laporan_Activity_KPI_{$bulan}_{$cs->name}.pdf");
    }
}
