<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SalesPlan;
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
        // Filter karyawan hanya Tursia dan Latifah
        $daftarCs = User::whereIn('name', ['Tursia', 'Latifah'])->orderBy('name')->get();
        return $this->getPenilaianData($request, $daftarCs, 'manager.penilaian-cs.index');
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

        // 1. TOTAL DATABASE (dari input Data baru bulan ini)
        // Asumsi: created_by di tabel 'data' menyimpan NAMA user
        $totalDatabase = \App\Models\Data::where('created_by', $namaUser)
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
        $nilaiOmset = round(($totalOmset / $targetOmset) * 100);

        // Variabel lain untuk view (opsional, sesuaikan dg view)
        $countTertarik      = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'tertarik')->count();
        $countMauTransfer   = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'mau_transfer')->count();
        $countSudahTransfer = $totalClosing;
        $countNo            = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'no')->count();
        $countCold          = SalesPlan::where('created_by', $userId)->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulan)->where('status', 'cold')->count();

        // Query Data Penilaian Manual
        $manual = \App\Models\PenilaianManual::where('user_id', $userId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

        return view('admin.penilaian-cs.index', compact(
            'bulan','tahun','userId','daftarCs',
            'totalDatabase','totalClosing',
            'persenClosing','closingTarget','totalOmset','nilaiOmset','targetOmset',
            'countTertarik','countMauTransfer','countSudahTransfer','countNo','countCold',
            'manual', 'routeAction'
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
