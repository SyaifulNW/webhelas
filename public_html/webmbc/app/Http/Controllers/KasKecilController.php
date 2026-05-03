<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KasKecil;
use Illuminate\Support\Facades\Auth;

class KasKecilController extends Controller
{
    public function index(Request $request)
    {
        // Auto-fix: Create kas_kecils table if missing on server
        if (!\Illuminate\Support\Facades\Schema::hasTable('kas_kecils')) {
            try {
                \Illuminate\Support\Facades\Schema::create('kas_kecils', function ($table) {
                    $table->id();
                    $table->date('tanggal');
                    $table->string('keterangan');
                    $table->decimal('masuk', 15, 2)->default(0);
                    $table->decimal('keluar', 15, 2)->default(0);
                    $table->decimal('sisa', 15, 2)->default(0);
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->timestamps();
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }

        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $kas = KasKecil::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.keuangan.kas_kecil', compact('kas', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
            'masuk' => 'nullable|numeric',
            'keluar' => 'nullable|numeric',
        ]);

        KasKecil::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'masuk' => $request->masuk ?? 0,
            'keluar' => $request->keluar ?? 0,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        KasKecil::findOrFail($id)->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
