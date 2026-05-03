@extends('layouts.masteradmin')

@section('content')
@php
    $user = Auth::user();
    $canEdit = strtolower($user->role) !== 'administrator' || stripos($user->name, 'Linda') !== false;
@endphp

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Zakat</h1>
        <form action="{{ route('admin.keuangan.zakat') }}" method="GET" class="d-flex" id="filterForm" style="gap: 5px;">
            <select name="bulan" class="form-control form-control-sm" style="width: 120px;" onchange="this.form.submit()">
                <option value="all" {{ $bulan == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                @for($m=1; $m<=12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="tahun" class="form-control form-control-sm" style="width: 100px;" onchange="this.form.submit()">
                <option value="all" {{ $tahun == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                @for($y=date('Y'); $y>=2024; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    <!-- Zakat Panels as Tabs -->
    <ul class="nav nav-tabs mb-4 px-2" id="zakatTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active premium-tab color-primary" id="zakat-kelas-tab-link" data-toggle="tab" data-target="#zakat-kelas-tab" type="button" role="tab" aria-controls="zakat-kelas-tab" aria-selected="true">
                <i class="fas fa-building"></i> Panel Zakat Kelas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link premium-tab color-success" id="zakat-fitra-tab-link" data-toggle="tab" data-target="#zakat-fitra-tab" type="button" role="tab" aria-controls="zakat-fitra-tab" aria-selected="false">
                <i class="fas fa-user-circle"></i> Panel Zakat Pribadi Fitra
            </button>
        </li>
    </ul>

    <div class="tab-content" id="zakatTabsContent">
        <!-- ================== TAB 1: ZAKAT KELAS ================== -->
        <div class="tab-pane fade show active" id="zakat-kelas-tab" role="tabpanel" aria-labelledby="zakat-kelas-tab-link">
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #4e73df;">
                    <h6 class="m-0 font-weight-bold text-white">Laporan Zakat Kelas (2.5%)</h6>
                    @if($canEdit)
                    <button class="btn btn-sm btn-light shadow-sm text-primary fw-bold" id="btnAddRowInline">
                        <i class="fas fa-plus fa-sm"></i> Tambah Baris
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered zakat-table mb-0" id="tableZakat" width="100%" cellspacing="0">
                            <thead style="background-color: #ffff00; color: #000; font-weight: bold;">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="45%" class="text-center">Kelas / Item</th>
                                    <th width="20%" class="text-center">Omset (Rp)</th>
                                    <th width="20%" class="text-center">Beban Zakat (2.5%)</th>
                                    @if($canEdit)
                                    <th width="10%" class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="zakat-body">
                                @forelse($zakatRecords as $index => $record)
                                    <tr class="row-zakat" data-id="{{ $record->id }}" data-type="zakat">
                                        <td class="text-center num-col">{{ $index + 1 }}</td>
                                        <td class="p-0">
                                            <div class="d-flex align-items-center">
                                                <input type="text" 
                                                       class="form-control form-control-sm font-weight-bold zakat-auto-save-keterangan" 
                                                       style="border: none; background: transparent; height: 100%; padding: 12px 15px;"
                                                       value="{{ $record->kelas }}"
                                                       placeholder="Ketik nama kelas/item..."
                                                       {{ !$canEdit || $record->is_auto ? 'readonly' : '' }}>
                                                @if($record->is_auto)
                                                    <span class="badge badge-info mr-2" style="font-size: 0.6rem; opacity: 0.7;">AUTO</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-0">
                                            <input type="text" 
                                                   class="form-control form-control-sm text-right font-weight-bold rupiah-format zakat-auto-save-omset" 
                                                   style="border: none; background: transparent; height: 100%; padding: 12px 15px; color: #4e73df;"
                                                   value="{{ number_format($record->omset, 0, ',', '.') }}"
                                                   placeholder="0"
                                                   {{ !$canEdit || $record->is_auto ? 'readonly' : '' }}>
                                        </td>
                                        <td class="font-weight-bold text-center zakat-val">
                                            <div class="d-flex justify-content-between px-3">
                                                <span>Rp</span>
                                                <span class="zakat-num">{{ number_format($record->beban_zakat, 0, ',', '.') }}</span>
                                            </div>
                                        </td>
                                        @if($canEdit)
                                        <td class="text-center">
                                            @if(!$record->is_auto)
                                            <form action="{{ route('admin.keuangan.laba-rugi.destroy', $record->id) }}" method="POST" class="d-inline delete-zakat-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm text-danger p-0" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @else
                                                <i class="fas fa-lock text-gray-300" title="Data Otomatis"></i>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr id="empty-row">
                                        <td colspan="{{ $canEdit ? '5' : '4' }}" class="text-center py-4 text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot style="background-color: #f8f9fc; font-weight: bold;">
                                <tr>
                                    <td colspan="2" class="text-right py-3 px-4">TOTAL</td>
                                    <td class="text-right px-3 py-3 text-primary" id="totalOmset" style="font-size: 1.1rem;">
                                        Rp {{ number_format($zakatRecords->sum('omset'), 0, ',', '.') }}
                                    </td>
                                    <td class="text-center py-3 text-primary" id="totalZakat" style="font-size: 1.1rem;">
                                        Rp {{ number_format($zakatRecords->sum('beban_zakat'), 0, ',', '.') }}
                                    </td>
                                    @if($canEdit)
                                    <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================== TAB 2: ZAKAT PRIBADI FITRA ================== -->
        <div class="tab-pane fade" id="zakat-fitra-tab" role="tabpanel" aria-labelledby="zakat-fitra-tab-link">
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #1cc88a;">
                    <h6 class="m-0 font-weight-bold text-white">Laporan Zakat Pribadi Fitra</h6>
                    @if($canEdit)
                    <button class="btn btn-sm btn-light shadow-sm text-success fw-bold" id="btnAddRowFitra">
                        <i class="fas fa-plus fa-sm"></i> Tambah Baris
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered zakat-table mb-0" id="tableFitra" width="100%" cellspacing="0">
                            <thead style="background-color: #ffff00; color: #000; font-weight: bold;">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="65%" class="text-center">Keterangan</th>
                                    <th width="20%" class="text-center">Nominal (Rp)</th>
                                    @if($canEdit)
                                    <th width="10%" class="text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="fitra-body">
                                @forelse($zakatFitraRecords as $index => $record)
                                    <tr class="row-fitra" data-id="{{ $record->id }}" data-type="zakat_fitra">
                                        <td class="text-center num-col-fitra">{{ $index + 1 }}</td>
                                        <td class="p-0">
                                            <input type="text" 
                                                   class="form-control form-control-sm font-weight-bold zakat-auto-save-keterangan" 
                                                   style="border: none; background: transparent; height: 100%; padding: 12px 15px;"
                                                    value="{{ $record->keterangan }}"
                                                   placeholder="Ketik keterangan..."
                                                   {{ !$canEdit ? 'readonly' : '' }}>
                                        </td>
                                        <td class="p-0">
                                            <input type="text" 
                                                   class="form-control form-control-sm text-right font-weight-bold rupiah-format zakat-auto-save-nominal" 
                                                   style="border: none; background: transparent; height: 100%; padding: 12px 15px; color: #1cc88a;"
                                                   value="{{ number_format($record->nominal, 0, ',', '.') }}"
                                                   placeholder="0"
                                                   {{ !$canEdit ? 'readonly' : '' }}>
                                        </td>
                                        @if($canEdit)
                                        <td class="text-center">
                                            <form action="{{ route('admin.keuangan.laba-rugi.destroy', $record->id) }}" method="POST" class="d-inline delete-zakat-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm text-danger p-0" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr id="empty-row-fitra">
                                        <td colspan="{{ $canEdit ? '4' : '3' }}" class="text-center py-4 text-muted">Belum ada data Zakat Fitra.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot style="background-color: #f8f9fc; font-weight: bold;">
                                <tr>
                                    <td colspan="2" class="text-right py-3 px-4">TOTAL ZAKAT FITRA</td>
                                    <td class="text-right px-3 py-3 text-success" id="totalFitra" style="font-size: 1.1rem;">
                                        Rp {{ number_format($zakatFitraRecords->sum('nominal'), 0, ',', '.') }}
                                    </td>
                                    @if($canEdit)
                                    <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Colored Tabs Styling (Horizontal - Attached) */
    .premium-tab {
        border-radius: 10px 10px 0 0 !important;
        padding: 12px 25px !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        background: #f1f3f9;
        color: #4e73df;
        border: 1px solid #e3e6f0 !important;
        border-bottom: none !important;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-right: 5px;
        position: relative;
        z-index: 1;
    }
    .premium-tab i {
        font-size: 1.1rem;
    }
    .premium-tab.active {
        color: white !important;
        border-bottom: none !important;
        transform: none !important;
        box-shadow: none !important;
        margin-bottom: -2px !important;
        z-index: 10;
    }
    .premium-tab.active.color-primary {
        background-color: #4e73df !important;
        border-color: #4e73df !important;
        margin-bottom: -2px !important;
        padding-bottom: 12px !important;
        z-index: 2;
    }
    .premium-tab.active.color-success {
        background-color: #1cc88a !important;
        color: white !important;
        border-color: #1cc88a !important;
        margin-bottom: -2px !important;
        padding-bottom: 12px !important;
        z-index: 2;
    }
    .premium-tab:hover:not(.active) {
        background-color: #e2e6f0;
    }
    .nav-tabs {
        border-bottom: 2px solid #e3e6f0;
        margin-bottom: 0px !important;
    }
    .nav-item {
        margin-bottom: 10px;
    }

    .zakat-table th, .zakat-table td {
        border: 2px solid #000 !important;
        vertical-align: middle;
        padding: 0 !important;
        color: #000;
        font-family: Arial, sans-serif;
    }
    .zakat-table thead th {
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.05em;
        padding: 12px !important;
    }
    .zakat-table tbody td.num-col, .zakat-table tbody td.font-weight-bold, .zakat-table tbody td.text-center {
        padding: 12px !important;
    }
    .zakat-table tbody td {
        background-color: #fff;
    }
    .form-control:focus {
        background: #fdfdea !important;
        box-shadow: inset 0 0 0 1px #000;
        outline: none;
    }
    .row-zakat:hover {
        background-color: #fcfdfe;
    }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function parseRupiah(formatted) {
        if (!formatted) return 0;
        return parseInt(formatted.toString().replace(/[^0-9]/g, '')) || 0;
    }

    function showMessage(type, msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    }

    function recalculateZakatTotals() {
        let totalOmset = 0;
        let totalZakat = 0;

        $('.row-zakat').each(function() {
            let omset = parseRupiah($(this).find('.zakat-auto-save-omset').val());
            let zakatVal = Math.round(omset * 0.025);
            
            $(this).find('.zakat-num').text(formatRupiah(zakatVal));
            
            totalOmset += omset;
            totalZakat += zakatVal;
        });

        $('#totalOmset').text('Rp ' + formatRupiah(totalOmset));
        $('#totalZakat').text('Rp ' + formatRupiah(totalZakat));
    }

    function recalculateFitraTotals() {
        let totalFitra = 0;
        $('.row-fitra').each(function() {
            let nominal = parseRupiah($(this).find('.zakat-auto-save-nominal').val());
            totalFitra += nominal;
        });
        $('#totalFitra').text('Rp ' + formatRupiah(totalFitra));
    }

    // Tambah Baris Langsung (Zakat Kelas)
    $('#btnAddRowInline').click(function() {
        $('#empty-row').remove();
        const nextNo = $('.row-zakat').length + 1;
        const newRow = `
            <tr class="row-zakat is-new" data-id="" data-type="zakat">
                <td class="text-center num-col">${nextNo}</td>
                <td class="p-0">
                    <input type="text" 
                           class="form-control form-control-sm font-weight-bold zakat-auto-save-keterangan" 
                           style="border: none; background: transparent; height: 100%; padding: 12px 15px;"
                           value=""
                           placeholder="Ketik nama kelas/item...">
                </td>
                <td class="p-0">
                    <input type="text" 
                           class="form-control form-control-sm text-right font-weight-bold rupiah-format zakat-auto-save-omset" 
                           style="border: none; background: transparent; height: 100%; padding: 12px 15px; color: #4e73df;"
                           value=""
                           placeholder="0">
                </td>
                <td class="font-weight-bold text-center zakat-val">
                    <div class="d-flex justify-content-between px-3">
                        <span>Rp</span>
                        <span class="zakat-num">0</span>
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm text-danger btn-remove-row p-0" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#zakat-body').append(newRow);
        $('.row-zakat.is-new:last').find('.zakat-auto-save-keterangan').focus();
    });

    // Tambah Baris Fitra
    $('#btnAddRowFitra').click(function() {
        $('#empty-row-fitra').remove();
        const nextNo = $('.row-fitra').length + 1;
        const newRow = `
            <tr class="row-fitra is-new" data-id="" data-type="zakat_fitra">
                <td class="text-center num-col-fitra">${nextNo}</td>
                <td class="p-0">
                    <input type="text" 
                           class="form-control form-control-sm font-weight-bold zakat-auto-save-keterangan" 
                           style="border: none; background: transparent; height: 100%; padding: 12px 15px;"
                           value=""
                           placeholder="Ketik keterangan...">
                </td>
                <td class="p-0">
                    <input type="text" 
                           class="form-control form-control-sm text-right font-weight-bold rupiah-format zakat-auto-save-nominal" 
                           style="border: none; background: transparent; height: 100%; padding: 12px 15px; color: #1cc88a;"
                           value=""
                           placeholder="0">
                </td>
                <td class="text-center">
                    <button class="btn btn-sm text-danger btn-remove-row p-0" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#fitra-body').append(newRow);
        $('.row-fitra.is-new:last').find('.zakat-auto-save-keterangan').focus();
    });

    $(document).on('click', '.btn-remove-row', function() {
        const isFitra = $(this).closest('tr').hasClass('row-fitra');
        $(this).closest('tr').remove();
        if ($('.row-zakat').length === 0) {
            $('#zakat-body').html('<tr id="empty-row"><td colspan="5" class="text-center py-4 text-muted">Belum ada data.</td></tr>');
        }
        if ($('.row-fitra').length === 0) {
            $('#fitra-body').html('<tr id="empty-row-fitra"><td colspan="4" class="text-center py-4 text-muted">Belum ada data Zakat Fitra.</td></tr>');
        }
        recalculateZakatTotals();
        recalculateFitraTotals();
    });

    // Auto save
    $(document).on('change blur', '.zakat-auto-save-keterangan, .zakat-auto-save-omset, .zakat-auto-save-nominal', function() {
        const $row = $(this).closest('tr');
        const keterangan = $row.find('.zakat-auto-save-keterangan').val().trim();
        const type = $row.data('type') || 'zakat';
        
        let jumlah = 0;
        if (type === 'zakat') {
            jumlah = parseRupiah($row.find('.zakat-auto-save-omset').val());
        } else {
            jumlah = parseRupiah($row.find('.zakat-auto-save-nominal').val());
        }

        // Only save if name and amount are present
        if (!keterangan) return;

        $.ajax({
            url: "{{ route('admin.keuangan.laba-rugi.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                bulan: '{{ $bulan == "all" ? date("m") : $bulan }}',
                tahun: '{{ $tahun == "all" ? date("Y") : $tahun }}',
                type: type,
                keterangan: keterangan,
                jumlah: jumlah,
                tanggal: '{{ date("Y-m-d") }}'
            },
            success: function(response) {
                if (response.success && response.data) {
                    $row.data('id', response.data.id);
                    if ($row.hasClass('is-new')) {
                        $row.removeClass('is-new');
                        // Replace cancel button with delete form
                        $row.find('td:last').html(`
                            <form action="/admin/keuangan/laba-rugi/${response.data.id}" method="POST" class="d-inline delete-zakat-form">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm text-danger p-0" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        `);
                    }
                }
                recalculateZakatTotals();
                recalculateFitraTotals();
            }
        });
    });

    // Delete
    $(document).on('submit', '.delete-zakat-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const isFitra = form.closest('tr').hasClass('row-fitra');
        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            success: function() {
                form.closest('tr').remove();
                if ($('.row-zakat').length === 0) {
                    $('#zakat-body').html('<tr id="empty-row"><td colspan="5" class="text-center py-4 text-muted">Belum ada data.</td></tr>');
                }
                if ($('.row-fitra').length === 0) {
                    $('#fitra-body').html('<tr id="empty-row-fitra"><td colspan="4" class="text-center py-4 text-muted">Belum ada data Zakat Fitra.</td></tr>');
                }
                recalculateZakatTotals();
                recalculateFitraTotals();
                showMessage('success', 'Baris dihapus.');
            }
        });
    });

    // Formatting
    $(document).on('keyup', '.rupiah-format', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        if (val !== "") $(this).val(formatRupiah(parseInt(val)));
        recalculateZakatTotals();
        recalculateFitraTotals();
    });
});
</script>
@endpush

@endsection
