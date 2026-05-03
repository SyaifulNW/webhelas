<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'wa' => 'nullable|string|max:20',
            'chapter' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.']);
            }
        }

        $data = $request->only(['name', 'email', 'wa', 'chapter', 'bio']);

        if ($request->hasFile('photo')) {
            // Robust path detection (supports public_html for shared hosting)
            $basePublic = public_path();
            if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'])) {
                $basePublic = $_SERVER['DOCUMENT_ROOT'];
            } elseif (is_dir(base_path('public_html'))) {
                $basePublic = base_path('public_html');
            }

            $subFolder = 'uploads/profile_photos';
            $destinationPath = rtrim($basePublic, '/') . '/' . $subFolder;

            // Delete old file if exists using manual path
            if ($user->photo && file_exists(rtrim($basePublic, '/') . '/' . $user->photo)) {
                @unlink(rtrim($basePublic, '/') . '/' . $user->photo);
            }

            $file = $request->file('photo');
            $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $data['photo'] = $subFolder . '/' . $filename;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
