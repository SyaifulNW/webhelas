<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\SalesPlan;
use App\Models\Activity;
use App\Models\DailyActiviti;
use App\Models\Data;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (auth()->check() && strtolower(auth()->user()->role) === 'produksi') {
            return redirect()->route('programkerja.index');
        }

        // ====================== 📅 FILTER BULAN ======================
        $bulan = $request->input('bulan') ?? Carbon::now()->format('Y-m');
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $tahun = $carbonBulan->year;
        $bulanNum = $carbonBulan->month;

        // ====================== 👤 USER LOGIN ======================
        $csId   = auth()->id();
        $csName = optional(auth()->user())->name;

        // ====================== 🔔 NOTIFIKASI ======================
        $notifikasi = Notifikasi::where('user_id', $csId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $notifCount = Notifikasi::where('user_id', $csId)
            ->where('is_read', false)
            ->count();

        // ====================== 💰 OMSET & KOMISI ======================
        $isCsSmi = optional(auth()->user())->role === 'cs-smi';
        $isCsMbc = optional(auth()->user())->role === 'cs-mbc';

        if ($isCsSmi) {
            // Khusus CS SMI: Ambil kelas Start-Up Muda Indonesia, Start-Up Muslim Indonesia & Kelas Privat (tanpa filter tanggal)
            $kelasOmset = Kelas::where(function($q) {
                    $q->where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                      ->orWhere('nama_kelas', 'like', '%Start-Up Muslim Indonesia%')
                      ->orWhere('nama_kelas', 'like', '%Zoom Privat%');
                })
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->whereYear('updated_at', $tahun)
                        ->whereMonth('updated_at', $bulanNum)
                        ->where('status', 'sudah_transfer');
                }])
                ->get();
        } elseif ($isCsMbc) {
            // Khusus CS MBC: Start-Up Muslim & Kelas Privat (tanpa filter tanggal) + Kelas lain (bulan berjalan)
            $kelasOmset = Kelas::where(function ($q) use ($tahun, $bulanNum) {
                    $q->where('nama_kelas', 'like', '%Start-Up Muslim Indonesia%')
                      ->orWhere('nama_kelas', 'like', '%Zoom Privat%')
                      ->orWhere(function ($sub) use ($tahun, $bulanNum) {
                          $sub->whereYear('tanggal_mulai', $tahun)
                              ->whereMonth('tanggal_mulai', $bulanNum);
                      });
                })
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->whereYear('updated_at', $tahun)
                        ->whereMonth('updated_at', $bulanNum);
                }])
                ->get();
        } else {
            // Role Lain: Ambil kelas sesuai bulan berjalan + Kelas Privat (tanpa filter tanggal)
            $kelasOmset = Kelas::where(function ($q) use ($tahun, $bulanNum) {
                    $q->where(function ($sub) use ($tahun, $bulanNum) {
                        $sub->whereYear('tanggal_mulai', $tahun)
                            ->whereMonth('tanggal_mulai', $bulanNum);
                    })->orWhere('nama_kelas', 'like', '%Zoom Privat%');
                })
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->whereYear('updated_at', $tahun)
                        ->whereMonth('updated_at', $bulanNum);
                }])
                ->get();
        }

        $kelasOmsetFiltered = $kelasOmset->groupBy('nama_kelas')->map(function ($group) {
            // Ambil data pertama untuk info nama & tanggal (asumsi tanggal sama/mirip)
            $kelas = $group->first();
            
            // Hitung total omset dari SEMUA kelas yang namanya sama
            $omset = $group->sum(function ($k) {
                return $k->salesplans->sum('nominal');
            });

            $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
            $targetSmi = \App\Models\Setting::where('key', 'target_omset_smi')->value('value') ?? 50000000;

            if (str_contains($kelas->nama_kelas, 'Start-Up Muda Indonesia') || str_contains($kelas->nama_kelas, 'Start-Up Muslim Indonesia')) {
                $target = $targetSmi;
            } else {
                $target = $targetGlobal / 2;
            }

            $komisiSementara = $omset * 0.01;
            $komisiTotal = $omset >= $target ? $komisiSementara + 300000 : $komisiSementara;

            return [
                'nama_kelas' => $kelas->nama_kelas,
                'tanggal'    => $kelas->tanggal_mulai,
                'omset'      => $omset,
                'target'     => $target,
                'persen'     => $target > 0 ? round(($omset / $target) * 100, 2) : 0,
                'komisi'     => $komisiTotal,
            ];
        })->values();

        $totalKomisi = $kelasOmsetFiltered->sum('komisi');

        // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
     

        // ====================== 📈 LEADS ======================
        $leads = SalesPlan::select('status', DB::raw('count(*) as total'))
            ->where('created_by', $csId)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->groupBy('status')
            ->pluck('total', 'status');

        $cold           = $leads['cold'] ?? 0;
        $tertarik       = $leads['tertarik'] ?? 0;
        $mau_transfer   = $leads['mau_transfer'] ?? 0;
        $sudah_transfer = $leads['sudah_transfer'] ?? 0;
        $no             = $leads['no'] ?? 0;

        $totalLeadAktif = $cold + $tertarik + $mau_transfer + $sudah_transfer + $no;

        // ====================== ⚙️ KPI ======================
        $hariKerja = 0;
        for ($d = 1; $d <= $carbonBulan->daysInMonth; $d++) {
            $day = Carbon::create($tahun, $bulanNum, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
        }

        $activityQuery = Activity::orderBy('categories_id');
        
        if ($csName === 'Nisa') {
            $activityQuery->whereIn('categories_id', [6, 7]);
        } elseif ($csName === 'Felmi') {
            $activityQuery->whereHas('kategori', function($q) {
                $q->where('nama', 'LIKE', '%INTAKE%');
            });
        } else {
            // Standard CS MBC
            $activityQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                          ->whereHas('kategori', function($q) {
                              $q->where('nama', 'NOT LIKE', '%INTAKE%');
                          });
        }

        $activities = $activityQuery->with('kategori')->get()->groupBy('categories_id');

        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
            'A. Aktivitas Harian (NON-NEGOTIABLE)' => 50,
            'B. Aktivitas Mingguan' => 50,
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
                $targetBulanan = $targetDaily * $hariKerja;

                $totalRealisasi = (float) DailyActiviti::where('user_id', $csId)
                    ->where('activity_id', $act->id)
                    ->whereMonth('tanggal', $bulanNum)
                    ->whereYear('tanggal', $tahun)
                    ->sum('realisasi');

                $percent = 0;
                if ($targetBulanan > 0) {
                    $percent = ($totalRealisasi / $targetBulanan) * 100;
                    if ($percent > 100) $percent = 100;
                }

                $activityPercents[] = $percent;
            }

            $skorKategori = count($activityPercents)
                ? (array_sum($activityPercents) / count($activityPercents))
                : 0;

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

            $totalKpi += $nilaiKategori;
            $totalBobot += $bobotKategori;
        }

        $totalNilai = round($totalKpi, 2);

        // ====================== DATABASE PERSEN ======================
        $databaseTotal = Data::where('created_by', $csId)->count();
        $persentaseDatabaseBaru = $databaseTotal > 0 ? round(($databaseBaru / $databaseTotal) * 100, 2) : 0;
        $persentaseDatabaseLama = 100 - $persentaseDatabaseBaru;


            // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
    
    // OMSET
    $totalOmset = $kelasOmsetFiltered->sum('omset'); 
    $targetBulananOmset = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
    
    // 🔥 Pencapaian Omset untuk ditampilkan di tabel
    $pencapaianOmset = $totalOmset;
    
    // Nilai Omset (0-100)
    $nilaiOmset = $targetBulananOmset > 0
        ? min(100, round(($totalOmset / $targetBulananOmset) * 100))
        : 0;
    
    // Bobot 40%
    $nilaiOmset = round(($nilaiOmset / 100) * 40, 2);
    
    
    // ============ Closing Paket ============
    $closingPaket = SalesPlan::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulanNum)
        ->where('status', 'sudah_transfer')
        ->count();
    
    // 🔥 Pencapaian Closing Paket untuk tabel
    $pencapaianClosingPaket = $closingPaket;
    
    $nilaiClosingPaket = $closingPaket >= 1 ? 100 : 0;
    $nilaiClosingPaket = round(($nilaiClosingPaket / 100) * 10, 2);
    
    
    // ============ Database Baru ============
    $databaseBaru = Data::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulanNum)
        ->count();
    
    // 🔥 Pencapaian Database Baru untuk tabel
    $pencapaianDatabaseBaru = $databaseBaru;
    
    $nilaiDatabaseBaru = $databaseBaru >= 50 ? 100 : ($databaseBaru * 2);
    if ($nilaiDatabaseBaru > 100) $nilaiDatabaseBaru = 100;
    
    $nilaiDatabaseBaru = round(($nilaiDatabaseBaru / 100) * 10, 2);



    // ====================== MANUAL ASSESSMENT ======================
    $manual = \App\Models\PenilaianManual::where('user_id', $csId)
        ->where('bulan', $bulanNum)
        ->where('tahun', $tahun)
        ->first();

    $nilaiManualPart = 0;
    if ($manual) {
        $sum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
        $bobotManual = ($isCsSmi) ? 30 : 20;
        $nilaiManualPart = round(($sum / 500) * $bobotManual);
    }

    // ====================== INTAKE (REKAP DAILY ACTIVITY) ======================
    $nilaiIntakePart = round(($totalNilai / 100) * 20, 2);

    // ====================== TOTAL NILAI HASIL ======================
    $totalNilaiHasil = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru + $nilaiManualPart + $nilaiIntakePart;


    // ====================== HISTORY KINERJA (12 BULAN) ======================
    $historyNilai = [];
    $role = optional(auth()->user())->role;

    for ($m = 1; $m <= 12; $m++) {
        $historyNilai[$m] = $this->hitungTotalNilaiHasil($csId, optional(auth()->user())->name, $m, $tahun, $role);
    }

    // ====================== RETURN ======================
    $skorDaily = $totalNilai;
    return view('home', compact(
        'kelasOmsetFiltered',
        'totalKomisi',

        // Nilai hasil CS
        'nilaiOmset',
        'nilaiClosingPaket',
        'nilaiDatabaseBaru',
        'nilaiManualPart',
        'nilaiIntakePart',
        'skorDaily',
        'totalNilaiHasil',
        'manual',
        'historyNilai',

        'cold',
        'tertarik',
        'mau_transfer',
        'sudah_transfer',
        'no',
        'totalLeadAktif',

        'csName',
        'bulan',

        'kpiData',
        'totalBobot',
        'totalNilai',

        'databaseBaru',
        'databaseTotal',
        'persentaseDatabaseBaru',
        'persentaseDatabaseLama',

        'pencapaianOmset',
        'pencapaianClosingPaket',
        'pencapaianDatabaseBaru',
        
        // Closing Paket
        'closingPaket',  

    
        'notifikasi',
        'notifCount'
    ));
}

