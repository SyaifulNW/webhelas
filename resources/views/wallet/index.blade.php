@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dompet Digital</h1>
        <div class="d-flex align-items-center">
            {{-- Filter --}}
            <form action="{{ route('wallet.index') }}" method="GET" class="form-inline mr-3">
                <div class="input-group input-group-sm mr-2">
                    @php
                        $months = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                    @endphp
                    <select name="month" class="form-control border-0 shadow-sm px-3" style="border-radius: 20px 0 0 20px; font-size: 0.8rem; height: 32px;">
                        @foreach($months as $val => $name)
                            <option value="{{ $val }}" {{ $selectedMonth == $val ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="year" class="form-control border-0 shadow-sm px-3" style="border-radius: 0; font-size: 0.8rem; height: 32px; border-left: 1px solid #eee !important;">
                        @for($y=2026; $y<=2030; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary px-3 shadow-sm" style="border-radius: 0 20px 20px 0; height: 32px; font-size: 0.8rem;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
                @if(request('month') || request('year'))
                    <a href="{{ route('wallet.index') }}" class="btn btn-sm btn-light text-danger shadow-sm px-3" style="border-radius: 20px; font-size: 0.75rem; height: 32px; display: flex; align-items: center;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                @endif
            </form>
            <div class="text-muted d-none d-md-block">ID Wallet: <span class="badge badge-primary px-3" style="border-radius: 12px;">{{ $wallet->wallet_id }}</span></div>
        </div>
    </div>

    <div class="row">
        <!-- Main Balance Card -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card bg-gradient-primary text-white shadow h-100 py-2 border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: rgba(255,255,255,0.95); letter-spacing: 0.5px;">Saldo Tersedia</div>
                            <div class="h2 mb-0 font-weight-bold text-white">Rp {{ number_format($availableBalance, 0, ',', '.') }}</div>
                            <div class="mt-3">
                                <button class="btn btn-success btn-sm font-weight-bold text-white px-4 shadow-sm" style="border-radius: 8px;" data-toggle="modal" data-target="#withdrawModal">
                                    <i class="fas fa-hand-holding-usd mr-1"></i> Tarik Saldo
                                </button>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-3x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Balance Card -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6d4c02; letter-spacing: 0.5px;">Saldo Dalam Proses Pencairan</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-900">Rp {{ number_format($wallet->pending_balance, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow mb-4" style="border-radius: 15px;">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white" style="border-radius: 15px 15px 0 0;">
            <h6 class="m-0 font-weight-bold text-primary">Transaksi Terbaru</h6>
            <a href="{{ route('wallet.history') }}" class="btn btn-sm btn-link font-weight-bold">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr style="background-color: #f1f4f9;">
                            <th class="border-0 text-dark py-3">Tanggal</th>
                            <th class="border-0 text-dark py-3">Deskripsi</th>
                            <th class="border-0 text-dark py-3 text-center">Bukti</th>
                            <th class="border-0 text-dark py-3 text-center">Keterangan</th>
                            <th class="border-0 text-dark py-3 text-right">Nominal</th>
                            <th class="border-0 text-dark py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $tx)
                        <tr>
                            <td class="align-middle">{{ $tx->created_at->format('d M Y H:i') }}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-gray-900">{{ $tx->source ?: ($tx->type == 'income' ? 'Pemasukan' : 'Penarikan Dana') }}</div>
                                <div class="text-xs text-gray-700">{{ $tx->reference_no }}</div>
                            </td>
                            <td class="align-middle text-center">
                                @if($tx->proof_of_transfer)
                                    <img src="{{ asset($tx->proof_of_transfer) }}" 
                                         alt="Bukti Transfer" 
                                         class="img-thumbnail shadow-sm" 
                                         style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; border-radius: 8px; transition: transform 0.2s;"
                                         onclick="showProof('{{ asset($tx->proof_of_transfer) }}')"
                                         onmouseover="this.style.transform='scale(1.1)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                            <td class="align-middle text-center text-xs">
                                @if($tx->admin_note)
                                    <div class="font-weight-bold {{ $tx->status == 'rejected' ? 'text-danger' : ($tx->status == 'success' ? 'text-success' : 'text-primary') }}" style="font-size: 0.8rem; line-height: 1.3;">
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
                                <div class="d-flex flex-column align-items-center justify-content-center py-2">
                                    <div class="d-flex flex-column align-items-center">
                                        @if($tx->status == 'success')
                                            <span class="badge badge-success px-3 py-1">Berhasil</span>
                                        @elseif($tx->status == 'pending')
                                            <span class="badge badge-warning px-3 py-1">Pending</span>
                                        @else
                                            <span class="badge badge-danger px-3 py-1">Ditolak</span>
                                        @endif
                                    </div>

                                    @if($tx->status == 'pending' || $tx->status == 'rejected')
                                    <form action="{{ route('wallet.transaction.destroy', $tx->id) }}" method="POST" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger py-1 px-3 shadow-sm font-weight-bold btn-cancel-withdrawal" style="font-size: 0.65rem; border-radius: 20px;" title="Batalkan Pengajuan">
                                            Batalkan
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada transaksi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes alertShakePulse {
        0%   { transform: translateX(0);    box-shadow: 0 0 0 0 rgba(220,38,38,0.5); }
        10%  { transform: translateX(-8px); box-shadow: 0 0 0 6px rgba(220,38,38,0.3); }
        20%  { transform: translateX(8px);  }
        30%  { transform: translateX(-6px); box-shadow: 0 0 0 10px rgba(220,38,38,0.1); }
        40%  { transform: translateX(6px);  }
        50%  { transform: translateX(-4px); box-shadow: 0 0 0 14px rgba(220,38,38,0); }
        60%  { transform: translateX(4px);  }
        70%  { transform: translateX(-2px); }
        80%  { transform: translateX(2px);  }
        90%  { transform: translateX(-1px); }
        100% { transform: translateX(0);    box-shadow: 0 0 0 0 rgba(220,38,38,0); }
    }
    .alert-shake {
        animation: alertShakePulse 0.7s ease;
        border-radius: 6px;
    }
</style>

<!-- Withdraw Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" role="dialog" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
        <form id="withdrawForm" action="{{ route('wallet.withdraw') }}" method="POST">
            @csrf
            <div class="modal-content border-0" style="border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">

                {{-- HEADER --}}
                <div class="modal-header border-0 px-4 py-3 d-flex align-items-center justify-content-between"
                    style="background: linear-gradient(135deg, #1e40af, #2563eb);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hand-holding-usd text-white mr-2" style="font-size:1.2rem;"></i>
                        <h5 class="modal-title font-weight-bold text-white mb-0" id="withdrawModalLabel" style="font-size:1rem; letter-spacing:0.5px;">
                            Ajukan Penarikan Saldo
                        </h5>
                    </div>
                    <button type="button" class="close text-white m-0 p-0" data-dismiss="modal"
                        style="font-size:1.4rem; opacity:1; line-height:1;">
                        <span>&times;</span>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="modal-body p-4" style="background: #f8fafc;">

                    {{-- Alert Merah: Nominal Kurang --}}
                    <div id="alertMinimal" class="d-none d-flex align-items-center px-3 py-2 mb-3 rounded"
                        style="background:#fee2e2; border-left:4px solid #dc2626;">
                        <i class="fas fa-exclamation-circle mr-2" style="color:#dc2626;"></i>
                        <span class="font-weight-bold" style="font-size:0.85rem; color:#dc2626;">
                            Nominal minimal penarikan adalah Rp 100.000
                        </span>
                    </div>

                    {{-- ROW: Nominal + Rekening --}}
                    <div class="row">
                        {{-- Kiri: Nominal --}}
                        <div class="col-md-5 mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size:0.85rem;">
                                <i class="fas fa-coins text-warning mr-1"></i> Nominal Penarikan
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold" style="background:#e8f0fe; border-right:0; color:#2563eb; font-size:0.95rem;">Rp</span>
                                </div>
                                <input type="text" id="withdrawAmountDisplay"
                                    class="form-control font-weight-bold"
                                    style="border-left:0; font-size:1rem;"
                                    placeholder="0" autocomplete="off">
                                <input type="hidden" id="withdrawAmount" name="amount">
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size:0.78rem;">
                                <i class="fas fa-wallet mr-1"></i>
                                Tersedia: <strong>Rp {{ number_format($availableBalance, 0, ',', '.') }}</strong>
                            </small>
                        </div>

                        {{-- Kanan: Informasi Rekening --}}
                        <div class="col-md-7 mb-3">
                            <label class="font-weight-bold text-dark mb-1" style="font-size:0.85rem;">
                                <i class="fas fa-university text-primary mr-1"></i> Informasi Rekening
                                @if($savedBankName)
                                    <span class="badge badge-success ml-1" style="font-size:0.65rem;">Tersimpan</span>
                                @endif
                            </label>
                            <input type="text" name="bank_name" class="form-control mb-2"
                                placeholder="Nama Bank (Contoh: BCA, Mandiri)"
                                value="{{ $savedBankName ?? '' }}" required
                                style="font-size:0.88rem;">
                            <input type="text" name="account_number" class="form-control mb-2"
                                placeholder="Nomor Rekening"
                                value="{{ $savedAccountNumber ?? '' }}" required
                                style="font-size:0.88rem;">
                            <input type="text" name="account_name" class="form-control"
                                placeholder="Nama Pemilik Rekening"
                                value="{{ $savedAccountName ?? '' }}" required
                                style="font-size:0.88rem;">
                            @if($savedBankName)
                                <small class="text-success mt-1 d-block" style="font-size:0.75rem;">
                                    <i class="fas fa-check-circle mr-1"></i> Data rekening otomatis terisi. Anda bisa mengubahnya jika perlu.
                                </small>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="modal-footer border-0 px-4 pb-3 pt-0" style="background:#f8fafc;">
                    <button type="button" class="btn btn-light px-4 font-weight-bold" data-dismiss="modal"
                        style="border-radius:8px; font-size:0.88rem;">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold"
                        style="border-radius:8px; font-size:0.88rem; background:linear-gradient(135deg, #2563eb, #1e40af); border:none;">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Pengajuan
                    </button>
                </div>

            </div>
        </form>
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
    
    // ... existing scripts ...
    // Format angka dengan titik ribuan
    function formatRibuan(val) {
        val = val.replace(/\D/g, '');
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    const displayInput = document.getElementById('withdrawAmountDisplay');
    const hiddenInput  = document.getElementById('withdrawAmount');

    displayInput.addEventListener('input', function () {
        const raw    = this.value.replace(/\./g, '');
        this.value   = formatRibuan(raw);
        hiddenInput.value = raw;

        // Tampilkan/sembunyikan alert merah
        const amount  = parseInt(raw) || 0;
        const alertEl = document.getElementById('alertMinimal');
        if (raw.length > 0 && amount < 100000) {
            alertEl.classList.remove('d-none');
        } else {
            alertEl.classList.add('d-none');
        }
    });

    // Pesan validasi Bahasa Indonesia untuk semua field required
    const pesanValidasi = {
        'bank_name':       'Harap isi nama bank terlebih dahulu.',
        'account_number':  'Harap isi nomor rekening terlebih dahulu.',
        'account_name':    'Harap isi nama pemilik rekening.',
    };

    document.querySelectorAll('#withdrawForm input[required]').forEach(function(input) {
        input.addEventListener('invalid', function () {
            if (pesanValidasi[this.name]) {
                this.setCustomValidity(pesanValidasi[this.name]);
            } else {
                this.setCustomValidity('Kolom ini wajib diisi.');
            }
        });
        input.addEventListener('input', function () {
            this.setCustomValidity(''); // reset agar bisa submit ulang
        });
    });

    document.getElementById('withdrawForm').addEventListener('submit', function (e) {
        const amount    = parseInt(hiddenInput.value) || 0;
        const available = {{ $availableBalance }};

        if (amount < 100000) {
            e.preventDefault();
            const alertEl = document.getElementById('alertMinimal');
            alertEl.classList.remove('d-none');
            
            // Tambahkan animasi denyut
            alertEl.classList.remove('alert-shake');
            void alertEl.offsetWidth; // trigger reflow
            alertEl.classList.add('alert-shake');
            
            displayInput.focus();
            return;
        }

        if (amount > available) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Saldo Tidak Mencukupi',
                text: 'Jumlah penarikan melebihi saldo tersedia Anda.',
                confirmButtonColor: '#2563eb',
            });
        }
    });

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
