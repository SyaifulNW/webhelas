<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::all();
        $menus = \App\Models\Menu::all();
        $targetOmset = \App\Models\Setting::where('key', 'target_omset')->value('value');
        $targetOmsetSmi = \App\Models\Setting::where('key', 'target_omset_smi')->value('value');

        // Fetch unique roles from users table (could also be a static list if preferred)
        $roles = \App\Models\User::distinct('role')->pluck('role')->map(function($role) {
            return strtolower(trim($role));
        })->unique()->values();

        return view('admin.settings.index', compact('users', 'menus', 'targetOmset', 'targetOmsetSmi', 'roles'));
    }

    // --- USERS ---
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'User berhasil dibuat.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required|string',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri!');
        }
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    // --- TARGET OMSET ---
    public function updateTarget(Request $request)
    {
        $request->validate([
            'target_omset' => 'required|numeric',
            'target_omset_smi' => 'nullable|numeric'
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'target_omset'],
            ['value' => $request->target_omset]
        );

        if ($request->has('target_omset_smi')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'target_omset_smi'],
                ['value' => $request->target_omset_smi]
            );
        }

        return redirect()->back()->with('success', 'Target Omset berhasil diperbarui.');
    }

    // --- MENUS ---
    public function toggleMenu(Request $request)
    {
        $menu = \App\Models\Menu::where('id', $request->id)->first();
        if ($menu) {
            $menu->is_active = $request->active;
            $menu->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function updateRoleMenu(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
            'menu_id' => 'required|exists:menus,id',
            'active' => 'required|boolean'
        ]);

        \App\Models\RoleMenu::updateOrCreate(
            ['role' => $request->role, 'menu_id' => $request->menu_id],
            ['can_access' => $request->active]
        );

        return response()->json(['success' => true]);
    }
}
