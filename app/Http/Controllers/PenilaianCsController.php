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
    $request->validate([
        'bulan' => 'nullable|in:01,02,03,04,05,06,07,08,09,10,11,12',
        'tahun' => 'nullable|integer|min:2023|max:' . date('Y'),
        'user_id' => 'nullable|exists:users,id',
    ]);

    $bulan  = $request->bulan ?? date('m');
    $tahun  = $request->tahun ?? date('Y');
    $userId = $request->user_id ?? auth()->id();

    $daftarCs = User::orderBy('name')->get();

    $query = SalesPlan::where('created_by', $userId)
        ->whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $bulan);

    // STATUS COUNTS
    $countTertarik      = (clone $query)->where('status', 'tertarik')->count();
    $countMauTransfer   = (clone $query)->where('status', 'mau_transfer')->count();
    $countSudahTransfer = (clone $query)->where('status', 'sudah_transfer')->count();
    $countNo            = (clone $query)->where('status', 'no')->count();
    $countCold          = (clone $query)->where('status', 'cold')->count();

    // PERHITUNGAN
    $totalDatabase      = $countTertarik + $countMauTransfer + $countSudahTransfer + $countNo + $countCold;
    $totalClosing       = $countSudahTransfer;
    $totalTidakClosing  = $totalDatabase - $totalClosing;
    $databaseBaru       = $countTertarik + $countMauTransfer + $countCold;

    $persenClosing      = $totalDatabase > 0 ? round(($totalClosing / $totalDatabase) * 100) : 0;
    $closingTarget      = round(($totalClosing / 30) * 100);

    // OMSET
    $totalOmset         = (clone $query)->where('status', 'sudah_transfer')->sum('nominal');
    $nilaiOmset         = round(($totalOmset / 50000000) * 100);

    // Query Data Penilaian Manual
    $manual = \App\Models\PenilaianManual::where('user_id', $userId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

    return view('admin.penilaian-cs.index', compact(
        'bulan','tahun','userId','daftarCs',
        'countTertarik','countMauTransfer','countSudahTransfer','countNo','countCold',
        'totalDatabase','totalClosing','totalTidakClosing','databaseBaru',
        'persenClosing','closingTarget','totalOmset','nilaiOmset',
        'manual'
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
