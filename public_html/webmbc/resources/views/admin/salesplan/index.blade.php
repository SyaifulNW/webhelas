@extends('layouts.masteradmin')
@section('content')

<style>
    table {
        border-collapse: collapse;
    }
    
    .table-scroll {
        /* Page-level vertical scroll, with horizontal table scroll */
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        border: 1px solid #ddd;
        background-color: #fff;
    }
    
    .table-scroll table {
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100%;
    }

    /* Fixed Headers (Vertical Sticky to Page) */
    .table-scroll thead th {
        position: sticky !important;
        top: 0; /* Sticks to top of viewport when topbar ends */
        z-index: 100 !important;
        background-color: #25799E !important;
        color: white !important;
        border: 0.5px solid #fff !important;
        vertical-align: middle;
        text-align: center;
        padding: 10px 5px !important;
    }

    /* Multi-row offsets (Approx heights: row 1~42px, row 2~32px) */
    .table-scroll thead tr:nth-child(2) th { top: 41px; z-index: 99 !important; }
    .table-scroll thead tr:nth-child(3) th { top: 73px; z-index: 98 !important; }

    /* Frozen Columns (Horizontal Sticky) - No and Nama */
    .table-scroll tr th:nth-child(1),
    .table-scroll tr td:nth-child(1) {
        position: sticky !important;
        left: 0;
        z-index: 60 !important;
        background-color: #fff;
        min-width: 45px;
        max-width: 45px;
        border-right: 1px solid #dee2e6 !important;
    }
    
    .table-scroll tr th:nth-child(2),
    .table-scroll tr td:nth-child(2) {
        position: sticky !important;
        left: 45px; 
        z-index: 60 !important;
        background-color: #fff;
        min-width: 180px;
        max-width: 180px;
        border-right: 1px solid #dee2e6 !important;
    }

    /* Handling Column 3 (Level / Leads) */
    /* If SMI: Column 3 is Level, let's keep it visible with a width */
    .level-select {
        min-width: 110px !important;
        width: 110px !important;
        color: #000 !important;
        font-weight: bold;
        padding: 2px 5px !important;
        height: auto !important;
        background-color: rgba(255, 255, 255, 0.4) !important;
        border: 1px solid rgba(0,0,0,0.1) !important;
    }

    /* Specific adjustment for columns after sticky ones to prevent overlapping */
    .table-scroll th:nth-child(3),
    .table-scroll td:nth-child(3) {
        min-width: 120px;
    }

    /* Column Header Overlap (Freeze Corners) */
    .table-scroll thead tr:nth-child(1) th:nth-child(1),
    .table-scroll thead tr:nth-child(1) th:nth-child(2) {
        background-color: #25799E !important;
        z-index: 110 !important;
    }
    /* Set left specifically */
    .table-scroll thead tr:nth-child(1) th:nth-child(1) { left: 0; }
    .table-scroll thead tr:nth-child(1) th:nth-child(2) { left: 45px; }

    /* Body cell backgrounds for frozen columns - following row status */
    .table-scroll tbody tr td:nth-child(1),
    .table-scroll tbody tr td:nth-child(2) {
        background-color: inherit;
    }

    /* Specific status colors for frozen columns to maintain stickiness */
    .table-scroll tr.table-info td:nth-child(1), .table-scroll tr.table-info td:nth-child(2) { background-color: #e2f0f3 !important; }
    .table-scroll tr.table-success td:nth-child(1), .table-scroll tr.table-success td:nth-child(2) { background-color: #e2f3e7 !important; }
    .table-scroll tr.table-warning td:nth-child(1), .table-scroll tr.table-warning td:nth-child(2) { background-color: #fff8e1 !important; }
    .table-scroll tr.table-danger td:nth-child(1), .table-scroll tr.table-danger td:nth-child(2) { background-color: #FF0000 !important; color: white !important; }
    .table-scroll tr.table-secondary td:nth-child(1), .table-scroll tr.table-secondary td:nth-child(2) { background-color: #f1f1f1 !important; }
    
    /* Global red override for status NO */
    .table-danger { background-color: #FF0000 !important; color: #fff !important; }
    .table-danger td { color: #fff !important; }
    .table-danger .editable { color: #fff !important; }
    .table-danger .status-dropdown { background-color: #FF0000 !important; border-color: #fff !important; }

    td {
        font-size: 14px;
        padding: 8px;
        text-align: left;
        color: #000 !important;
        font-weight: 500;
        border: 1px solid #dee2e6 !important;
    }

    @media only screen and (max-width: 768px) {

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
        }

        thead {
            display: none;
        }

        td {
            position: relative;
            padding-left: 50%;
        }

        td:before {
            position: absolute;
            left: 6px;
            white-space: nowrap;
            font-weight: bold;
        }

        td:nth-of-type(1):before {
            content: "Nama";
        }

        td:nth-of-type(2):before {
            content: "Kelas";
        }

        td:nth-of-type(3):before {
            content: "FU1 Hasil";
        }

        td:nth-of-type(4):before {
            content: "FU1 TL";
        }

        td:nth-of-type(5):before {
            content: "FU2 Hasil";
        }

        td:nth-of-type(6):before {
            content: "FU2 TL";
        }

        td:nth-of-type(7):before {
            content: "FU3 Hasil";
        }

        td:nth-of-type(8):before {
            content: "FU3 TL";
        }

        td:nth-of-type(9):before {
            content: "FU4 Hasil";
        }

        td:nth-of-type(10):before {
            content: "FU4 TL";
        }

        td:nth-of-type(11):before {
            content: "FU5 Hasil";
        }

        td:nth-of-type(12):before {
            content: "FU5 TL";
        }
        
        
        
        
    }
</style>

@if(($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi'))
<style>
    /* High Density Full Screen Mode for SMI */
    #wrapper #content-wrapper { background-color: #f4f7f6; }
    .container { max-width: 99% !important; padding: 0 10px !important; }
    .card { margin-bottom: 0.5rem !important; }
    .card-body { padding: 0.6rem !important; }
    
    /* Full Screen Page Scroll Mode (SMI-style requested) */
    /* Full Screen Page Scroll Mode (SMI-style requested) */
    .table-scroll {
        max-height: none !important;
        overflow-y: visible !important;
        overflow-x: auto !important;
    }
    
    /* Ensure offsets are sync'd for frozen columns */
    .table-scroll thead tr:nth-child(1) th:nth-child(1), .table-scroll tbody tr td:nth-child(1) { left: 0 !important; min-width: 45px !important; }
    .table-scroll thead tr:nth-child(1) th:nth-child(2), .table-scroll tbody tr td:nth-child(2) { left: 45px !important; min-width: 180px !important; }
    
    /* Adjust column offsets for SMI (larger font/padding) */
    .table-scroll td:nth-child(1), .table-scroll th:nth-child(1) { left: 0; width: 45px; }
    .table-scroll td:nth-child(2), .table-scroll th:nth-child(2) { left: 45px; }
    .table-scroll thead th:nth-child(2) { left: 45px; }
    
    /* Tabel Lebih Rapat tapi Font Terbaca Jelas */
    .table thead th {
        font-size: 14px !important;
        padding: 8px 4px !important;
        line-height: 1.2 !important;
    }
    
    .table tbody td {
        font-size: 14px !important;
        padding: 6px 10px !important;
        white-space: nowrap; 
    }

    /* Penyesuaian Kolom FU dan Kebutuhan */
    .table td[data-field="kebutuhan"],
    .table td[data-field^="fu"] {
        max-width: 220px !important;
        min-width: 160px !important;
        white-space: normal !important; 
        word-wrap: break-word !important;
        line-height: 1.4 !important;
    }
    
    /* Make headers match */
    .table thead th:nth-child(n+3) {
        max-width: 200px !important;
    }
    
    /* Elemen UI Lebih Jelas */
    .status-dropdown {
        min-width: 140px !important;
        font-size: 14px !important;
        padding: 5px !important;
        height: 36px !important;
    }
    
    .btn-checklist {
        width: 24px !important;
        height: 24px !important;
        font-size: 12px !important;
    }

    /* Kurangi Vertical Gaps */
    .mt-5 { margin-top: 0.75rem !important; }
    .mb-4 { margin-bottom: 0.5rem !important; }
    h4, .h3 { margin-bottom: 0.5rem !important; }
    
    /* Bar Progress Tipis */
    .progress { height: 5px !important; }
    .badge { padding: 0.25em 0.5em !important; font-size: 0.75rem !important; }
    
    /* Bar Filter Jelas */
    .input-group input, .filter-select {
        height: 38px !important;
        font-size: 14px !important;
    }
</style>
@endif


<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        Sales Plan
        @if($kelasFilter)
        / {{ $kelasFilter }}
        @endif
    </h1>

    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item">Sales Plan</li>
            @if($kelasFilter)
            <li class="breadcrumb-item active">{{ $kelasFilter }}</li>
            @endif
        </ol>
    </div>
</div>

@if(session('message'))
<div class="alert alert-info">
    {{ session('message') }}
</div>
@endif

@if($salesplans->isEmpty())
<div class="alert alert-info">
    Tidak ada data yang sesuai dengan filter.
</div>
@else
{{-- tampilkan tabel atau isi salesplans --}}
@endif

<div class="container">
@php
    $isSmiClass = ($kelasFilter == 'Start-Up Muda Indonesia' || $kelasFilter == 'Start-Up Muslim Indonesia');
    $targetOmset = 25000000; // Rp 25.000.000
    $groupedByCS = $salesplans->groupBy('created_by');

    $namaCS = [
        1 => 'Administrator',
        2 => 'Linda',
        3 => 'Yasmin',
        4 => 'Tursia',
        10 => 'Qiyya',
        6 => 'Shafa',
    ];

    // Hitung total keseluruhan awal (akan dihitung ulang di loop untuk akurasi tabel)
    $totalSeluruhCS = 0;
    $totalTargetSemua = 0;
    $totalKekurangan = 0;
    $persentaseTotal = 0;
@endphp

<!-- Filter hanya administrator -->
@if(auth()->id() == 1 || auth()->id() == 13 || (auth()->user()->name == 'Linda' && empty($isRestrictedView)))
<style>
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        align-items: center;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-label {
        font-weight: 600;
        color: #333;
        white-space: nowrap;
    }

    .filter-select {
        min-width: 180px;
        padding: 0.45rem 0.75rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #fff;
    }

    .filter-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        outline: none;
    }

    .btn-reset {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
        border-radius: 8px;
    }

    .btn-reset i {
        font-size: 0.9rem;
    }

    @media (max-width: 576px) {
        .filter-container {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-select {
            width: 100%;
        }
        .filter-group {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@if(auth()->id() == 1 || auth()->id() == 13 || auth()->user()->name == 'Linda')
<form method="GET" action="{{ route('admin.salesplan.index') }}" class="filter-container">
{{-- ✅ Filter CS --}}
<div class="filter-group">
    <label for="cs_filter" class="filter-label"><i class="fas fa-user-tie text-primary"></i> Nama Tim:</label>
    <select name="created_by" id="cs_filter" class="form-select filter-select" 
        onchange="{{ ($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi') ? 'handleFilterTeam(this)' : 'this.form.submit()' }}">
        <option value="">-- Semua Tim --</option>
        @foreach($csList as $cs)
            @if(
                (auth()->id() == 1 && !in_array($cs->name, ['Latifah', 'Tursia'])) ||
                 (auth()->id() == 13 && in_array($cs->name, ['Latifah', 'Tursia', 'Gunawan', 'Puput'])) ||
                (auth()->user()->name == 'Linda')
            )
                <option value="{{ $cs->id }}" {{ request('created_by') == $cs->id ? 'selected' : '' }}>
                    {{ $cs->name }}
                </option>
            @endif
        @endforeach
    </select>
</div>

@if($kelasFilter != 'Start-Up Muslim Indonesia')
@if(auth()->id() != 13)
{{-- ✅ Filter Kelas --}}
<div class="filter-group">
    <label for="kelas_filter" class="filter-label"><i class="fas fa-chalkboard-teacher text-success"></i> Kelas:</label>
    <select name="kelas" id="kelas_filter" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">-- Semua Kelas --</option>
        @foreach($kelasList as $kelas)
            @if(
                (auth()->id() == 1 && !in_array($kelas->nama_kelas, ['Start-Up Muda Indonesia', 'Sekolah Kaya'])) ||
                         (auth()->id() == 13 && $kelas->nama_kelas == 'Start-Up Muda Indonesia') ||
                (auth()->user()->name == 'Linda')
            )
                <option value="{{ $kelas->nama_kelas }}" {{ request('kelas') == $kelas->nama_kelas ? 'selected' : '' }}>
                    {{ $kelas->nama_kelas }}
                </option>
            @endif
        @endforeach
    </select>
</div>
@endif
@endif
@endif

{{-- ✅ Filter Status --}}
<div class="filter-group">
    <label for="status_filter" class="filter-label"><i class="fas fa-filter text-warning"></i> Status:</label>
    <select name="status" id="status_filter" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">-- Semua Status --</option>
        <option value="cold" {{ request('status') == 'cold' ? 'selected' : '' }}>⚪ Cold</option>
        <option value="tertarik" {{ request('status') == 'tertarik' ? 'selected' : '' }}>🟡 Tertarik</option>
        <option value="mau_transfer" {{ request('status') == 'mau_transfer' ? 'selected' : '' }}>🟢 Mau Transfer</option>
        <option value="sudah_transfer" {{ request('status') == 'sudah_transfer' ? 'selected' : '' }}>🔵 Sudah Transfer</option>
        <option value="no" {{ request('status') == 'no' ? 'selected' : '' }}>🔴 No</option>
    </select>
</div>

    @if($kelasFilter != 'Start-Up Muslim Indonesia' && request('type') != 'smi')
    <div class="filter-group">
        <label for="bulan_filter" class="filter-label">
            <i class="fas fa-calendar-alt text-info"></i> Bulan:
        </label>
        <select name="bulan" id="bulan_filter" class="form-select filter-select" onchange="this.form.submit()">
            <option value="">-- Semua Bulan --</option>
            @foreach([
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
            ] as $num => $name)
                <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>

       <div class="filter-group">
        <label for="tahun_filter" class="filter-label">
            <i class="fas fa-calendar text-secondary"></i> Tahun:
        </label>
        <select name="tahun" id="tahun_filter" class="form-select filter-select" onchange="this.form.submit()">
            <option value="">-- Semua Tahun --</option>
            @php $currentYear = date('Y'); @endphp
            @for ($i = $currentYear; $i >= $currentYear - 3; $i--)
                <option value="{{ $i }}" {{ request('tahun', $currentYear) == $i ? 'selected' : '' }}>
                    {{ $i }}
                </option>
            @endfor
        </select>
    </div>
    @endif




    {{-- 🔄 Tombol Reset --}}
    @if(request('kelas') || request('created_by') || request('status'))
    <div>
        <a href="{{ route('admin.salesplan.index') }}" class="btn btn-outline-secondary btn-reset">
            <i class="fas fa-undo-alt"></i> Reset
        </a>
    </div>
    @endif
</form>
@endif


@if(!$kelasFilter && !$csFilter && !$statusFilter)
    <div class="alert alert-info text-center mt-3">
        Silakan pilih filter untuk menampilkan data.
    </div>
@endif




{{-- Font Awesome CDN --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">







        
<style>
    table {
        border-radius: 10px;
        overflow: hidden;
    }
    thead {
        background: linear-gradient(90deg, #0d6efd, #0b5ed7);
        color: #fff;
    }
    tbody tr:hover {
        background-color: #f8f9fa;
        transition: background 0.2s ease;
    }
    tfoot {
        background-color: #e7f0ff; /* biru muda elegan */
        font-weight: 600;
    }
    tfoot td {
        border-top: 2px solid #0d6efd; /* garis pemisah atas tegas */
    }
    .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.7em;
        border-radius: 8px;
    }
    .progress {
        background: #e9ecef;
        border-radius: 5px;
    }
    .progress-bar {
        border-radius: 5px;
        transition: width 0.4s ease;
    }
    .table th, .table td { vertical-align: middle; }
.progress { background-color: #e9ecef; border-radius: 10px; }
.progress-bar { border-radius: 10px; transition: width 0.6s ease; }


/* Custom Badge Colors for Leads */
.badge-leads-iklan { background-color: #28a745; color: white; } /* Hijau */
.badge-leads-instagram { background-color: #6f42c1; color: white; } /* Ungu */
.badge-leads-facebook { background-color: #0d6efd; color: white; } /* Biru */
.badge-leads-alumni { background-color: #dc3545; color: white; } /* Merah */
.badge-leads-marketing { background-color: #ffc107; color: black; } /* Kuning */
.badge-leads-lain { background-color: #6c757d; color: white; } /* Abu-abu */




    .editable {
        cursor: text;
        transition: background-color 0.2s;
    }
    .editable:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
        outline: 1px dashed #ccc;
    }
    .editable:focus {
        background-color: #fff !important;
        outline: 2px solid #0d6efd;
        color: #000;
        min-width: 100px;
    }

    /* Checklist Button Styling */
    .btn-checklist {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        font-weight: bold;
        border: 1px solid #ccc;
        background: #fff;
        cursor: pointer;
    }
    .btn-checklist.done {
        background: #2ecc71;
        color: white;
        border-color: #27ae60;
    }

    /* Status Dropdown Styling */
    .status-dropdown {
        min-width: 160px;
        padding: 4px 8px;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
    }
    .status-sudah_transfer { background-color: #48e7ecff; color: #030303ff !important; }
    .status-mau_transfer { background-color: #1cff07ff; color: #000 !important; }
    .status-tertarik { background-color: #ffd900ff; color: #000 !important; }
    .status-cold { background-color: #6c757d; color: #fff !important; }
    .status-no { background-color: #FF0000; color: #fff !important; }
</style>




    <!--<a href="{{ route('salesplan.export') }}" class="btn btn-success mb-3">-->
    <!--    Download Excel-->
    <!--</a>-->
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Daftar Sales Plan</h5>
        </div>
        <div class="card-body">
     @php
    $countTertarik = $salesplans->where('status', 'tertarik')->count();
    $countMauTransfer = $salesplans->where('status', 'mau_transfer')->count();
    $countNo = $salesplans->where('status', 'no')->count();
    $countSudahTransfer = $salesplans->where('status', 'sudah_transfer')->count();
    $countCold = $salesplans->where('status', 'cold')->count();

    $totalSalesplan = $countTertarik + $countMauTransfer + $countNo + $countSudahTransfer + $countCold;

    $targetSalesplan = 30;
    $selisihTarget = $targetSalesplan - $totalSalesplan;
@endphp

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between gap-5">
        
        <!-- Target -->
        <div class="text-center">
            <div class="mb-1 fw-semibold text-dark">
                Target
            </div>
            <span class="badge bg-primary fs-5 px-4 py-2 fw-bold text-white">
                {{ $targetSalesplan }}
            </span>
        </div>
        &nbsp;
        <!-- Sudah -->
        <div class="text-center">
            <div class="mb-1 fw-semibold text-dark">
                Sudah
            </div>
            <span class="badge bg-success fs-5 px-4 py-2 fw-bold text-white">
                {{ $totalSalesplan }}
            </span>
        </div>
        &nbsp;
        <!-- Belum -->
        <div class="text-center">
            <div class="mb-1 fw-semibold text-dark">
                Belum
            </div>
            <span class="badge bg-danger fs-5 px-4 py-2 fw-bold text-white">
                {{ max(0, $targetSalesplan - $totalSalesplan) }}
            </span>
        </div>
        &nbsp;
        <!-- Keterangan -->
        <div class="text-center">
            <div class="mb-1 fw-semibold text-dark">
                Keterangan
            </div>
            @if($totalSalesplan >= $targetSalesplan)
                <span class="badge bg-success fs-6 px-4 py-2 fw-bold text-white">
                    🎉 Tercapai
                </span>
            @else
                <span class="badge bg-danger fs-6 px-4 py-2 fw-bold text-white">
                    ⚠️ Belum tercapai
                </span>
            @endif
        </div>

    </div>

    <!-- Progress bar -->

</div>


   <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="input-group" style="max-width: 350px;">
      
        <input type="text" id="searchSalesPlan" class="form-control" placeholder="Cari nama peserta...">
    </div>
    
    
  <!-- FILTER STATUS (Modern Style) -->



        <form method="GET" class="d-flex gap-2">
            
            <input type="hidden" name="kelas" value="{{ request('kelas') }}">
            @if(request('created_by'))
            <input type="hidden" name="created_by" value="{{ request('created_by') }}">
            @endif

            @if($kelasFilter != 'Start-Up Muslim Indonesia' && request('type') != 'smi')
            <select name="bulan" id="bulan_filter_cs"
                class="form-select filter-select"
                onchange="this.form.submit()">
                <option value="">📅 Semua Bulan</option>
                @foreach([
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
                ] as $num => $name)
                    <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            
                       <select name="tahun" id="tahun_filter_cs"
                class="form-select filter-select"
                onchange="this.form.submit()">
                <option value="" {{ request()->has('tahun') && request('tahun') == '' ? 'selected' : '' }}>📅 Semua Tahun</option>
                @php $currentYear = date('Y'); @endphp
                @for ($i = $currentYear; $i >= $currentYear - 3; $i--)
                    <option value="{{ $i }}" {{ request('tahun', $currentYear) == $i ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
            @endif

            <select name="status" id="status_filter"
                class="form-select filter-select"
                onchange="this.form.submit()">

                <option value="">🔍 Semua Status</option>

                <option value="cold" {{ request('status') == 'cold' ? 'selected' : '' }}>
                    ⚪ Cold 
                </option>

                <option value="tertarik" {{ request('status') == 'tertarik' ? 'selected' : '' }}>
                    🟡 Tertarik 
                </option>

                <option value="mau_transfer" {{ request('status') == 'mau_transfer' ? 'selected' : '' }}>
                    🟢 Mau Transfer 
                </option>

                <option value="sudah_transfer" {{ request('status') == 'sudah_transfer' ? 'selected' : '' }}>
                    🔵 Sudah Transfer 
                </option>

                <option value="no" {{ request('status') == 'no' ? 'selected' : '' }}>
                    🔴 No 
                </option>

            </select>
        </form>

    


<style>
    /* Card ringan pembungkus */
    .filter-container {
        background: #ffffff;
        border-radius: 12px;
        border-left: 5px solid #ffb300;
        transition: 0.2s ease-in-out;
    }

    .filter-container:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    /* Select Style */
    .filter-select {
        min-width: 230px;
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid #ddd;
        transition: 0.2s ease-in-out;
        background-color: #fafafa;
        cursor: pointer;
    }

    .filter-select:hover {
        box-shadow: 0 0 8px rgba(255, 179, 0, 0.4);
        border-color: #ffb300;
    }

    .filter-select:focus {
        border-color: #ffb300;
        box-shadow: 0 0 8px rgba(255, 179, 0, 0.6);
    }
</style>
    
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function initFloatingScroll() {
        const tableScroll = document.querySelector('.table-scroll');
        if (!tableScroll) return;

        // Remove existing if any (to avoid duplicates on re-init)
        const existing = document.querySelector('.floating-scroll-bar');
        if (existing) existing.remove();

        // Create scrollbar element
        const floatingScroll = document.createElement('div');
        floatingScroll.className = 'floating-scroll-bar';
        // Increased z-index and added a subtle shadow/border to make it "pop"
        floatingScroll.style.cssText = `
            position: fixed; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            overflow-x: auto; 
            z-index: 9999; 
            background: #fff; 
            border-top: 2px solid #25799E; 
            height: 20px; 
            display: none;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        `;
        
        const inner = document.createElement('div');
        inner.style.height = '1px';
        floatingScroll.appendChild(inner);
        document.body.appendChild(floatingScroll);

        const syncScroll = () => {
            if (!tableScroll.isConnected) return; // Guard for detached elements
            
            inner.style.width = tableScroll.scrollWidth + 'px';
            const rect = tableScroll.getBoundingClientRect();
            
            // Logic: Show if the table's own scrollbar (at its bottom) is below the screen
            // and the top of the table is at least partially on screen.
            const isVisible = rect.top < window.innerHeight && rect.bottom > window.innerHeight;
            
            // Also check if there's actually something to scroll
            const hasScroll = tableScroll.scrollWidth > tableScroll.clientWidth;
            
            floatingScroll.style.display = (isVisible && hasScroll) ? 'block' : 'none';
            
            // Sync position and width with the table container
            floatingScroll.style.left = rect.left + 'px';
            floatingScroll.style.width = rect.width + 'px';
            
            // Sync scroll progress
            floatingScroll.scrollLeft = tableScroll.scrollLeft;
        };

        floatingScroll.onscroll = () => {
            tableScroll.scrollLeft = floatingScroll.scrollLeft;
        };

        tableScroll.onscroll = () => {
            floatingScroll.scrollLeft = tableScroll.scrollLeft;
        };

        window.addEventListener('scroll', syncScroll);
        window.addEventListener('resize', syncScroll);
        
        // Initial sync
        setTimeout(syncScroll, 100);
    }

    function initSmiScripts() {
        // 🔍 Search Participant
        $('#searchSalesPlan').off('keyup').on('keyup', function() {
            let query = $(this).val().toLowerCase();
            $('.data-row').each(function() {
                let nama = $(this).find('td:nth-child(2)').text().toLowerCase();
                $(this).toggle(nama.includes(query));
            });
            // Re-sync scroll after filter
            initFloatingScroll();
        });

        // Initialize floating scroll
        initFloatingScroll();
    }

    initSmiScripts();

    // 🚀 Smooth Team Filter for SMI
    window.handleFilterTeam = function(select) {
        const csId = select.value;
        const url = new URL(window.location.href);
        
        if (csId) url.searchParams.set('created_by', csId);
        else url.searchParams.delete('created_by');

        // Show loading state
        const wrapper = $('#content');
        wrapper.css('opacity', '0.5').css('pointer-events', 'none');
        
        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newInnerContent = doc.querySelector('#content').innerHTML;
            
            // Update content smoothly
            document.querySelector('#content').innerHTML = newInnerContent;
            
            // Update URL and state
            window.history.pushState({}, '', url.toString());
            $('#content').css('opacity', '1').css('pointer-events', 'auto');
            
            // Re-initialize
            initSmiScripts();
        })
        .catch(err => {
            console.error('Filter error:', err);
            window.location.href = url.toString(); // final fallback
        });
    };
});
</script>


</div>

            <div class="table-responsive table-scroll">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="text-white" style="background-color:#25799E;">


                        <tr>
                            <th rowspan="3">No</th>
                            <th rowspan="3">Nama</th>
                            @if($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi')
                            <th rowspan="3">Level</th>
                            @endif
                            @if(strtolower(auth()->user()->role) !== 'administrator')
                                <th rowspan="3">Sumber Leads</th>
                            @endif
                         {{-- <th rowspan="3">
    {{ $kelasFilter == 'Start-Up Muda Indonesia' ? 'Situasi Anak' : 'Situasi Bisnis' }}
</th> --}}
                            <th rowspan="3">KEBUTUHAN</th>

                            {{-- Header grup untuk FU --}}
                            <th colspan="10" class="text-center">Follow Up</th>

                            @unless($isSmiClass || request('type') == 'smi')
                            <th rowspan="3">Potensi</th>
                            @endunless
                            @unless($isSmiClass || request('type') == 'smi')
                             <th rowspan="3">Closing Paket</th>
                            @endunless
                             <th rowspan="3">Status</th>
                            @if(strtolower(auth()->user()->role) !== 'administrator')
                                <th rowspan="3">Terakhir Update</th>
                            @endif
                        
                             @if(Auth::user()->email == "mbchamasah@gmail.com" || 
                                 in_array(strtolower(auth()->user()->role), ['administrator', 'cs-mbc']))
                             <th rowspan="3">Nama CS</th>
                             @endif
                             @if(false) {{-- Komentar Atasan Dihapus --}}
                             <th rowspan="3" style="min-width: 200px;">Komentar Atasan</th>
                             @endif
                            <th rowspan="3">Aksi</th>
                        </tr>
                        <tr>
                            {{-- Header FU 1 - 5 --}}
                            @for ($i = 1; $i <= 5; $i++)
                                <th colspan="2" class="text-center">FU {{ $i }}</th>
                                @endfor
                        </tr>
                        <tr>
                            {{-- Sub kolom Hasil & Tindak Lanjut --}}
                            @for ($i = 1; $i <= 5; $i++)
                                <th>Hasil FU</th>
                                <th>RTL</th>
                                @endfor
                        </tr>
                    </thead>



                    <tbody>
                        @php $currentMonth = null; @endphp
                        @forelse ($salesplans as $plan)
                         @if(($kelasFilter == 'Start-Up Muslim Indonesia' || $kelasFilter == 'Start-Up Muda Indonesia' || request('type') == 'smi') && $plan->created_at)
                            @php
                                $planMonth = \Carbon\Carbon::parse($plan->created_at)->locale('id')->isoFormat('MMMM Y');
                            @endphp
                            @if($currentMonth !== $planMonth)
                                <tr class="table-light">
                                    <td colspan="{{ ($isSmiClass || request('type') == 'smi') ? 22 : 25 }}" class="fw-bold text-start ps-4 py-2" style="background-color: #e9ecef;">
                                        🗓️ {{ $planMonth }}
                                    </td>
                                </tr>
                                @php $currentMonth = $planMonth; @endphp
                            @endif
                        @endif

                        @php
                            $rowClass = '';
                            if ($plan->status == 'sudah_transfer') {
                                $rowClass = 'table-info';
                            } elseif ($plan->status == 'mau_transfer') {
                                $rowClass = 'table-success';
                            } elseif ($plan->status == 'tertarik') {
                                $rowClass = 'table-warning';
                            } elseif ($plan->status == 'no') {
                                $rowClass = 'table-danger';
                            } elseif ($plan->status == 'cold') {
                                $rowClass = 'table-secondary'; 
                            }
                        @endphp

                        <tr class="{{ $rowClass }} data-row" data-cs-id="{{ $plan->created_by }}">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $plan->nama ?? '-' }}</td>
                            @if($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi')
                            <td class="p-1" style="width: 120px;">
                                <select class="form-control form-control-sm level-select" data-id="{{ $plan->id }}" style="width:100% !important;">
                                    <option value="">- Pilih -</option>
                                    <option value="Start Up" {{ $plan->level == 'Start Up' ? 'selected' : '' }}>Start Up</option>
                                    <option value="Grow Up" {{ $plan->level == 'Grow Up' ? 'selected' : '' }}>Grow Up</option>
                                    <option value="Scale Up" {{ $plan->level == 'Scale Up' ? 'selected' : '' }}>Scale Up</option>
                                </select>
                            </td>
                            @endif
                            @if(strtolower(auth()->user()->role) !== 'administrator')
                                @php
                                $leadSource = $plan->data->leads ?? ($dataMap[$plan->nama]->leads ?? '-');
                                $leadLower = strtolower($leadSource);
                                $badgeClass = 'badge-leads-lain'; // Default abu-abu

                                if (str_contains($leadLower, 'iklan')) {
                                    $badgeClass = 'badge-leads-iklan';
                                } elseif (str_contains($leadLower, 'instagram') || str_contains($leadLower, 'ig')) {
                                    $badgeClass = 'badge-leads-instagram';
                                } elseif (str_contains($leadLower, 'facebook') || str_contains($leadLower, 'fb')) {
                                    $badgeClass = 'badge-leads-facebook';
                                } elseif (str_contains($leadLower, 'alumni')) {
                                    $badgeClass = 'badge-leads-alumni';
                                } elseif (str_contains($leadLower, 'marketing')) {
                                    $badgeClass = 'badge-leads-marketing';
                                }
                            @endphp
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $leadSource }}
                                </span>
                            </td>
                            @endif
                            {{-- <td>{{ $plan->situasi_bisnis ?? '-' }}</td> --}}
                            <!--<td>{{ $plan->kendala ?? '-' }}</td>-->
                            
                                   {{-- KEBUTUHAN (Editable) --}}
                            <td contenteditable="true" class="editable"
                                data-id="{{ $plan->id }}"
                                data-field="kebutuhan">
                                {{ $plan->kebutuhan ?? '-' }}
                            </td>


                            @for ($i = 1; $i <= 5; $i++)
                                <td contenteditable="true" class="editable"
                                data-id="{{ $plan->id }}"
                                data-field="fu{{ $i }}_hasil">
                                {{ $plan->{'fu'.$i.'_hasil'} ?? '-' }}
                                </td>

                                <td contenteditable="true" class="editable"
                                    data-id="{{ $plan->id }}"
                                    data-field="fu{{ $i }}_tindak_lanjut">
                                    {{ $plan->{'fu'.$i.'_tindak_lanjut'} ?? '-' }}
                                </td>
                                @endfor

                                @unless($isSmiClass || request('type') == 'smi')
                                <td contenteditable="true" class="editable fw-bold text-bold"
                                    data-id="{{ $plan->id }}"
                                    data-field="nominal">
                                    {{ number_format($plan->nominal, 0, ',', '.') }}
                                </td>
                                @endunless

                         @unless($isSmiClass || request('type') == 'smi')
                         <td class="text-center">
    <button class="btn btn-sm btn-checklist {{ $plan->closing_paket ? 'done' : '' }}"
        data-id="{{ $plan->id }}"
        data-field="closing_paket"
        data-value="{{ $plan->closing_paket ? 1 : 0 }}"
        style="font-size:18px;">
        @if($plan->closing_paket)
            ✔
        @else
            ☐
        @endif
    </button>
</td>
@endunless

@php $totalSeluruhCS = 0; @endphp {{-- Dummy for consistency if needed --}}



                                <td class="text-center">
                                    <select class="form-control form-control-sm status-dropdown 
                                      status-{{ $plan->status }}"
                                        data-id="{{ $plan->id }}"
                                        style="min-width: 160px;">
                                        <option value="sudah_transfer" {{ $plan->status == 'sudah_transfer' ? 'selected' : '' }}>Sudah Transfer</option>
                                        <option value="mau_transfer" {{ $plan->status == 'mau_transfer' ? 'selected' : '' }}>Mau Transfer</option>
                                        <option value="tertarik" {{ $plan->status == 'tertarik' ? 'selected' : '' }}>Tertarik</option>
                                        <option value="cold" {{ $plan->status == 'cold' ? 'selected' : '' }}>Cold</option>
                                        <option value="no" {{ $plan->status == 'no' ? 'selected' : '' }}>No</option>
                                    </select>
                                </td>

                                @if(strtolower(auth()->user()->role) !== 'administrator')
                                <td class="text-center">
                                    <small>{{ $plan->updated_at ? $plan->updated_at->format('d M Y H:i') : '-' }}</small>
                                </td>
                                @endif








                                @if(Auth::user()->email == "mbchamasah@gmail.com" || 
                                    in_array(strtolower(auth()->user()->role), ['administrator', 'cs-mbc']))
                                <td>
                                    {{ \App\Models\User::find($plan->created_by)->name ?? '-' }}
                                </td>
                                @endif

                                 <!-- Komentar Atasan -->
                                 @if(false)
                                 <td @if(strtolower(auth()->user()->role) == 'administrator') 
                                         contenteditable="true" 
                                         class="editable bg-light"
                                     @endif
                                     data-id="{{ $plan->id }}"
                                     data-field="komentar_atasan">
                                     {{ $plan->komentar_atasan ?? '' }}
                                 </td>
                                 @endif

                                <!--Form Hapus-->
                                <td>
                                                        <form id="delete-form-{{ $plan->id }}"
      action="{{ route('admin.salesplan.destroy', $plan->id) }}"
      method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-danger btn-sm btn-delete-trigger" data-id="{{ $plan->id }}">
        <i class="fas fa-trash"></i> Hapus
    </button>
</form>
                                </td>



                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($isSmiClass || request('type') == 'smi') ? 22 : 25 }}" class="text-center text-muted">
                                Tidak ada data sales plan ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
            <div class="d-flex justify-content-center mt-3">
    @if(method_exists($salesplans, 'links'))
            {{ $salesplans->links('pagination::bootstrap-4') }}
@endif

            </div>
<style>
    /* Fix for giant pagination icons if Tailwind view leaks in */
    nav svg {
        max-height: 20px;
        width: auto;
    }
</style>


        </div>
    </div>


    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Simpan nilai awal saat fokus
        $(document).on('focus', '.editable', function() {
            let currentText = $(this).text().trim();
            $(this).data('original', currentText);
            
            // UX: Jika isinya hanya strip '-', kosongkan saat user mau ngetik
            if (currentText === '-') {
                $(this).text('');
            }
        });

        $(document).on('blur', '.editable', function() {
            let id = $(this).data('id');
            let field = $(this).data('field');
            let value = $(this).text().trim();
            let original = $(this).data('original');
            let $element = $(this); // Capture element reference

            // Jika kosong, kembalikan ke '-' agar rapi
            if (value === '') {
                value = '-';
                $element.text('-');
            }

            // Jika tidak ada perubahan, jangan kirim request
            if (value === original) return;

            $.ajax({
                url: "{{ route('admin.salesplan.inline-update') }}", 
                type: "POST",
                context: this, // Ensure 'this' refers to the DOM element in callbacks
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: field,
                    value: value
                },
                success: function(res) {
                    console.log("✅ Update sukses:", res);
                    $element.data('original', value);
                     
                    Toast.fire({
                        icon: 'success',
                        title: 'Tersimpan'
                    });
                },
                error: function(xhr, status, error) {
                    console.error("❌ Gagal update:", xhr.responseText);
                    $element.text(original); // Revert safely
                    
                    let msg = "Gagal update data!";
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg += "\n" + xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg += "\n" + xhr.responseJSON.error;
                    } else {
                         msg += "\nStatus: " + xhr.status + " " + xhr.statusText;
                         if (xhr.responseText) {
                             msg += "\n" + xhr.responseText.substring(0, 50);
                         }
                    }
                    alert(msg);
                }
            });
        });
    </script>

    <script>
    $(document).ready(function() {
        // Event Delegation for Checklist Button
        $(document).on('click', '.btn-checklist', function() {
            let $btn = $(this);
            let id = $btn.data('id');
            let field = $btn.data('field');
            let current = $btn.data('value');

            // Toggle value (1 for true/done, 0 for false/pending)
            let newValue = (current == 1) ? 0 : 1;

            // Update UI
            if (newValue == 1) {
                $btn.html("✔").addClass("done");
            } else {
                $btn.html("☐").removeClass("done");
            }
            $btn.data('value', newValue);

            $.ajax({
                url: "{{ route('admin.salesplan.inline-update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: field,
                    value: newValue
                },
                success: function(res) {
                    console.log("Checklist updated:", res);
                }
            });
        });

        // 🚀 Level Update for SMI
        $(document).on('change', '.level-select', function() {
            let id = $(this).data('id');
            let value = $(this).val();

            $.post("{{ route('admin.salesplan.inline-update') }}", {
                _token: "{{ csrf_token() }}",
                id: id,
                field: 'level',
                value: value
            }).done(function() {
                 Toast.fire({
                    icon: 'success',
                    title: 'Level Terupdate'
                });
            });
        });

        // Event Delegation for Status Dropdown
        $(document).on('change', '.status-dropdown', function() {
            let id = $(this).data('id');
            let value = $(this).val();
            let $dropdown = $(this);

            $.ajax({
                url: "/admin/salesplan/update-status/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: value
                },
                success: function(res) {
                    // Update Dropdown Class
                    $dropdown.removeClass("status-sudah_transfer status-mau_transfer status-tertarik status-cold status-no")
                            .addClass("status-" + value);

                    // Update Row Color
                    let $row = $dropdown.closest('tr');
                    $row.removeClass("table-info table-success table-warning table-danger table-secondary");

                    if (value === "sudah_transfer") $row.addClass("table-info");
                    if (value === "mau_transfer")    $row.addClass("table-success");
                    if (value === "tertarik")        $row.addClass("table-warning");
                    if (value === "no")              $row.addClass("table-danger");
                    if (value === "cold")            $row.addClass("table-secondary");
                }
            });
        });

        // Event Delegation for Delete Button
        $(document).on('click', '.btn-delete-trigger', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data yang sudah dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#delete-form-${id}`).submit();
                }
            });
        });
    });
    </script>






<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge bg-warning text-white p-2 me-2 fs-6" style="font-size: 13px">
            Tertarik: {{ $countTertarik }}
        </span>
        <span class="badge bg-success text-white p-2 me-2 fs-6" style="font-size: 13px">
            Mau Transfer: {{ $countMauTransfer }}
        </span>
        <span class="badge bg-danger text-white p-2 me-2 fs-6" style="font-size: 13px">
            No: {{ $countNo }}
        </span>
        <span class="badge bg-info text-white p-2 me-2  fs-6" style="font-size: 13px">
            Sudah Transfer: {{ $countSudahTransfer }}
        </span>
        <span class="badge bg-secondary text-white p-2 fs-6"style="font-size: 13px">
            Cold: {{ $countCold }}
        </span>
    </div>
</div>
</div>



{{-- Tabel Sales Plan yang sudah ada --}}

{{-- Tabel Daftar Peserta --}}
<h4 class="mt-4 fw-bold text-center">
    Daftar Peserta / {{ $kelasFilter }}
    @if(auth()->check() && strtolower(auth()->user()->role) === 'cs-mbc')
        - {{ auth()->user()->name }}
    @endif
</h4>


<!-- Dropdown contoh -->

<hr>


<!-- Tabel daftar peserta -->


<div style="overflow-x: auto; white-space: nowrap;">
    <table id="tabelPeserta" style="border-collapse: collapse; width: 100%; text-align: center; font-family: Arial, sans-serif; font-size: 14px; min-width: 500px;">
        <thead>
            <tr style="background: linear-gradient(to right, #376bb9ff, #1c7f91ff); color: white;">
                <th style="padding: 10px; border: 1px solid #ccc;">No</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nama Peserta</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nominal</th>
                                <th style="padding: 10px; border: 1px solid #ccc;">Nama CS Closing</th>
            </tr>
        </thead>
   <tbody>
    @php $totalNominal = 0; @endphp
    @forelse(($pesertaTransfer ?? collect()) as $i => $p)
        <tr>
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $i+1 }}</td>
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $p->nama }}</td>
            <td style="padding: 8px; border: 1px solid #ccc;">
                Rp {{ number_format($p->nominal, 0, ',', '.') }}
            </td>
                     <td style="padding: 8px; border: 1px solid #ccc;">
                {{ \App\Models\User::find($p->created_by)->name ?? '-' }}
            </td>
        </tr>
        @php $totalNominal += $p->nominal; @endphp
    @empty
        <tr>
            <td colspan="3" style="text-align: center; padding: 15px; color: #999;">
                Salesplan belum ada
            </td>
        </tr>
    @endforelse
</tbody>

         <tfoot>
            <tr style="background: #f2f2f2; font-weight: bold; color: #040e0fff;">
                <td colspan="2" style="padding: 10px; border: 1px solid #ccc; text-align: right;">Total Omset</td>
                <td style="padding: 10px; border: 1px solid #ccc;">
                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                </td>
                                <td style="padding: 10px; border: 1px solid #ccc;"></td>
            </tr>

            <!-- Target Omset -->
            <tr style="background: #d1e7dd; font-weight: bold; color: #0f5132;">
                <td colspan="2" style="padding: 10px; border: 1px solid #ccc; text-align: right;">Target Omset</td>
                <td style="padding: 10px; border: 1px solid #ccc;">
                    @php
                        if (!request('created_by')) {
                            $targetOmsetVal = 125000000;
                        } elseif ($kelasFilter == 'Start-Up Muda Indonesia' || $kelasFilter == 'Start-Up Muslim Indonesia') {
                            $targetOmsetVal = isset($targetOmsetSmi) && $targetOmsetSmi > 0 ? $targetOmsetSmi : 50000000;
                        } else {
                            $targetOmsetVal = isset($targetOmsetGlobal) && $targetOmsetGlobal > 0 ? $targetOmsetGlobal : 25000000;
                        }
                    @endphp
                    Rp {{ number_format($targetOmsetVal, 0, ',', '.') }}
                </td>
                           <td style="padding: 10px; border: 1px solid #ccc;"></td>
            </tr>
        </tfoot>
    </table>
</div>



<script>
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            if (this.value === 'done') {
                let nama = this.dataset.nama;
                let nominal = this.dataset.nominal;

                let tbody = document.querySelector('#tabelPeserta tbody');
                let emptyRow = document.getElementById('emptyRow');
                if (emptyRow) emptyRow.remove();

                let rowCount = tbody.rows.length + 1;
                let newRow = `
        <tr style="background: #fdfdfd; color: black;">
          <td style="padding: 8px; border: 1px solid #ccc;">${rowCount}</td>
          <td style="padding: 8px; border: 1px solid #ccc;">${nama}</td>
          <td style="padding: 8px; border: 1px solid #ccc;">Rp ${parseInt(nominal).toLocaleString('id-ID')}</td>
        </tr>
      `;
                tbody.insertAdjacentHTML('beforeend', newRow);
            }
        });
    });
</script>



</div>
<script>
    $(document).ready(function() {
        $('.status-cell').each(function() {
            const status = $(this).text().trim().toLowerCase();
            const row = $(this).closest('tr');

            switch (status) {
                case 'hot':
                    row.css('background-color', '#d4edda'); // Hijau muda
                    break;
                case 'warm':
                    row.css('background-color', '#fff3cd'); // Kuning muda
                    break;
                case 'cold':
                    row.css('background-color', '#ffffff'); // Putih (default)
                    break;
                case 'no':
                    row.css('background-color', '#f8d7da'); // Merah muda
                    break;
                default:
                    row.css('background-color', '#f0f0f0'); // Abu (jika status tidak dikenal)
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "lengthMenu": [
                [15, 25, 50, 100, 500],
                [15, 25, 50, 100, 500]
            ]
        });
    });
</script>

@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
