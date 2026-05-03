@extends('layouts.masteradmin')

@section('content')
    @php
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        $selectedMonth = request('bulan', $bulan ?? date('m'));
        $selectedYear = request('tahun', $tahun ?? date('Y'));

        $totalMasuk = $kas->sum('masuk');
        $totalKeluar = $kas->sum('keluar');
        $saldoAwal = $saldoAwal ?? 0;
        $saldoAkhir = $saldoAwal + $totalMasuk - $totalKeluar;
    @endphp

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Kas Kecil</h1>
            <form action="{{ route('admin.keuangan.kas-kecil.index') }}" method="GET"
                class="form-inline shadow-sm bg-white p-2 rounded border">
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
                <a href="{{ route('admin.keuangan.kas-kecil.export-pdf', ['bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}"
                    class="btn btn-danger btn-sm ml-2 shadow-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak PDF
                </a>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card border-left-info shadow h-100 py-1">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Saldo Bulan Sebelumnya
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">Rp
                                    {{ number_format($saldoAwal, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card border-left-success shadow h-100 py-1">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pemasukan</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">Rp
                                    {{ number_format($totalMasuk, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card border-left-danger shadow h-100 py-1">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Pengeluaran
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">Rp
                                    {{ number_format($totalKeluar, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="card border-left-primary shadow h-100 py-1">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Saldo Akhir</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">Rp
                                    {{ number_format($saldoAkhir, 0, ',', '.') }}</div>
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
                                <th>KET</th>
                                <th width="15%">SALDO MASUK</th>
                                <th width="15%">SALDO KELUAR</th>
                                <th width="15%">SISA</th>
                                <th width="10%">BUKTI</th>
                                <th width="5%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKasKecil">
                            @php $runningBalance = $saldoAwal; @endphp
                            @if($saldoAwal != 0)
                            <tr class="bg-light">
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                                <td class="align-middle px-3 font-italic text-muted">Saldo Bulan Sebelumnya</td>
                                <td class="text-right align-middle px-3">-</td>
                                <td class="text-right align-middle px-3">-</td>
                                <td class="text-right align-middle px-3 font-weight-bold">
                                    {{ number_format($saldoAwal, 0, ',', '.') }}
                                </td>
                                <td class="text-center align-middle">-</td>
                                <td class="text-center align-middle">-</td>
                            </tr>
                            @endif
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
                                        <div class="d-flex flex-column align-items-center">
                                            @if($item->bukti_transfer)
                                                <div class="position-relative mb-1">
                                                    <img src="{{ asset($item->bukti_transfer) }}" alt="Bukti"
                                                        class="img-thumbnail shadow-sm preview-image"
                                                        style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;"
                                                        onclick="previewImage('{{ asset($item->bukti_transfer) }}', 'Bukti Kas - {{ $item->keterangan }}')">
                                                </div>
                                            @endif

                                            {{-- Tombol Upload untuk Lama/Baru --}}
                                            <label class="btn btn-sm btn-outline-primary p-0 px-2 m-0"
                                                style="font-size: 10px; cursor: pointer;" title="Upload/Ganti Bukti">
                                                <i class="fas fa-upload mr-1"></i>
                                                {{ $item->bukti_transfer ? 'Ganti' : 'Upload' }}
                                                <input type="file" class="d-none upload-bukti-existing"
                                                    data-id="{{ $item->id }}" accept="image/*">
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <form action="{{ route('admin.keuangan.kas-kecil.destroy', $item->id) }}" method="POST"
                                            class="d-inline delete-form">
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
                                    <td colspan="8" class="text-center py-4 text-muted small italic">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function parseRupiah(formatted) {
                if (!formatted) return 0;
                return parseInt(formatted.replace(/[^0-9]/g, '')) || 0;
            }

            $('#btnTambahBaris').click(function () {
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
                    <td class="p-2 align-middle">
                        <div class="custom-file" style="font-size: 10px;">
                            <input type="file" class="custom-file-input" id="new_bukti" accept="image/*">
                            <label class="custom-file-label" for="new_bukti" style="padding: 0.25rem 0.5rem; height: auto;">Pilih Gambar</label>
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

                $('#tbodyKasKecil').append(newRow);
                $('#new_keterangan').focus();
            });

            $(document).on('input', '.rupiah', function () {
                let p = parseRupiah($(this).val());
                $(this).val(p > 0 ? formatRupiah(p) : '');
            });

            $(document).on('click', '#btnSimpanBaris', function () {
                let btn = $(this);
                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('tanggal', $('#new_tanggal').val());
                formData.append('keterangan', $('#new_keterangan').val());
                formData.append('masuk', parseRupiah($('#new_masuk').val()));
                formData.append('keluar', parseRupiah($('#new_keluar').val()));

                let fileInput = document.getElementById('new_bukti');
                if (fileInput.files.length > 0) {
                    formData.append('bukti_transfer', fileInput.files[0]);
                }

                if (!$('#new_keterangan').val() || (parseRupiah($('#new_masuk').val()) === 0 && parseRupiah($('#new_keluar').val()) === 0)) {
                    alert('Keterangan dan Nominal (Masuk/Keluar) harus diisi!');
                    return;
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.keuangan.kas-kecil.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        location.reload();
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                        alert('Gagal menyimpan data.');
                    }
                });
            });

            $(document).on('change', '.custom-file-input', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Handle upload untuk data existing
            $(document).on('change', '.upload-bukti-existing', function () {
                let file = this.files[0];
                let id = $(this).data('id');
                let container = $(this).closest('td');

                if (!file) return;

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('bukti_transfer', file);

                let originalContent = container.html();
                container.html('<i class="fas fa-spinner fa-spin text-primary"></i>');

                $.ajax({
                    url: `/admin/keuangan/kas-kecil/${id}/upload-bukti`,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            location.reload(); // Reload paling aman untuk update tampilan sisa dll
                        }
                    },
                    error: function () {
                        alert('Gagal mengupload bukti.');
                        container.html(originalContent);
                    }
                });
            });

            $(document).on('submit', '.delete-form', function (e) {
                if (!confirm('Hapus data ini?')) {
                    e.preventDefault();
                }
            });

            window.previewImage = function (src, title) {
                $('#imageFullPreview').attr('src', src);
                $('#previewTitle').text(title);
                $('#modalPreviewImage').modal('show');
            };
        });
    </script>

    <!-- Modal Preview Gambar -->
    <div class="modal fade" id="modalPreviewImage" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg bg-transparent">
                <div class="modal-header border-0 bg-dark text-white rounded-top" style="opacity: 0.9;">
                    <h5 class="modal-title" id="previewTitle">Bukti Transfer</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark rounded-bottom" style="opacity: 0.9;">
                    <img id="imageFullPreview" src="" class="img-fluid w-100 rounded-bottom" alt="Preview">
                </div>
            </div>
        </div>
    </div>

    <style>
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        .header-yellow th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #ffff00 !important;
            border-bottom: 2px solid #000 !important;
        }

        .header-yellow {
            background-color: #ffff00 !important;
        }

        .table-bordered td,
        .table-bordered th {
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

        .preview-image {
            transition: transform 0.2s;
        }

        .preview-image:hover {
            transform: scale(1.1);
            z-index: 10;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
        }
    </style>
@endsection