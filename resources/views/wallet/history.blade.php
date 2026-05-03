@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Riwayat Transaksi</h1>
        <a href="{{ route('wallet.index') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form action="{{ route('wallet.history') }}" method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label class="font-weight-bold text-xs text-uppercase">Jenis Transaksi</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Penarikan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-xs text-uppercase">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Berhasil</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm btn-block shadow-sm">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr style="background-color: #f8faff;">
                            <th class="border-0 py-3">Tanggal</th>
                            <th class="border-0 py-3">Reference</th>
                            <th class="border-0 py-3">Deskripsi</th>
                            <th class="border-0 py-3 text-center">Bukti</th>
                            <th class="border-0 py-3 text-center">Keterangan</th>
                            <th class="border-0 py-3 text-right">Nominal</th>
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
                            <td class="align-middle text-xs font-weight-bold">{{ $tx->reference_no }}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $tx->source ?: ($tx->type == 'income' ? 'Pemasukan' : 'Penarikan Dana') }}</div>
                                @if($tx->description)
                                    <div class="text-xs text-muted">{{ $tx->description }}</div>
                                @endif
                                @if($tx->type == 'withdrawal')
                                    <div class="text-xs text-primary">{{ $tx->bank_name }} - {{ $tx->account_number }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($tx->proof_of_transfer)
                                    <img src="{{ asset($tx->proof_of_transfer) }}" 
                                         alt="Bukti Transfer" 
                                         class="img-thumbnail shadow-sm" 
                                         style="width: 45px; height: 45px; object-fit: cover; cursor: pointer; border-radius: 6px; transition: transform 0.2s;"
                                         onclick="showProof('{{ asset($tx->proof_of_transfer) }}')"
                                         onmouseover="this.style.transform='scale(1.1)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($tx->admin_note)
                                    <div class="text-xs font-weight-bold {{ $tx->status == 'rejected' ? 'text-danger' : ($tx->status == 'success' ? 'text-success' : 'text-primary') }}" style="font-size: 0.9rem; line-height: 1.3;">
                                        {{ $tx->admin_note }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-right font-weight-bold {{ $tx->type == 'income' ? 'text-success' : 'text-danger' }}">
                                {{ $tx->type == 'income' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="align-middle text-center">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        @if($tx->status == 'success')
                                            <span class="badge badge-success px-3 py-1 shadow-sm">Success</span>
                                        @elseif($tx->status == 'pending')
                                            <span class="badge badge-warning px-3 py-1 shadow-sm">Pending</span>
                                        @else
                                            <div class="badge badge-danger px-3 py-1 shadow-sm">Rejected</div>
                                        @endif

                                        @if($tx->status == 'pending' || $tx->status == 'rejected')
                                        <form action="{{ route('wallet.transaction.destroy', $tx->id) }}" method="POST" class="ml-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger py-1 px-3 shadow-sm font-weight-bold btn-cancel-withdrawal" style="font-size: 0.65rem; border-radius: 20px;" title="Batalkan Pengajuan">
                                                Batalkan
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Data transaksi tidak ditemukan</td>
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

    // SweetAlert untuk pembatalan penarikan
    $(document).on('click', '.btn-cancel-withdrawal', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        Swal.fire({
            title: 'Batalkan Pengajuan?',
            text: "Apakah Anda yakin ingin membatalkan pengajuan penarikan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endsection
