<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyActiviti;
use App\Models\Activity;
use App\Models\Data;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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

        $csList = $csQuery->where('is_active', 1)->where('id', '!=', 1)->orderBy('name')->get();
        if (!$csId && $csList->isNotEmpty()) {
             $csId = $csList->first()->id;
        }

        // ==============================
        // 🔹 2. Ambil Master Activity & Realisasi Harian
        // ==============================
        
        // Ambil target user untuk filter kategori
        $targetUserForFilter = User::find($csId);
        $targetNameForFilter = $targetUserForFilter ? trim($targetUserForFilter->name) : '';

        // Ambil master aktivitas dengan filter kategori
        $activityQuery = Activity::with('kategori')->orderBy('categories_id');
        
        if ($targetNameForFilter === 'Nisa') {
            $activityQuery->whereIn('categories_id', [6, 7, 11]);
        } elseif ($targetNameForFilter === 'Felmi') {
            $activityQuery->whereIn('categories_id', [8, 9, 10]);
        } else {
            // Pelanggan CS Standard (Arifa, Shafa, Qiyya, dll)
            $activityQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                          ->whereHas('kategori', function($q) {
                              $q->where('nama', 'NOT LIKE', '%INTAKE%');
                          });
        }
        $activities = $activityQuery->get()->groupBy('categories_id');
        
        // Mapping bobot KPI (Pastikan konsisten dengan kategori yang ada)
        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
            'A. Aktivitas Harian (NON-NEGOTIABLE)' => 40,
            'B. Aktivitas Mingguan' => 30,
            'C. Aktivitas Bulanan' => 30,
        ];
        
        // Khusus Nisa (Kategori 6 & 7 mungkin punya bobot berbeda, namun user tidak menspesifikkan)
        // Jika 6-7, kita asumsikan bobot menyesuaikan atau tetap menggunakan mapping jika nama cocok.
        // Untuk amannya, kita tetap pakai mapping yang ada.

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


            foreach ($activities as $kategoriId => $list) {
                $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
                $activityPercents = [];

                foreach ($list as $act) {
                    $targetDaily = (float) ($act->target_daily ?? 0);
                    $targetBulanan = $targetDaily * $hariKerja;

                    if (strpos($act->nama, 'List Building / Database') !== false) {
                        $totalRealisasi = (float) Data::where('created_by', $targetUserForFilter->name)
                            ->whereMonth('created_at', $bulan)
                            ->whereYear('created_at', $tahun)
                            ->count();
                    } else {
                        $totalRealisasi = (float) DailyActiviti::where('user_id', $csId)
                            ->where('activity_id', $act->id)
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->sum('realisasi');
                    }

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

        $csName = trim($cs->name);

        $activityQuery = Activity::query();
        if ($csName === 'Nisa') {
            $activityQuery->whereIn('categories_id', [6, 7, 11]);
        } elseif ($csName === 'Felmi') {
            $activityQuery->whereIn('categories_id', [8, 9, 10]);
        } else {
            $activityQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                          ->whereHas('kategori', function($q) {
                              $q->where('nama', 'NOT LIKE', '%INTAKE%');
                          });
        }
        $allActivities = $activityQuery->get();
        // Hitung Hari Kerja (Senin-Sabtu)
        $daysInMonth = $carbonBulan->daysInMonth;
        $hariKerja = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $day = Carbon::create($carbonBulan->year, $carbonBulan->month, $d);
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
            'A. Aktivitas Harian (NON-NEGOTIABLE)' => 40,
            'B. Aktivitas Mingguan' => 30,
            'C. Aktivitas Bulanan' => 30,
            'DAILY INTAKE' => 33.33,
            'WEEKLY INTAKE' => 33.33,
            'MONTHLY INTAKE' => 33.34,
        ];

        $activitiesByCat = $allActivities->groupBy('categories_id');
        $kpiData = [];
        $totalNilai = 0;
        $totalBobot = 0;
        $categories = [];
        $total = [];

        foreach ($activitiesByCat as $kategoriId => $list) {
            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
            $activityPercents = [];
            
            if (!isset($categories[$categoryName])) {
                $categories[$categoryName] = [];
                $total[$categoryName] = [
                    'target_daily' => 0,
                    'target_bulanan' => 0,
                    'bobot' => 0,
                    'real' => 0,
                    'nilai' => 0,
                    'harian' => []
                ];
            }

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulananTotal = ($targetDaily > 0) ? ($targetDaily * $hariKerja) : (float) ($act->target_bulanan ?? 0);

                if (strpos($act->nama, 'List Building / Database') !== false) {
                    $totalRealisasiAct = (float) \App\Models\Data::where('created_by', $csName)
                        ->whereMonth('created_at', $carbonBulan->month)
                        ->whereYear('created_at', $carbonBulan->year)
                        ->count();
                    
                    $harian = [];
                    for ($d = 1; $d <= $jumlahHari; $d++) {
                        $dateStr = $carbonBulan->format("Y-m-") . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $harian[$d] = \App\Models\Data::where('created_by', $csName)
                            ->whereDate('created_at', $dateStr)
                            ->count();
                    }
                } else {
                    $totalRealisasiAct = (float) $activities->where('activity_id', $act->id)->sum('realisasi');
                    
                    $harian = [];
                    for ($d = 1; $d <= $jumlahHari; $d++) {
                        $harian[$d] = $activities
                            ->where('activity_id', $act->id)
                            ->where('tanggal', $carbonBulan->format("Y-m-") . str_pad($d, 2, '0', STR_PAD_LEFT))
                            ->sum('realisasi');
                    }
                }
                
                $percent = 0;
                if ($targetBulananTotal > 0) {
                    $percent = ($totalRealisasiAct / $targetBulananTotal) * 100;
                    if ($percent > 100) $percent = 100;
                }
                $activityPercents[] = $percent;
                
                $nilaiAct = ($percent / 100) * $act->bobot;

                $categories[$categoryName][] = [
                    'nama' => $act->nama,
                    'target_daily' => $act->target_daily,
                    'target_bulanan' => $targetBulananTotal,
                    'bobot' => $act->bobot,
                    'real' => $totalRealisasiAct,
                    'nilai' => round($nilaiAct, 2),
                    'harian' => $harian
                ];

                $total[$categoryName]['target_daily'] += $act->target_daily;
                $total[$categoryName]['target_bulanan'] += $targetBulananTotal;
                $total[$categoryName]['bobot'] += $act->bobot;
                $total[$categoryName]['real'] += $totalRealisasiAct;
                $total[$categoryName]['nilai'] += $nilaiAct;
                for ($d = 1; $d <= $jumlahHari; $d++) {
                    $total[$categoryName]['harian'][$d] = ($total[$categoryName]['harian'][$d] ?? 0) + $harian[$d];
                }
            }

            $skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
            $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
            
            if ($bobotKategori == 0) {
                $upperCatName = strtoupper(trim($categoryName));
                if (strpos($upperCatName, 'DAILY') !== false) $bobotKategori = 33.33;
                elseif (strpos($upperCatName, 'WEEKLY') !== false) $bobotKategori = 33.33;
                elseif (strpos($upperCatName, 'MONTHLY') !== false) $bobotKategori = 33.34;
            }

            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

            $kpiData[] = [
                'nama'        => $categoryName,
                'target'      => '100%',
                'bobot'       => $bobotKategori,
                'persentase'  => round($skorKategori, 2),
                'nilai'       => round($nilaiKategori, 2),
            ];

            $totalNilai += $nilaiKategori;
            $totalBobot += $bobotKategori;
        }

        $pdf = Pdf::loadView('admin.dailyactivity.pdf', [
            'categories' => $categories,
            'total' => $total,
            'jumlahHari' => $jumlahHari,
            'tahun' => $carbonBulan->year,
            'bulan_int' => $carbonBulan->month,
            'bulan' => $carbonBulan->translatedFormat('F Y'),
            'csName' => $cs->name,
            'downloadDate' => now()->translatedFormat('d F Y H:i'),
            'kpiData' => $kpiData,
            'totalNilai' => round($totalNilai, 2),
            'totalBobot' => $totalBobot
        ]);

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans'
        ]);

        $pdf->setPaper('F4', 'landscape');

        return $pdf->stream("Laporan_Activity_KPI_{$bulan}_{$cs->name}.pdf");
    }
}
