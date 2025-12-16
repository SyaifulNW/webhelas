<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalesPlan;
use App\Models\Data;
use App\Models\PenilaianManual;
use App\Models\Activity;
use App\Models\DailyActiviti;
use Carbon\Carbon;

class PenilaianCsController extends Controller
{
    public function index(Request $request)
    {
        $daftarCs = User::orderBy('name')->get();
        return $this->getPenilaianData($request, $daftarCs, 'admin.penilaian-cs.index');
    }

    public function managerIndex(Request $request)
    {
        $userName = auth()->user()->name;

        // Custom Logic untuk Dropdown User
        $routeView = 'manager.penilaian-cs.index'; // Default view for manager
        
        if (in_array($userName, ['Linda', 'Yasmin'])) {
            // Linda & Yasmin bisa melihat SEMUA user
            $daftarCs = User::orderBy('name')->get();
            // Use admin route for Linda and Yasmin
            $routeView = 'admin.penilaian-cs.index';
        } elseif ($userName === 'Agus Setyo') {
            // Agus Setyo hanya Tursia dan Latifah
            $daftarCs = User::whereIn('name', ['Tursia', 'Latifah'])->orderBy('name')->get();
        } else {
            // Default Fallback (jika ada manager lain) -> Filter Tursia & Latifah
            $daftarCs = User::whereIn('name', ['Tursia', 'Latifah'])->orderBy('name')->get();
        }

        return $this->getPenilaianData($request, $daftarCs, $routeView);
    }

