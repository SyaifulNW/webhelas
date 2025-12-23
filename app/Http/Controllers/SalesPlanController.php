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
    public function index(Request $request)
    {
        $kelasFilter  = $request->input('kelas');
        $csFilter     = $request->input('created_by');

        // ======================================
        // 🔥 AUTO UPDATE STATUS
        // Jika status 'tertarik' sudah > 5 hari tidak berubah -> ubah jadi 'no'
        // ======================================
        $cutoffDate = now()->subDays(5);
        $updatedCount = SalesPlan::where('status', 'tertarik')
            ->where('updated_at', '<', $cutoffDate)
            ->update(['status' => 'no']);

        if ($updatedCount > 0) {
            $dateString = $cutoffDate->format('d M Y H:i');
            session()->flash('warning', "$updatedCount data peserta dengan status 'Tertarik' telah otomatis diubah menjadi 'No' (Tidak ada update sejak $dateString).");
        }

        $statusFilter = $request->input('status');
        $bulanFilter  = $request->input('bulan');
        $tahunFilter  = $request->input('tahun', date('Y')); // Default tahun ini

        $userId       = auth()->id();
        $perPage      = $request->get('per_page', 100);

    // Dropdown data
    $kelasList = Kelas::all();
    $csList    = User::orderBy('name', 'asc')->get();


    // =====================================================
    // 🔥 JIKA ADMIN BELUM MEMFILTER → JANGAN TAMPILKAN DATA
    // =====================================================
    $isAdmin = in_array($userId, [1, 13]);
    $noFilter = empty($kelasFilter) && empty($csFilter) && empty($statusFilter);

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
            'message'         => "Silakan pilih filter untuk menampilkan data."
        ]);
    }


    // ======================================
    // 🔥 QUERY UTAMA SALESPLAN
    // ======================================
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

        ->when($bulanFilter, function ($query) use ($bulanFilter, $tahunFilter) {
            $query->whereMonth('updated_at', $bulanFilter)
                  ->whereYear('updated_at', $tahunFilter);
        })

        ->when(! $isAdmin && auth()->user()->name !== 'Agus Setyo', function ($query) use ($userId) {
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

        ->when(! $isAdmin, function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })

        ->when($bulanFilter, function ($query) use ($bulanFilter, $tahunFilter) {
            $query->whereMonth('updated_at', $bulanFilter)
                  ->whereYear('updated_at', $tahunFilter);
        })
        ->get();


    $salesplansByCS = $salesplans->groupBy('created_by');

    // Fallback: Ambil data berdasarkan nama jika data_id null (untuk data lama)
    $names = $salesplans->pluck('nama')->filter()->toArray();
    $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');


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
        'salesplansByCS'  => $salesplansByCS,
        'dataMap'         => $dataMap,
        'message'         => null
    ]);
}




    /**
     * FILTER â€” sekarang tetep kirim variabel yang sama seperti index()
     */
    public function filter($kelas)
    {
        $request = new Request(['kelas' => $kelas]);
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
            'nominal','keterangan', 'komentar_atasan'
        ];

        if (!in_array($request->field, $allowedFields)) {
            return response()->json(['error' => 'Field tidak diizinkan'], 400);
        }

        $plan->{$request->field} = $request->value;
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
