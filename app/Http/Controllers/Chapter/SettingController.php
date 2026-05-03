<?php

namespace App\Http\Controllers\Chapter;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:chapter']);
    }

    public function index()
    {
        $user = Auth::user();
        $downlines = User::where('role', 'reseller')
            ->where('chapter', $user->chapter)
            ->get();
        return view('chapter.setting.index', compact('user', 'downlines'));
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
        return redirect()->back()->with('success', 'Akun reseller baru berhasil didaftarkan oleh Chapter.');
    }

    public function updateReseller(Request $request, $id)
    {
        $user = User::where('role', 'reseller')
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

        $user->update($data);

        return redirect()->back()->with('success', 'Akun reseller berhasil diperbarui.');
    }
}