    private function getPenilaianData(Request $request, $daftarCs, $routeAction)
    {
        $request->validate([
            'bulan' => 'nullable|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'tahun' => 'nullable|integer|min:2023|max:' . date('Y'),
            'user_id' => 'nullable|exists:users,id',
        ]);

        $bulan  = $request->bulan ?? date('m');
        $tahun  = $request->tahun ?? date('Y');
        
        // Jika user_id tidak ada di request, dan ada daftarCs, gunakan id pertama dari daftarCs sebagai default
        if ($request->has('user_id')) {
            $userId = $request->user_id;
        } else {
             // Default ke user login jika dia ada di daftarCS, jika tidak ambil yg pertama dari list
             $userId = auth()->id();
             // Cek apakah auth id ada di daftarCs
             if (!$daftarCs->contains('id', $userId)) {
                 $userId = $daftarCs->first()->id ?? $userId;
             }
        }

        // $daftarCs = User::orderBy('name')->get(); // Sudah dipassing via parameter

        $userTarget = User::find($userId);
        $namaUser = $userTarget->name ?? '';

        // Initialize variables to prevent undefined error
        $scoreOmset = 0;
        $scoreClosingPaket = 0;
        $scoreDatabase = 0;
        $scoreManual = 0;
        $grandTotal = 0;
        $manualTotalSum = 0;
        $closingPaketCount = 0; 
        
        // 1. TOTAL DATABASE (dari input Data baru bulan ini)
        // Asumsi: created_by di tabel 'data' menyimpan NAMA user
        $totalDatabase = Data::where('created_by', $namaUser)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();

        // 2. TOTAL CLOSING (SalesPlan kategori sudah transfer)
        $totalClosing = SalesPlan::where('created_by', $userId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('status', 'sudah_transfer')
            ->count();

        // 3. PERSENTASE CLOSING
        $persenClosing = $totalDatabase > 0 ? round(($totalClosing / $totalDatabase) * 100) : 0;

        // 4. CLOSING TARGET ACHIEVEMENT (Target misal 30)
        $targetClosingBulanan = 30; // Bisa disesuaikan
        $closingTarget = round(($totalClosing / $targetClosingBulanan) * 100);

        // 5. PENCAPAIAN OMSET
        $totalOmset = SalesPlan::where('created_by', $userId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('status', 'sudah_transfer')
            ->sum('nominal');
        
        $targetOmset = 50000000; // 50 Juta
        $nilaiOmset = $targetOmset > 0 ? min(100, round(($totalOmset / $targetOmset) * 100)) : 0;
        
        // --- SCORE CALCULATIONS ---

        // 1. Omset (Bobot 40%)
        $scoreOmset = $targetOmset > 0 ? min(40, round(($totalOmset / $targetOmset) * 40)) : 0;

        // 2. Closing Paket (Bobot 20%)
        $closingPaketCount = SalesPlan::where('created_by', $userId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('closing_paket', 1)
            ->count();
        $targetClosingPaket = 1;
        $scoreClosingPaket = min(20, $closingPaketCount * 20);

        // 3. Database Baru (Bobot 20%)
        $targetDatabase = 50;
        $scoreDatabase = $targetDatabase > 0 ? min(20, round(($totalDatabase / $targetDatabase) * 20)) : 0;

        // 4. Manual (Bobot 20%)
        $scoreManual = 0;
        $manualTotalSum = 0;
        
        // Query Data Penilaian Manual
        $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

        if ($manual) {
             $scoreManual = round(($manual->total_nilai / 100) * 20);
             $manualTotalSum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
        }

        // 5. Daily Activity Score Logic (Reused from DailyController)
        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $hariKerja = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $day = Carbon::create($tahun, $bulan, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) {
                $hariKerja++;
            }
        }

        // Ambil aktivitas dan hitung KPI
        $activities = Activity::with('kategori')->orderBy('categories_id')->get()->groupBy('categories_id');
        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
        ];
        
        $dailyTotalKpi = 0;
        
        foreach ($activities as $kategoriId => $list) {
            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
            $activityPercents = []; // tiap aktivitas -> persentase cap 100%

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulanan = $targetDaily * $hariKerja;

                // total realisasi bulan ini untuk aktivitas ini (user spesifik)
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

            // skor kategori = rata-rata persentase aktivitas di kategori
            $skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
            // ambil bobot kategori dari mapping
            $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
            // nilai kategori = (skorKategori% / 100) * bobotKategori
            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;
            
            $dailyTotalKpi += $nilaiKategori;
        }

        // TOTAL SCORE
        $grandTotal = $scoreOmset + $scoreClosingPaket + $scoreDatabase + $scoreManual;

        // Variabel lain untuk view
        $countTertarik      = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'tertarik')->count();
        $countMauTransfer   = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'mau_transfer')->count();
        $countSudahTransfer = $totalClosing;
        $countNo            = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'no')->count();
        $countCold          = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'cold')->count();

        return view('admin.penilaian-cs.index', compact(
            'bulan','tahun','userId','daftarCs', 'namaUser',
            'totalDatabase','totalClosing',
            'persenClosing','closingTarget','totalOmset','nilaiOmset','targetOmset',
            'countTertarik','countMauTransfer','countSudahTransfer','countNo','countCold',
            'manual', 'routeAction',
            'scoreOmset', 'scoreClosingPaket', 'scoreDatabase', 'scoreManual', 'grandTotal',
            'closingPaketCount', 'targetClosingPaket', 'targetDatabase', 'manualTotalSum',
            'dailyTotalKpi'
        ));
    }

public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required',
        'bulan' => 'required',
        'tahun' => 'required',
        'kerajinan' => 'required|integer|min:0|max:100',
        'kerjasama' => 'required|integer|min:0|max:100',
        'tanggung_jawab' => 'required|integer|min:0|max:100',
        'inisiatif' => 'required|integer|min:0|max:100',
        'komunikasi' => 'required|integer|min:0|max:100',
    ]);

    // Hitung rata-rata atau total
    $total = ($request->kerajinan + $request->kerjasama + $request->tanggung_jawab + $request->inisiatif + $request->komunikasi) / 5;

    \App\Models\PenilaianManual::updateOrCreate(
        [
            'user_id' => $request->user_id,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ],
        [
            'kerajinan' => $request->kerajinan,
            'kerjasama' => $request->kerjasama,
            'tanggung_jawab' => $request->tanggung_jawab,
            'inisiatif' => $request->inisiatif,
            'komunikasi' => $request->komunikasi,
            'total_nilai' => $total,
            'catatan' => $request->catatan,
            'created_by' => auth()->id(),
        ]
    );

    return redirect()->back()->with('success', 'Penilaian berhasil disimpan.');
}

}
