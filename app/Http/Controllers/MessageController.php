<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\User;

class MessageController extends Controller
{
    /**
     * Tampilkan daftar semua pesan masuk (Inbox Admin)
     */
    public function index()
    {
        // Ambil semua pesan dengan relasi pengirim (sender)
        $messages = ChatMessage::with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.messages.index', compact('messages'));
    }

    /**
     * Tampilkan detail pesan dan ubah status menjadi "dibaca"
     */
    public function show($id)
    {
        $message = ChatMessage::with('sender')->findOrFail($id);

        // Jika pesan belum dibaca, ubah statusnya
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Balas pesan ke user
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string'
        ]);

        $original = ChatMessage::findOrFail($id);

        ChatMessage::create([
            'sender_id'   => auth()->id(),      // admin sebagai pengirim
            'receiver_id' => $original->sender_id, // user penerima
            'message'     => $request->reply,
            'is_read'     => false
        ]);

        return redirect()->route('admin.messages.show', $id)->with('success', 'Pesan berhasil dikirim.');
    }
}
