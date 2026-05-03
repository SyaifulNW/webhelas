<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Data;
use Rap2hpoutre\FastExcel\FastExcel;

class SalesPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $kelasFilter  = $request->input('kelas');
        $csFilter     = $request->input('created_by');
        $typeFilter   = $request->input('type'); // mbc or smi

        if (auth()->check() && optional(auth()->user())->name == 'Agus Setyo') {
            $kelasFilter = 'Start-Up Muslim Indonesia';
        }

        if ($typeFilter == 'smi') {
            $kelasFilter = 'Start-Up Muslim Indonesia';
        }

        $restrictedView = $request->input('restricted_view', false);

        // ======================================
        // 🔥 AUTO UPDATE STATUS
        // Jika status 'tertarik' sudah > 5 hari tidak berubah -> ubah jadi 'no'
        // ======================================
        $cutoffDate = now()->subDays(5);
        $updatedCount = SalesPlan::where('status', 'tertarik')
            ->where('updated_at', '<', $cutoffDate)
            ->update(['status' => 'cold']);

        if ($updatedCount > 0) {
            $dateString = $cutoffDate->format('d M Y H:i');
            session()->flash('warning', "$updatedCount data peserta dengan status 'Tertarik' telah otomatis diubah menjadi 'Cold' (Tidak ada update sejak $dateString).");
        }

        $statusFilter = $request->input('status');
        $bulanFilter  = $request->input('bulan');
        $tahunFilter  = $request->input('tahun', date('Y')); 
        if ($request->has('tahun') && $request->input('tahun') == '') {
            $tahunFilter = null;
        }

        $userId       = auth()->id();
        $perPage      = $request->get('per_page', 100);

        // Dropdown data
        $kelasList = Kelas::all();
        $csList    = User::orderBy('name', 'asc')->get();
        
        // Filter CS List for Admin Dropdown (Specific Request)
        if (in_array(auth()->id(), [1, 2])) {
             $csList = User::whereIn('name', ['Yasmin', 'Linda', 'Arifa', 'Diah Putri', 'Puput', 'Gunawan'])
                           ->orderBy('name', 'asc')
                           ->get();
        } else {
             $csList = User::orderBy('name', 'asc')->get();
        }

        // =====================================================
        // 🔥 JIKA ADMIN BELUM MEMFILTER → JANGAN TAMPILKAN DATA
        // =====================================================
        $isAdmin = in_array($userId, [1]);
        $noFilter = empty($kelasFilter) && empty($csFilter) && empty($statusFilter) && empty($bulanFilter) && empty($typeFilter);

        if ($isAdmin && $noFilter) {

            return view('admin.salesplan.index', [
                'salesplans'      => collect(),  // kosongkan
                'pesertaTransfer' => collect(),  // kosongkan
                'kelasList'       => $kelasList,
                'csList'          => $csList,
                'kelasFilter'     => $kelasFilter,
                'csFilter'        => $csFilter,
                'statusFilter'    => $statusFilter,
                'bulanFilter'     => $bulanFilter,
                'salesplansByCS'  => collect(),  // kosongkan
                'message'         => "Silakan pilih filter untuk menampilkan data.",
                'isRestrictedView' => $restrictedView
            ]);
        }


    // ======================================
    // 🔥 QUERY UTAMA SALESPLAN
    // ======================================
    
    
    
        
    // Determine exempt users (who can see all data)
    $exemptUsers = ['Agus Setyo', 'Fitra Jaya Saleh','Linda'];
    // Linda is exempt only if NOT in restricted view
    // if (auth()->user()->name == 'Linda' && !$restrictedView) {
    //     $exemptUsers[] = 'Linda';
    // }
        
    // Clone query logic untuk statistik agar menyertakan semua data (tidak terpotong pagination)
    $salesplanStats = SalesPlan::where('status', 'sudah_transfer')
        ->when($kelasFilter, function ($query) use ($kelasFilter) {
            $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                $sub->where('nama_kelas', $kelasFilter);
            });
        })
        ->when($csFilter, function ($query) use ($csFilter) {
            $query->where('created_by', $csFilter);
        })
        ->when($bulanFilter, function ($query) use ($bulanFilter) {
            $query->whereMonth('updated_at', $bulanFilter);
        })
        ->when($tahunFilter, function ($query) use ($tahunFilter) {
            $query->whereYear('updated_at', $tahunFilter);
        })
        ->when(! $isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })
        ->selectRaw('created_by, SUM(nominal) as total_nominal')
        ->groupBy('created_by')
        ->pluck('total_nominal', 'created_by');
    
    $salesplans = SalesPlan::with(['kelas', 'data'])

        ->when($kelasFilter, function ($query) use ($kelasFilter) {
            $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                $sub->where('nama_kelas', $kelasFilter);
            });
        })

        ->when($csFilter, function ($query) use ($csFilter) {
            $query->where('created_by', $csFilter);
        })

        ->when($statusFilter, function ($query) use ($statusFilter) {
            $query->where('status', $statusFilter);
        })

             ->when($bulanFilter, function ($query) use ($bulanFilter) {
            $query->whereMonth('updated_at', $bulanFilter);
        })
        ->when($tahunFilter, function ($query) use ($tahunFilter) {
            $query->whereYear('updated_at', $tahunFilter);
        })
        
        

   
        ->when(! $isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })
        
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);


    // ======================================
    // 🔥 PESERTA TRANSFER
    // ======================================
    $pesertaTransfer = SalesPlan::where('status', 'sudah_transfer')

        ->when($kelasFilter, function ($query) use ($kelasFilter) {
            $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                $sub->where('nama_kelas', $kelasFilter);
            });
        })

        ->when($csFilter, function ($query) use ($csFilter) {
            $query->where('created_by', $csFilter);
        })

     ->when(! $isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })

      ->when($bulanFilter, function ($query) use ($bulanFilter) {
            $query->whereMonth('updated_at', $bulanFilter);
        })
        ->when($tahunFilter, function ($query) use ($tahunFilter) {
            $query->whereYear('updated_at', $tahunFilter);
        })
        ->get();


    $salesplansByCS = $salesplans->groupBy('created_by');

    // Fallback: Ambil data berdasarkan nama jika data_id null (untuk data lama)
    $names = $salesplans->pluck('nama')->filter()->toArray();
    $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');
    
    
    // ======================================
    // 🔥 CALCULATE DYNAMIC TARGET PER CS
    // ======================================
    $csTargets = [];
    if (empty($kelasFilter) && !empty($bulanFilter)) {
        // Jika filter Bulan aktif & Semua Kelas -> Target adalah SUM dari target kelas yang diikuti CS
        // Ambil semua kelas yang ada salesplannya untuk filter ini (tanpa pagination)
        $distinctClasses = SalesPlan::with('kelas')
            ->select('created_by', 'kelas_id')
            ->when($csFilter, fn($q) => $q->where('created_by', $csFilter))
            ->when($bulanFilter, fn($q) => $q->whereMonth('updated_at', $bulanFilter))
            ->when($tahunFilter, fn($q) => $q->whereYear('updated_at', $tahunFilter))
            ->distinct()
            ->get()
            ->groupBy('created_by');
            
        foreach ($distinctClasses as $csId => $items) {
            $totalTargetCS = 0;
            foreach ($items as $item) {
                if (!$item->kelas) continue;
                $namaKelas = $item->kelas->nama_kelas;
                
                // Logic Target: SMI = 50jt, Lainnya = 25jt
                if (str_contains($namaKelas, 'Start-Up Muda Indonesia') || str_contains($namaKelas, 'Start-Up Muslim Indonesia')) {
                    $t = 50000000;
                } else {
                    $t = 25000000;
                }
                $totalTargetCS += $t;
            }
            $csTargets[$csId] = $totalTargetCS;
        }
    }



    return view('admin.salesplan.index', [
        'salesplans'      => $salesplans,
        'pesertaTransfer' => $pesertaTransfer,
        'kelasList'       => $kelasList,
        'csList'          => $csList,
        'kelasFilter'     => $kelasFilter,
        'csFilter'        => $csFilter,
        'csFilter'        => $csFilter,
        'statusFilter'    => $statusFilter,
        'bulanFilter'     => $bulanFilter,
        'tahunFilter'     => $tahunFilter,
           'csTargets'       => $csTargets,
        'salesplansByCS'  => $salesplansByCS,
                'salesplanStats'  => $salesplanStats,
        'dataMap'         => $dataMap,
        'message'         => null,
                'isRestrictedView' => $restrictedView
    ]);
}




    /**
     * FILTER â€” sekarang tetep kirim variabel yang sama seperti index()
     */
    public function filter($kelas)
    {
       $request = new Request(['kelas' => $kelas, 'restricted_view' => true]);
        return $this->index($request);
    }


    /**
     * SEARCH â€” tetep kirim variabel view yang sama
     */
    public function search(Request $request)
    {
        $q = $request->input('q');

        $kelasList = Kelas::all();

        $salesplans = SalesPlan::with(['kelas', 'data'])
            ->where('nama', 'like', "%$q%")
            ->orWhereHas('kelas', fn($q2) => $q2->where('nama_kelas', 'like', "%$q%"))
            ->paginate(100);

        $kelasFilter     = null;
        $pesertaTransfer = collect([]);
        $salesplansByCS  = $salesplans->groupBy('created_by');

        // Fallback: Ambil data berdasarkan nama
        $names = $salesplans->pluck('nama')->filter()->toArray();
        $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');

        return view('admin.salesplan.index', [
            'salesplans'      => $salesplans,
            'kelasList'       => $kelasList,
            'kelasFilter'     => $kelasFilter,
            'pesertaTransfer' => $pesertaTransfer,
            'salesplansByCS'  => $salesplansByCS,
            'dataMap'         => $dataMap,
            'message'         => "Hasil pencarian: $q"
        ]);
    }


    public function inlineUpdate(Request $request)
    {
        $plan = SalesPlan::findOrFail($request->id);

        $allowedFields = [
            'fu1_hasil','fu1_tindak_lanjut',
            'fu2_hasil','fu2_tindak_lanjut',
            'fu3_hasil','fu3_tindak_lanjut',
            'fu4_hasil','fu4_tindak_lanjut',
            'fu5_hasil','fu5_tindak_lanjut',
            'nominal','keterangan', 'komentar_atasan', 'kebutuhan', 'closing_paket', 'level'
        ];

        if (!in_array($request->field, $allowedFields)) {
            return response()->json(['error' => 'Field tidak diizinkan'], 400);
        }

        $value = $request->value;

        // Bersihkan nominal dari titik jika ada (misal: 25.000.000 -> 25000000)
        if ($request->field === 'nominal') {
            $value = str_replace('.', '', $value);
        }

        $plan->{$request->field} = $value;
        $plan->save();

        return response()->json(['success' => true]);
    }


public function updateStatus(Request $request, $id)
{
    $plan = SalesPlan::findOrFail($id);
    $plan->status = $request->status;
    $plan->save();

    return response()->json(['success' => true]);
}


    public function export()
    {
        $sales = SalesPlan::all();
        return (new FastExcel($sales))->download('sales_plan.xlsx');
    }


    public function destroy($id)
    {
        $plan = SalesPlan::findOrFail($id);
        $plan->delete();

        return back()->with('success', 'Data berhasil dihapus');
    }
}
