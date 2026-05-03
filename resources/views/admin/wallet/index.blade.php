@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Dompet & Penarikan</h1>
        <a href="{{ route('admin.wallet.transactions') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-list fa-sm text-white-50"></i> Semua Transaksi
        </a>
    </div>

    <!-- Pending Withdrawals -->
    <div class="card shadow mb-4" style="border-radius: 12px; border: none;">
        <div class="card-header py-3 bg-gradient-primary text-white" style="border-radius: 12px 12px 0 0;">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-clock mr-2"></i>Menunggu Persetujuan Penarikan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">User</th>
                            <th class="border-0">Waktu</th>
                            <th class="border-0">Bank Info</th>
                            <th class="border-0 text-right">Nominal</th>
                            <th class="border-0 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingWithdrawals as $wd)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $wd->wallet->user->name }}</div>
                                <div class="badge badge-secondary">{{ strtoupper($wd->wallet->user->role) }}</div>
                            </td>
                            <td class="align-middle text-dark">{{ $wd->created_at->format('d/m/Y H:i') }}</td>
                            <td class="align-middle">
                                <div class="text-dark font-weight-bold">{{ $wd->bank_name }}</div>
                                <div class="text-xs text-muted">{{ $wd->account_number }} a/n {{ $wd->account_name }}</div>
                            </td>
                            <td class="align-middle text-right font-weight-bold text-primary">
                                Rp {{ number_format($wd->amount, 0, ',', '.') }}
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-success btn-sm font-weight-bold px-3 mr-1" 
                                        onclick="showProcessForm({{ $wd->id }}, 'approve', '{{ $wd->wallet->user->name }}', '{{ number_format($wd->amount, 0, ',', '.') }}')">
                                    Approve
                                </button>
                                <button class="btn btn-outline-danger btn-sm font-weight-bold px-3" 
                                        onclick="showProcessForm({{ $wd->id }}, 'reject', '{{ $wd->wallet->user->name }}', '{{ number_format($wd->amount, 0, ',', '.') }}')">
                                    Reject
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Tidak ada pengajuan penarikan baru</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Wallet Summary -->
    <div class="card shadow mb-4" style="border-radius: 12px; border: none;">
        <div class="card-header py-3 bg-white" style="border-radius: 12px 12px 0 0;">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i>Ringkasan Saldo User</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="walletsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">User</th>
                            <th class="border-0">Chapter</th>
                            <th class="border-0">ID Wallet</th>
                            <th class="border-0 text-right">Saldo Tersedia</th>
                            <th class="border-0 text-right">Saldo Tertahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wallets as $w)
                        <tr>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $w->user->name }}</div>
                                <div class="text-xs text-muted">{{ $w->user->role }}</div>
                            </td>
                            <td class="align-middle text-dark font-weight-bold">{{ $w->user->chapter }}</td>
                            <td class="align-middle"><span class="badge badge-light border">{{ $w->wallet_id }}</span></td>
                            <td class="align-middle text-right font-weight-bold text-success">
                                Rp {{ number_format($w->balance, 0, ',', '.') }}
                            </td>
                            <td class="align-middle text-right font-weight-bold text-warning">
                                Rp {{ number_format($w->pending_balance, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Process Modal -->
<div class="modal fade" id="processModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="processForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" id="modalAction">
            <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0 py-3" id="modalHeader" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold" id="modalTitle">Konfirmasi</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <p id="modalMessage" class="text-dark"></p>
                    <div class="form-group" id="proofUploadGroup">
                        <label class="font-weight-bold text-dark text-uppercase small">Upload Bukti Transfer</label>
                        <div class="custom-file">
                            <input type="file" name="proof_of_transfer" class="custom-file-input" id="proofInput">
                            <label class="custom-file-label" for="proofInput">Pilih gambar...</label>
                        </div>
                        <small class="text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
                    </div>
                    <div class="form-group mt-3">
                        <label class="font-weight-bold text-dark text-uppercase small">Catatan (Optional)</label>
                        <textarea name="admin_note" class="form-control" rows="3" placeholder="Masukkan alasan jika ditolak atau info transfer jika disetujui"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button class="btn btn-secondary px-4" type="button" data-dismiss="modal">Batal</button>
                    <button id="modalSubmitBtn" class="btn px-4 font-weight-bold" type="submit">Konfirmasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showProcessForm(id, action, name, amount) {
    const form = document.getElementById('processForm');
    const actionInput = document.getElementById('modalAction');
    const header = document.getElementById('modalHeader');
    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const submitBtn = document.getElementById('modalSubmitBtn');

    const proofGroup = document.getElementById('proofUploadGroup');
    const proofInput = document.getElementById('proofInput');

    form.action = `/admin/wallet/withdrawal/${id}/process`;
    actionInput.value = action;
    
    header.classList.remove('bg-primary', 'bg-success', 'bg-danger');
    submitBtn.classList.remove('btn-success', 'btn-danger');
    
    if (action === 'approve') {
        header.classList.add('bg-success');
        title.innerText = 'Approve Penarikan';
        message.innerHTML = `Apakah Anda yakin ingin menyetujui penarikan dana untuk <b>${name}</b> sebesar <b>Rp ${amount}</b>? <br><br> Pastikan dana sudah ditransfer secara manual dan <b>upload bukti transfer</b> di bawah ini.`;
        submitBtn.classList.add('btn-success');
        proofGroup.style.display = 'block';
        proofInput.required = true;
    } else {
        header.classList.add('bg-danger');
        title.innerText = 'Reject Penarikan';
        message.innerHTML = `Apakah Anda yakin ingin menolak pengajuan penarikan dana untuk <b>${name}</b> sebesar <b>Rp ${amount}</b>?`;
        submitBtn.classList.add('btn-danger');
        proofGroup.style.display = 'none';
        proofInput.required = false;
    }

    // Custom file input label update
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    $('#processModal').modal('show');
}
</script>
@endsection
