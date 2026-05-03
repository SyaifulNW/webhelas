<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $notifikasi = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function show($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);

        // Tandai notifikasi sudah dibaca
        if (!$notifikasi->is_read) {
            $notifikasi->update(['is_read' => true]);
        }

        // Kembalikan ke halaman detail (atau redirect)
        return redirect()->back()->with('success', 'Notifikasi dibaca: ' . $notifikasi->pesan);
    }
}
