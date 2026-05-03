@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="text-primary mb-0">
            <i class="bi bi-envelope-fill"></i> Kotak Masuk Pesan
        </h4>
        <span class="badge bg-primary fs-6">
            Total Pesan: {{ $messages->total() }}
        </span>
    </div>

    @if($messages->count() > 0)
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Pengirim</th>
                        <th>Isi Pesan</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $index => $message)
                        <tr class="{{ $message->is_read ? '' : 'table-warning fw-bold' }}">
                            <td>{{ $messages->firstItem() + $index }}</td>
                            <td>{{ $message->sender->name ?? 'Pengguna' }}</td>
                            <td style="white-space: pre-wrap;">{!! nl2br(e($message->message)) !!}</td>
                            <td>
                                @if(!$message->is_read)
                                    <span class="badge bg-warning text-white">
                                        <i class="bi bi-envelope-fill"></i> Baru
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill"></i> Dibaca
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $message->created_at->diffForHumans() }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $messages->links() }}
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="bi bi-inbox-fill"></i> Belum ada pesan masuk.
        </div>
    @endif
</div>

<style>
.table thead th {
    font-weight: 600;
    vertical-align: middle;
}
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
.table-warning {
    background-color: #fff8e1 !important;
}
.badge {
    font-size: 0.75rem;
    padding: 6px 10px;
}
.table td {
    vertical-align: top;
}
</style>
@endsection
