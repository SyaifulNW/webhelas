<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SalesPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:reseller']);
    }

    public function index()
    {
        $user = Auth::user();
        $chapter = $user->chapter;
        
        $downlines = User::where('role', 'reseller')
            ->where('created_by', $user->id)
            ->withCount(['salesplans as total_closing' => function ($query) {
                $query->where('status', 'sudah_transfer');
            }])
            ->get();

        // Calculate omset performance
        foreach ($downlines as $reseller) {
            $reseller->total_omset = SalesPlan::where('created_by', $reseller->id)
                ->where('status', 'sudah_transfer')
                ->sum('nominal');
        }

        return view('reseller.setting.index', compact('user', 'downlines', 'chapter'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $reseller = User::where('role', 'reseller')
            ->where('created_by', $user->id)
            ->findOrFail($id);

        $students = SalesPlan::where('created_by', $reseller->id)
            ->with('kelas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reseller.setting.show', compact('reseller', 'students'));
    }

    public function storeReseller(Request $request)
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
            'email' => $request->email,
            'wa' => $request->wa,
            'chapter' => Auth::user()->chapter,
            'role' => 'reseller',
            'created_by' => Auth::id(),
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Akun reseller baru berhasil didaftarkan.');
    }

    public function updateReseller(Request $request, $id)
    {
        $user = User::where('role', 'reseller')
            ->where('created_by', Auth::id())
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

        $user->update($data);

        return redirect()->back()->with('success', 'Akun reseller berhasil diperbarui.');
    }

    public function destroyReseller($id)
    {
        $user = User::where('role', 'reseller')
            ->where('created_by', Auth::id())
            ->findOrFail($id);

        $user->delete();

        return redirect()->back()->with('success', 'Akun reseller berhasil dihapus.');
    }
}
