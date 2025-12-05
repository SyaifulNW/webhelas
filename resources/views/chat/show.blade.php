@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">
    <h4 class="mb-3 text-primary">
        <i class="bi bi-chat-dots-fill"></i> Chat dengan {{ $receiver->name }}
    </h4>

    <div class="card shadow-sm mb-3">
        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
            @forelse($messages as $msg)
                <div class="mb-2">
                    <div class="{{ $msg->sender_id == Auth::id() ? 'text-end' : 'text-start' }}">
                        <div class="d-inline-block p-2 rounded {{ $msg->sender_id == Auth::id() ? 'bg-primary text-white' : 'bg-light' }}">
                            {{ $msg->message }}
                        </div>
                    </div>
                    <small class="text-muted d-block {{ $msg->sender_id == Auth::id() ? 'text-end' : 'text-start' }}">
                        {{ $msg->created_at->format('H:i') }}
                    </small>
                </div>
            @empty
                <p class="text-center text-muted">Belum ada pesan.</p>
            @endforelse
        </div>
    </div>

    <form method="POST" action="{{ route('chat.store', $receiver->id) }}">
        @csrf
        <div class="input-group">
            <input type="text" name="message" class="form-control" placeholder="Ketik pesan..." required>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Kirim
            </button>
        </div>
    </form>
</div>
@endsection
