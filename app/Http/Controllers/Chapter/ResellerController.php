<?php

namespace App\Http\Controllers\Chapter;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SalesPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ResellerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:chapter']);
    }

    public function index()
    {
        $chapter = Auth::user()->chapter;
        
        // Only show resellers personally recruited by this Chapter leader
        $resellers = User::where('role', 'reseller')
            ->where('created_by', Auth::id())
            ->withCount(['salesplans as total_closing' => function ($query) {
                $query->where('status', 'sudah_transfer');
            }])
            ->get();

        // Calculate omset performance
        foreach ($resellers as $reseller) {
            $sales = SalesPlan::where('created_by', $reseller->id)
                ->where('status', 'sudah_transfer')
                ->get();

            $reseller->total_omset = $sales->sum(function($sale) {
                // Priority 1: Use nominal from SalesPlan if set
                if ($sale->nominal > 0) return $sale->nominal;
                
                // Priority 2: Use biaya_pendaftaran from PesertaSmi if exists
                $peserta = $sale->pesertaSmi;
                if ($peserta && $peserta->biaya_pendaftaran > 0) return $peserta->biaya_pendaftaran;
                if ($peserta && $peserta->pembayaran_spp > 0) return $peserta->pembayaran_spp;
                
                // Priority 3: Fallback based on Level (Grow Up = 1.5M, others 1M)
                $pLevel = strtolower($sale->level ?? ($peserta->level ?? ''));
                return str_contains($pLevel, 'grow') ? 1500000 : 1000000;
            });
        }

        return view('chapter.reseller.index', compact('resellers', 'chapter'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'wa' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'username' => $request->email,
            'name' => $request->name,
            'id_no' => null,
            'email' => $request->email,
            'wa' => $request->wa,
            'chapter' => Auth::user()->chapter,
            'role' => 'reseller',
            'created_by' => Auth::id(),
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('chapter.reseller.index')->with('success', 'Reseller berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $reseller = User::where('role', 'reseller')
            ->where('chapter', Auth::user()->chapter)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'wa' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->email,
            'wa' => $request->wa,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $reseller->update($data);

        return redirect()->route('chapter.reseller.index')->with('success', 'Data reseller berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $reseller = User::where('role', 'reseller')
            ->where('chapter', Auth::user()->chapter)
            ->findOrFail($id);

        $reseller->delete();

        return redirect()->route('chapter.reseller.index')->with('success', 'Reseller berhasil dihapus.');
    }

    public function show($id)
    {
        $reseller = User::where('role', 'reseller')
            ->where('chapter', Auth::user()->chapter)
            ->findOrFail($id);

        $students = SalesPlan::where('created_by', $reseller->id)
            ->with('kelas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('chapter.reseller.show', compact('reseller', 'students'));
    }
}
