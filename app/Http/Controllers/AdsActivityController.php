<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdsPerformance;
use App\Models\Kelas;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdsActivityController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $status = $request->input('status'); // 'running', 'not_running', or null (all)
        $userId = auth()->id();
        
        if ($bulan !== 'all') {
            $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $endOfMonth = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            // 1. Ambil daftar kelas yang aktif pada bulan/tahun tersebut (overlap)
            $targetClasses = Kelas::where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('tanggal_selesai', [$startOfMonth, $endOfMonth])
                ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                    $q2->where('tanggal_mulai', '<=', $startOfMonth)
                        ->where('tanggal_selesai', '>=', $endOfMonth);
                });
            })->get();

            // 2. Pastikan setiap kelas tersebut punya record AdsPerformance untuk user ini
            foreach ($targetClasses as $kelas) {
                // ... (existing logic for counting leads/sales) ...
                $leadsCount = \App\Models\Data::where('kelas_id', $kelas->id)
                    ->where('leads', 'Iklan')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();

                $salesData = \App\Models\SalesPlan::where('kelas_id', $kelas->id)
                    ->where('status', 'sudah_transfer')
                    ->whereHas('data', function($q) {
                        $q->where('leads', 'Iklan');
                    })
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->selectRaw('COUNT(*) as total_closing, SUM(nominal) as total_omset')
                    ->first();

                $currentAds = AdsPerformance::where([
                    'user_id'  => $userId,
                    'kelas_id' => $kelas->id,
                    'bulan'    => $bulan,
                    'tahun'    => $tahun,
                ])->first();
                $budget = $currentAds->budget_iklan ?? 0;
                $totalLeads = $leadsCount;
                $jumlahClosing = $salesData->total_closing ?? 0;
                $totalOmset = $salesData->total_omset ?? 0;

                $convRate = ($totalLeads > 0) ? ($jumlahClosing / $totalLeads) * 100 : 0;
                $cpaRate = ($totalOmset > 0) ? ($budget / $totalOmset) * 100 : 0;
                $roas = ($budget > 0) ? ($totalOmset / $budget) : 0;
                $cpl = ($totalLeads > 0) ? ($budget / $totalLeads) : 0;

                AdsPerformance::updateOrCreate(
                    [
                        'user_id'  => $userId,
                        'kelas_id' => $kelas->id,
                        'bulan'    => $bulan,
                        'tahun'    => $tahun,
                    ],
                    [
                        'tanggal_kelas'  => $kelas->tanggal_mulai,
                        'total_leads'    => $totalLeads,
                        'jumlah_closing' => $jumlahClosing,
                        'omset'          => $totalOmset,
                        'conv_rate'      => $convRate,
                        'cpa'            => $cpaRate,
                        'roas'           => $roas,
                        'cpl'            => $cpl,
                    ]
                );
            }

            // 3. Ambil data performance yang sesuai
            $query = AdsPerformance::with('kelas')
                ->where('user_id', $userId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->where(function($q) use ($targetClasses) {
                    $q->whereIn('kelas_id', $targetClasses->pluck('id'))
                      ->orWhereNull('kelas_id');
                });

            if ($status === 'running') {
                $query->where('is_running', true);
            } elseif ($status === 'not_running') {
                $query->where('is_running', false);
            }

            $adsPerformances = $query->orderBy('is_running', 'desc')
                ->orderBy('tanggal_kelas', 'asc')
                ->get();

            // Append Realisasi from LabaRugi
            foreach ($adsPerformances as $item) {
                $item->realisasi = \App\Models\LabaRugi::where('bulan', str_pad($bulan, 2, '0', STR_PAD_LEFT))
                    ->where('tahun', $tahun)
                    ->where('type', 'biaya')
                    ->whereIn('parent_keterangan', ['Biaya Pengeluaran Kelas', 'Biaya Iklan'])
                    ->where('keterangan', 'like', '%' . ($item->kelas->nama_kelas ?? $item->manual_name ?? '') . '%')
                    ->sum('jumlah');
            }
        } else {
            $query = AdsPerformance::with('kelas')
                ->where('user_id', $userId)
                ->where('tahun', $tahun)
                ->selectRaw('
                    MAX(id) as id,
                    kelas_id,
                    MAX(manual_name) as manual_name,
                    MIN(bulan) as bulan,
                    MIN(tanggal_kelas) as tanggal_kelas,
                    MIN(tanggal_set) as tanggal_set,
                    SUM(total_leads) as total_leads,
                    SUM(jumlah_closing) as jumlah_closing,
                    SUM(omset) as omset,
                    AVG(conv_rate) as conv_rate,
                    AVG(cpa) as cpa,
                    AVG(roas) as roas,
                    SUM(budget_iklan) as budget_iklan,
                    SUM(pengajuan_budget) as pengajuan_budget,
                    AVG(ctr) as ctr,
                    AVG(cpl) as cpl,
                    MAX(CASE WHEN is_running = 1 THEN 1 ELSE 0 END) as is_running
                ')
                ->groupBy('kelas_id', 'manual_name');

            if ($status === 'running') {
                $query->having('is_running', '=', 1);
            } elseif ($status === 'not_running') {
                $query->having('is_running', '=', 0);
            }

            $adsPerformances = $query->orderBy('is_running', 'desc')
                ->orderBy('tanggal_kelas', 'asc')
                ->get();

            // Append Realisasi from LabaRugi (Summed for the year)
            foreach ($adsPerformances as $item) {
                $item->realisasi = \App\Models\LabaRugi::where('tahun', $tahun)
                    ->where('type', 'biaya')
                    ->whereIn('parent_keterangan', ['Biaya Pengeluaran Kelas', 'Biaya Iklan'])
                    ->where('keterangan', 'like', '%' . ($item->kelas->nama_kelas ?? $item->manual_name ?? '') . '%')
                    ->sum('jumlah');
            }
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $years = range(now()->year - 2, now()->year + 1);

        return view('admin.adsbeauty.index', compact(
            'adsPerformances', 'bulan', 'tahun', 'months', 'years', 'status'
        ));
    }

    public function updateAds(Request $request)
    {
        $id = $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');
        $bulanInput = $request->input('bulan');
        $tahunInput = $request->input('tahun');
        $userId = auth()->id();

        // Target month/year
        $bulan = ($bulanInput === 'all') ? now()->month : $bulanInput;
        $tahun = ($tahunInput === 'all') ? now()->year : $tahunInput;

        // Handle boolean fields
        if ($field === 'is_running') {
            $value = ($value === '1' || $value === true || $value === 'true');
        }

        $updateData = [
            'user_id' => $userId,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            $field    => $value,
        ];

        $ads = AdsPerformance::updateOrCreate(
            ['id' => $id ?: null],
            $updateData
        );

        return response()->json(['id' => $ads->id]);
    }

    public function destroyAds($id)
    {
        AdsPerformance::destroy($id);
        return response()->json(['success' => true]);
    }

    public function getKelasInfo($id)
    {
        $kelas = Kelas::find($id);
        return response()->json($kelas);
    }

    public function exportPdf(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $status = $request->input('status');
        
        $userId = $request->input('user_id', auth()->id());
        $user = \App\Models\User::find($userId);
        $userName = $user ? $user->name : 'Unknown';

        if ($bulan !== 'all') {
            $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $endOfMonth = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $targetClasses = Kelas::where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                ->orWhereBetween('tanggal_selesai', [$startOfMonth, $endOfMonth])
                ->orWhere(function($q2) use ($startOfMonth, $endOfMonth) {
                    $q2->where('tanggal_mulai', '<=', $startOfMonth)
                        ->where('tanggal_selesai', '>=', $endOfMonth);
                });
            })->get();

            $query = AdsPerformance::with('kelas')
                ->where('user_id', $userId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->whereIn('kelas_id', $targetClasses->pluck('id'));

            if ($status === 'running') {
                $query->where('is_running', true);
            } elseif ($status === 'not_running') {
                $query->where('is_running', false);
            }

            $adsPerformances = $query->orderBy('is_running', 'desc')
                ->orderBy('tanggal_kelas', 'asc')
                ->get();

            // Append Realisasi from LabaRugi
            foreach ($adsPerformances as $item) {
                $item->realisasi = \App\Models\LabaRugi::where('bulan', str_pad($bulan, 2, '0', STR_PAD_LEFT))
                    ->where('tahun', $tahun)
                    ->where('type', 'biaya')
                    ->whereIn('parent_keterangan', ['Biaya Pengeluaran Kelas', 'Biaya Iklan'])
                    ->where('keterangan', 'like', '%' . ($item->kelas->nama_kelas ?? '') . '%')
                    ->sum('jumlah');
            }

            $monthName = Carbon::create()->month($bulan)->translatedFormat('F');
        } else {
            $query = AdsPerformance::with('kelas')
                ->where('user_id', $userId)
                ->where('tahun', $tahun)
                ->selectRaw('
                    kelas_id,
                    MIN(bulan) as bulan,
                    MIN(tanggal_kelas) as tanggal_kelas,
                    MIN(tanggal_set) as tanggal_set,
                    SUM(total_leads) as total_leads,
                    SUM(jumlah_closing) as jumlah_closing,
                    SUM(omset) as omset,
                    AVG(conv_rate) as conv_rate,
                    AVG(cpa) as cpa,
                    AVG(roas) as roas,
                    SUM(budget_iklan) as budget_iklan,
                    SUM(pengajuan_budget) as pengajuan_budget,
                    AVG(ctr) as ctr,
                    AVG(cpl) as cpl,
                    MAX(CASE WHEN is_running = 1 THEN 1 ELSE 0 END) as is_running
                ')
                ->groupBy('kelas_id');

            if ($status === 'running') {
                $query->having('is_running', '=', 1);
            } elseif ($status === 'not_running') {
                $query->having('is_running', '=', 0);
            }

            $adsPerformances = $query->orderBy('is_running', 'desc')
                ->orderBy('tanggal_kelas', 'asc')
                ->get();

            // Append Realisasi from LabaRugi (Summed for the year)
            foreach ($adsPerformances as $item) {
                $item->realisasi = \App\Models\LabaRugi::where('tahun', $tahun)
                    ->where('type', 'biaya')
                    ->whereIn('parent_keterangan', ['Biaya Pengeluaran Kelas', 'Biaya Iklan'])
                    ->where('keterangan', 'like', '%' . ($item->kelas->nama_kelas ?? '') . '%')
                    ->sum('jumlah');
            }

            $monthName = 'Semua Bulan';
        }

        $pdf = Pdf::loadView('admin.adsbeauty.pdf', compact(
            'adsPerformances', 'bulan', 'tahun', 'monthName', 'userName'
        ));

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download("Activity_Advertiser_{$monthName}_{$tahun}_{$userName}.pdf");
    }
}
