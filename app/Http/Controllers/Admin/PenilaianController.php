<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Data;
use App\Models\Kelas;
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
        $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulanNum)
            ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulanNum) {
                $q->where('created_by', $csId)
                  ->whereYear('updated_at', $tahun)
                  ->whereMonth('updated_at', $bulanNum);
            }])
            ->get();

        $kelasOmsetFiltered = $kelasOmset->map(function ($kelas) {
            $omset = $kelas->salesplans->sum('nominal');
            $target = 25000000;

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
        // 2. NILAI OMSET
        // ============================
        $nilaiOmset = min(40, intval($totalOmset / 50000000 * 40));

        // ============================
        // 3. CLOSING PAKET
        // ============================
        $closingPaket = SalesPlan::where('created_by', $csId)
            ->where('closing_paket', 1)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulanNum)
            ->count();

        $nilaiClosingPaket = min(30, $closingPaket * 15);

        // ============================
        // 4. DATABASE BARU
        // ============================
        $databaseBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->count();

        $nilaiDatabaseBaru = min(30, intval($databaseBaru / 50 * 30));

        // ============================
        // 5. TOTAL NILAI
        // ============================
        $totalNilai = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru;

        // ============================
        // 6. CHART 6 BULAN (REAL)
        // ============================
        $labels = [];
        $scores = [];

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $labels[] = $dt->format('M Y');

            $scores[] = $this->hitungTotalNilai(
                $csId,
                $namaUserData,
                $dt->month,
                $dt->year
            );
        }

        // ============================
        // 7. HISTORY NILAI 12 BULAN
        // ============================
        $historyNilai = array_fill(1, 12, 0);

        for ($m = 1; $m <= 12; $m++) {
            $historyNilai[$m] = $this->hitungTotalNilai(
                $csId,
                $namaUserData,
                $m,
                $tahun
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
            'totalNilai',
            'labels',
            'scores',
            'historyNilai',
            'kelasOmsetFiltered'
        ));
    }


    // ======================================================
    // FUNGSI HITUNG TOTAL NILAI (REUSABLE)
    // ======================================================
    private function hitungTotalNilai($csId, $namaUserData, $bulan, $tahun)
    {
        // OMSET
        $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                $q->where('created_by', $csId)
                  ->whereYear('updated_at', $tahun)
                  ->whereMonth('updated_at', $bulan);
            }])
            ->get();

        $totalOmset = $kelasOmset->sum(fn ($k) => $k->salesplans->sum('nominal'));
        $nilaiOmset = min(40, intval($totalOmset / 50000000 * 40));

        // CLOSING PAKET
        $closing = SalesPlan::where('created_by', $csId)
            ->where('closing_paket', 1)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->count();

        $nilaiClosing = min(30, $closing * 15);

        // DATABASE BARU
        $dbBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();

        $nilaiDb = min(30, intval($dbBaru / 50 * 30));

        return $nilaiOmset + $nilaiClosing + $nilaiDb;
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

        $nilai = $this->hitungTotalNilai($csId, $namaUserData, $bulan, $tahun);

        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'totalNilai' => $nilai
        ];

        $pdf = PDF::loadView('admin.penilaian.pdf', $data)
                ->setPaper('a4', 'portrait');

        return $pdf->download('penilaian_cs_' . now()->format('Ymd_His') . '.pdf');
    }
}
