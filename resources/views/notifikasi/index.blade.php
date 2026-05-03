@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">
    <h4 class="mb-4 text-primary">
        <i class="bi bi-bell-fill"></i> INBOX
    </h4>

    @if($notifikasi->count() > 0)
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Pengirim</th>
                        <th>Pesan</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifikasi as $index => $notif)
                        <tr class="{{ $notif->is_read ? 'table-light' : 'table-warning fw-bold' }}">
                            <td>{{ $loop->iteration + ($notifikasi->currentPage()-1) * $notifikasi->perPage() }}</td>
                            <td>{{ $notif->sender->name ?? 'Administrator' }}</td>
                            <td>{{ $notif->pesan }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $notif->created_at->diffForHumans() }}
                                </small>
                            </td>
                            <td>
                                @if(!$notif->is_read)
                                    <span class="badge bg-danger text-white">Baru</span>
                                @else
                                    <span class="badge bg-secondary text-white">Dibaca</span>
                                @endif
                            </td>
                            <td>
                                @if($notif->sender)
                                    <a href="{{ route('chat.show', $notif->sender->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-chat-dots"></i> Balas
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-chat-dots"></i> Tidak dapat dibalas
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 📄 Pagination --}}
        <div class="mt-3 d-flex justify-content-center">
            {{ $notifikasi->links() }}
        </div>
    @else
        <div class="alert alert-info text-center">
            📨 Tidak ada notifikasi.
        </div>
    @endif
</div>
@endsection
