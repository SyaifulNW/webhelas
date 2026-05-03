<?php

namespace App\Http\Controllers;

use App\Models\ProgramKerja;
use App\Models\Inisiatif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramKerjaController extends Controller
{
    /**
     * Tampilkan daftar program kerja beserta inisiatifnya
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $viewRole = $request->query('view_role');

        $query = ProgramKerja::with('inisiatifs')
            ->orderBy('created_at', 'asc'); // ASCENDING

        // Jika administrator ingin melihat monitoring role tertentu
        if ($user->role === 'administrator' && $viewRole) {
            $query->where('created_by_role', $viewRole);
        }
        // Jika role adalah marketing → lihat semua yang dibuat oleh marketing
        else if ($user->role === 'marketing') {
            $query->where('created_by_role', 'marketing');
        }
        // Jika role adalah manager → lihat semua yang dibuat oleh manager
        else if ($user->role === 'manager') {
            $query->where('created_by_role', 'manager');
        }
        // Jika role lain → hanya lihat miliknya sendiri
        else {
            $query->where('created_by', $user->id);
        }

        $programs = $query->get();

        return view('marketing.programkerja.index', compact('programs', 'viewRole'));
    }

    /**
     * Update kolom secara inline di tabel
     */
    public function updateInline(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'field' => 'required|string',
            'value' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        if ($request->type === 'program') {
            $item = ProgramKerja::findOrFail($request->id);
        } else {
            $item = Inisiatif::findOrFail($request->id);
        }
        
        // Jika field numeric
        if (in_array($request->field, ['target', 'realisasi'])) {
            $item->{$request->field} = (int) $request->value;
        } else {
            $item->{$request->field} = $request->value;
        }

        $item->save();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $user = Auth::user();

        $program = ProgramKerja::create([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'created_by' => $user->id,
            'created_by_role' => $user->role,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'program' => $program]);
        }

        return redirect()
            ->route('programkerja.index')
            ->with('success', 'Program Kerja berhasil ditambahkan!');
    }

    public function storeInisiatif(Request $request)
    {
        try {
            $request->merge(json_decode($request->getContent(), true) ?? []);

            $validated = $request->validate([
                'program_kerja_id' => 'required|integer|exists:program_kerjas,id',
                'judul' => 'required|string|max:255',
                'pic' => 'nullable|string|max:100',
                'target' => 'required|integer|min:0',
                'realisasi' => 'required|integer|min:0',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'status' => 'required|in:progress,done,overdue',
                'deskripsi' => 'nullable|string',
            ]);

            // -----------------------------------------
            // HITUNG NILAI QTY
            // -----------------------------------------
            $nilai_qty = $validated['target'] > 0
                ? ($validated['realisasi'] / $validated['target']) * 100
                : 0;

            if ($nilai_qty > 100) $nilai_qty = 100;

            // -----------------------------------------
            // HITUNG NILAI WAKTU
            // -----------------------------------------
            $nilai_waktu = 0;

            if ($validated['status'] === 'done') {
                if (!empty($validated['tanggal_selesai']) && now()->lte($validated['tanggal_selesai'])) {
                    $nilai_waktu = 100;
                } else {
                    $nilai_waktu = 50;
                }
            } elseif ($validated['status'] === 'progress') {
                if (!empty($validated['tanggal_selesai']) && now()->lte($validated['tanggal_selesai'])) {
                    $nilai_waktu = 60;
                } else {
                    $nilai_waktu = 30;
                }
            } elseif ($validated['status'] === 'overdue') {
                $nilai_waktu = 0;
            }

            // -----------------------------------------
            // NILAI FINAL
            // -----------------------------------------
            $nilai_final = ($nilai_qty + $nilai_waktu) / 2;

            // Tambahkan nilai ke array validated
            $validated['nilai'] = $nilai_final;

            $inisiatif = Inisiatif::create($validated);

            return response()->json([
                'success' => true,
                'id' => $inisiatif->id,
                'nilai' => $nilai_final
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateInisiatif(Request $request, $id)
    {
        try {
            $request->merge(json_decode($request->getContent(), true) ?? []);

            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'pic' => 'nullable|string|max:100',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'status' => 'required|in:progress,done,overdue',
                'target' => 'nullable|integer|min:0',
                'realisasi' => 'nullable|integer|min:0',
            ]);

            $inisiatif = Inisiatif::findOrFail($id);

            // Ambil data lama jika tidak dikirim
            $target = $validated['target'] ?? $inisiatif->target;
            $realisasi = $validated['realisasi'] ?? $inisiatif->realisasi;

            // Hitung nilai qty
            $nilai_qty = $target > 0 ? ($realisasi / $target) * 100 : 0;
            if ($nilai_qty > 100) $nilai_qty = 100;

            // Hitung nilai waktu
            $nilai_waktu = 0;
            $status = $validated['status'];

            if ($status === 'done') {
                if (!empty($validated['tanggal_selesai']) && now()->lte($validated['tanggal_selesai'])) {
                    $nilai_waktu = 100;
                } else {
                    $nilai_waktu = 50;
                }
            } elseif ($status === 'progress') {
                if (!empty($validated['tanggal_selesai']) && now()->lte($validated['tanggal_selesai'])) {
                    $nilai_waktu = 60;
                } else {
                    $nilai_waktu = 30;
                }
            } elseif ($status === 'overdue') {
                $nilai_waktu = 0;
            }

            // Nilai final
            $validated['nilai'] = ($nilai_qty + $nilai_waktu) / 2;

            $inisiatif->update($validated);

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus program kerja & inisiatifnya
     */
    public function destroyInisiatif(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:inisiatifs,id',
        ]);

        $inisiatif = Inisiatif::find($request->id);

        if (!$inisiatif) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        $inisiatif->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Hapus program kerja
     */
    public function destroy($id)
    {
        try {
            $programKerja = ProgramKerja::findOrFail($id);
            
            // Hapus inisiatif terkait
            $programKerja->inisiatifs()->delete();
            $programKerja->delete();

            return redirect()->route('programkerja.index')->with('success', 'Program Kerja berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus Program Kerja: ' . $e->getMessage());
        }
    }

    public function performance()
    {
        $user = Auth::user();
        $userName = $user->name;

        // Ambil semua Inisiatif yang terkait dengan Program Kerja yang dibuat oleh user ini
        // ATAU yang PIC-nya adalah user ini
        $myPrograms = ProgramKerja::where('created_by', $user->id)->pluck('id');
        
        $myInisiatifs = Inisiatif::where('pic', $userName)->get();
        
        $menteeInisiatifs = Inisiatif::whereIn('program_kerja_id', $myPrograms)
            ->where('pic', '!=', $userName)
            ->get();

        $stats = [
            'pribadi' => [
                'aktif' => $myInisiatifs->where('status', 'progress')->count(),
                'selesai' => $myInisiatifs->where('status', 'done')->count(),
                'overdue' => $myInisiatifs->where('status', 'overdue')->count(),
            ],
            'mentee' => [
                'aktif' => $menteeInisiatifs->where('status', 'progress')->count(),
                'selesai' => $menteeInisiatifs->where('status', 'done')->count(),
                'overdue' => $menteeInisiatifs->where('status', 'overdue')->count(),
                'total_orang' => $menteeInisiatifs->pluck('pic')->unique()->count()
            ],
            'total_grup' => $myPrograms->count()
        ];

        return view('marketing.produksi.performance', compact('stats'));
    }
}
