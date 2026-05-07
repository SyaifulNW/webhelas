<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Auto-fix: Create role_menus table if it doesn't exist on server
        if (!\Illuminate\Support\Facades\Schema::hasTable('role_menus')) {
            try {
                \Illuminate\Support\Facades\Schema::create('role_menus', function ($table) {
                    $table->id();
                    $table->string('role');
                    $table->unsignedBigInteger('menu_id');
                    $table->boolean('can_access')->default(true);
                    $table->timestamps();
                    $table->unique(['role', 'menu_id']);
                });
            } catch (\Exception $e) {
            }
        }

        // Auto-fix: Add missing columns to users table on server
        $userColumns = ['chapter', 'id_no', 'wa', 'username', 'is_active'];
        foreach ($userColumns as $col) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('users', $col)) {
                try {
                    \Illuminate\Support\Facades\Schema::table('users', function ($table) use ($col) {
                        $table->string($col)->nullable();
                    });
                } catch (\Exception $e) {
                }
            }
        }

        $usersPusat = \App\Models\User::whereNotIn('role', ['chapter', 'reseller'])->get();
        $usersCabang = \App\Models\User::whereIn('role', ['chapter', 'reseller'])->get();
        $menus = \App\Models\Menu::all();
        $targetOmset = \App\Models\Setting::where('key', 'target_omset')->value('value');
        $targetOmsetSmi = \App\Models\Setting::where('key', 'target_omset_smi')->value('value');

        // Chapters taken by 'chapter' role users
        $takenChapters = \App\Models\User::where('role', 'chapter')
            ->whereNotNull('chapter')
            ->pluck('chapter')
            ->toArray();

        // Define roles
        $roles = [
            'administrator',
            'marketing',
            'cs-mbc',
            'operasional',
            'manager',
            'hrd',
            'produksi',
            'advertising',
            'reseller',
            'chapter'
        ];

        return view('admin.settings.index', [
            'usersPusat' => $usersPusat,
            'usersCabang' => $usersCabang,
            'menus' => $menus,
            'targetOmset' => $targetOmset,
            'targetOmsetSmi' => $targetOmsetSmi,
            'roles' => $roles,
            'takenChapters' => $takenChapters
        ]);
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
            'chapter' => $request->chapter,
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'User berhasil dibuat.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'chapter' => $request->chapter,
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

        \DB::table('role_menus')->updateOrInsert(
            ['role' => $request->role, 'menu_id' => $request->menu_id],
            ['can_access' => $request->active, 'updated_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function toggleUserStatus(Request $request)
    {
        $user = \App\Models\User::where('id', $request->id)->first();
        if ($user) {
            $user->is_active = $request->active;
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function transferUserDatabase(Request $request)
    {
        $request->validate([
            'from_id' => 'required|exists:users,id',
            'to_id' => 'required|exists:users,id',
        ]);

        if ($request->from_id == $request->to_id) {
            return response()->json(['success' => false, 'message' => 'User sumber dan tujuan tidak boleh sama.'], 422);
        }

        $fromUser = \App\Models\User::find($request->from_id);
        $toUser = \App\Models\User::find($request->to_id);

        \DB::beginTransaction();
        try {
            // 1. Update Data Leads
            $countData = \DB::table('data')
                ->where('created_by', $fromUser->id)
                ->update(['created_by' => $toUser->id]);

            // 2. Update SalesPlans
            $countSales = \DB::table('salesplans')
                ->where('created_by', $fromUser->id)
                ->update(['created_by' => $toUser->id]);

            // 3. Update Peserta SMI
            \DB::table('peserta_smis')
                ->where('created_by', $fromUser->id)
                ->update(['created_by' => $toUser->id]);

            \DB::table('peserta_smis')
                ->where('closing_cs_id', $fromUser->id)
                ->update(['closing_cs_id' => $toUser->id]);

            $countSmi = \DB::table('peserta_smis')
                ->where('cs_name', $fromUser->name)
                ->update(['cs_name' => $toUser->name]);

            \DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Berhasil memindahkan database dari {$fromUser->name} ke {$toUser->name}. " .
                             "({$countData} Leads, {$countSales} Prospek, {$countSmi} Peserta M1T diperbarui)"
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
