<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Data;
use App\Models\Kelas;
use App\Models\Activity;
use App\Models\DailyActiviti;
use PDF;
use Carbon\Carbon;

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $csId = $user->id;
        $namaUserData = $user->name;

        // ============================
        // FILTER BULAN & TAHUN
        // ============================
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $bulanNum = intval($bulan);

        $tanggalDipilih = Carbon::createFromDate($tahun, $bulan, 1);

        // ============================
        // 1. HITUNG OMSET REAL
        // ============================
        if ($user->role === 'cs-smi') {
            $kelasOmset = Kelas::where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulanNum) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulanNum)
                      ->where('status', 'sudah_transfer');
                }])
                ->get();
        } else {
            $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulanNum)
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulanNum) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulanNum);
                }])
                ->get();
        }

        $kelasOmsetFiltered = $kelasOmset->map(function ($kelas) {
            $omset = $kelas->salesplans->sum('nominal');
            $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
            $target = $targetGlobal / 2;

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
        });

        $totalOmset = $kelasOmsetFiltered->sum('omset');

        // ============================
        // 2. NILAI OMSET (40%)
        // ============================
        $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
        $nilaiOmset = $targetGlobal > 0 ? min(40, intval($totalOmset / $targetGlobal * 40)) : 0;

        // ============================
        // 3. CLOSING PAKET (10%)
        // ============================
        $closingPaket = SalesPlan::where('created_by', $csId)
            ->where('closing_paket', 1)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulanNum)
            ->count();

        if ($user->role === 'cs-smi') {
            $nilaiClosingPaket = 0;
        } else {
            $nilaiClosingPaket = min(10, $closingPaket * 10);
        }

        // ============================
        // 4. DATABASE BARU (10%)
        // ============================
        $databaseBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->count();

        $nilaiDatabaseBaru = min(10, intval($databaseBaru / 50 * 10));

        // ============================
        // 5. INTAKE / DAILY ACTIVITY (20%)
        // ============================
        // Hitung skor rata-rata dari semua kategori daily activity
        $dayQuery = Activity::where('role', 'cs');
        if (strtolower($user->name) === 'nisa') {
            $dayQuery->whereIn('categories_id', [6, 7]);
        } elseif (strtolower($user->name) === 'felmi') {
            $dayQuery->whereHas('kategori', function($q) { $q->where('nama', 'LIKE', '%INTAKE%'); });
        } else {
            $dayQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                     ->whereHas('kategori', function($q) { $q->where('nama', 'NOT LIKE', '%INTAKE%'); });
        }

        $allActivities = $dayQuery->with('kategori')->get()->groupBy('categories_id');
        
        // Hitung hari kerja
        $daysInMonth = $tanggalDipilih->daysInMonth;
        $hariKerja = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($tahun, $bulanNum, $d);
            if ($date->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
        }

        $allKpiPercents = [];
        foreach ($allActivities as $list) {
            $rowPercents = [];
            foreach ($list as $act) {
                $targetD = (float)($act->target_daily ?? 0);
                $targetB = ($targetD > 0) ? ($targetD * $hariKerja) : (float)($act->target_bulanan ?? 0);
                
                // Cek jika ini automated 'List Building'
                if (strpos($act->nama, 'List Building / Database') !== false) {
                    $real = (float) \App\Models\Data::where('created_by', $user->name)
                        ->whereMonth('created_at', $bulanNum)
                        ->whereYear('created_at', $tahun)
                        ->count();
                } else {
                    $real = (float) DailyActiviti::where('user_id', $csId)
                        ->where('activity_id', $act->id)
                        ->whereMonth('tanggal', $bulanNum)
                        ->whereYear('tanggal', $tahun)
                        ->sum('realisasi');
                }

                $p = ($targetB > 0) ? min(100, ($real / $targetB) * 100) : 0;
                $rowPercents[] = $p;
            }
            $allKpiPercents[] = count($rowPercents) ? (array_sum($rowPercents) / count($rowPercents)) : 0;
        }
        $skorIntake = count($allKpiPercents) ? (array_sum($allKpiPercents) / count($allKpiPercents)) : 0;
        $nilaiIntake = round(($skorIntake / 100) * 20, 2);

        // ============================
        // 6. NILAI MANUAL (20%)
        // ============================
        $manual = \App\Models\PenilaianManual::where('user_id', $csId)
                    ->where('bulan', $bulanNum)
                    ->where('tahun', $tahun)
                    ->first();

        $nilaiManualPart = 0;
        $totalSumManual = 0; 

        if ($manual) {
            $totalSumManual = $manual->kerajinan + 
                              $manual->kerjasama + 
                              $manual->tanggung_jawab + 
                              $manual->inisiatif + 
                              $manual->komunikasi;

            $nilaiManualPart = round(($totalSumManual / 500) * 20); 
        }

        // ============================
        // 7. TOTAL NILAI (SUM)
        // ============================
        $totalNilai = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru + $nilaiIntake + $nilaiManualPart;
        
        $nilaiSistem = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru + $nilaiIntake;

        // ============================
        // 7. CHART & HISTORY
        // ============================
        $labels = [];
        $scores = [];

        $role = $user->role;

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $labels[] = $dt->format('M Y');

            $scores[] = $this->hitungTotalNilai(
                $csId,
                $namaUserData,
                $dt->month,
                $dt->year,
                $role
            );
        }

        $historyNilai = array_fill(1, 12, 0);

        for ($m = 1; $m <= 12; $m++) {
            $historyNilai[$m] = $this->hitungTotalNilai(
                $csId,
                $namaUserData,
                $m,
                $tahun,
                $role
            );
        }

        // ============================
        // 8. KIRIM KE VIEW
        // ============================
        return view('admin.penilaian.index', compact(
            'bulan',
            'tahun',
            'totalOmset',
            'nilaiOmset',
            'closingPaket',
            'nilaiClosingPaket',
            'databaseBaru',
            'nilaiDatabaseBaru',
            'skorIntake',
            'nilaiIntake',
            'nilaiManualPart',
            'totalSumManual',
            'totalNilai',
            'nilaiSistem',
            'labels',
            'scores',
            'historyNilai',
            'kelasOmsetFiltered',
            'manual'
        ));
    }


    // ======================================================
    // FUNGSI HITUNG TOTAL NILAI (REUSABLE)
    // ======================================================
    private function hitungTotalNilai($csId, $namaUserData, $bulan, $tahun, $role)
    {
        // OMSET (40%)
        if ($role === 'cs-smi') {
            $kelasOmset = Kelas::where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulan)
                      ->where('status', 'sudah_transfer');
                }])
                ->get();
        } else {
            $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulan)
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulan);
                }])
                ->get();
        }

        $totalOmset = $kelasOmset->sum(fn ($k) => $k->salesplans->sum('nominal'));
        $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
        $nilaiOmset = $targetGlobal > 0 ? min(40, intval($totalOmset / $targetGlobal * 40)) : 0;

        // INTAKE (20%)
        $dayQuery = Activity::where('role', 'cs');
        if (strtolower($namaUserData) === 'nisa') {
            $dayQuery->whereIn('categories_id', [6, 7]);
        } elseif (strtolower($namaUserData) === 'felmi') {
            $dayQuery->whereHas('kategori', function($q) { $q->where('nama', 'LIKE', '%INTAKE%'); });
        } else {
            $dayQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                     ->whereHas('kategori', function($q) { $q->where('nama', 'NOT LIKE', '%INTAKE%'); });
        }
        $allActivities = $dayQuery->with('kategori')->get()->groupBy('categories_id');
        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
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
                    $real = (float) DailyActiviti::where('user_id', $csId)
                        ->where('activity_id', $act->id)
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->sum('realisasi');
                }
                $rowPercents[] = ($targetB > 0) ? min(100, ($real / $targetB) * 100) : 0;
            }
            $allKpiPercents[] = count($rowPercents) ? (array_sum($rowPercents) / count($rowPercents)) : 0;
        }
        $skorIntake = count($allKpiPercents) ? (array_sum($allKpiPercents) / count($allKpiPercents)) : 0;
        $nilaiIntake = ($skorIntake / 100) * 20;

        // CLOSING PAKET (10%)
        if ($role === 'cs-smi') {
            $nilaiClosing = 0;
        } else {
            $closing = SalesPlan::where('created_by', $csId)
                ->where('closing_paket', 1)
                ->whereYear('updated_at', $tahun)
                ->whereMonth('updated_at', $bulan)
                ->count();
            $nilaiClosing = min(10, $closing * 10);
        }

        // DATABASE BARU (10%)
        $dbBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();
        $nilaiDb = min(10, intval($dbBaru / 50 * 10));

        // MANUAL (20%)
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


    // ======================================================
    // EXPORT PDF
    // ======================================================
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $csId = $user->id;
        $namaUserData = $user->name;

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $role = $user->role;

        $nilai = $this->hitungTotalNilai($csId, $namaUserData, $bulan, $tahun, $role);
        
        // Pass complete data same as index
        // Since we need all variables, let's just fetch them or recalculate
        // For simplicity, we can just call some parts of index logic here
        // or just pass what hitungTotalNilai calculates.
        // Actually, to keep it simple, let's just use the result of hitungTotalNilai 
        // and add some basic info.
        
        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'totalNilai' => $nilai,
            // Add other variables if needed by template
        ];

        $pdf = PDF::loadView('admin.penilaian.pdf', $data);
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans'
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('penilaian_cs_' . now()->format('Ymd_His') . '.pdf');
    }
}
