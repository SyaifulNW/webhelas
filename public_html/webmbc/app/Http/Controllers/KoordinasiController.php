<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kelas;
use App\Models\SalesPlan;
use App\Models\Activity;
use App\Models\DailyActiviti;
use App\Models\Data;
use App\Models\Notifikasi; // ✅ Tambahkan
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KoordinasiController extends Controller
{
public function show($id, Request $request)
{
    // ✅ 1. Ambil role user yang sedang login
    $user = auth()->user();
    $userRole = strtolower($user->role);

    // ✅ 2. Hanya Administrator & Manager yang boleh akses halaman ini
    if (!in_array($userRole, ['administrator', 'manager'])) {
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }

    // ✅ 3. Ambil data CS yang dipilih
    $cs = User::findOrFail($id);
    $csId = $cs->id;
    $csName = strtolower(trim($cs->name));

    // ✅ 4. Jika role Manager, hanya boleh akses CS tertentu
    if ($userRole === 'manager') {
        $allowedCs = ['latifah', 'tursia', 'gunawan', 'puput'];
        if (!in_array($csName, $allowedCs)) {
            abort(403, 'Manager hanya dapat mengakses data milik Latifah, Tursia, Gunawan, dan Puput.');
        }
    }

    // ✅ 5. Ambil data calon peserta berdasarkan nama CS
    $data = Data::query()
        ->whereRaw('LOWER(created_by) LIKE ?', ['%' . $csName . '%'])
        ->latest()
        ->get();

    // ✅ 6. Ambil semua data kelas
    $kelas = Kelas::all();

    // ✅ 7. Ambil komentar / notifikasi untuk CS tersebut
    $komentar = Notifikasi::where('user_id', $csId)
        ->latest()
        ->get();

    // ✅ 8. Kirim semua data ke view
    return view('admin.database.database', [
        'data' => $data,
        'kelas' => $kelas,
        'csName' => ucfirst($csName),
        'komentar' => $komentar,
        'readonly' => true,
        'user' => $cs
    ]);
}





public function kirimKomentar(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'pesan'   => 'required|string|max:255',
    ]);

    Notifikasi::create([
        'user_id'   => $request->user_id,
        'sender_id' => auth()->id(), // 👈 Tambahkan sender_id
        'pesan'     => $request->pesan,
    ]);

    // Redirect back dengan flash message
    return redirect()->back()->with('success', 'Komentar berhasil dikirim!');
}

}
