<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Kelas;
use App\Models\User;
use Rap2hpoutre\FastExcel\FastExcel;

class SalesPlanController extends Controller
{
public function index(Request $request)
{
    $kelasFilter  = $request->input('kelas');
    $csFilter     = $request->input('created_by');
    $statusFilter = $request->input('status');
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
            'salesplansByCS'  => collect(),  // kosongkan
            'message'         => "Silakan pilih filter untuk menampilkan data."
        ]);
    }


    // ======================================
    // 🔥 QUERY UTAMA SALESPLAN
    // ======================================
    $salesplans = SalesPlan::with('kelas')

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

        ->when(! $isAdmin, function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })

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

        ->get();


    $salesplansByCS = $salesplans->groupBy('created_by');


    return view('admin.salesplan.index', [
        'salesplans'      => $salesplans,
        'pesertaTransfer' => $pesertaTransfer,
        'kelasList'       => $kelasList,
        'csList'          => $csList,
        'kelasFilter'     => $kelasFilter,
        'csFilter'        => $csFilter,
        'statusFilter'    => $statusFilter,
        'salesplansByCS'  => $salesplansByCS,
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

        $salesplans = SalesPlan::with('kelas')
            ->where('nama', 'like', "%$q%")
            ->orWhereHas('kelas', fn($q2) => $q2->where('nama_kelas', 'like', "%$q%"))
            ->paginate(100);

        $kelasFilter     = null;
        $pesertaTransfer = collect([]);
        $salesplansByCS  = $salesplans->groupBy('created_by');

        return view('admin.salesplan.index', [
            'salesplans'      => $salesplans,
            'kelasList'       => $kelasList,
            'kelasFilter'     => $kelasFilter,
            'pesertaTransfer' => $pesertaTransfer,
            'salesplansByCS'  => $salesplansByCS,
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
            'nominal','keterangan'
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
