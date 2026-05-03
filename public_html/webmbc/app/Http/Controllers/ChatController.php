<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function show($id)
    {
        $receiver = User::findOrFail($id);

        $messages = ChatMessage::where(function ($q) use ($id) {
            $q->where('sender_id', Auth::id())
              ->where('receiver_id', $id);
        })->orWhere(function ($q) use ($id) {
            $q->where('sender_id', $id)
              ->where('receiver_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return view('chat.show', compact('receiver', 'messages'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $id,
            'message' => $request->message,
        ]);

        return redirect()->route('chat.show', $id);
    }
}
