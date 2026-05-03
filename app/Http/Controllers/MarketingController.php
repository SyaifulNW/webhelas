<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketingPerformance;
use App\Models\Data;
use App\Models\ProgramKerja;
use App\Models\Inisiatif;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $isAdministrator = ($user->role === 'administrator' || $user->role === 'advertising' || $user->role === 'Advertising');
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $status = $request->input('status');
        
        $marketingUsers = [];
        $selectedMarketingUserId = $request->input('marketing_user_id');

        if ($isAdministrator) {
            $marketingUsers = \App\Models\User::whereIn('name', ['Felmi', 'Nisa'])->get();
            if (!$selectedMarketingUserId && $marketingUsers->isNotEmpty()) {
                $selectedMarketingUserId = $marketingUsers->firstWhere('name', 'Felmi')->id ?? $marketingUsers->first()->id;
            }
            $targetUser = \App\Models\User::find($selectedMarketingUserId);
            $query = MarketingPerformance::where('user_id', $selectedMarketingUserId);
            $userName = $targetUser ? $targetUser->name : 'Unknown';
        } else {
            $query = MarketingPerformance::where('user_id', $user->id);
            $userName = $user->name;
            $selectedMarketingUserId = $user->id;
        }

        if ($bulan !== 'all') {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        } else {
            $query->whereYear('tanggal', $tahun);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $performances = $query->orderBy('tanggal', 'asc')->get();

        // Sync Real Closing for Felmi
        if (stripos($userName, 'Felmi') !== false) {
            foreach ($performances as $perf) {
                $perfDate = \Carbon\Carbon::parse($perf->tanggal);
                $closingCount = \App\Models\SalesPlan::where('status', 'sudah_transfer')
                    ->whereMonth('updated_at', $perfDate->month)
                    ->whereYear('updated_at', $perfDate->year)
                    ->whereHas('data', function($q) {
                        $q->where('leads', 'Event');
                    })
                    ->count();
                
                if ($perf->real_closing != $closingCount) {
                    $perf->real_closing = $closingCount;
                    $perf->selisih = ($perf->target_peserta ?? 0) - $perf->real_closing;
                    $perf->save();
                }
            }
        }

        // Seed example data if empty to match requirements for first time
        if ($performances->isEmpty() && !request()->has('bulan')) {
             if (stripos($userName, 'Nisa') !== false) {
                 MarketingPerformance::create([
                     'user_id' => $selectedMarketingUserId,
                     'event_name' => 'E- Forum',
                     'tanggal' => now()->format('Y-m-d'),
                     'lokasi' => 'Markaz MBC',
                     'jenis_event' => 'Edukasi',
                     'target_peserta' => 50,
                     'target_closing' => 10,
                     'status' => 'Terlaksana',
                 ]);
             } elseif (stripos($userName, 'Felmi') !== false) {
                 MarketingPerformance::create([
                     'user_id' => $selectedMarketingUserId,
                     'event_name' => 'Zoom',
                     'tanggal' => now()->format('Y-m-d'),
                     'lokasi' => 'Zoom',
                     'jenis_event' => 'Zoom Online',
                     'target_peserta' => 50,
                     'target_closing' => 10,
                     'status' => 'Terlaksana',
                 ]);
             }
             $performances = $query->get();
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $years = range(now()->year - 2, now()->year + 1);

        return view('marketing', compact('performances', 'bulan', 'tahun', 'status', 'userName', 'months', 'years', 'isAdministrator', 'marketingUsers', 'selectedMarketingUserId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'administrator' || $user->role === 'advertising' || $user->role === 'Advertising') {
            return response()->json(['error' => 'Anda tidak memiliki hak untuk menambah data'], 403);
        }
        $userName = $user->name;

        // Determine automatic values
        $targetPeserta = 50; 
        
        $perf = MarketingPerformance::create([
            'user_id' => $user->id,
            'event_name' => '-',
            'target_peserta' => $targetPeserta,
            'target_closing' => 10,
            'status' => 'Terlaksana',
            'tanggal' => now()->format('Y-m-d'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $perf,
                'formatted_date' => $perf->tanggal ? \Carbon\Carbon::parse($perf->tanggal)->format('Y-m-d') : ''
            ]);
        }

        return back()->with('success', 'Data berhasil ditambahkan');
    }

    public function updateInline(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'administrator' || $user->role === 'advertising' || $user->role === 'Advertising') {
            return response()->json(['error' => 'Anda tidak memiliki hak untuk mengubah data'], 403);
        }

        $perf = MarketingPerformance::findOrFail($request->id);
        $field = $request->field;
        $value = $request->value;

        if (in_array($field, ['target_peserta', 'target_closing', 'selisih'])) {
            return response()->json(['error' => 'Kolom ini otomatis/tidak dapat diubah'], 403);
        }

        $perf->$field = $value;

        // Recalculate selisih: target_peserta - real_closing
        $perf->selisih = ($perf->target_peserta ?? 0) - ($perf->real_closing ?? 0);

        $perf->save();

        return response()->json([
            'success' => true,
            'selisih' => $perf->selisih
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role === 'administrator' || $user->role === 'advertising' || $user->role === 'Advertising') {
            return back()->with('error', 'Anda tidak memiliki hak untuk menghapus data');
        }

        MarketingPerformance::destroy($id);
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $isAdministrator = ($user->role === 'administrator' || $user->role === 'advertising' || $user->role === 'Advertising');
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $status = $request->input('status');
        $selectedMarketingUserId = $request->input('marketing_user_id');

        if ($isAdministrator && $selectedMarketingUserId) {
            $targetUser = \App\Models\User::find($selectedMarketingUserId);
            $query = MarketingPerformance::where('user_id', $selectedMarketingUserId);
            $userName = $targetUser ? $targetUser->name : 'Unknown';
        } else {
            $query = MarketingPerformance::where('user_id', $user->id);
            $userName = $user->name;
        }

        if ($bulan !== 'all') {
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        } else {
            $query->whereYear('tanggal', $tahun);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $performances = $query->orderBy('tanggal', 'asc')->get();

        // Sync Real Closing for Felmi in PDF
        if (stripos($userName, 'Felmi') !== false) {
            foreach ($performances as $perf) {
                $perfDate = \Carbon\Carbon::parse($perf->tanggal);
                $closingCount = \App\Models\SalesPlan::where('status', 'sudah_transfer')
                    ->whereMonth('updated_at', $perfDate->month)
                    ->whereYear('updated_at', $perfDate->year)
                    ->whereHas('data', function($q) {
                        $q->where('leads', 'Event');
                    })
                    ->count();
                
                if ($perf->real_closing != $closingCount) {
                    $perf->real_closing = $closingCount;
                    $perf->selisih = ($perf->target_peserta ?? 0) - $perf->real_closing;
                    $perf->save();
                }
            }
        }
        $monthName = ($bulan === 'all') ? 'Semua Bulan' : \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('marketing_pdf', compact('performances', 'bulan', 'tahun', 'userName', 'monthName'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download("Performance_Marketing_{$userName}_{$monthName}_{$tahun}.pdf");
    }

}
