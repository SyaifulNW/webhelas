@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">
    <h4 class="mb-4 text-primary fw-bold"><i class="bi bi-inbox-fill"></i> INBOX KOMENTAR</h4>

    @forelse($notifikasis as $notif)
        <a href="{{ route('notifikasi.show', $notif->id) }}" 
           class="card mb-3 shadow-sm border-{{ $notif->is_read ? 'light' : 'primary' }} text-decoration-none">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $notif->sender->name ?? 'Administrator' }}</strong><br>
                    <span class="text-muted">{{ $notif->pesan }}</span>
                </div>
                <small class="text-secondary">{{ $notif->created_at->diffForHumans() }}</small>
            </div>
        </a>
    @empty
        <div class="alert alert-info">Tidak ada notifikasi saat ini.</div>
    @endforelse
</div>
@endsection
