@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Semua Transaksi Wallet</h1>
        <a href="{{ route('admin.wallet.index') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Waktu</th>
                            <th class="border-0">User</th>
                            <th class="border-0">Deskripsi</th>
                            <th class="border-0 text-center">Bukti</th>
                            <th class="border-0 text-right">Nominal</th>
                            <th class="border-0 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $tx->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-muted">{{ $tx->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $tx->wallet->user->name }}</div>
                                <div class="text-xs text-muted">{{ $tx->wallet->wallet_id }}</div>
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $tx->source ?: ($tx->type == 'income' ? 'Pemasukan' : 'Penarikan Dana') }}</div>
                                @if($tx->type == 'withdrawal')
                                    <div class="text-xs text-primary">{{ $tx->bank_name }} - {{ $tx->account_number }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($tx->proof_of_transfer)
                                    <img src="{{ asset($tx->proof_of_transfer) }}" 
                                         alt="Bukti" 
                                         class="img-thumbnail shadow-sm" 
                                         style="width: 40px; height: 40px; object-fit: cover; cursor: pointer; border-radius: 4px; transition: transform 0.2s;"
                                         onclick="showProof('{{ asset($tx->proof_of_transfer) }}')"
                                         onmouseover="this.style.transform='scale(1.1)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                @else
                                    <span class="text-muted" style="font-size: 0.75rem;">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-right font-weight-bold {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}">
                                {{ $tx->type == 'income' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    @if($tx->status == 'success')
                                        <span class="badge badge-success px-3 py-1">Success</span>
                                    @elseif($tx->status == 'pending')
                                        <span class="badge badge-warning px-3 py-1">Pending</span>
                                    @else
                                        <span class="badge badge-danger px-3 py-1">Rejected</span>
                                    @endif

                                    <form action="{{ route('admin.wallet.transaction.destroy', $tx->id) }}" method="POST" class="ml-2" onsubmit="return confirm('Hapus data transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" title="Hapus">
                                            <i class="fas fa-trash-alt fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada transaksi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-3 border-top">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Proof Modal -->
<div class="modal fade" id="proofModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-info text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold" style="font-size: 1rem;">Bukti Transfer</h5>
                <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0 text-center bg-light">
                <img id="proofImage" src="" class="img-fluid" style="max-height: 80vh;">
            </div>
            <div class="modal-footer border-0 bg-white">
                <a id="downloadProof" href="" download class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-download mr-1"></i> Simpan Gambar
                </a>
                <button class="btn btn-secondary btn-sm px-3" type="button" data-dismiss="modal" style="border-radius: 8px;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showProof(url) {
        document.getElementById('proofImage').src = url;
        document.getElementById('downloadProof').href = url;
        $('#proofModal').modal('show');
    }
</script>
@endsection
