<?php

namespace App\Http\Controllers\Marketing;

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
        $userId = $user->id;
        $namaUserData = $user->name;

        // ============================
        // FILTER BULAN & TAHUN
        // ============================
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $bulanNum = intval($bulan);

        // ============================
        // 1. LEADS MBC (45%)
        // ============================
        // List CS
        $csSMI = ['Latifah', 'Tursia'];
        $csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

        // ============================
        // 1. LEADS MBC (45%)
        // ============================
        // Logic: Leads from CS MBC with source 'Marketing'
        $leadsMBC = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->where('leads', 'like', '%Marketing%')
            ->whereIn('created_by', $csMBC)
            ->count();

        $targetLeadsMBC = 75;
        $persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
        $bobotLeadsMBC = 45;
        $nilaiLeadsMBC = round(($persenLeadsMBC / 100) * $bobotLeadsMBC, 2);


        // ============================
        // 2. LEADS SMI (45%)
        // ============================
        // Logic: Leads from CS SMI with source 'Marketing'
        $leadsSMI = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->where('leads', 'like', '%Marketing%')
            ->whereIn('created_by', $csSMI)
            ->count();

        $targetLeadsSMI = 50;
        $persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
        $bobotLeadsSMI = 45;
        $nilaiLeadsSMI = round(($persenLeadsSMI / 100) * $bobotLeadsSMI, 2);


        // ============================
        // 3. PENILAIAN ATASAN (10%)
        // ============================
        $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                    ->where('bulan', $bulanNum)
                    ->where('tahun', $tahun)
                    ->first();

        $totalSumManual = $manual ? $manual->total_nilai : 0; 
        
        $persenManual = $totalSumManual; // Assumed 0-100
        $bobotManual = 10;
        $nilaiManualPart = round(($persenManual / 100) * $bobotManual, 2);


        // ============================
        // 4. TOTAL NILAI
        // ============================
        $totalNilai = $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;

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
                $userId,
                $namaUserData,
                $dt->month,
                $dt->year,
                $role
            );
        }

        $historyNilai = array_fill(1, 12, 0);

        for ($m = 1; $m <= 12; $m++) {
            $historyNilai[$m] = $this->hitungTotalNilai(
                $userId,
                $namaUserData,
                $m,
                $tahun,
                $role
            );
        }

        // ============================
        // 8. KIRIM KE VIEW
        // ============================
        return view('marketing.penilaian.index', compact(
            'bulan',
            'tahun',
            'leadsMBC',
            'targetLeadsMBC',
            'persenLeadsMBC',
            'nilaiLeadsMBC',
            'leadsSMI',
            'targetLeadsSMI',
            'persenLeadsSMI',
            'nilaiLeadsSMI',
            'totalNilai',
            'nilaiManualPart',
            'totalSumManual',
            'persenManual',
            'labels',
            'scores',
            'historyNilai',
            'manual'
        ));
    }


    // ======================================================
    // FUNGSI HITUNG TOTAL NILAI (REUSABLE)
    // ======================================================
    private function hitungTotalNilai($userId, $namaUserData, $bulan, $tahun, $role)
    {
        // List CS
        $csSMI = ['Latifah', 'Tursia'];
        $csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

        // 1. LEADS MBC (45%)
        $leadsMBC = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->where('leads', 'like', '%Marketing%')
            ->whereIn('created_by', $csMBC)
            ->count();
        $targetLeadsMBC = 150;
        $persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
        $nilaiLeadsMBC = round(($persenLeadsMBC / 100) * 45, 2);

        // 2. LEADS SMI (45%)
        $leadsSMI = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->where('leads', 'like', '%Marketing%')
            ->whereIn('created_by', $csSMI)
            ->count();
        $targetLeadsSMI = 100;
        $persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
        $nilaiLeadsSMI = round(($persenLeadsSMI / 100) * 45, 2);

        // 3. MANUAL (10%)
        $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();

        // Use total_nilai directly if available, else sum
        $manualVal = $manual ? $manual->total_nilai : 0;
        $nilaiManualPart = round(($manualVal / 100) * 10, 2);

        return $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;
    }


    // ======================================================
    // EXPORT PDF
    // ======================================================
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $namaUserData = $user->name;

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $role = $user->role;

        $nilai = $this->hitungTotalNilai($userId, $namaUserData, $bulan, $tahun, $role);

        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'totalNilai' => $nilai
        ];

        // Ensure this view exists or use Admin's if generic
        $pdf = PDF::loadView('admin.penilaian.pdf', $data)
                ->setPaper('a4', 'portrait');

        return $pdf->download('penilaian_marketing_' . now()->format('Ymd_His') . '.pdf');
    }
}