private function hitungTotalNilaiHasil($csId, $namaUserData, $bulan, $tahun, $role)
{
    // OMSET (40%)
    if ($role === 'cs-smi') {
        $kelasOmset = Kelas::where(function($q) {
                $q->where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                  ->orWhere('nama_kelas', 'like', '%Start-Up Muslim Indonesia%')
                  ->orWhere('nama_kelas', 'like', '%Zoom Privat%');
            })
            ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                $q->where('created_by', $csId)
                    ->whereYear('updated_at', $tahun)
                    ->whereMonth('updated_at', $bulan)
                    ->where('status', 'sudah_transfer');
            }])
            ->get();
    } else {
        $kelasOmset = Kelas::where(function ($q) use ($tahun, $bulan) {
                $q->where(function ($sub) use ($tahun, $bulan) {
                    $sub->whereYear('tanggal_mulai', $tahun)
                        ->whereMonth('tanggal_mulai', $bulan);
                })->orWhere('nama_kelas', 'like', '%Zoom Privat%')
                  ->orWhere('nama_kelas', 'like', '%Start-Up Muslim Indonesia%');
            })
            ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                $q->where('created_by', $csId)
                    ->whereYear('updated_at', $tahun)
                    ->whereMonth('updated_at', $bulan);
            }])
            ->get();
    }

    $totalOmset = $kelasOmset->sum(fn ($k) => $k->salesplans->sum('nominal'));
    $totalOmset = $kelasOmset->sum(fn ($k) => $k->salesplans->sum('nominal'));
    $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
    
    // OMSET (40%)
    $nilaiOmsetSkor = $targetGlobal > 0 ? min(100, round(($totalOmset / $targetGlobal) * 100)) : 0;
    $nilaiOmset = round(($nilaiOmsetSkor / 100) * 40, 2);

    // INTAKE / DAILY ACTIVITY (20%)
    $dayQuery = \App\Models\Activity::where('role', 'cs');
    if (strtolower($namaUserData) === 'nisa') {
        $dayQuery->whereIn('categories_id', [6, 7]);
    } elseif (strtolower($namaUserData) === 'felmi') {
        $dayQuery->whereHas('kategori', function($q) { $q->where('nama', 'LIKE', '%INTAKE%'); });
    } else {
        $dayQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                 ->whereHas('kategori', function($q) { $q->where('nama', 'NOT LIKE', '%INTAKE%'); });
    }
    $allActivities = $dayQuery->with('kategori')->get()->groupBy('categories_id');
    $carbon = Carbon::create($tahun, $bulan, 1);
    $daysInMonth = $carbon->daysInMonth;
    $hariKerja = 0;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = Carbon::create($tahun, $bulan, $d);
        if ($date->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
    }
    $allKpiPercents = [];
    foreach ($allActivities as $list) {
        $rowPercents = [];
        foreach ($list as $act) {
            $targetD = (float)($act->target_daily ?? 0);
            $targetB = ($targetD > 0) ? ($targetD * $hariKerja) : (float)($act->target_bulanan ?? 0);
            if (strpos($act->nama, 'List Building / Database') !== false) {
                $real = (float) \App\Models\Data::where('created_by', $namaUserData)
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->count();
            } else {
                $real = (float) \App\Models\DailyActiviti::where('user_id', $csId)
                    ->where('activity_id', $act->id)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->sum('realisasi');
            }
            $rowPercents[] = ($targetB > 0) ? min(100, ($real / $targetB) * 100) : 0;
        }
        $allKpiPercents[] = count($rowPercents) ? (array_sum($rowPercents) / count($rowPercents)) : 0;
    }
    $skorDailyOverall = count($allKpiPercents) ? (array_sum($allKpiPercents) / count($allKpiPercents)) : 0;
    $nilaiIntake = ($skorDailyOverall / 100) * 20;

    // CLOSING PAKET (10%)
    $closing = \App\Models\SalesPlan::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulan)
        ->where('status', 'sudah_transfer')
        ->count();
    $closingScore = $closing >= 1 ? 100 : 0;
    $nilaiClosing = round(($closingScore / 100) * 10, 2);

    // DATABASE BARU (10%)
    $dbBaru = \App\Models\Data::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulan)
        ->count();
    $dbScore = $dbBaru >= 50 ? 100 : ($dbBaru * 2);
    if ($dbScore > 100) $dbScore = 100;
    $nilaiDb = round(($dbScore / 100) * 10, 2);

    // MANUAL ASSESSMENT (20%)
    $manual = \App\Models\PenilaianManual::where('user_id', $csId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
    $nilaiManualPart = 0;
    if ($manual) {
        $sum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
        $nilaiManualPart = round(($sum / 500) * 20);
    }

    return round($nilaiOmset + $nilaiClosing + $nilaiDb + $nilaiIntake + $nilaiManualPart, 2);
}
}
