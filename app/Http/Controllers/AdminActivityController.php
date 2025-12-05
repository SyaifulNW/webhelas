<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyActiviti;
use App\Models\Activity;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminActivityController extends Controller
{
   public function index(Request $request)
{
    $bulan = $request->input('bulan', now()->format('Y-m'));
    $csId = $request->input('cs_id');
    $user = auth()->user();

    // ==============================
    // 🔹 1. Tentukan daftar CS yang bisa dilihat
    // ==============================
    $csQuery = User::query();

    if ($user->role === 'administrator') {
        $csQuery->where('role', 'cs-mbc');
    } elseif ($user->name === 'Agus Setyo') {
        $csQuery->whereIn('name', ['Tursia', 'Latifah']);
    } else {
        // CS biasa hanya bisa melihat dirinya sendiri
        $csQuery->where('id', $user->id);
    }

    $csList = $csQuery->orderBy('name')->get();

    // ==============================
    // 🔹 2. Ambil data aktivitas sesuai bulan dan CS
    // ==============================
    $query = DailyActiviti::with(['activity', 'user'])
        ->whereMonth('tanggal', Carbon::parse($bulan)->month)
        ->whereYear('tanggal', Carbon::parse($bulan)->year);

    if ($csId) {
        $query->where('user_id', $csId);
    }

    $dailyData = $query->get();
    $dataPerCs = $dailyData->groupBy('user_id');

    // ==============================
    // 🔹 3. Kirim ke view
    // ==============================
    
    
    return view('admin.activity-cs.index', compact('bulan', 'csList', 'dataPerCs', 'csId'));
}

    public function viewPdfBulanan(Request $request)
    {
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

        $allActivities = Activity::all();
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

            $totalRealisasi = $activities->where('activity_id', $act->id)->sum('realisasi');
            $persentase = $act->target_bulanan > 0
                ? min(100, ($totalRealisasi / $act->target_bulanan) * 100)
                : 0;
            $nilai = ($persentase / 100) * $act->bobot;

            $harian = [];
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $harian[$d] = $activities
                    ->where('activity_id', $act->id)
                    ->where('tanggal', $carbonBulan->format("Y-m-") . str_pad($d, 2, '0', STR_PAD_LEFT))
                    ->sum('realisasi');
            }

            $categories[$kategori][] = [
                'nama' => $act->nama,
                'target_daily' => $act->target_daily,
                'target_bulanan' => $act->target_bulanan,
                'bobot' => $act->bobot,
                'real' => $totalRealisasi,
                'nilai' => round($nilai, 2),
                'harian' => $harian
            ];

            $total[$kategori]['target_daily'] += $act->target_daily;
            $total[$kategori]['target_bulanan'] += $act->target_bulanan;
            $total[$kategori]['bobot'] += $act->bobot;
            $total[$kategori]['real'] += $totalRealisasi;
            $total[$kategori]['nilai'] += $nilai;
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $total[$kategori]['harian'][$d] = ($total[$kategori]['harian'][$d] ?? 0) + $harian[$d];
            }
        }

        $pdf = Pdf::loadView('admin.dailyactivity.pdf', [
            'categories' => $categories,
            'total' => $total,
            'jumlahHari' => $jumlahHari,
            'bulan' => $carbonBulan->translatedFormat('F Y'),
            'csName' => $cs->name,
            'downloadDate' => now()->translatedFormat('d F Y H:i')
        ])->setPaper('F4', 'landscape');

        // Stream PDF agar langsung tampil di browser
        return $pdf->stream("Laporan_Activity_KPI_{$bulan}_{$cs->name}.pdf");
    }
}
