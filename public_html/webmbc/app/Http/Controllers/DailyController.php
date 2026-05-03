<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;        // Master data aktivitas
use App\Models\DailyActiviti;   // Input realisasi harian
use App\Models\Data;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class DailyController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $user = auth()->user();
        $userId = $user->id;

        $userRole = strtolower(trim($user->role));
        $activityRole = ($userRole === 'marketing') ? 'marketing' : 'cs';
        $userName = trim($user->name);
        
        $query = Activity::where('role', $activityRole);
        
        if (strtolower($userName) === 'nisa') {
            $query->whereIn('categories_id', [6, 11]);
        } elseif (strtolower($userName) === 'felmi') {
            $query->whereIn('categories_id', [8, 9, 10]);
        } else {
            $categoryNames = [
                'Aktivitas Pribadi', 
                'Aktivitas Mencari Leads', 
                'Aktivitas Memprospek', 
                'Aktivitas Closing', 
                'Aktivitas Merawat Customer'
            ];
            $query->whereHas('kategori', function($q) use ($categoryNames) {
                $q->where(function($sub) use ($categoryNames) {
                    foreach ($categoryNames as $name) {
                        $sub->orWhere('nama', 'LIKE', '%' . trim($name) . '%');
                    }
                });
            });
        }
        
        $activities = $query->with('kategori')->orderBy('categories_id')
                        ->get()
                        ->groupBy('categories_id');

        // Ambil realisasi user untuk TANGGAL yang dipilih
        $daily = DailyActiviti::where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->pluck('realisasi', 'activity_id')->toArray();

        // ========================================================
        // 🔹 OTOMATISASI REALISASI (List Building, WA, Telp)
        // ========================================================
        $automatedIds = [];
        
        // Trigger update otomatis
        \App\Models\DailyActiviti::updateAutomated($userId, $tanggal);

        // Identifikasi ID aktivitas yang diotomatisasi untuk ditandai di view
        $autoNames = [
            '%List Building / Database%',
            '%Edukasi % Membangun Hubungan%',
            '%Telepon database%',
            '%Telepon Database Prospek%'
        ];
        
        foreach ($autoNames as $namePattern) {
            $actAct = Activity::where('nama', 'LIKE', $namePattern)->first();
            if ($actAct) {
                $automatedIds[] = $actAct->id;
                // Ambil nilai terbaru dari database
                $val = DailyActiviti::where('user_id', $userId)
                    ->where('activity_id', $actAct->id)
                    ->whereDate('tanggal', $tanggal)
                    ->value('realisasi');
                $daily[$actAct->id] = $val ?? 0;
            }
        }

        // Get calculated KPI data
        $kpiCalculated = $this->getKpiData($user, $tanggal, $activities);
        $kpiData = $kpiCalculated['kpiData'];
        $totalNilai = $kpiCalculated['totalNilai'];
        $totalBobot = $kpiCalculated['totalBobot'];

        return view('admin.dailyactivity.index', compact(
            'activities', 'daily', 'tanggal',
            'kpiData', 'totalNilai', 'totalBobot', 'automatedIds'
        ));
    }

    private function getKpiData($user, $tanggal, $activities = null)
    {
        $userId = $user->id;
        $userName = trim($user->name);
        $userRole = strtolower(trim($user->role));
        $activityRole = ($userRole === 'marketing') ? 'marketing' : 'cs';

        if (!$activities) {
            $query = Activity::where('role', $activityRole);
            
            if (strtolower($userName) === 'nisa') {
                $query->whereIn('categories_id', [6, 11]);
            } elseif (strtolower($userName) === 'felmi') {
                $query->whereIn('categories_id', [8, 9, 10]);
            } else {
                $categoryNames = [
                    'Aktivitas Pribadi', 
                    'Aktivitas Mencari Leads', 
                    'Aktivitas Memprospek', 
                    'Aktivitas Closing', 
                    'Aktivitas Merawat Customer'
                ];
                $query->whereHas('kategori', function($q) use ($categoryNames) {
                    $q->where(function($sub) use ($categoryNames) {
                        foreach ($categoryNames as $name) {
                            $sub->orWhere('nama', 'LIKE', '%' . trim($name) . '%');
                        }
                    });
                });
            }

            $activities = $query->with('kategori')->orderBy('categories_id')->get()->groupBy('categories_id');
        }

        $carbon = Carbon::parse($tanggal);
        $bulan = $carbon->month;
        $tahun = $carbon->year;

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
            'A. Aktivitas Harian (NON-NEGOTIABLE)' => 60,
            'B. Aktivitas Mingguan' => 0,
            'C. Aktivitas Bulanan' => 40,
            'DAILY INTAKE' => 33.33,
            'WEEKLY INTAKE' => 33.33,
            'MONTHLY INTAKE' => 33.34,
        ];

        $kpiData = [];
        $totalKpi = 0;
        $totalBobot = 0;

        foreach ($activities as $kategoriId => $list) {
            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
            $activityPercents = [];

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulanan = ($targetDaily > 0) ? ($targetDaily * $hariKerja) : (float) ($act->target_bulanan ?? 0);

                $totalRealisasi = (float) DailyActiviti::where('user_id', $userId)
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
            
            if ($bobotKategori == 0) {
                // Hardcoded fallback for known Felmi Categories by name keywords
                $upperCatName = strtoupper(trim($categoryName));
                if (strpos($upperCatName, 'DAILY') !== false) $bobotKategori = 33.33;
                elseif (strpos($upperCatName, 'WEEKLY') !== false) $bobotKategori = 33.33;
                elseif (strpos($upperCatName, 'MONTHLY') !== false) $bobotKategori = 33.34;
            }

            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

            $kpiData[] = [
                'categories_id' => $kategoriId,
                'nama'        => $categoryName,
                'target'      => '100%',
                'bobot'       => $bobotKategori,
                'persentase'  => round($skorKategori, 2),
                'nilai'       => round($nilaiKategori, 2),
            ];

            $totalKpi += $nilaiKategori;
            $totalBobot += $bobotKategori;
        }

        return [
            'kpiData' => $kpiData,
            'totalNilai' => round($totalKpi, 2),
            'totalBobot' => $totalBobot
        ];
    }

    public function store(Request $request)
    {
        $tanggal = $request->input('tanggal');

        foreach ($request->realisasi as $activityId => $value) {
            DailyActiviti::updateOrCreate(
                [
                    'user_id'    => auth()->id(),
                    'tanggal'    => $tanggal,
                    'activity_id'=> $activityId,
                ],
                [
                    'realisasi'  => $value ?? 0
                ]
            );
        }

        if ($request->ajax()) {
            $user = auth()->user();
            $kpiCalculated = $this->getKpiData($user, $tanggal);
            
            return response()->json([
                'message' => 'Berhasil disimpan',
                'kpiData' => $kpiCalculated['kpiData'],
                'totalNilai' => $kpiCalculated['totalNilai'],
                'totalBobot' => $kpiCalculated['totalBobot']
            ]);
        }

        return redirect()->back()->with('success', 'Aktivitas berhasil disimpan!');
    }
    
  public function exportPdf($bulan)
{
    $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
    $jumlahHari = $carbonBulan->daysInMonth;
    $tahun = $carbonBulan->year;
    $bulanNum = $carbonBulan->month;

    // Hitung hari kerja (Senin-Sabtu)
    $hariKerja = 0;
    for ($d = 1; $d <= $jumlahHari; $d++) {
        $day = Carbon::create($tahun, $bulanNum, $d);
        if ($day->dayOfWeek != Carbon::SUNDAY) {
            $hariKerja++;
        }
    }

    // Sync automated data for the entire month
    for ($d = 1; $d <= $jumlahHari; $d++) {
        $syncDate = Carbon::create($tahun, $bulanNum, $d)->toDateString();
        \App\Models\DailyActiviti::updateAutomated(auth()->id(), $syncDate);
    }

    $activities = DailyActiviti::with('activity')
        ->where('user_id', auth()->id())
        ->whereMonth('tanggal', $bulanNum)
        ->whereYear('tanggal', $tahun)
        ->get();

    $user = auth()->user();
    $userRole = strtolower(trim($user->role));
    $activityRole = ($userRole === 'marketing') ? 'marketing' : 'cs';
    $userName = trim($user->name);
    
    $query = Activity::where('role', $activityRole);
    
    if (strtolower($userName) === 'nisa') {
        $query->whereIn('categories_id', [6, 11]);
    } elseif (strtolower($userName) === 'felmi') {
        $query->whereIn('categories_id', [8, 9, 10]);
    } else {
        $categoryNames = [
            'Aktivitas Pribadi', 
            'Aktivitas Mencari Leads', 
            'Aktivitas Memprospek', 
            'Aktivitas Closing', 
            'Aktivitas Merawat Customer'
        ];
        $query->whereHas('kategori', function($q) use ($categoryNames) {
            $q->where(function($sub) use ($categoryNames) {
                foreach ($categoryNames as $name) {
                    $sub->orWhere('nama', 'LIKE', '%' . trim($name) . '%');
                }
            });
        });
    }
    
    if ($activityRole === 'marketing') {
        $query->where('nama', '!=', 'Review Data & Konten');
    }
    
    $allActivities = $query->get();
    
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

        $totalRealisasi = (float) $activities->where('activity_id', $act->id)->sum('realisasi');
        
        $targetDaily = (float) ($act->target_daily ?? 0);
        $targetBulanan = ($targetDaily > 0) ? ($targetDaily * $hariKerja) : (float) ($act->target_bulanan ?? 0);

        $persentase = 0;
        if ($targetBulanan > 0) {
            $persentase = ($totalRealisasi / $targetBulanan) * 100;
            if ($persentase > 100) $persentase = 100;
        }
        
        $nilai = ($persentase / 100) * $act->bobot;

        $harian = [];
        for ($d = 1; $d <= $jumlahHari; $d++) {
            $harian[$d] = $activities
                ->where('activity_id', $act->id)
                ->where('tanggal', $carbonBulan->format('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT))
                ->sum('realisasi');
        }

        $categories[$kategori][] = [
            'nama' => $act->nama,
            'deskripsi' => $act->deskripsi,
            'target_daily' => $act->target_daily,
            'target_bulanan' => $targetBulanan,
            'bobot' => $act->bobot,
            'real' => $totalRealisasi,
            'nilai' => round($nilai, 2),
            'harian' => $harian
        ];

        $total[$kategori]['target_daily'] += $act->target_daily;
        $total[$kategori]['target_bulanan'] += $targetBulanan;
        $total[$kategori]['bobot'] += $act->bobot;
        $total[$kategori]['real'] += $totalRealisasi;
        $total[$kategori]['nilai'] += $nilai;
        for ($d = 1; $d <= $jumlahHari; $d++) {
            $total[$kategori]['harian'][$d] = ($total[$kategori]['harian'][$d] ?? 0) + $harian[$d];
        }
    }

    // Tambahan: nama CS & tanggal unduhan
    $csName = optional(auth()->user())->name ?? 'Unknown User';
    $downloadDate = now()->translatedFormat('d F Y H:i');

    // Hitung Rekap KPI untuk ditampilkan di bawah PDF
    $kpiCalculated = $this->getKpiData($user, $carbonBulan->toDateString());

    $pdf = Pdf::loadView('admin.dailyactivity.pdf', [
        'categories' => $categories,
        'total' => $total,
        'jumlahHari' => $jumlahHari,
        'tahun' => $tahun,
        'bulan_int' => $bulanNum,
        'bulan' => $carbonBulan->translatedFormat('F Y'),
        'csName' => $csName,
        'downloadDate' => $downloadDate,
        'kpiData' => $kpiCalculated['kpiData'] ?? [],
        'totalNilai' => $kpiCalculated['totalNilai'] ?? 0,
        'totalBobot' => $kpiCalculated['totalBobot'] ?? 0
    ]);

    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'DejaVu Sans'
    ]);

    $pdf->setPaper('F4', 'landscape');

    return $pdf->download("Laporan_Activity_KPI_{$bulan}_{$csName}.pdf");
}

}
