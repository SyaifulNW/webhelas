@extends('layouts.masteradmin')

@section('content')
@php
    $months = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $selectedMonth = request('bulan', $bulan ?? date('m'));
    $selectedYear = request('tahun', $tahun ?? date('Y'));
    
    $totalMasuk = $kas->where('type', 'masuk')->sum('nominal');
    $totalKeluar = $kas->where('type', 'keluar')->sum('nominal');
    $saldo = $totalMasuk - $totalKeluar;

    // Access Control
    $user = Auth::user();
    $userName = $user->name ?? '';
    $isLinda = stripos($userName, 'Linda') !== false;
    $isManager = strtolower($user->role ?? '') === 'manager';
    $isAdmin = strtolower($user->role ?? '') === 'administrator';
    $canEdit = $isLinda || $isManager || $isAdmin;
@endphp

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kas</h1>
        <form action="{{ route('admin.keuangan.kas') }}" method="GET" class="form-inline shadow-sm bg-white p-2 rounded border">
            <div class="form-group mx-sm-2">
                <label for="bulan" class="mr-2 small font-weight-bold">Bulan:</label>
                <select name="bulan" id="bulan" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($months as $value => $name)
                        <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mx-sm-2">
                <label for="tahun" class="mr-2 small font-weight-bold">Tahun:</label>
                <select name="tahun" id="tahun" class="form-control form-control-sm" onchange="this.form.submit()">
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-2">
            <div class="card border-left-success shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pemasukan</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-2">
            <div class="card border-left-danger shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Pengeluaran</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-2">
            <div class="card border-left-primary shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($saldo, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Transaksi Kas</h6>
            @if($canEdit)
            <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnTambahBaris">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah data
            </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tableKas">
                    <thead class="bg-light">
                        <tr class="text-center font-weight-bold text-dark">
                            <th width="5%">No</th>
                            <th width="15%">Tanggal Transaksi</th>
                            <th width="45%">Deskripsi</th>
                            <th width="25%">Nominal</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyKas">
                        @forelse($kas as $index => $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                            <td class="text-center align-middle">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                            <td class="align-middle px-3 text-dark">{{ $item->deskripsi }}</td>
                            <td class="text-right align-middle px-3 font-weight-bold {{ $item->type == 'masuk' ? 'text-success' : 'text-danger' }}">
                                {{ $item->type == 'masuk' ? '+' : '-' }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                            </td>
                            <td class="text-center align-middle">
                                @if($canEdit && (!$isAdmin || $isLinda))
                                <form action="{{ route('admin.keuangan.kas.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="5" class="text-center py-4 text-muted small italic">Belum ada data transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
    
    function parseRupiah(formatted) {
        if(!formatted) return 0;
        return parseInt(formatted.replace(/[^0-9]/g, '')) || 0;
    }

    // Handle "Tambah Baris"
    $('#btnTambahBaris').click(function() {
        $('#emptyRow').remove();
        
        let rowCount = $('#tbodyKas tr').length + 1;
        let today = new Date().toISOString().split('T')[0];
        
        // Reverse for display if needed or keep as YYYY-MM-DD for input
        
        let newRow = `
            <tr class="bg-light shadow-sm" id="newRowInput">
                <td class="text-center align-middle font-weight-bold">${rowCount}</td>
                <td class="p-1">
                    <input type="date" id="new_tanggal" class="form-control form-control-sm border-0 bg-white" value="${today}">
                </td>
                <td class="p-1">
                    <input type="text" id="new_deskripsi" class="form-control form-control-sm border-0 bg-white" placeholder="Keterangan transaksi...">
                </td>
                <td class="p-1">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <select id="new_type" class="custom-select custom-select-sm border-0 border-right bg-white" style="border-radius: 0.2rem 0 0 0.2rem;">
                                <option value="masuk" class="text-success">🟢 Masuk</option>
                                <option value="keluar" class="text-danger">🔴 Keluar</option>
                            </select>
                        </div>
                        <input type="text" id="new_nominal_display" class="form-control border-0 bg-white text-right font-weight-bold" placeholder="0">
                    </div>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-success btn-sm btn-circle" id="btnSimpanBaris">
                        <i class="fas fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm btn-circle" onclick="$(this).closest('tr').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#tbodyKas').append(newRow);
        $('#new_deskripsi').focus();
    });

    // Handle input nominal formatting
    $(document).on('input', '#new_nominal_display', function() {
        let p = parseRupiah($(this).val());
        $(this).val(formatRupiah(p));
    });

    // Handle Simpan via AJAX
    $(document).on('click', '#btnSimpanBaris', function() {
        let btn = $(this);
        let row = btn.closest('tr');
        
        let data = {
            _token: '{{ csrf_token() }}',
            tanggal: $('#new_tanggal').val(),
            deskripsi: $('#new_deskripsi').val(),
            nominal: parseRupiah($('#new_nominal_display').val()),
            type: $('#new_type').val()
        };

        if(!data.deskripsi || !data.nominal) {
            alert('Deskripsi dan Nominal harus diisi!');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('admin.keuangan.kas.store') }}",
            type: "POST",
            data: data,
            success: function(response) {
                location.reload(); // Simple reload to update totals and table
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                alert('Gagal menyimpan: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Silakan coba lagi'));
            }
        });
    });

    // Confirm Delete
    $(document).on('submit', '.delete-form', function(e) {
        if(!confirm('Hapus transaksi ini?')) {
            e.preventDefault();
        }
    });

    // Auto focus on deskripsi when date is entered (optional UX)
    $(document).on('change', '#new_tanggal', function() {
        $('#new_deskripsi').focus();
    });
});
</script>

<style>
    .btn-circle {
        width: 30px;
        height: 30px;
        padding: 6px 0px;
        border-radius: 15px;
        text-align: center;
        font-size: 12px;
        line-height: 1.42857;
    }
    .form-control-sm, .custom-select-sm {
        height: calc(1.5em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    #tableKas thead th {
        border-top: none;
        vertical-align: middle;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
</style>
@endsection
