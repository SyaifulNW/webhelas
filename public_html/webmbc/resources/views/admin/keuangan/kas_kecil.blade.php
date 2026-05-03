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
    
    $totalMasuk = $kas->sum('masuk');
    $totalKeluar = $kas->sum('keluar');
    $saldoAkhir = $totalMasuk - $totalKeluar;
@endphp

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Kas Kecil</h1>
        <form action="{{ route('admin.keuangan.kas-kecil.index') }}" method="GET" class="form-inline shadow-sm bg-white p-2 rounded border">
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
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo Akhir</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Data Kas Kecil</h6>
            <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnTambahBaris">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah data
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="tableKasKecil">
                    <thead>
                        <tr class="text-center font-weight-bold text-dark header-yellow">
                            <th width="5%">NO</th>
                            <th width="12%">TGL</th>
                            <th>KETERANGAN</th>
                            <th width="15%">SALDO MASUK</th>
                            <th width="15%">SALDO KELUAR</th>
                            <th width="15%">SISA</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyKasKecil">
                        @php $runningBalance = 0; @endphp
                        @forelse($kas as $index => $item)
                        @php $runningBalance += ($item->masuk - $item->keluar); @endphp
                        <tr data-id="{{ $item->id }}">
                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                            <td class="text-center align-middle">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                            <td class="align-middle px-3">{{ $item->keterangan }}</td>
                            <td class="text-right align-middle px-3">
                                {{ $item->masuk > 0 ? number_format($item->masuk, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-right align-middle px-3">
                                {{ $item->keluar > 0 ? number_format($item->keluar, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-right align-middle px-3 font-weight-bold">
                                {{ number_format($runningBalance, 0, ',', '.') }}
                            </td>
                            <td class="text-center align-middle">
                                <form action="{{ route('admin.keuangan.kas-kecil.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="7" class="text-center py-4 text-muted small italic">Belum ada data.</td>
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

    $('#btnTambahBaris').click(function() {
        $('#emptyRow').remove();
        
        let rowCount = $('#tbodyKasKecil tr').length + 1;
        let today = new Date().toISOString().split('T')[0];
        
        let newRow = `
            <tr class="bg-light shadow-sm" id="newRowInput">
                <td class="text-center align-middle font-weight-bold">${rowCount}</td>
                <td class="p-1">
                    <input type="date" id="new_tanggal" class="form-control form-control-sm border-0 bg-white" value="${today}">
                </td>
                <td class="p-1">
                    <input type="text" id="new_keterangan" class="form-control form-control-sm border-0 bg-white" placeholder="Keterangan...">
                </td>
                <td class="p-1">
                    <input type="text" id="new_masuk" class="form-control form-control-sm border-0 bg-white text-right rupiah" placeholder="0">
                </td>
                <td class="p-1">
                    <input type="text" id="new_keluar" class="form-control form-control-sm border-0 bg-white text-right rupiah" placeholder="0">
                </td>
                <td class="text-center align-middle font-weight-bold">-</td>
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
        
        $('#tbodyKasKecil').append(newRow);
        $('#new_keterangan').focus();
    });

    $(document).on('input', '.rupiah', function() {
        let p = parseRupiah($(this).val());
        $(this).val(p > 0 ? formatRupiah(p) : '');
    });

    $(document).on('click', '#btnSimpanBaris', function() {
        let btn = $(this);
        let data = {
            _token: '{{ csrf_token() }}',
            tanggal: $('#new_tanggal').val(),
            keterangan: $('#new_keterangan').val(),
            masuk: parseRupiah($('#new_masuk').val()),
            keluar: parseRupiah($('#new_keluar').val())
        };

        if(!data.keterangan || (data.masuk === 0 && data.keluar === 0)) {
            alert('Keterangan dan Nominal (Masuk/Keluar) harus diisi!');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('admin.keuangan.kas-kecil.store') }}",
            type: "POST",
            data: data,
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                alert('Gagal menyimpan data.');
            }
        });
    });

    $(document).on('submit', '.delete-form', function(e) {
        if(!confirm('Hapus data ini?')) {
            e.preventDefault();
        }
    });
});
</script>

<style>
    .header-yellow {
        background-color: #ffff00 !important;
    }
    .table-bordered td, .table-bordered th {
        border: 1px solid #000 !important;
    }
    .btn-circle {
        width: 30px;
        height: 30px;
        padding: 6px 0px;
        border-radius: 15px;
        text-align: center;
        font-size: 12px;
        line-height: 1.42857;
    }
</style>
@endsection
