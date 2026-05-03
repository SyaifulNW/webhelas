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
    
    // Access Control
    $user = Auth::user();
    $userName = $user->name ?? '';
    $userRole = strtolower($user->role ?? '');
    $isLinda = stripos($userName, 'Linda') !== false;
    $isAdmin = $userRole === 'administrator';
    $canEdit = !$isAdmin || $isLinda;

    // Pre-calculate total pendapatan for percentage column
    $calcTotalPendapatan = ($totalMbc ?? 0) + ($totalSmi ?? 0) + ($totalPrivate ?? 0) + ($pendapatan ? $pendapatan->sum('jumlah') : 0);
    $totalSmiPendaftaran = $totalSmiPendaftaran ?? 0;
    $totalSmiSpp = $totalSmiSpp ?? 0;
@endphp

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Laba Rugi</h1>
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.keuangan.laba-rugi.export-pdf', ['bulan' => $selectedMonth, 'tahun' => $selectedYear]) }}" class="btn btn-sm btn-danger shadow-sm mr-3">
                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Export PDF
            </a>
            <form action="{{ route('admin.keuangan.laba-rugi') }}" method="GET" class="form-inline shadow-sm bg-white p-2 rounded">
                @if(request('embed'))
                    <input type="hidden" name="embed" value="{{ request('embed') }}">
                @endif
                <div class="form-group mx-sm-2">
                    <label for="bulan" class="mr-2 small font-weight-bold">Bulan:</label>
                    <select name="bulan" id="bulan" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                        @foreach($months as $value => $name)
                            <option value="{{ $value }}" {{ $selectedMonth == $value ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mx-sm-2">
                    <label for="tahun" class="mr-2 small font-weight-bold">Tahun:</label>
                    <select name="tahun" id="tahun" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                        @php
                            $currentYear = date('Y');
                        @endphp
                        @for($y = $currentYear - 2; $y <= $currentYear + 2; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>
        </div>
    <style>
        :root {
            --primary-soft: rgba(78, 115, 223, 0.08);
            --danger-soft: rgba(231, 74, 59, 0.08);
            --success-soft: rgba(28, 200, 138, 0.08);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1) !important;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f1f1ff;
            padding: 1.25rem;
        }

        .table-laba-rugi thead th {
            background-color: #f8f9fc;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #4e73df;
            border-top: none;
            border-bottom: 2px solid #e3e6f0;
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table-laba-rugi tbody tr {
            transition: all 0.15s ease;
        }

        .table-laba-rugi tbody tr:hover:not(.section-header):not(.font-weight-bold) {
            background-color: #fcfdfe !important;
        }

        .hover-underline {
            text-decoration: none !important;
        }
        
        .hover-underline:hover {
            text-decoration: underline !important;
        }

        .nominal-input {
            background: #fff;
            border: 1px solid #d1d3e2;
            text-align: right;
            width: 120px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: inherit;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: inline-block;
        }

        .nominal-input:hover:not([readonly]) {
            border-color: #4e73df;
            background: #f8f9ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .nominal-input:focus:not([readonly]) {
            outline: none;
            border-color: #4e73df !important;
            background: #fff;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15), 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }

        .nominal-input[readonly] {
            background-color: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
            cursor: default;
            color: #5a5c69;
        }

        .nominal-input-ghost {
            background: transparent !important;
            border: 1px solid transparent !important;
            text-align: right;
            width: 120px;
            font-weight: 700;
            padding: 4px 8px;
            transition: all 0.2s;
            cursor: pointer;
            box-shadow: none !important;
            color: inherit;
        }

        .nominal-input-ghost:hover {
            background: #f8f9fc !important;
            border-color: #d1d3e2 !important;
        }

        .nominal-input-ghost:focus {
            outline: none;
            background: #fff !important;
            border-color: #4e73df !important;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1) !important;
            cursor: text;
        }

        .date-input-ghost {
            background: transparent;
            border: none;
            font-size: 0.75rem;
            color: #5a5c69;
            padding: 2px 0;
            cursor: pointer;
            width: auto;
            transition: color 0.2s;
            font-weight: 500;
        }

        .date-input-ghost:hover {
            color: #2e59d9;
        }

        .sub-row-indicator {
            position: relative;
            padding-left: 2.5rem !important;
            padding-right: 10px !important;
        }

        .sub-row-indicator::before {
            content: "";
            position: absolute;
            left: 1.25rem;
            top: 0;
            bottom: 0;
            width: 1.5px;
            background: #ededf5;
        }

        .item-letter {
            font-weight: 700;
            color: #858796;
            margin-right: 8px;
            font-family: monospace;
        }

        .badge-system {
            background-color: #f0f3ff;
            color: #4e73df;
            font-weight: 700;
            font-size: 0.6rem;
            padding: 3px 7px;
            border-radius: 4px;
            letter-spacing: 0.02em;
        }

        .section-header {
            background-color: #f8f9fc;
            font-weight: 800;
            color: #2d2e33;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }

        .btn-toggle-sub {
            width: 22px;
            height: 22px;
            padding: 0;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #e3e6f0;
            color: #858796;
            transition: all 0.2s;
        }

        .btn-toggle-sub:hover {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
        }
        .btn-add-biaya {
            width: 22px;
            height: 22px;
            padding: 0;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1cc88a;
            border: 1px solid #169b6b;
            color: white;
            transition: all 0.2s;
            font-size: 10px;
        }
        .btn-add-biaya:hover {
            background: #17a673;
            border-color: #169b6b;
            color: white;
            transform: scale(1.1);
        }
        .table-laba-rugi {
            border-collapse: collapse;
        }

        .table-laba-rugi th,
        .table-laba-rugi td {
            border: 2px solid #b7b9cc !important;
            vertical-align: middle;
            color: #2d2e33;
        }

        .text-muted {
            color: #5a5c69 !important;
        }

        .text-dark {
            color: #1a1b1f !important;
        }
    </style>
</div>


    <!-- Table Card -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list-alt mr-2"></i>
                Detail Laporan Periode {{ $selectedMonth == 'all' ? 'Semua Bulan' : $months[$selectedMonth] }} {{ $selectedYear == 'all' ? 'Semua Tahun' : $selectedYear }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-laba-rugi mb-0" id="table-laba-rugi" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="50%">Deskripsi Transaksi</th>
                            <th width="20%" class="text-right">Nominal (Rp)</th>
                            <th width="10%" class="text-center">%</th>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <th width="15%" class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <!-- PENDAPATAN SECTION -->
                        <tr class="section-header">
                            <td colspan="{{ strtolower(Auth::user()->role) === 'administrator' ? '4' : '5' }}" class="px-3 py-2">PENDAPATAN</td>
                        </tr>
                        @php $totalPendapatan = 0; @endphp
                        
                        <!-- Auto Data from Sales Plan -->
                        <tr data-category="Pendapatan MBC" class="row-pendapatan">
                            <td class="text-center font-weight-bold">1</td>
                            <td class="px-3">
                                <div class="font-weight-bold text-dark">Pendapatan MBC (Auto)</div>
                                <div class="small text-muted">Berdasarkan data Sales Plan</div>
                            </td>
                            <td class="text-right px-3 text-primary font-weight-bold amount-cell">
                                <a href="{{ route('admin.salesplan.index', ['bulan' => $selectedMonth, 'tahun' => $selectedYear, 'status' => 'sudah_transfer']) }}" class="text-primary hover-underline">
                                    {{ number_format($totalMbc, 0, ',', '.') }}
                                </a>
                            </td>
                            <td class="text-center font-weight-bold text-dark">
                                {{ $calcTotalPendapatan > 0 ? number_format(($totalMbc / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td class="text-center action-cell">
                                <span class="badge badge-system">SYSTEM</span>
                            </td>
                            @endif
                        </tr>
                        @php 
                            $mbcLetters = range('a', 'z'); 
                            $mbcIdx = 0; 
                            
                            // Ensure it is an array for array_keys
                            $mbcArray = is_array($mbcBreakdown) ? $mbcBreakdown : ($mbcBreakdown instanceof \Illuminate\Support\Collection ? $mbcBreakdown->toArray() : []);
                            
                            // Combine classes from schedule and breakdown to ensure all appear
                            $scheduledClassNames = $kelasBulanIni->pluck('nama_kelas')->toArray();
                            
                            // Ensure it is an array for array_keys
                            $mbcArray = is_array($mbcBreakdown) ? $mbcBreakdown : ($mbcBreakdown instanceof \Illuminate\Support\Collection ? $mbcBreakdown->toArray() : []);

                            $allMbcClasses = collect($scheduledClassNames)
                                ->merge(array_keys($mbcArray))
                                ->unique()
                                ->filter(function($name) {
                                    // Pastikan kelas-kelas milik SMI dan Private tidak bocor ke MBC List
                                    if (stripos($name, 'Muslim Indonesia') !== false) return false;
                                    if (stripos($name, 'SMI - ') !== false) return false;
                                    if (stripos($name, 'Privat') !== false) return false;
                                    if (stripos($name, 'Coaching') !== false) return false;
                                    return true;
                                })
                                ->filter(function($name) use ($mbcArray, $scheduledClassNames) {
                                    // Hanya tampilkan jika benar-benar ada pendapatan > 0 ATAU jadwal kelas bulan ini
                                    $hasIncome = ($mbcArray[$name] ?? 0) > 0;
                                    $isScheduled = in_array($name, $scheduledClassNames);
                                    return $hasIncome || $isScheduled;
                                });
                        @endphp
                        
                        @foreach($allMbcClasses as $namaKelas)
                        <tr class="row-pendapatan-sub sub-Pendapatan-MBC" style="font-size: 0.85rem; background-color: #fafafa;">
                            <td></td>
                            <td class="sub-row-indicator">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="item-letter">{{ $mbcLetters[$mbcIdx++] ?? '-' }}.</span>
                                        <span class="text-muted">{{ $namaKelas }}</span>
                                    </span>
                                    <span class="text-primary font-weight-bold ml-2">
                                        <a href="{{ route('admin.salesplan.index', ['bulan' => $selectedMonth, 'tahun' => $selectedYear, 'status' => 'sudah_transfer', 'kelas' => $namaKelas]) }}" class="text-primary hover-underline">
                                            {{ number_format($mbcBreakdown[$namaKelas] ?? 0, 0, ',', '.') }}
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td class="text-right px-3 text-primary amount-cell"></td>
                            <td class="text-center text-muted" style="font-size: 0.75rem;">
                                {{ $calcTotalPendapatan > 0 ? number_format((($mbcBreakdown[$namaKelas] ?? 0) / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>
                        @endforeach
                        <tr data-category="Pendapatan SMI" class="row-pendapatan border-top">
                            <td class="text-center font-weight-bold">2</td>
                            <td class="px-3">
                                <div class="font-weight-bold text-dark">Pendapatan M1T</div>
                                <div class="small text-muted">Pendaftaran (Auto) & SPP (Auto)</div>
                            </td>
                            <td class="text-right px-3 text-primary font-weight-bold amount-cell" id="total-smi-header">
                                {{ number_format($totalSmi, 0, ',', '.') }}
                            </td>
                            <td class="text-center font-weight-bold text-dark">
                                {{ $calcTotalPendapatan > 0 ? number_format(($totalSmi / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td class="text-center action-cell">
                                <span class="badge badge-system">MANUAL / AUTO</span>
                            </td>
                            @endif
                        </tr>
                        {{-- Sub-items for SMI: Pendaftaran and SPP --}}
                        <tr class="row-pendapatan-sub sub-Pendapatan-SMI" style="font-size: 0.85rem; background-color: #fafafa;">
                            <td></td>
                            <td class="sub-row-indicator">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="item-letter">a.</span>
                                        <span class="text-muted">Pendaftaran M1T <small class="badge badge-light border">AUTO</small></span>
                                        <a href="{{ route('admin.salesplan.index', ['bulan' => $selectedMonth, 'tahun' => $selectedYear, 'status' => 'sudah_transfer', 'kelas' => 'Start-Up Muslim Indonesia']) }}" class="ml-1 text-primary small" title="Lihat Data">
                                            <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                                        </a>
                                    </span>
                                    <div class="ml-auto">
                                        <input type="text" 
                                               class="nominal-input-ghost text-primary laba-rugi-auto-save rupiah-format" 
                                               data-type="pendapatan" 
                                               data-keterangan="Pendaftaran SMI" 
                                               value="{{ number_format($totalSmiPendaftaran, 0, ',', '.') }}"
                                               readonly>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2"></td>
                            <td class="text-center text-muted" style="font-size: 0.75rem;">
                                {{ $calcTotalPendapatan > 0 ? number_format(($totalSmiPendaftaran / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>
                        <tr class="row-pendapatan-sub sub-Pendapatan-SMI" style="font-size: 0.85rem; background-color: #fafafa;">
                            <td></td>
                            <td class="sub-row-indicator">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="item-letter">b.</span>
                                        <span class="text-muted">SPP M1T <small class="badge badge-light border">AUTO</small></span>
                                        <a href="{{ route('peserta-smi.index', ['filter_year' => $selectedYear, 'filter_spp_month' => (int)$selectedMonth, 'filter_spp_status' => '1']) }}" class="ml-1 text-primary small" title="Lihat Data">
                                            <i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i>
                                        </a>
                                    </span>
                                    <div class="ml-auto">
                                        <input type="text" 
                                               class="nominal-input-ghost text-primary rupiah-format" 
                                               value="{{ number_format($totalSmiSpp, 0, ',', '.') }}"
                                               readonly>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2"></td>
                            <td class="text-center text-muted" style="font-size: 0.75rem;">
                                {{ $calcTotalPendapatan > 0 ? number_format(($totalSmiSpp / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>

                        <tr data-category="Pendapatan Private Coaching" class="row-pendapatan border-top">
                            <td class="text-center font-weight-bold">3</td>
                            <td class="px-3 font-weight-bold text-dark">Pendapatan Private Coaching (Auto)</td>
                            <td class="text-right px-3 text-primary font-weight-bold amount-cell">{{ number_format($totalPrivate, 0, ',', '.') }}</td>
                            <td class="text-center font-weight-bold text-dark">
                                {{ $calcTotalPendapatan > 0 ? number_format(($totalPrivate / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td class="text-center action-cell">
                                <span class="badge badge-system">SYSTEM</span>
                            </td>
                            @endif
                        </tr>
                        @php $totalPendapatan += ($totalMbc + $totalSmi + $totalPrivate); @endphp

                        @foreach(['Pendapatan Lainnya'] as $index => $item)
                            @php 
                                $row = $pendapatan->where('keterangan', $item)->first();
                                $nilai = $row ? $row->jumlah : 0;
                                $totalPendapatan += $nilai;
                            @endphp
                             <tr data-category="{{ $item }}" class="row-pendapatan border-top">
                                <td class="text-center">{{ $index + 4 }}</td>
                                <td class="px-3">
                                    <div class="font-weight-bold text-dark">{{ $item }}</div>
                                    <div class="mt-1">
                                        <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="pendapatan" data-keterangan="{{ $item }}" value="{{ ($row && $row->tanggal) ? $row->tanggal : date('Y-m-d') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                    </div>
                                </td>
                                <td class="px-2">
                                    <input type="text" 
                                           class="nominal-input text-primary laba-rugi-auto-save rupiah-format amount-cell" 
                                           data-type="pendapatan" 
                                           data-keterangan="{{ $item }}" 
                                           value="{{ number_format($nilai, 0, ',', '.') }}"
                                            {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="text-center text-dark font-weight-bold">
                                    {{ $calcTotalPendapatan > 0 ? number_format(($nilai / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                </td>
                                @if($canEdit)
                                    <td class="text-center action-cell">
                                        @if($row)
                                            <form action="{{ route('admin.keuangan.laba-rugi.destroy', $row->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm text-danger hover-danger border-0">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        
                        {{-- NO. 5 PENDAPATAN CHAPTER --}}
                        @php 
                            $chapters = \App\Models\User::where('role', 'chapter')->orderBy('name')->get();
                            
                            $chapterMainSum = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === 'Pendapatan Chapter' && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                            $chapterSubSum = $pendapatan->filter(fn($r) => trim($r->parent_keterangan ?? '') === 'Pendapatan Chapter')->sum('jumlah');
                            $chapterTotal = $chapterMainSum + $chapterSubSum;
                            // the global $totalPendapatan adds from LabaRugi table, but we iterate them specifically
                            $totalPendapatan += $chapterTotal; 
                            
                            // Adjust $calcTotalPendapatan logic just in case for UI calculation 
                            // though $pendapatan->sum('jumlah') already captures Pendapatan Chapter.
                        @endphp
                        <tr class="row-pendapatan border-top bg-light">
                            <td class="text-center font-weight-bold">5</td>
                            <td class="px-3">
                                <div class="d-flex align-items-center">
                                    <span class="font-weight-bold text-dark">Pendapatan Chapter</span>
                                    <button type="button" class="btn btn-toggle-sub btn-light border ml-2 shadow-sm" data-target="pendapatan-chapter">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-2">
                                <div class="nominal-input text-primary amount-cell font-weight-bold" style="cursor: default;">
                                    {{ number_format($chapterTotal, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="text-center font-weight-bold text-primary">
                                {{ $calcTotalPendapatan > 0 ? number_format(($chapterTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>
                        <tbody class="sub-pendapatan-chapter d-none">
                            @php $pChapterLetters = range('a', 'z'); $pChapterLetterIdx = 0; @endphp
                            @foreach($chapters as $ch)
                                @php
                                    $chKeterangan = $ch->name;
                                    $subRow = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === $chKeterangan && trim($r->parent_keterangan ?? '') === 'Pendapatan Chapter')->first();
                                    $subJumlah = $subRow ? $subRow->jumlah : 0;
                                @endphp
                                <tr class="row-pendapatan-sub" style="font-size: 0.85rem; background-color: #fafafa;">
                                    <td></td>
                                    <td class="sub-row-indicator">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex flex-column">
                                                <span>
                                                    <span class="item-letter">{{ $pChapterLetters[$pChapterLetterIdx++] }}.</span> 
                                                    <span class="text-dark font-weight-bold">{{ $chKeterangan }} <span class="badge badge-info shadow-sm ml-2" style="font-size: 0.65rem; padding: 4px 8px; border-radius: 6px; letter-spacing: 0.02em;">{{ $ch->chapter }}</span></span>
                                                </span>
                                                <div class="mt-1">
                                                    <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="pendapatan" data-keterangan="{{ $chKeterangan }}" data-parent="Pendapatan Chapter" value="{{ $subRow->tanggal ?? date('Y-m-d') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="text-primary font-weight-bold ml-auto">
                                                <input type="text" class="nominal-input text-primary laba-rugi-auto-save rupiah-format amount-cell" data-type="pendapatan" data-keterangan="{{ $chKeterangan }}" data-parent="Pendapatan Chapter" value="{{ number_format($subJumlah, 0, ',', '.') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2"></td>
                                    <td class="text-center text-muted" style="font-size: 0.75rem;">
                                        {{ $calcTotalPendapatan > 0 ? number_format(($subJumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                    </td>
                                    @if($canEdit)
                                    <td class="text-center">
                                        @if($subRow)
                                            <form action="{{ route('admin.keuangan.laba-rugi.destroy', $subRow->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- NO. 6 PENDAPATAN AGEN --}}
                        @php 
                            $agens = \App\Models\User::where('role', 'reseller')->orderBy('name')->get();
                            
                            $agenMainSum = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === 'Pendapatan Agen' && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                            $agenSubSum = $pendapatan->filter(fn($r) => trim($r->parent_keterangan ?? '') === 'Pendapatan Agen')->sum('jumlah');
                            $agenTotal = $agenMainSum + $agenSubSum;
                            $totalPendapatan += $agenTotal; 
                        @endphp
                        <tr class="row-pendapatan border-top bg-light">
                            <td class="text-center font-weight-bold">6</td>
                            <td class="px-3">
                                <div class="d-flex align-items-center">
                                    <span class="font-weight-bold text-dark">Pendapatan Agen</span>
                                    <button type="button" class="btn btn-toggle-sub btn-light border ml-2 shadow-sm" data-target="pendapatan-agen">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-2">
                                <div class="nominal-input text-primary amount-cell font-weight-bold" style="cursor: default;">
                                    {{ number_format($agenTotal, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="text-center font-weight-bold text-primary">
                                {{ $calcTotalPendapatan > 0 ? number_format(($agenTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>
                        <tbody class="sub-pendapatan-agen d-none">
                            @php $pAgenLetters = range('a', 'z'); $pAgenLetterIdx = 0; @endphp
                            @foreach($agens as $ag)
                                @php
                                    $agKeterangan = $ag->name;
                                    $subRow = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === $agKeterangan && trim($r->parent_keterangan ?? '') === 'Pendapatan Agen')->first();
                                    $subJumlah = $subRow ? $subRow->jumlah : 0;
                                @endphp
                                <tr class="row-pendapatan-sub" style="font-size: 0.85rem; background-color: #fafafa;">
                                    <td></td>
                                    <td class="sub-row-indicator">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex flex-column">
                                                <span>
                                                    <span class="item-letter">{{ $pAgenLetters[$pAgenLetterIdx++] ?? '-' }}.</span> 
                                                    <span class="text-dark font-weight-bold">{{ $agKeterangan }} <span class="badge badge-info shadow-sm ml-2" style="font-size: 0.65rem; padding: 4px 8px; border-radius: 6px; letter-spacing: 0.02em;">{{ $ag->chapter }}</span></span>
                                                </span>
                                                <div class="mt-1">
                                                    <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="pendapatan" data-keterangan="{{ $agKeterangan }}" data-parent="Pendapatan Agen" value="{{ $subRow->tanggal ?? date('Y-m-d') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="text-primary font-weight-bold ml-auto">
                                                <input type="text" class="nominal-input text-primary laba-rugi-auto-save rupiah-format amount-cell" data-type="pendapatan" data-keterangan="{{ $agKeterangan }}" data-parent="Pendapatan Agen" value="{{ number_format($subJumlah, 0, ',', '.') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2"></td>
                                    <td class="text-center text-muted" style="font-size: 0.75rem;">
                                        {{ $calcTotalPendapatan > 0 ? number_format(($subJumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                    </td>
                                    @if($canEdit)
                                    <td class="text-center">
                                        @if($subRow)
                                            <form action="{{ route('admin.keuangan.laba-rugi.destroy', $subRow->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>

                        
                        <!-- TOTAL PENDAPATAN ROW -->
                        <tr class="font-weight-bold" style="background-color: var(--primary-soft);">
                            <td colspan="2" class="px-4 py-3 text-uppercase text-right">TOTAL PENDAPATAN</td>
                            <td class="text-right px-3 py-3 text-primary" id="total-pendapatan-text" style="font-size: 1.1rem;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                            <td class="text-center py-3 text-primary" style="font-size: 1.1rem;">100%</td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>

                        <!-- SPACER ROW -->
                        <tr style="height: 10px; border: none;">
                            <td colspan="{{ strtolower(Auth::user()->role) === 'administrator' ? '4' : '5' }}" style="border: none; background: transparent;"></td>
                        </tr>

                        <!-- BIAYA SECTION -->
                        <tr class="section-header">
                            <td colspan="{{ strtolower(Auth::user()->role) === 'administrator' ? '4' : '5' }}" class="px-3 py-2">PENGELUARAN</td>
                        </tr>
                        @php 
                            $totalBiaya = 0; 
                            $expenseGroups = [
                                [
                                    'title' => 'Biaya Event Kelas',
                                    'is_standalone' => true,
                                    'db_key' => 'Biaya Event Kelas'
                                ],
                                [
                                    'title' => 'Biaya Gaji Karyawan',
                                    'is_standalone' => true,
                                    'db_key' => 'Biaya Gaji Karyawan'
                                ],
                                [
                                    'title' => 'Biaya Rumah Tangga',
                                    'is_standalone' => false,
                                    'members' => [
                                        'Biaya Air' => 'Biaya Air',
                                        'Biaya Listrik' => 'Biaya Listrik',
                                        'Biaya Maintenance Web' => 'Biaya Maintenance Web',
                                        'Biaya Kuota' => 'Biaya Kuota/Pulsa',
                                        'Biaya BPJS' => 'Biaya BPJS',
                                        'Biaya Internet & Wifi' => 'Biaya Internet Wifi',
                                        'Biaya Kebersihan & Keamanan' => 'Biaya Kebersihan & Keamanan',
                                    ]
                                ],
                                [
                                    'title' => 'Biaya Marketing',
                                    'is_standalone' => false,
                                    'members' => [
                                        'Biaya Iklan' => 'Biaya Iklan',
                                    ]
                                ],
                                [
                                    'title' => 'Biaya ATK',
                                    'is_standalone' => false,
                                    'members' => [
                                        'Biaya Cetak/Print' => 'Biaya Cetak/Print',
                                        'Biaya Alat Tulis' => 'Biaya Alat Tulis',
                                    ]
                                ],
                                [
                                    'title' => 'Biaya Lain-lain',
                                    'is_standalone' => true,
                                    'db_key' => 'Biaya Lain-lain'
                                ],
                                [
                                    'title' => 'Pengeluaran Coach',
                                    'is_standalone' => true,
                                    'db_key' => 'Pengeluaran Coach'
                                ],
                            ];
                            $mainIndex = 1;
                        @endphp

                        @foreach($expenseGroups as $group)
                            @php
                                $groupTotal = 0;
                                $groupItems = [];
                                
                                if ($group['is_standalone']) {
                                    $itemKey = $group['db_key'];
                                    $subItems = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $itemKey);
                                    $mainSum = $biaya->filter(fn($r) => trim($r->keterangan ?? '') === $itemKey && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                                    $groupTotal = $mainSum + $subItems->sum('jumlah');
                                } else {
                                    // Calculate total for Group
                                    foreach ($group['members'] as $dbKey => $displayTitle) {
                                        $mSub = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $dbKey);
                                        $mMainSum = $biaya->filter(fn($r) => trim($r->keterangan ?? '') === $dbKey && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                                        $groupTotal += $mMainSum + $mSub->sum('jumlah');
                                    }
                                    // Add any manually added items that have this group as parent but aren't members
                                    $extraItems = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $group['title'] && !array_key_exists(trim($r->keterangan ?? ''), $group['members']));
                                    $groupTotal += $extraItems->sum('jumlah');
                                }
                                $totalBiaya += $groupTotal;
                            @endphp

                            {{-- Main Group Row --}}
                            <tr class="row-biaya border-top {{ !$group['is_standalone'] ? 'bg-light' : '' }}">
                                <td class="text-center font-weight-bold">{{ $mainIndex++ }}</td>
                                <td class="px-3">
                                    <div class="d-flex align-items-center">
                                        <span class="font-weight-bold text-dark">{{ $group['title'] }}</span>
                                        <button type="button" class="btn btn-toggle-sub btn-light border ml-2 shadow-sm" data-target="{{ Str::slug($group['title']) }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <button type="button" class="btn btn-add-manual btn-add-biaya ml-2 shadow-sm" data-parent="{{ $group['is_standalone'] ? ($group['db_key'] ?? $group['title']) : $group['title'] }}" data-target="{{ Str::slug($group['title']) }}" title="Tambah Biaya">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    @if($group['is_standalone'] && isset($mainRow) && $mainRow->tanggal)
                                        <div class="mt-1">
                                            <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $group['db_key'] }}" value="{{ $mainRow->tanggal }}" {{ !$canEdit ? 'readonly' : '' }}>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-2">
                                    @if(!$group['is_standalone'] || ($group['is_standalone'] && in_array($group['title'], ['Biaya Event Kelas', 'Biaya Lain-lain', 'Pengeluaran Coach'])))
                                        <div class="nominal-input text-danger amount-cell font-weight-bold" style="cursor: default;">
                                            {{ number_format($groupTotal, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <input type="text" 
                                               class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" 
                                               data-type="biaya" 
                                               data-keterangan="{{ $group['db_key'] }}" 
                                               value="{{ number_format($groupTotal, 0, ',', '.') }}"
                                               {{ !$canEdit ? 'readonly' : '' }}>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold text-danger">
                                    {{ $calcTotalPendapatan > 0 ? number_format(($groupTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                </td>
                                @if($canEdit)
                                <td class="text-center action-cell">
                                    @if($group['is_standalone'] && isset($mainRow))
                                        <form action="{{ route('admin.keuangan.laba-rugi.destroy', $mainRow->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger border-0">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                @endif
                            </tr>

                            {{-- Render Members or Sub-Items --}}
                            <tbody class="sub-{{ Str::slug($group['title']) }} d-none">
                            @php $letterIdx = 0; $letters = range('a', 'z'); @endphp
                            
                            @if($group['is_standalone'])
                                {{-- Standalone items like Event, Coach, Lain-lain --}}
                                @php 
                                    $itemKey = $group['db_key'];
                                    $subItems = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $itemKey);
                                    
                                    if ($itemKey === 'Biaya Event Kelas') {
                                        $displayKelas = collect([(object)['nama_kelas' => 'Scale Up Muslim Institute']])->merge($kelasBulanIni)->unique('nama_kelas')->filter();
                                    } elseif ($itemKey === 'Pengeluaran Coach') {
                                        $displayKelas = collect(); // Coach usually uses manual sub-items
                                    } else {
                                        $displayKelas = collect();
                                    }
                                @endphp

                                {{-- Auto Kelas for Event --}}
                                @foreach($displayKelas as $kelas)
                                    @php 
                                        $sub = $subItems->where('keterangan', $kelas->nama_kelas)->first();
                                        $jumlah = $sub ? $sub->jumlah : 0;
                                    @endphp
                                    <tr class="row-biaya-sub" style="font-size: 0.85rem; background-color: #fafafa;">
                                        <td></td>
                                        <td class="sub-row-indicator">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span><span class="item-letter">{{ $letters[$letterIdx++] ?? '-' }}.</span> <span class="text-muted">{{ $kelas->nama_kelas }}</span></span>
                                                    @if($sub)
                                                    <div class="mt-1">
                                                        <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $kelas->nama_kelas }}" data-parent="{{ $itemKey }}" value="{{ $sub->tanggal }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="text-danger font-weight-bold ml-auto">
                                                    <input type="text" class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" data-type="biaya" data-keterangan="{{ $kelas->nama_kelas }}" data-parent="{{ $itemKey }}" value="{{ number_format($jumlah, 0, ',', '.') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2"></td>
                                        <td class="text-center text-muted" style="font-size: 0.75rem;">
                                            {{ $calcTotalPendapatan > 0 ? number_format(($jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                        </td>
                                        @if($canEdit)
                                        <td class="text-center">
                                            @if($sub)
                                                <form action="{{ route('admin.keuangan.laba-rugi.destroy', $sub->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @else
                                                <span class="badge badge-system">AUTO</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach

                                {{-- Manual Sub-items for Standalone --}}
                                @foreach($subItems as $sub)
                                    @if(isset($displayKelas) && $displayKelas->pluck('nama_kelas')->contains($sub->keterangan)) @continue @endif
                                    @if($sub->jumlah <= 0 && !isset($sub->is_auto)) @continue @endif
                                    <tr class="row-biaya-sub" style="font-size: 0.85rem; background-color: #fafafa;">
                                        <td></td>
                                        <td class="sub-row-indicator">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span><span class="item-letter">{{ $letters[$letterIdx++] ?? '-' }}.</span> <span class="text-muted">{{ $sub->keterangan }}</span></span>
                                                    <div class="mt-1">
                                                        <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $sub->keterangan }}" data-parent="{{ $itemKey }}" value="{{ $sub->tanggal }}" {{ !$canEdit || isset($sub->is_auto) ? 'readonly' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="text-danger font-weight-bold ml-auto">
                                                    <input type="text" class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" data-type="biaya" data-keterangan="{{ $sub->keterangan }}" data-parent="{{ $itemKey }}" value="{{ number_format($sub->jumlah, 0, ',', '.') }}" {{ !$canEdit || isset($sub->is_auto) ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2"></td>
                                        <td class="text-center text-muted" style="font-size: 0.75rem;">
                                            {{ $calcTotalPendapatan > 0 ? number_format(($sub->jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                        </td>
                                        @if($canEdit)
                                        <td class="text-center">
                                            @if(isset($sub->is_auto)) <span class="badge badge-system">AUTO</span>
                                            @else
                                                <form action="{{ route('admin.keuangan.laba-rugi.destroy', $sub->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                {{-- Rendering for Groups (Rumah Tangga, Marketing, ATK) --}}
                                @foreach($group['members'] as $dbKey => $displayTitle)
                                    @php
                                        // Get all entries with transactions (> 0)
                                        $mEntries = $biaya->filter(fn($r) => (trim($r->keterangan ?? '') === $dbKey || trim($r->parent_keterangan ?? '') === $dbKey) && $r->jumlah > 0);
                                        // Get ALL entries for calculation (to handle 0 case correctly)
                                        $mAll = $biaya->filter(fn($r) => trim($r->keterangan ?? '') === $dbKey || trim($r->parent_keterangan ?? '') === $dbKey);
                                        $mTotal = $mAll->sum('jumlah');
                                        $hasNested = ($dbKey === 'Biaya Iklan'); 
                                        
                                        // Multiple entries only if more than one item has a non-zero transaction
                                        $multipleEntries = (!$hasNested && $mEntries->count() > 1);
                                    @endphp
                                    
                                    {{-- Sub-category Header/Main Row --}}
                                    <tr class="row-biaya-sub" style="font-size: 0.85rem; background-color: #fafafa;">
                                        <td></td>
                                        <td class="sub-row-indicator">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex flex-column">
                                                    <span>
                                                        <span class="item-letter">{{ $letters[$letterIdx++] ?? '-' }}.</span> 
                                                        <span class="text-dark font-weight-bold">{{ $displayTitle }}</span>
                                                    </span>
                                                    {{-- Only show date if it's a single non-zero entry --}}
                                                    @if(!$multipleEntries && $mEntries->count() === 1)
                                                        @php $mFirst = $mEntries->first(); @endphp
                                                        @if($mFirst->tanggal)
                                                            <div class="mt-1">
                                                                <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $mFirst->keterangan }}" data-parent="{{ $mFirst->parent_keterangan }}" value="{{ $mFirst->tanggal }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center ml-auto">
                                                    <div class="text-danger font-weight-bold mr-1">
                                                        @if($multipleEntries || ($hasNested && $mAll->where('keterangan', $dbKey)->count() == 0))
                                                            <div class="text-right" style="min-width: 100px;">
                                                                {{ number_format($mTotal, 0, ',', '.') }}
                                                            </div>
                                                        @else
                                                            @php $mFirst = $mEntries->first() ?? $mAll->first(); @endphp
                                                            <input type="text" 
                                                                   class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" 
                                                                   data-type="biaya" 
                                                                   data-keterangan="{{ $mFirst ? $mFirst->keterangan : $dbKey }}" 
                                                                   data-parent="{{ ($mFirst && $mFirst->parent_keterangan) ? $mFirst->parent_keterangan : '' }}"
                                                                   value="{{ number_format($mTotal, 0, ',', '.') }}" 
                                                                   {{ !$canEdit || ($mFirst && isset($mFirst->is_auto)) ? 'readonly' : '' }}>
                                                        @endif
                                                    </div>
                                                    @if($hasNested || $multipleEntries)
                                                        <button type="button" class="btn btn-toggle-sub btn-light border btn-xs" data-target="nested-{{ Str::slug($dbKey) }}">
                                                            <i class="fas fa-chevron-down" style="font-size: 8px;"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2"></td>
                                        <td class="text-center text-muted" style="font-size: 0.75rem;">
                                            {{ $calcTotalPendapatan > 0 ? number_format(($mTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                        </td>
                                        @if($canEdit)
                                        <td class="text-center">
                                            {{-- Delete button only for single entries --}}
                                            @if(!$multipleEntries && $mEntries->count() === 1)
                                                <form action="{{ route('admin.keuangan.laba-rugi.destroy', $mEntries->first()->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @elseif(!$multipleEntries && $mEntries->count() === 0 && $mAll->count() === 1)
                                                 <form action="{{ route('admin.keuangan.laba-rugi.destroy', $mAll->first()->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>

                                    {{-- Indented parts for members with multiple entries (e.g. Listrik 1, Listrik 2) --}}
                                    @if($multipleEntries)
                                        @foreach($mEntries as $entry)
                                            <tr class="row-biaya-nested sub-nested-{{ Str::slug($dbKey) }} d-none" style="font-size: 0.8rem; background-color: #fcfcfc;">
                                                <td></td>
                                                <td class="pl-4" style="padding-left: 3.5rem !important;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex flex-column">
                                                            <span class="text-muted"><i class="fas fa-caret-right mr-1"></i> {{ $entry->keterangan }}</span>
                                                            @if($entry->tanggal)
                                                                <div class="mt-1">
                                                                    <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $entry->keterangan }}" data-parent="{{ $entry->parent_keterangan }}" value="{{ $entry->tanggal }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="text-danger font-weight-bold ml-auto">
                                                            <input type="text" class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" data-type="biaya" data-keterangan="{{ $entry->keterangan }}" data-parent="{{ $entry->parent_keterangan }}" value="{{ number_format($entry->jumlah, 0, ',', '.') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-2"></td>
                                                <td class="text-center text-muted" style="font-size: 0.75rem;">
                                                    {{ $calcTotalPendapatan > 0 ? number_format(($entry->jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                                </td>
                                                @if($canEdit)
                                                <td class="text-center">
                                                    <form action="{{ route('admin.keuangan.laba-rugi.destroy', $entry->id) }}" method="POST" class="d-inline delete-form">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif

                                    {{-- Nested Items for Biaya Iklan within Group --}}
                                    @if($hasNested)
                                        @php 
                                            $nIdx = 1; 
                                            // Get all existing entries for this parent
                                            $mSubClass = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $dbKey);
                                        @endphp
                                        @foreach($kelasBulanIni as $kelas)
                                            @php 
                                                $sub = $mSubClass->where('keterangan', $kelas->nama_kelas)->first();
                                                $jumlah = $sub ? $sub->jumlah : 0;
                                            @endphp
                                            <tr class="row-biaya-nested sub-nested-{{ Str::slug($dbKey) }} d-none" style="font-size: 0.8rem; background-color: #f5f5f5;">
                                                <td></td>
                                                <td class="pl-5 text-muted" style="padding-left: 4rem !important;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex flex-column">
                                                            <span><i class="fas fa-caret-right mr-1"></i> {{ $kelas->nama_kelas }}</span>
                                                            @if($sub && $sub->tanggal)
                                                                <div class="mt-1">
                                                                    <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $kelas->nama_kelas }}" data-parent="{{ $dbKey }}" value="{{ $sub->tanggal }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="text-danger font-weight-bold ml-auto">
                                                            <input type="text" class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" data-type="biaya" data-keterangan="{{ $kelas->nama_kelas }}" data-parent="{{ $dbKey }}" value="{{ number_format($jumlah, 0, ',', '.') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-2"></td>
                                                <td class="text-center text-muted" style="font-size: 0.75rem;">
                                                    {{ $calcTotalPendapatan > 0 ? number_format(($jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                                </td>
                                                @if($canEdit)
                                                <td class="text-center">
                                                    @if($sub)
                                                        <form action="{{ route('admin.keuangan.laba-rugi.destroy', $sub->id) }}" method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                        </form>
                                                    @else
                                                        <span class="badge badge-system">AUTO</span>
                                                    @endif
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach

                                        {{-- Fallback: Show entries that AREN'T in the schedule but exist in DB --}}
                                        @foreach($mSubClass as $sub)
                                            @if($kelasBulanIni->pluck('nama_kelas')->contains($sub->keterangan)) @continue @endif
                                            <tr class="row-biaya-nested" style="font-size: 0.8rem; background-color: #f5f5f5;">
                                                <td></td>
                                                <td class="pl-5 text-muted" style="padding-left: 4rem !important;">
                                                    <div class="d-flex flex-column">
                                                        <span><i class="fas fa-caret-right mr-1"></i> {{ $sub->keterangan }}</span>
                                                        @if($sub->tanggal)
                                                            <div class="mt-1">
                                                                <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $sub->keterangan }}" data-parent="{{ $dbKey }}" value="{{ $sub->tanggal }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-2">
                                                    <input type="text" class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" data-type="biaya" data-keterangan="{{ $sub->keterangan }}" data-parent="{{ $dbKey }}" value="{{ number_format($sub->jumlah, 0, ',', '.') }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                </td>
                                                <td class="text-center text-muted" style="font-size: 0.75rem;">
                                                    {{ $calcTotalPendapatan > 0 ? number_format(($sub->jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                                </td>
                                                @if($canEdit)
                                                <td class="text-center">
                                                    <form action="{{ route('admin.keuangan.laba-rugi.destroy', $sub->id) }}" method="POST" class="d-inline delete-form">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                    </form>
                                                </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach

                                {{-- Extra items for Group --}}
                                @php
                                    $extraGroupItems = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $group['title'] && !array_key_exists(trim($r->keterangan ?? ''), $group['members']));
                                @endphp
                                @foreach($extraGroupItems as $sub)
                                    @if($sub->jumlah <= 0 && !isset($sub->is_auto)) @continue @endif
                                    <tr class="row-biaya-sub" style="font-size: 0.85rem; background-color: #fafafa;">
                                        <td></td>
                                        <td class="sub-row-indicator">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span><span class="item-letter">{{ $letters[$letterIdx++] ?? '-' }}.</span> <span class="text-muted">{{ $sub->keterangan }}</span></span>
                                                    <div class="mt-1">
                                                        <input type="date" class="date-input-ghost laba-rugi-auto-save" data-type="biaya" data-keterangan="{{ $sub->keterangan }}" data-parent="{{ $group['title'] }}" value="{{ $sub->tanggal }}" {{ !$canEdit || isset($sub->is_auto) ? 'readonly' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="text-danger font-weight-bold ml-auto">
                                                    <input type="text" class="nominal-input text-danger laba-rugi-auto-save rupiah-format amount-cell" data-type="biaya" data-keterangan="{{ $sub->keterangan }}" data-parent="{{ $group['title'] }}" value="{{ number_format($sub->jumlah, 0, ',', '.') }}" {{ !$canEdit || isset($sub->is_auto) ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2"></td>
                                        <td class="text-center text-muted" style="font-size: 0.75rem;">
                                            {{ $calcTotalPendapatan > 0 ? number_format(($sub->jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                                        </td>
                                        @if($canEdit)
                                        <td class="text-center">
                                            @if(isset($sub->is_auto)) <span class="badge badge-system">AUTO</span>
                                            @else
                                                <form action="{{ route('admin.keuangan.laba-rugi.destroy', $sub->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-xs text-danger border-0"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        @endforeach

                        <!-- TOTAL PENGELUARAN ROW -->
                        <tr class="font-weight-bold" style="background-color: var(--danger-soft);">
                            <td colspan="2" class="px-4 py-3 text-uppercase text-right">TOTAL PENGELUARAN</td>
                            <td class="text-right px-3 py-3 text-danger" id="total-biaya-text" style="font-size: 1.1rem;">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                            <td class="text-center py-3 text-danger" style="font-size: 1.1rem;">
                                {{ $calcTotalPendapatan > 0 ? number_format(($totalBiaya / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>
                    </tbody>
                    <tfoot class="bg-white">
                        <tr class="section-header" style="font-size: 1.1rem; border-top: 3px solid #e3e6f0;">
                            <td colspan="2" class="px-4 py-4 text-uppercase font-weight-bolder text-right">LABA / RUGI BERSIH</td>
                            <td class="text-right px-3 py-4 font-weight-bolder {{ ($totalPendapatan - $totalBiaya) >= 0 ? 'text-success' : 'text-danger' }}" id="laba-rugi-text" style="font-size: 1.4rem;">
                                Rp {{ number_format($totalPendapatan - $totalBiaya, 0, ',', '.') }}
                            </td>
                            <td class="text-center py-4 font-weight-bolder {{ ($totalPendapatan - $totalBiaya) >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 1.4rem;">
                                {{ $calcTotalPendapatan > 0 ? number_format((($totalPendapatan - $totalBiaya) / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}
                            </td>
                            @if(strtolower(Auth::user()->role) !== 'administrator')
                            <td></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Peserta SMI -->
<div class="modal fade" id="modalSmiDetail" tabindex="-1" role="dialog" aria-labelledby="modalSmiDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalSmiDetailLabel">
                    <i class="fas fa-users mr-2"></i> Detail Peserta Closing - <span id="label-nama-kelas"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="table-smi-detail">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Nama Peserta</th>
                                <th class="text-right px-4">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="smi-detail-body">
                            <!-- Data will be loaded here via AJAX -->
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="2" class="text-center">TOTAL</td>
                                <td class="text-right px-4 text-primary" id="smi-detail-total">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id="loading-smi-detail" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
                <div id="empty-smi-detail" class="text-center py-5 d-none">
                    <i class="fas fa-info-circle fa-3x text-light mb-3"></i>
                    <p class="text-muted">Tidak ada data peserta closing untuk kelas ini.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // Standard Utility Functions
    function parseRupiah(formatted) {
        if (!formatted) return 0;
        let value = formatted.toString().replace(/[^0-9]/g, '');
        return parseInt(value) || 0;
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Initialize Toast (with safety check)
    let Toast = null;
    if (typeof Swal !== 'undefined') {
        Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    const showMessage = (icon, title) => {
        if (Toast) {
            Toast.fire({ icon, title });
        } else {
            alert(title);
        }
    };

    // Manual Item Inline Logic
    $(document).on('click', '.btn-add-manual', function() {
        if ($('#row-manual-temp').length > 0) {
            $('#manual-keterangan').focus();
            return;
        }

        const $btn = $(this);
        const parent = $btn.data('parent');
        const target = $btn.data('target');
        const $targetBody = $('.sub-' + target);
        const lastSubRow = $targetBody.find('tr').last();
        const categoryRow = $btn.closest('tr');
        
        const isAdministrator = "{{ strtolower(Auth::user()->role) }}" === 'administrator';
        const colSpanValue = isAdministrator ? '3' : '4';
        
        const newRow = `
            <tr id="row-manual-temp" class="bg-white shadow" style="border-left: 4px solid #e74a3b;" data-parent="${parent}">
                <td class="bg-gray-100"></td>
                <td class="p-3" colspan="${isAdministrator ? '1' : '2'}">
                    <div class="row no-gutters">
                        <div class="col-md-4 pr-2">
                             <label class="small font-weight-bold text-uppercase text-muted mb-1">Tanggal</label>
                             <input type="date" id="manual-tanggal" class="form-control form-control-sm border-0 bg-light" value="${new Date().toISOString().split('T')[0]}">
                        </div>
                        <div class="col-md-8">
                             <label class="small font-weight-bold text-uppercase text-muted mb-1">Keterangan Biaya</label>
                             <div contenteditable="true" id="manual-keterangan" class="p-2 border rounded bg-white shadow-sm font-weight-bold text-dark" style="outline: none; min-height: 38px; border-color: #d1d3e2 !important;" placeholder="Masukkan keterangan..."></div>
                        </div>
                    </div>
                </td>
                <td class="p-3">
                    <label class="small font-weight-bold text-uppercase text-muted mb-1 d-block text-right">Nominal (Rp)</label>
                    <div contenteditable="true" id="manual-jumlah" class="p-2 border rounded bg-white shadow-sm text-right font-weight-bold text-danger rupiah-format-editable" style="outline: none; font-size: 1.1rem; border-color: #d1d3e2 !important;" placeholder="0"></div>
                </td>
                ${!isAdministrator ? `
                <td class="text-center align-middle bg-gray-100">
                    <div class="d-flex flex-column p-1">
                        <button type="button" class="btn btn-primary btn-sm mb-1 shadow-sm" id="btnSaveManualInline">
                            <i class="fas fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-xs shadow-sm border" id="btnCancelManualInline">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>` : ''}
            </tr>
        `;

        if (lastSubRow.length > 0) {
            lastSubRow.after(newRow);
        } else {
            $targetBody.removeClass('d-none').prepend(newRow);
            $btn.siblings('.btn-toggle-sub').find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
        
        $('#manual-keterangan').focus();
    });

    $(document).on('click', '#btnCancelManualInline', function() {
        $('#row-manual-temp').remove();
    });

    $(document).on('click', '#btnSaveManualInline', function() {
        const keterangan = $('#manual-keterangan').text().trim();
        const jumlahRaw = parseRupiah($('#manual-jumlah').text());
        const tanggal = $('#manual-tanggal').val();
        const parent = $('#row-manual-temp').data('parent');
        
        if (!keterangan || jumlahRaw <= 0) {
            showMessage('error', 'Keterangan dan Nominal harus diisi!');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('admin.keuangan.laba-rugi.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                bulan: $('#bulan').val(),
                tahun: $('#tahun').val(),
                type: 'biaya',
                keterangan: keterangan,
                parent_keterangan: parent,
                tanggal: tanggal,
                jumlah: jumlahRaw
            },
            success: function(response) {
                showMessage('success', 'Data berhasil ditambahkan.');
                setTimeout(() => location.reload(), 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                let msg = 'Gagal menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showMessage('error', msg);
            }
        });
    });

    // Formatting for editable divs
    $(document).on('keyup', '.rupiah-format-editable', function() {
        let val = $(this).text().replace(/[^0-9]/g, '');
        if (val !== "") {
            // Keep cursor position if possible or just replace text
            $(this).text(formatRupiah(parseInt(val)));
            
            // Move cursor to end
            const range = document.createRange();
            const sel = window.getSelection();
            range.selectNodeContents(this);
            range.collapse(false);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    });
    // Initialize Select2 if exists
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Pilih Kategori...'
        });
    }

    // Recalculate Totals Logic
    function recalculateTotals() {
        let totalPendapatan = 0;
        let totalBiaya = 0;

        $('.row-pendapatan').each(function() {
            let $amt = $(this).find('.amount-cell');
            let val = $amt.is('input') ? $amt.val() : $amt.text();
            totalPendapatan += parseRupiah(val);
        });

        $('.row-biaya').each(function() {
            let $amt = $(this).find('.amount-cell');
            let val = $amt.is('input') ? $amt.val() : $amt.text();
            totalBiaya += parseRupiah(val);
        });

        let labaRugi = totalPendapatan - totalBiaya;

        $('#total-pendapatan-text').text('Rp ' + formatRupiah(totalPendapatan));
        $('#total-biaya-text').text('Rp ' + formatRupiah(totalBiaya));
        
        let labaRugiCell = $('#laba-rugi-text');
        labaRugiCell.text('Rp ' + formatRupiah(labaRugi));
        
        if (labaRugi >= 0) {
            labaRugiCell.removeClass('text-danger').addClass('text-success');
        } else {
            labaRugiCell.removeClass('text-success').addClass('text-danger');
        }
    }

    // Event Handlers
    $(document).on('submit', '.delete-form', function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr('action');
        let row = form.closest('tr');

        if (typeof Swal === 'undefined') {
            if (confirm("Apakah anda yakin ingin menghapus data ini?")) {
                performDelete(url, form, row);
            }
            return;
        }

        Swal.fire({
            title: "Apakah anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) performDelete(url, form, row);
        });
    });

    function performDelete(url, form, row) {
        $.ajax({
            url: url,
            type: "POST",
            data: form.serialize(),
            success: function() { 
                showMessage('success', 'Data telah dihapus.');
                if (row.hasClass('row-biaya-sub') || row.find('.btn-add-sub').length > 0) {
                    setTimeout(() => location.reload(), 500);
                } else {
                    row.find('.amount-cell').text('0');
                    row.find('.action-cell').empty();
                    recalculateTotals();
                }
            },
            error: function() { form.off('submit').submit(); }
        });
    }

    // Toggle Sub-items Logic
    $(document).on('click', '.btn-toggle-sub', function() {
        let $btn = $(this);
        let target = $btn.data('target');
        let $rows = $('.sub-' + target);
        let $icon = $btn.find('i');

        if ($rows.hasClass('d-none')) {
            $rows.removeClass('d-none');
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            $rows.addClass('d-none');
            $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });

    // Event Handlers for Auto-Save
    $(document).on('change blur', '.laba-rugi-auto-save', function(e) {
        let $input = $(this);
        
        // Prevent double trigger (both change and blur)
        if (e.type === 'blur' && $input.data('last-val') === $input.val()) return;
        $input.data('last-val', $input.val());

        let type = $input.data('type');
        let keterangan = $input.data('keterangan');
        let parent = $input.data('parent') || '';
        
        // Get both date and amount from the same row to ensure we save both
        let $row = $input.closest('tr');
        let tanggal = $row.find('input[type="date"]').val() || '';
        let valRaw = $row.find('.rupiah-format, .nominal-input').filter(function() {
            return $(this).data('keterangan') == keterangan;
        }).val();
        
        let jumlah = parseRupiah(valRaw);

        // Don't save if both are empty
        if (!tanggal && !valRaw) return;

        $.ajax({
            url: "{{ route('admin.keuangan.laba-rugi.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                bulan: $('#bulan').val() || '{{ $selectedMonth }}',
                tahun: $('#tahun').val() || '{{ $selectedYear }}',
                type: type,
                keterangan: keterangan,
                parent_keterangan: parent,
                tanggal: tanggal,
                jumlah: jumlah
            },
            success: function() {
                if (parent && $input.attr('type') !== 'date') {
                    // Only reload if it's not a date change in a sub-category to avoid losing focus
                    // Actually, if it's a parent change, we might need reload. 
                    // Let's keep it simple for now.
                    recalculateTotals();
                } else {
                    recalculateTotals();
                }
            },
            error: function(xhr) {
                let msg = 'Gagal menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showMessage('error', msg);
            }
        });
    });

    // Formatting Handlers
    $(document).on('keyup', '.rupiah-format', function() {
        let val = $(this).val().replace(/[^0-9]/g, '');
        if (val !== "") $(this).val(formatRupiah(parseInt(val)));
    });

    $(document).on('focus', '.rupiah-format', function() {
        let $el = $(this);
        $el.data('last-val', $el.val());
        if (parseRupiah($el.val()) === 0) $el.val('');
    });

    $(document).on('blur', '.rupiah-format', function() {
        if ($(this).val() === '') $(this).val('0');
    });
    // SMI Detail Modal Logic
    $(document).on('click', '.btn-smi-detail', function() {
        const btn = $(this);
        const kelas = btn.data('kelas');
        const bulan = btn.data('bulan');
        const tahun = btn.data('tahun');
        
        $('#label-nama-kelas').text(kelas);
        $('#smi-detail-body').empty();
        $('#smi-detail-total').text('0');
        $('#loading-smi-detail').removeClass('d-none');
        $('#table-smi-detail').addClass('d-none');
        $('#empty-smi-detail').addClass('d-none');
        
        $('#modalSmiDetail').modal('show');
        
        $.ajax({
            url: "{{ route('admin.keuangan.laba-rugi.smi-details') }}",
            type: "GET",
            data: {
                kelas: kelas,
                bulan: bulan,
                tahun: tahun
            },
            success: function(response) {
                $('#loading-smi-detail').addClass('d-none');
                
                if (response.success && (response.baru.length > 0 || response.spp.length > 0)) {
                    let html = '';
                    
                    // Section 1: Peserta Baru
                    if (response.baru.length > 0) {
                        html += `
                            <div class="bg-light p-2 font-weight-bold text-primary border-bottom border-top">
                                <i class="fas fa-user-plus mr-1"></i> Peserta Baru (Closing)
                            </div>
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr class="small text-muted">
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th>Nama Peserta</th>
                                        <th class="text-right px-4">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        response.baru.forEach((p, i) => {
                            html += `
                                <tr>
                                    <td class="text-center text-muted small">${i + 1}</td>
                                    <td class="font-weight-bold">${p.nama}</td>
                                    <td class="text-right px-4">${formatRupiah(p.nominal)}</td>
                                </tr>
                            `;
                        });
                        html += `
                                    <tr class="bg-light-primary font-weight-bold">
                                        <td colspan="2" class="text-center small">Subtotal Peserta Baru</td>
                                        <td class="text-right px-4 text-primary">${formatRupiah(response.total_baru)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        `;
                    }
                    
                    // Section 2: SPP
                    if (response.spp.length > 0) {
                        html += `
                            <div class="bg-light p-2 font-weight-bold text-success border-bottom border-top mt-3">
                                <i class="fas fa-history mr-1"></i> SPP (Recurring)
                            </div>
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr class="small text-muted">
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th>Nama Peserta</th>
                                        <th class="text-right px-4">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        response.spp.forEach((p, i) => {
                            html += `
                                <tr>
                                    <td class="text-center text-muted small">${i + 1}</td>
                                    <td class="font-weight-bold">${p.nama}</td>
                                    <td class="text-right px-4">${formatRupiah(p.nominal)}</td>
                                </tr>
                            `;
                        });
                        html += `
                                    <tr class="bg-light-success font-weight-bold">
                                        <td colspan="2" class="text-center small">Subtotal SPP</td>
                                        <td class="text-right px-4 text-success">${formatRupiah(response.total_spp)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        `;
                    }
                    
                    $('#smi-detail-body').html(`<tr><td colspan="3" class="p-0 border-0">${html}</td></tr>`);
                    $('#smi-detail-total').text(formatRupiah(parseFloat(response.total_baru) + parseFloat(response.total_spp)));
                    $('#table-smi-detail').removeClass('d-none');
                } else {
                    $('#empty-smi-detail').removeClass('d-none');
                }
            },
            error: function() {
                $('#loading-smi-detail').addClass('d-none');
                showMessage('error', 'Gagal memuat detail peserta.');
            }
        });
    });
});
</script>

@endsection
    