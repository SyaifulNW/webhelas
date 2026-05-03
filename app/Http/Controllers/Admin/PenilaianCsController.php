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
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $userName = trim(optional(auth()->user())->name);

        if ($userName === 'Linda') {
             $daftarCs = User::where(function($q) {
                                $q->whereIn('name', ['Felmi', 'Eko Sulis', 'Arifa', 'Nisa'])
                                  ->orWhereIn('role', ['cs-mbc', 'cs-smi']);
                             })
                             ->whereNotIn('name', ['Linda', 'Yasmin'])
                             ->orderBy('name')
                             ->get();
        } elseif ($userName === 'Agus Setyo') {
             $daftarCs = User::where('name', 'Agus Setyo')->get();
        } else {
             // Revised List for Admin & Others
             $daftarCs = User::whereIn('name', ['Arifa', 'Puput', 'Yasmin', 'Linda', 'Diah Putri', 'Nisa', 'Felmi', 'Rofi'])
                             ->orWhere('id', 14)
                             ->orderBy('name')
                             ->get();
        }

        return $this->getPenilaianData($request, $daftarCs, 'admin.penilaian-cs.index');
    }

    public function managerIndex(Request $request)
    {
        $userName = trim(optional(auth()->user())->name);

        // Custom Logic untuk Dropdown User
        $routeView = 'manager.penilaian-cs.index'; // Default view for manager
        
        if ($userName === 'Linda') {
             // Linda melihat: (Felmi, Eko Sulis, Arifa, Nisa) + Semua CS-MBC + Semua CS-SMI
             $daftarCs = User::where(function($q) {
                                $q->whereIn('name', ['Felmi', 'Eko Sulis', 'Arifa', 'Nisa'])
                                  ->orWhereIn('role', ['cs-mbc', 'cs-smi']);
                             })
                             ->whereNotIn('name', ['Linda', 'Yasmin'])
                             ->orderBy('name')
                             ->get();
             $routeView = 'admin.penilaian-cs.index'; // Tetap gunakan view admin jika diperlukan
        } elseif ($userName === 'Yasmin') {
            // Yasmin melihat 8 user spesifik
            $daftarCs = User::whereIn('name', ['Arifa', 'Puput', 'Yasmin', 'Linda', 'Diah Putri', 'Nisa', 'Felmi', 'Rofi'])
                             ->orWhere('id', 14)
                             ->orderBy('name')
                             ->get();
            $routeView = 'admin.penilaian-cs.index';
        } elseif ($userName === 'Agus Setyo') {
            // Agus Setyo view self
            $daftarCs = User::where('name', 'Agus Setyo')->get();
            $routeView = 'admin.penilaian-cs.index';
        } else {
            // Administrator / Other Managers -> See all CS and Marketing
            $daftarCs = User::whereIn('role', ['cs', 'cs-mbc', 'cs-smi', 'marketing'])
                ->orderBy('name')
                ->get();
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

        // Logic check if tanggal exists (from self view datepicker) and is not empty
        if ($request->input('tanggal')) {
            try {
                $dateParsed = \Carbon\Carbon::parse($request->input('tanggal'));
                $bulan = $dateParsed->format('m');
                $tahun = $dateParsed->format('Y');
            } catch (\Exception $e) {
                // Ignore invalid date, keep default bulan/tahun
            }
        }
        
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
        
        $targetUser = User::find($userId);
        $namaUser = trim($targetUser->name ?? '');

        // Check if user is Marketing (Felmi, Nisa, Eko Sulis)
        // Jika target adalah Marketing (Felmi, Nisa, Eko Sulis)
        if (in_array($namaUser, ['Felmi', 'Nisa', 'Eko Sulis'])) {
            return $this->getMarketingPenilaianData($request, $targetUser, $bulan, $tahun, $daftarCs, $routeAction);
        }

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

        // 2. Closing Paket (Bobot 10%)
        $closingPaketCount = SalesPlan::where('created_by', $userId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('closing_paket', 1)
            ->count();
        $targetClosingPaket = 1;
        $scoreClosingPaket = min(10, $closingPaketCount * 10);

        // 3. Database Baru (Bobot 10%)
        $targetDatabase = 50;
        $scoreDatabase = $targetDatabase > 0 ? min(10, round(($totalDatabase / $targetDatabase) * 10)) : 0;

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
        $dailyTotalKpi = $this->hitungDailyKpi($userId, $bulan, $tahun);
        $scoreIntake = round(($dailyTotalKpi / 100) * 20, 2);

        // TOTAL SCORE
        $grandTotal = $scoreOmset + $scoreClosingPaket + $scoreDatabase + $scoreManual + $scoreIntake;

        // Variabel lain untuk view
        $countTertarik      = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'tertarik')->count();
        $countMauTransfer   = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'mau_transfer')->count();
        $countSudahTransfer = $totalClosing;
        $countNo            = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'no')->count();
        $countCold          = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'cold')->count();

        // 6. HISTORY PENILAIAN BULANAN (Added for consistency with Marketing view)
        $historyNilai = array_fill(1, 12, 0);
        for ($m = 1; $m <= 12; $m++) {
             // To avoid recreating heavy logic, we might need a separate function.
             // But for now, let's assume valid function exists or we init with 0 and update later.
             // Actually, I will call $this->hitungTotalNilaiCS(...) which I will define next.
             $historyNilai[$m] = $this->hitungTotalNilaiCS($userId, $m, $tahun);
        }

        // Determine View
        $viewName = 'admin.penilaian-cs.index';
        
        // Jika Agus Setyo login dan melihat datanya sendiri -> Tampilkan Self View
        if ($namaUser === 'Agus Setyo' && optional(auth()->user())->name === 'Agus Setyo') {
            $viewName = 'admin.penilaian-cs.self';
        }

        return view($viewName, compact(
            'bulan','tahun','userId','daftarCs', 'namaUser',
            'totalDatabase','totalClosing',
            'persenClosing','closingTarget','totalOmset','nilaiOmset','targetOmset',
            'countTertarik','countMauTransfer','countSudahTransfer','countNo','countCold',
            'manual', 'routeAction',
            'scoreOmset', 'scoreClosingPaket', 'scoreDatabase', 'scoreManual', 'scoreIntake', 'grandTotal',
            'closingPaketCount', 'targetClosingPaket', 'targetDatabase', 'manualTotalSum',
            'dailyTotalKpi',
            'historyNilai'
        ) + ['listPesertaSMI' => \App\Models\PesertaSmi::all()]);
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

    // ======================================================
    // LOGIC PENILAIAN MARKETING (Felmi / Nisa)
    // ======================================================
    private function getMarketingPenilaianData(Request $request, $targetUser, $bulan, $tahun, $daftarCs, $routeAction)
    {
        $userId = $targetUser->id;
        $namaUserData = trim($targetUser->name); // Use Trim here
        $bulanNum = intval($bulan);

        // List CS for Leads Calculation
        $csSMI = ['Latifah', 'Tursia'];
        $csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

        if ($namaUserData === 'Eko Sulis') {
            // --- LOGIK KHUSUS EKO SULIS (ADVERTISING) ---
            
            // 0. ROAS (30%)
            $totalOmset = SalesPlan::with('data')
                ->whereYear('updated_at', $tahun)
                ->whereMonth('updated_at', $bulanNum)
                ->where('status', 'sudah_transfer')
                ->whereHas('data', function($q) {
                    $q->where('leads', 'LIKE', '%Iklan%');
                })
                ->sum('nominal');
            
            $biayaIklan = \App\Models\Setting::where('key', 'biaya_iklan')->value('value') ?? 5000000;
            $roas = $biayaIklan > 0 ? round($totalOmset / $biayaIklan, 2) : 0;
            $targetRoas = 10;
            $persenRoas = $targetRoas > 0 ? min(($roas / $targetRoas) * 100, 100) : 0;
            $nilaiRoas = $persenRoas; // Base 100
            $nilaiAkhirRoas = round(($persenRoas / 100) * 30, 2); // Bobot 30%

            // 1. LEADS MBC (30%) - Target 300
            $leadsMBC = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanNum)
                ->where('leads', 'like', '%Iklan%')
                ->whereIn('created_by', $csMBC)
                ->count();
            $targetLeadsMBC = 300;
            $persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
            $nilaiLeadsMBC = round(($persenLeadsMBC / 100) * 30, 2);

            // 2. LEADS SMI (30%) - Target 200
            $leadsSMI = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanNum)
                ->where('leads', 'like', '%Iklan%')
                ->whereIn('created_by', $csSMI)
                ->count();
            $targetLeadsSMI = 200;
            $persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
            $nilaiLeadsSMI = round(($persenLeadsSMI / 100) * 30, 2);

            // 3. PENILAIAN ATASAN (10%)
            // Reuse code below
            $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->first();
            $totalSumManual = $manual ? $manual->total_nilai : 0;
            $persenManual = $totalSumManual;
            $nilaiManualPart = round(($persenManual / 100) * 10, 2);

            $totalNilai = $nilaiAkhirRoas + $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;

        } else {
            // --- LOGIK DEFAULT MARKETING (FELMI / NISA) ---

            // 1. LEADS MBC (45%)
            $leadsMBC = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanNum)
                ->where('leads', 'like', '%Marketing%')
                ->whereIn('created_by', $csMBC)
                ->count();

            $targetLeadsMBC = 75;
            $persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
            $bobotLeadsMBC = 45;
            $nilaiLeadsMBC = round(($persenLeadsMBC / 100) * $bobotLeadsMBC, 2);

            // 2. LEADS SMI (45%)
            $leadsSMI = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanNum)
                ->where('leads', 'like', '%Marketing%')
                ->whereIn('created_by', $csSMI)
                ->count();

            $targetLeadsSMI = 50;
            $persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
            $bobotLeadsSMI = 45;
            $nilaiLeadsSMI = round(($persenLeadsSMI / 100) * $bobotLeadsSMI, 2);

            // 3. PENILAIAN ATASAN (10%)
            $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->first();

            $totalSumManual = $manual ? $manual->total_nilai : 0; 
            $persenManual = $totalSumManual; 
            $bobotManual = 10;
            $nilaiManualPart = round(($persenManual / 100) * $bobotManual, 2);

            // 4. TOTAL NILAI
            $totalNilai = $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;
        }

        // 5. HISTORY
        $historyNilai = array_fill(1, 12, 0);
        for ($m = 1; $m <= 12; $m++) {
            $historyNilai[$m] = $this->hitungTotalNilaiMarketing(
                $userId,
                $m,
                $tahun
            );
        }

        // 6. Daily Activity Score
        $dailyTotalKpi = $this->hitungDailyKpi($userId, $bulan, $tahun);

        // Return View Marketing
        // NOTE: View path is likely 'marketing.penilaian.index' but we need to pass sidebar variables?
        // Actually, since we are in Admin layout, we might need to verify if 'layouts.masteradmin' works fine.
        // Yes, 'marketing.penilaian.index' extends 'layouts.masteradmin'.
        // But we need to ensure the variable names match what the view expects.

        // Return View Marketing dengan variabel yang sesuai ekspektasi view
        return view('marketing.penilaian.index', compact(
            'bulan', 'tahun', 'targetUser', 'daftarCs', 'routeAction', 'userId',
            'totalNilai', 'nilaiManualPart', 'totalSumManual', 'persenManual',
            'historyNilai', 'manual', 'dailyTotalKpi'
        ) + [
            // Variabel untuk Eko Sulis
            'roas' => $roas ?? 0,
            'targetRoas' => $targetRoas ?? 10,
            'persenRoas' => $persenRoas ?? 0,
            'nilaiAkhirRoas' => $nilaiAkhirRoas ?? 0,
            'leadsAds' => $leadsMBC ?? 0,
            'targetLeadsAds' => $targetLeadsMBC ?? 0,
            'persenLeadsAds' => $persenLeadsMBC ?? 0,
            'nilaiLeadsAds' => $nilaiLeadsMBC ?? 0,
            'leadsFelmi' => $leadsSMI ?? 0,
            'targetLeadsFelmi' => $targetLeadsSMI ?? 0,
            'persenLeadsFelmi' => $persenLeadsSMI ?? 0,
            'nilaiLeadsFelmi' => $nilaiLeadsSMI ?? 0,
            'leadsNisa' => 0, // Placeholder jika ada logic tambahan
            'targetLeadsNisa' => 50,
            'persenLeadsNisa' => 0,
            'nilaiLeadsNisa' => 0,

            // Variabel untuk Felmi / Marketing Umum
            'leadsMBC' => $leadsMBC ?? 0,
            'targetLeadsMBC' => $targetLeadsMBC ?? 0,
            'persenLeadsMBC' => $persenLeadsMBC ?? 0,
            'nilaiLeadsMBC' => $nilaiLeadsMBC ?? 0,
            'leadsSMI' => $leadsSMI ?? 0,
            'targetLeadsSMI' => $targetLeadsSMI ?? 0,
            'persenLeadsSMI' => $persenLeadsSMI ?? 0,
            'nilaiLeadsSMI' => $nilaiLeadsSMI ?? 0,
            'leadsFelmiCount' => $leadsSMI ?? 0, // Untuk view Felmi
            'nilaiLeadsFelmiPart' => $nilaiLeadsSMI ?? 0
        ]);
    }

    private function hitungTotalNilaiMarketing($userId, $bulan, $tahun)
    {
        $csSMI = ['Latifah', 'Tursia'];
        $csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

        $userObj = User::find($userId);
        if ($userObj && trim($userObj->name) === 'Eko Sulis') {
             // 0. ROAS (30%)
            $totalOmset = SalesPlan::with('data')
                ->whereYear('updated_at', $tahun)
                ->whereMonth('updated_at', $bulan)
                ->where('status', 'sudah_transfer')
                ->whereHas('data', function($q) {
                    $q->where('leads', 'LIKE', '%Iklan%');
                })
                ->sum('nominal');
            $biayaIklan = \App\Models\Setting::where('key', 'biaya_iklan')->value('value') ?? 5000000;
            $roas = $biayaIklan > 0 ? round($totalOmset / $biayaIklan, 2) : 0;
            $targetRoas = 10;
            $persenRoas = $targetRoas > 0 ? min(($roas / $targetRoas) * 100, 100) : 0;
            $nilaiAkhirRoas = round(($persenRoas / 100) * 30, 2);

            // 1. LEADS MBC (30%)
            $leadsMBC = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulan)
                ->where('leads', 'like', '%Iklan%')
                ->whereIn('created_by', $csMBC)
                ->count();
            $targetLeadsMBC = 300;
            $persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
            $nilaiLeadsMBC = round(($persenLeadsMBC / 100) * 30, 2);

            // 2. LEADS SMI (30%)
            $leadsSMI = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulan)
                ->where('leads', 'like', '%Iklan%')
                ->whereIn('created_by', $csSMI)
                ->count();
            $targetLeadsSMI = 200;
            $persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
            $nilaiLeadsSMI = round(($persenLeadsSMI / 100) * 30, 2);

            // 3. MANUAL (10%)
            $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->first();
            $manualVal = $manual ? $manual->total_nilai : 0;
            $nilaiManualPart = round(($manualVal / 100) * 10, 2);

            return $nilaiAkhirRoas + $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;
        }

        // --- DEFAULT ---

        // 1. LEADS MBC (45%)
        $leadsMBC = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->where('leads', 'like', '%Marketing%')
            ->whereIn('created_by', $csMBC)
            ->count();
        $targetLeadsMBC = 75;
        $persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
        $nilaiLeadsMBC = round(($persenLeadsMBC / 100) * 45, 2);

        // 2. LEADS SMI (45%)
        $leadsSMI = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->where('leads', 'like', '%Marketing%')
            ->whereIn('created_by', $csSMI)
            ->count();
        $targetLeadsSMI = 50;
        $persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
        $nilaiLeadsSMI = round(($persenLeadsSMI / 100) * 45, 2);

        // 3. MANUAL (10%)
        $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();
        $manualVal = $manual ? $manual->total_nilai : 0;
        $nilaiManualPart = round(($manualVal / 100) * 10, 2);

        return $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;
    }

    private function hitungTotalNilaiCS($userId, $bulan, $tahun)
    {
        // 1. Omset (40%)
        $totalOmset = SalesPlan::where('created_by', $userId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('status', 'sudah_transfer')
            ->sum('nominal');
        $targetOmset = 50000000; 
        $scoreOmset = $targetOmset > 0 ? min(40, round(($totalOmset / $targetOmset) * 40)) : 0;

        // 2. Closing Paket (10%)
        $closingPaketCount = SalesPlan::where('created_by', $userId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('closing_paket', 1)
            ->count();
        $scoreClosingPaket = min(10, $closingPaketCount * 10);

        // 3. Database (10%)
        $userTarget = User::find($userId);
        $namaUser = $userTarget->name ?? '';
        $totalDatabase = Data::where('created_by', $namaUser)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();
        $targetDatabase = 50;
        $scoreDatabase = $targetDatabase > 0 ? min(10, round(($totalDatabase / $targetDatabase) * 10)) : 0;

        // 4. Manual (20%)
        $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
        $scoreManual = 0;
        if ($manual) {
             $scoreManual = round(($manual->total_nilai / 100) * 20);
        }

        // 5. Intake (20%)
        $dailyKpi = $this->hitungDailyKpi($userId, $bulan, $tahun);
        $scoreIntake = round(($dailyKpi / 100) * 20, 2);

        return $scoreOmset + $scoreClosingPaket + $scoreDatabase + $scoreManual + $scoreIntake;
    }

    private function hitungDailyKpi($userId, $bulan, $tahun)
    {
        $targetUser = User::find($userId);
        $targetName = $targetUser ? trim($targetUser->name) : '';

        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $hariKerja = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $day = Carbon::create($tahun, $bulan, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) {
                $hariKerja++;
            }
        }

        // Ambil aktivitas dan hitung KPI
        $activityQuery = Activity::with('kategori')->orderBy('categories_id');
        if ($targetName === 'Nisa') {
            $activityQuery->whereIn('categories_id', [6, 7]);
        } else {
            $activityQuery->whereIn('categories_id', [1, 2, 3, 4, 5]);
        }
        $activities = $activityQuery->get()->groupBy('categories_id');

        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
            'A. Aktivitas Harian (NON-NEGOTIABLE)' => 50,
            'B. Aktivitas Mingguan' => 50,
        ];
        
        $dailyTotalKpi = 0;
        
        foreach ($activities as $kategoriId => $list) {
            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
            $activityPercents = [];

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulanan = $targetDaily * $hariKerja;

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
            
            // Jika kategori 6-7 (Nisa), dan kita tidak punya bobot di mapping, kita asumsikan bobot proporsional atau 0 jika tidak diset.
            // Namun user biasanya ingin 100% KPI Harian dihitung.
            // Sejauh ini kita pakai mapping yang ada.
            
            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;
            $dailyTotalKpi += $nilaiKategori;
        }

        return $dailyTotalKpi;
    }

    public function exportPdf(Request $request)
    {
        $data = json_decode($request->pdf_data, true);

        // Basic data
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $date = Carbon::parse($tanggal);
        $bulan = $date->format('m');
        $tahun = $date->format('Y');

        // User info (Agus Setyo)
        $userId = auth()->id();
        $targetUser = User::find($userId);
        $namaUser = trim($targetUser->name ?? '');

        // Fetch same data as index
        // ... (This might be redundant if we just print the form, but let's pass listPesertaSMI for the checklist)
        $listPesertaSMI = \App\Models\PesertaSmi::all();
        
        $pdf = \PDF::loadView('admin.penilaian-cs.pdf', compact('data', 'tanggal', 'listPesertaSMI', 'namaUser'));
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans'
        ]);
        return $pdf->download('Daily_Activity_' . $tanggal . '.pdf');
    }
}
