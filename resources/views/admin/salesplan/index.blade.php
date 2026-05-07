@extends('layouts.masteradmin')
@section('content')
@php 
    $userRole = strtolower(Auth::user()->role);
    $canEdit = ($userRole !== 'administrator'); 
@endphp

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
        top: 0; /* Sticks to top of viewport */
        z-index: 100 !important;
        background-color: #25799E !important;
        color: white !important;
        border: 2px solid #999 !important;
        vertical-align: middle;
        text-align: center;
        padding: 10px 5px !important;
    }

    /* Multi-row offsets for 3-row header */
    /* Row 1 cells with rowspan=3 will stay at top: 0 */
    .table-scroll thead tr:nth-child(2) th { 
        top: 60px !important; /* Adjusted for first row height */
        z-index: 99 !important; 
    }
    .table-scroll thead tr:nth-child(3) th { 
        top: 92px !important; /* Adjusted for second row height */
        z-index: 98 !important; 
    }

    /* Ensure parent containers don't break vertical stickiness */
    #content-wrapper, #content {
        overflow-y: visible !important;
    }

    /* Additional polish for the main table row groups (Maret 2026, etc) */
    .table-light td {
        position: sticky !important;
        top: 130px !important; /* Below the 3-row header */
        z-index: 90 !important;
        background-color: #f8f9fc !important;
    }

    /* Frozen Columns (Horizontal Sticky) - No and Nama */
    .sticky-col-no {
        position: sticky !important;
        left: 0 !important;
        z-index: 60 !important;
        background-color: #fff;
        min-width: 45px;
        max-width: 45px;
        border-right: 2px solid #999 !important;
    }
    
    .sticky-col-nama {
        position: sticky !important;
        left: 45px !important; 
        z-index: 60 !important;
        background-color: #fff;
        min-width: 180px;
        max-width: 180px;
        border-right: 2px solid #999 !important;
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

    /* Column Header Overlap (Freeze Corners) */
    th.sticky-col-no,
    th.sticky-col-nama {
        background-color: #25799E !important;
        z-index: 110 !important;
    }

    /* Body cell backgrounds for frozen columns - following row status */
    .table-scroll tbody tr td.sticky-col-no,
    .table-scroll tbody tr td.sticky-col-nama {
        background-color: inherit;
    }

    /* Specific status colors for frozen columns to maintain stickiness */
    .table-scroll tr.table-info td.sticky-col-no, .table-scroll tr.table-info td.sticky-col-nama { background-color: #e2f0f3 !important; }
    .table-scroll tr.table-success td.sticky-col-no, .table-scroll tr.table-success td.sticky-col-nama { background-color: #e2f3e7 !important; }
    .table-scroll tr.table-warning td.sticky-col-no, .table-scroll tr.table-warning td.sticky-col-nama { background-color: #fff8e1 !important; }
    .table-scroll tr.table-danger td.sticky-col-no, .table-scroll tr.table-danger td.sticky-col-nama { background-color: #FF0000 !important; color: white !important; }
    .table-scroll tr.table-secondary td.sticky-col-no, .table-scroll tr.table-secondary td.sticky-col-nama { background-color: #ffffff !important; }
    
    /* Global red override for status NO */
    .table-danger, 
    .table-danger td { background-color: #FF0000 !important; color: #fff !important; }
    .table-danger .editable { color: #fff !important; }
    .table-danger .status-dropdown { background-color: #FF0000 !important; border-color: #fff !important; color: #fff !important; }
    
    /* Cold status contrast override */
    .table-secondary,
    .table-secondary td { background-color: #ffffff !important; }

    td {
        font-size: 14px;
        padding: 8px;
        text-align: left;
        color: #000 !important;
        font-weight: 500;
        border: 2px solid #999 !important;
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

    /* FU Textarea Style */
    .fu-textarea {
        width: 150px !important;
        min-height: 65px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #999;
        border-radius: 6px;
        padding: 5px;
        background-color: #fff;
        color: #000 !important;
        line-height: 1.4;
        resize: vertical;
        display: block;
        margin-bottom: 5px;
    }
    .fu-textarea:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 5px rgba(13, 110, 253, 0.2);
        outline: none;
    }
    .shadowed-btn {
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        border: 2px solid #fff !important;
        color: #fff !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }
    .shadowed-btn:hover {
        box-shadow: 0 6px 8px rgba(0,0,0,0.2);
        transform: translateY(-1px);
    }
</style>

@if(($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc'))
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
    th.sticky-col-no, td.sticky-col-no { left: 0 !important; min-width: 45px !important; }
    th.sticky-col-nama, td.sticky-col-nama { left: 45px !important; min-width: 180px !important; }
    
    /* Adjust column offsets for SMI (larger font/padding) */
    td.sticky-col-no, th.sticky-col-no { left: 0; width: 45px; }
    td.sticky-col-nama, th.sticky-col-nama { left: 45px; }
    thead th.sticky-col-nama { left: 45px; }
    
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
        {{ in_array(auth()->user()->name, ['Yasmin', 'Linda', 'Shafa Zahra']) ? 'DATA PESERTA' : 'PROSPEK' }} 
        @if(request('type') == 'mbc' || ($isCsMbc && request('type') != 'smi'))
            MBC
        @endif
        @if($kelasFilter)
        / {{ $kelasFilter == 'Start-Up Muslim Indonesia' ? 'M1T' : $kelasFilter }}
        @endif
    </h1>

    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item">{{ in_array(auth()->user()->name, ['Yasmin', 'Linda', 'Shafa Zahra']) ? 'DATA PESERTA' : 'PROSPEK' }}</li>
            @if($kelasFilter)
            <li class="breadcrumb-item active">{{ $kelasFilter == 'Start-Up Muslim Indonesia' ? 'M1T' : $kelasFilter }}</li>
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
    $targetOmset = 50000000; // Rp 50.000.000
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

<!-- Filter Container -->
@php
    $isAdminView = (auth()->id() == 1 || auth()->id() == 13 || (auth()->user()->name == 'Linda' && empty($isRestrictedView)));
    $isCsMbc = (strtolower(auth()->user()->role) === 'cs-mbc');
@endphp
@if($isAdminView || $isCsMbc)
<form method="GET" action="{{ route('admin.salesplan.index') }}" class="filter-container" id="filterFormMbc">
    @if(request()->has('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
    @endif
<style>
    .filter-container {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 0.5rem 0.75rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
        align-items: center;
        overflow: hidden; /* No scrollbar */
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
        flex: 1;
        min-width: 100px;
        padding: 0.4rem 0.5rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 0.8rem;
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

    .btn-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.85rem;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-filter:hover {
        background: linear-gradient(135deg, #0b5ed7, #0a58ca);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        color: white;
    }

    .btn-filter:active {
        transform: translateY(0);
    }

    @media (max-width: 992px) {
        .filter-container {
            flex-wrap: wrap;
        }
        .filter-select {
            width: 100%;
            min-width: unset;
        }
        .filter-group {
            flex-direction: column;
            align-items: flex-start;
        }
        .btn-filter, .btn-reset {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@if($isAdminView && !$isCsMbc)
{{-- ✅ Filter CS --}}
<div class="filter-group">
    <label for="cs_filter" class="filter-label" title="Nama Tim"><i class="fas fa-user-tie text-primary"></i></label>
    <select name="created_by" id="cs_filter" class="form-select filter-select">
        <option value="">-- Semua Tim --</option>
        @foreach($csList as $cs)
            @if((auth()->id() == 1 && !in_array($cs->name, ['Latifah', 'Tursia'])) || (auth()->id() == 13 && in_array($cs->name, ['Latifah', 'Tursia', 'Gunawan', 'Puput'])) || (auth()->user()->name == 'Linda'))
                <option value="{{ $cs->id }}" {{ request('created_by') == $cs->id ? 'selected' : '' }}>
                    {{ $cs->name }}
                </option>
            @endif
        @endforeach
    </select>
</div>
@endif

@if($kelasFilter != 'Start-Up Muslim Indonesia')
@if(auth()->id() != 13)
{{-- ✅ Filter Kelas --}}
<div class="filter-group">
    <label for="kelas_filter" class="filter-label" title="Kelas"><i class="fas fa-chalkboard-teacher text-success"></i></label>
    <select name="kelas" id="kelas_filter" class="form-select filter-select">
        <option value="">-- Kelas --</option>
        @foreach($kelasList as $kelas)
            @if(request('type') == 'mbc' && $kelas->nama_kelas == 'Start-Up Muslim Indonesia')
                @continue
            @endif
            @if(
                (auth()->id() == 1 && !in_array($kelas->nama_kelas, ['Start-Up Muda Indonesia', 'Sekolah Kaya', 'Start-Up Muslim Indonesia'])) ||
                         (auth()->id() == 13 && $kelas->nama_kelas == 'Start-Up Muda Indonesia') ||
                (auth()->user()->name == 'Linda') ||
                (strtolower(auth()->user()->role) === 'cs-mbc')
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

@if($kelasFilter != 'Start-Up Muslim Indonesia' && request('type') != 'smi')
{{-- ✅ Filter Bulan & Tahun (Untuk Semua Role) --}}
<div class="filter-group">
    <label for="bulan_top" class="filter-label" title="Bulan"><i class="fas fa-calendar-alt text-danger"></i></label>
    <select name="bulan" id="bulan_top" class="form-select filter-select" style="min-width: 80px;">
        <option value="">- Bulan -</option>
        @foreach([
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ] as $num => $name)
            <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
    </select>
</div>

<div class="filter-group">
    <label for="tahun_top" class="filter-label" title="Tahun"><i class="fas fa-calendar text-secondary"></i></label>
    <select name="tahun" id="tahun_top" class="form-select filter-select" style="min-width: 80px;">
        <option value="semua" {{ request('tahun') == 'semua' ? 'selected' : '' }}>Tahun</option>
        @php $currentYear = date('Y'); @endphp
        @for ($i = $currentYear; $i >= $currentYear - 3; $i--)
            <option value="{{ $i }}" {{ (request('tahun', $currentYear) == $i && request('tahun') != 'semua') ? 'selected' : '' }}>{{ $i }}</option>
        @endfor
    </select>
</div>
@endif



    <div class="d-flex gap-2 ms-auto">
        @if(request('kelas') || request('created_by') || request('status') || request('bulan') || request('tahun'))
            <a href="{{ route('admin.salesplan.index') }}" class="btn btn-outline-secondary btn-reset">
                <i class="fas fa-undo-alt"></i>
            </a>
        @endif
        <button type="submit" class="btn-filter" style="padding: 0.4rem 1rem;">
            <i class="fas fa-search me-1"></i> Search
        </button>
    </div>
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
    /* Timestamp Input Styling */
    .timestamp-input {
        font-size: 11px;
        background: #fff;
        border: 1px solid #ccc !important;
        border-radius: 4px;
        color: #000 !important;
        font-weight: bold !important;
        padding: 1px 3px;
        height: auto;
        width: auto;
        max-width: 135px;
        cursor: pointer;
        display: inline-block;
        margin-top: 2px;
    }
    .timestamp-input:focus {
        outline: none;
        border-color: #0d6efd !important;
        box-shadow: 0 0 3px rgba(13, 110, 253, 0.3);
        color: #0d6efd !important;
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
    .status-cold { background-color: #ffffff !important; color: #000 !important; border: 2px solid #999 !important; }
    .status-no { background-color: #FF0000; color: #fff !important; }
</style>






@php
    $initialMaxFu = 5;
    foreach ($salesplans as $item) {
        for ($f = 12; $f >= 6; $f--) {
            if (!empty($item->{'fu'.$f.'_hasil'}) || !empty($item->{'fu'.$f.'_tindak_lanjut'})) {
                $initialMaxFu = max($initialMaxFu, $f);
            }
        }
    }
@endphp
@if(strtolower(Auth::user()->role) !== 'administrator' && !in_array(auth()->user()->name, ['Yasmin', 'Linda', 'Shafa Zahra']))
    <div class="card shadow-lg border-0 rounded-lg mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Daftar PROSPEK</h5>
        </div>
        <div class="card-body">
     @php
    $countTertarik = $salesplans->where('status', 'tertarik')->count();
    $countMauTransfer = $salesplans->where('status', 'mau_transfer')->count();
    $countNo = $salesplans->where('status', 'no')->count();
    $countSudahTransfer = (strtolower(auth()->user()->role) === 'administrator') ? $pesertaTransfer->count() : $salesplans->where('status', 'sudah_transfer')->count();
    $countCold = $salesplans->where('status', 'cold')->count();

    $totalSalesplan = $countTertarik + $countMauTransfer + $countNo + $countSudahTransfer + $countCold;

    $targetSalesplan = 30;
    $selisihTarget = $targetSalesplan - $totalSalesplan;
@endphp

@if(!in_array($userRole, ['reseller', 'chapter']))
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
@endif


   <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <div class="input-group" style="max-width: 350px;">
            <input type="text" id="searchSalesPlan" class="form-control" placeholder="Cari nama peserta...">
        </div>
        <button type="button" class="btn btn-danger d-flex align-items-center gap-2 px-3 shadow-sm rounded-pill" 
                onclick="exportSalesplanPdf()"
                style="background: linear-gradient(45deg, #c0392b, #e74c3c); border: none; font-weight: 600; white-space: nowrap;">
            <i class="fas fa-file-pdf"></i> Export PDF
        </button>
        <button type="button" class="btn btn-warning d-flex align-items-center gap-2 px-3 shadow-sm rounded-pill" 
                onclick="showTodayTasks()"
                style="background: linear-gradient(45deg, #f39c12, #f1c40f); border: none; font-weight: 600; white-space: nowrap; color: white;">
            <i class="fas fa-tasks"></i> To Do List
        </button>

    </div>
    
    
  <!-- FILTER STATUS (Modern Style) -->
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

    // 🚀 Smooth AJAX Filter for SMI
    window.handleAjaxFilter = function(select, paramName) {
        const val = select.value;
        const url = new URL(window.location.href);
        
        if (val) url.searchParams.set(paramName, val);
        else url.searchParams.delete(paramName);

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

    // 🚀 Handle Global Filter Submit via AJAX
    $('#filterFormMbc').on('submit', function(e) {
        if (!{{ ($isCsMbc || $kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc') ? 'true' : 'false' }}) {
            return; // let standard submit happen if not in AJAX mode
        }
        
        e.preventDefault();
        const url = new URL(this.action);
        const formData = new FormData(this);
        
        for (let [key, value] of formData.entries()) {
            if (value && value !== 'semua') {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        }

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
            console.error('Submit filter error:', err);
            window.location.href = url.toString();
        });
    });
});
</script>


</div>

            @if(!in_array(auth()->user()->name, ['Yasmin', 'Linda', 'Shafa Zahra']))
            <div class="table-responsive table-scroll">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="text-white" style="background-color:#25799E;">


                        <tr>
                            <th class="sticky-col-no" rowspan="3">No</th>
                            <th class="sticky-col-nama" rowspan="3">Nama</th>
                            @if(request('type') == 'mbc')
                                <th rowspan="3">Nominal</th>
                            @endif

                             <th rowspan="3" style="min-width: 160px;">
                                 Status
                                 <div class="mt-2">
                                     <select id="status_filter_header" class="form-select form-select-sm" 
                                         style="font-size: 11px; padding: 3px 6px; background-color: rgba(255,255,255,0.95); color: #333; border: 1px solid rgba(255,255,255,0.5); border-radius: 5px; cursor: pointer; min-width: 130px;"
                                         onchange="filterByStatusHeader(this.value)">
                                         <option value="">🔍 Semua</option>
                                         <option value="cold" {{ request('status') == 'cold' ? 'selected' : '' }}>⚪ Cold</option>
                                         <option value="tertarik" {{ request('status') == 'tertarik' ? 'selected' : '' }}>🟡 Tertarik</option>
                                         @if(!($kelasFilter == 'Start-Up Muslim Indonesia' || $kelasFilter == 'Start-Up Muda Indonesia' || request('type') == 'smi'))
                                         <option value="mau_transfer" {{ request('status') == 'mau_transfer' ? 'selected' : '' }}>🟢 Assesmen</option>
                                         @endif
                                         <option value="sudah_transfer" {{ request('status') == 'sudah_transfer' ? 'selected' : '' }}>🔵 Sudah Transfer</option>
                                         <option value="no" {{ request('status') == 'no' ? 'selected' : '' }}>🔴 No</option>
                                     </select>
                                 </div>
                             </th>

                         {{-- <th rowspan="3">
    {{ $kelasFilter == 'Start-Up Muda Indonesia' ? 'Situasi Anak' : 'Situasi Bisnis' }}
</th> --}}
                            @if(!in_array($userRole, ['reseller', 'chapter', 'agen']))
                                <th rowspan="3">Keterangan</th>
                                <th rowspan="3" style="min-width: 150px;">Tgl Closing</th>
                            @endif
                            <th rowspan="3">KEBUTUHAN</th>

                            {{-- Header grup untuk FU --}}
                            <th colspan="{{ $initialMaxFu * 2 }}" class="text-center" id="fuGroupHeader" style="vertical-align: middle;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span style="font-size: 16px; letter-spacing: 1px;">FOLLOW UP</span>
                                </div>
                            </th>
                            @unless($isSmiClass || request('type') == 'smi')
                             <th rowspan="3">Closing Paket</th>
                            @endunless


                        
                             @if(Auth::user()->email == "mbchamasah@gmail.com" || in_array(strtolower(auth()->user()->role), ['administrator', 'cs-mbc']))
                             <th rowspan="3">Nama CS</th>
                             @endif
                             @if(false) {{-- Komentar Atasan Dihapus --}}
                             <th rowspan="3" style="min-width: 200px;">Komentar Atasan</th>
                             @endif
                            <th rowspan="3">Aksi</th>
                        </tr>
                        <tr>
                            {{-- Header FU 1 - 12 --}}
                            @for ($i = 1; $i <= 12; $i++)
                                <th colspan="2" class="text-center {{ $i > 5 ? 'fu-extra-col fu-col-'.$i : '' }}" {!! $i > $initialMaxFu ? 'style="display:none;"' : '' !!}>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        FU {{ $i }}
                                        @if($i == 5 && $canEdit && strtolower(auth()->user()->role) !== 'cs-mbc')
                                            <button type="button" class="btn btn-warning btn-sm shadow-sm" 
                                                onclick="addFuColumn()" id="btnAddFu" 
                                                title="Tambah Kolom FU"
                                                style="font-size: 10px; padding: 1px 8px; border-radius: 8px; font-weight: 800; border: 2px solid #fff; {{ $initialMaxFu >= 12 ? 'display:none;' : '' }}">
                                                <i class="fas fa-plus-circle"></i> TAMBAH KOLOM
                                            </button>
                                        @endif
                                    </div>
                                </th>
                            @endfor
                        </tr>
                        <tr>
                            {{-- Sub kolom Hasil & Tindak Lanjut --}}
                            @for ($i = 1; $i <= 12; $i++)
                                <th class="{{ $i > 5 ? 'fu-extra-col fu-col-'.$i : '' }}" {!! $i > $initialMaxFu ? 'style="display:none;"' : '' !!}>Hasil FU</th>
                                <th class="{{ $i > 5 ? 'fu-extra-col fu-col-'.$i : '' }}" {!! $i > $initialMaxFu ? 'style="display:none;"' : '' !!}>RTL</th>
                                @endfor
                        </tr>
                    </thead>



                    <tbody>
                        @php $currentMonth = null; @endphp
                        @forelse ($salesplans as $plan)
                         @if(!in_array($userRole, ['reseller', 'chapter']))
                         @if(($isSmiClass || request('type') == 'smi' || request('type') == 'mbc') && $plan->created_at)
                             @php
                                 $planMonth = \Carbon\Carbon::parse($plan->created_at)->locale('id')->isoFormat('MMMM Y');
                             @endphp
                             @if($currentMonth !== $planMonth)
                                 <tr class="table-light">
                                     <td colspan="50" class="fw-bold text-start ps-4 py-2" style="background-color: #e9ecef;">
                                         🗓️ {{ $planMonth }}
                                     </td>
                                 </tr>
                                 @php
                                     $currentMonth = $planMonth;
                                 @endphp
                             @endif
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
                            <td class="text-center sticky-col-no">{{ $loop->iteration }}</td>
                            <td class="sticky-col-nama">
                                <div class="mt-2" style="font-size: 0.85rem;">
                                    @php
                                        $customArr = [];
                                        if ($plan->pesertaSmi) {
                                            for ($i = 1; $i <= 12; $i++) {
                                                $n = $plan->pesertaSmi->{"spp_$i"};
                                                $t = $plan->pesertaSmi->{"tanggal_spp_$i"};
                                                if ($n || $t) {
                                                    $customArr[] = ['month' => $i, 'nominal' => $n, 'tanggal' => $t];
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    <div class="d-flex align-items-center">
                                        <strong class="text-dark">{{ $plan->nama ?? '-' }}</strong>
                                        <i class="fas fa-eye text-info cursor-pointer btn-month-detail-trigger ml-2" 
                                           title="Lihat Detail"
                                           data-name="{{ $plan->nama }}" 
                                           data-custom-payments='@json($customArr)'
                                           data-selection='@json($plan->selected_months ?? [])'
                                           data-tanggal-masuk="{{ $plan->pesertaSmi ? ($plan->pesertaSmi->tanggal_masuk ? \Carbon\Carbon::parse($plan->pesertaSmi->tanggal_masuk)->format('Y-m-d') : '') : '' }}"
                                           data-tanggal-selesai="{{ $plan->pesertaSmi ? ($plan->pesertaSmi->tanggal_selesai ? \Carbon\Carbon::parse($plan->pesertaSmi->tanggal_selesai)->format('Y-m-d') : '') : '' }}"
                                           data-spp-awal="{{ $plan->pesertaSmi ? $plan->pesertaSmi->spp_awal : '' }}"
                                           data-biaya-pendaftaran="{{ $plan->pesertaSmi ? $plan->pesertaSmi->biaya_pendaftaran : '' }}"
                                           data-pembayaran-spp="{{ $plan->pesertaSmi ? $plan->pesertaSmi->pembayaran_spp : '' }}"
                                           data-total-pembayaran="{{ $plan->pesertaSmi ? $plan->pesertaSmi->total_pembayaran : '' }}"
                                           style="font-size: 0.8rem; cursor: pointer;"></i>
                                    </div>

                                    @if($plan->pesertaSmi && $plan->pesertaSmi->trashed())
                                        <div class="mt-1">
                                            <span class="badge" style="font-size: 8px; padding: 2px 6px; border-radius: 4px; background: #fff5f5; color: #e53e3e; border: 1px solid #feb2b2; font-weight: 700; text-transform: uppercase;">
                                                <i class="fas fa-user-minus mr-1"></i> Terhapus dari SMI
                                            </span>
                                        </div>
                                    @endif
                                </div>

                            </td>

                            {{-- NOMINAL --}}
                            @if(request('type') == 'mbc')
                            <td class="text-center fw-bold text-dark">
                                <div class="d-flex align-items-center gap-1 justify-content-center">
                                    <span class="small text-muted">Rp</span>
                                    <span contenteditable="{{ $canEdit ? 'true' : 'false' }}" 
                                          class="{{ $canEdit ? 'editable' : '' }} nominal-editable"
                                          data-id="{{ $plan->id }}"
                                          data-field="nominal">
                                          {{ number_format($plan->nominal ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </td>
                            @endif

                            <td class="text-center">
                                <select class="form-control form-control-sm status-dropdown 
                                  status-{{ $plan->status }}"
                                    data-id="{{ $plan->id }}"
                                    style="min-width: 160px;"
                                    {{ $canEdit ? '' : 'disabled' }}>
                                    <option value="sudah_transfer" {{ $plan->status == 'sudah_transfer' ? 'selected' : '' }}>Sudah Transfer</option>
                                    @if(!($kelasFilter == 'Start-Up Muslim Indonesia' || $kelasFilter == 'Start-Up Muda Indonesia' || request('type') == 'smi'))
                                    <option value="mau_transfer" {{ $plan->status == 'mau_transfer' ? 'selected' : '' }}>Assesmen</option>
                                    @endif
                                    <option value="tertarik" {{ $plan->status == 'tertarik' ? 'selected' : '' }}>Tertarik</option>
                                    <option value="cold" {{ $plan->status == 'cold' ? 'selected' : '' }}>Cold</option>
                                    <option value="no" {{ $plan->status == 'no' ? 'selected' : '' }}>No</option>
                                </select>

                                {{-- Tombol Pilih Bulan (Hanya untuk SMI/SMI type atau Row dengan Kelas SMI) --}}
                                @if($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || (isset($plan->kelas) && str_contains($plan->kelas->nama_kelas, 'Muslim Indonesia')))
                                <div class="month-selection-container mt-1 d-none" 
                                     id="month-container-{{ $plan->id }}">
                                    <button type="button" class="btn btn-xs btn-primary w-100 btn-month-modal-trigger shadowed-btn" 
                                        data-id="{{ $plan->id }}"
                                        data-name="{{ $plan->nama }}"
                                        data-selection='@json($plan->selected_months ?? [])'
                                        data-tanggal-masuk="{{ $plan->pesertaSmi ? ($plan->pesertaSmi->tanggal_masuk ? \Carbon\Carbon::parse($plan->pesertaSmi->tanggal_masuk)->format('Y-m-d') : '') : '' }}"
                                        data-tanggal-selesai="{{ $plan->pesertaSmi ? ($plan->pesertaSmi->tanggal_selesai ? \Carbon\Carbon::parse($plan->pesertaSmi->tanggal_selesai)->format('Y-m-d') : '') : '' }}"
                                        data-spp-awal="{{ $plan->pesertaSmi ? $plan->pesertaSmi->spp_awal : '' }}"
                                        data-biaya-pendaftaran="{{ $plan->pesertaSmi ? $plan->pesertaSmi->biaya_pendaftaran : '' }}"
                                        data-pembayaran-spp="{{ $plan->pesertaSmi ? $plan->pesertaSmi->pembayaran_spp : '' }}"
                                        data-total-pembayaran="{{ $plan->pesertaSmi ? $plan->pesertaSmi->total_pembayaran : '' }}"
                                        data-level="{{ $plan->level }}"
                                        data-tanggal-closing="{{ $plan->tanggal_closing ? $plan->tanggal_closing->format('Y-m-d') : '' }}"
                                        style="font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <i class="fas fa-calendar-alt mr-1"></i> SETTING PEMBAYARAN
                                    </button>
                                </div>
                                @endif
                            </td>

                            {{-- <td>{{ $plan->situasi_bisnis ?? '-' }}</td> --}}
                            <!--<td>{{ $plan->kendala ?? '-' }}</td>-->
                            
                            @if(!in_array($userRole, ['reseller', 'chapter']))
                            {{-- KETERANGAN --}}
                            <td contenteditable="{{ $canEdit ? 'true' : 'false' }}" 
                                class="{{ $canEdit ? 'editable' : '' }}"
                                data-id="{{ $plan->id }}"
                                data-field="keterangan"
                                style="max-width: 200px; white-space: normal;">
                                {{ $plan->keterangan ?: '-' }}
                            </td>
                            @endif
                            @if(!in_array($userRole, ['reseller', 'chapter']))
                            {{-- TGL CLOSING --}}
                            <td>
                                <input type="date" 
                                    class="timestamp-input bg-transparent border-0"
                                    value="{{ $plan->tanggal_closing ? \Carbon\Carbon::parse($plan->tanggal_closing)->format('Y-m-d') : '' }}"
                                    data-id="{{ $plan->id }}"
                                    data-field="tanggal_closing"
                                    onchange="handleTimestampChange(this)"
                                    {{ $canEdit ? '' : 'disabled' }}
                                    style="min-width: 140px; font-size: 14px;">
                            </td>
                            @endif

                             {{-- KEBUTUHAN (Editable) --}}
                             <td contenteditable="{{ $canEdit ? 'true' : 'false' }}" 
                                 class="{{ $canEdit ? 'editable' : '' }}"
                                 data-id="{{ $plan->id }}"
                                 data-field="kebutuhan">
                                 {{ $plan->kebutuhan ?? '-' }}
                             </td>


                            @for ($i = 1; $i <= 12; $i++)
                                <td class="{{ $i > 5 ? 'fu-extra-col fu-col-'.$i : '' }}" {!! $i > $initialMaxFu ? 'style="display:none;"' : '' !!}>
                                     <textarea class="fu-textarea"
                                         data-id="{{ $plan->id }}"
                                         data-field="fu{{ $i }}_hasil"
                                         {{ $canEdit ? '' : 'readonly' }}>{{ $plan->{'fu'.$i.'_hasil'} ?? '-' }}</textarea>
                                    
                                     <div class="mt-1">
                                         <input type="datetime-local" class="timestamp-input" 
                                                data-id="{{ $plan->id }}" data-field="fu{{ $i }}_at"
                                                value="{{ $plan->{'fu'.$i.'_at'} ? $plan->{'fu'.$i.'_at'}->format('Y-m-d\TH:i') : '' }}"
                                                onchange="handleTimestampChange(this)"
                                                {{ $canEdit ? '' : 'disabled' }}>
                                     </div>
                                </td>

                                <td class="{{ $i > 5 ? 'fu-extra-col fu-col-'.$i : '' }}" {!! $i > $initialMaxFu ? 'style="display:none;"' : '' !!}>
                                     <textarea class="fu-textarea"
                                         data-id="{{ $plan->id }}"
                                         data-field="fu{{ $i }}_tindak_lanjut"
                                         {{ $canEdit ? '' : 'readonly' }}>{{ $plan->{'fu'.$i.'_tindak_lanjut'} ?? '-' }}</textarea>
                                    
                                     <div class="mt-1">
                                         <input type="datetime-local" class="timestamp-input" 
                                                data-id="{{ $plan->id }}" data-field="fu{{ $i }}_rtl_at"
                                                value="{{ $plan->{'fu'.$i.'_rtl_at'} ? $plan->{'fu'.$i.'_rtl_at'}->format('Y-m-d\TH:i') : '' }}"
                                                onchange="handleTimestampChange(this)"
                                                {{ $canEdit ? '' : 'disabled' }}>
                                     </div>
                                </td>
                                @endfor

                         @unless($isSmiClass || request('type') == 'smi')
                          <td class="text-center">
    @if($canEdit)
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
    @else
        <span style="font-size:18px;">{{ $plan->closing_paket ? '✔' : '☐' }}</span>
    @endif
</td>
@endunless

@php $totalSeluruhCS = 0; @endphp {{-- Dummy for consistency if needed --}}




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
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                    @if($canEdit || $userRole === 'administrator')
                                        <form id="delete-form-{{ $plan->id }}" action="{{ route('admin.salesplan.destroy', $plan->id) }}" method="POST" class="w-100 m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm w-100 btn-delete-trigger" data-id="{{ $plan->id }}">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-warning btn-sm shadow-sm w-100" onclick="resetFu({{ $plan->id }})" title="Refresh seluruh FU">
                                            <i class="fas fa-sync"></i> Refresh FU
                                        </button>
                                    @endif

                                    {{-- Tombol Histori - Selalu muncul untuk Administrator, atau jika ada history --}}
                                    @if(strtolower(auth()->user()->role) === 'administrator' || !empty($plan->fu_history))
                                        <button type="button" class="btn btn-primary btn-sm shadow-sm w-100" 
                                            onclick="showFuHistory({{ $plan->id }}, '{{ base64_encode(json_encode($plan->fu_history ?? [])) }}')" 
                                            id="btn-history-{{ $plan->id }}"
                                            style="background-color: #6610f2; border-color: #520dc2;">
                                            <i class="fas fa-history"></i> Riwayat Histori
                                        </button>
                                    @else
                                        -
                                    @endif

                                    @if($plan->pesertaSmi && $plan->pesertaSmi->trashed())
                                        <form action="{{ route('peserta-smi.restore', $plan->pesertaSmi->id) }}" method="POST" class="w-100 m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100 shadow-sm mt-1" style="font-size: 10px; font-weight: bold; border: 1px solid #fff;">
                                                <i class="fas fa-undo-alt"></i> RESTORE SMI
                                            </button>
                                        </form>
                                    @endif
                                    </div>
                                </td>



                        </tr>
                        @empty
                        <tr>
                            <td colspan="50" class="text-center text-muted">
                                Tidak ada data sales plan ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
            @endif
            
            <!-- Modal Histori FU -->
            <div class="modal fade" id="fuHistoryModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header bg-gradient bg-info text-white rounded-top-4">
                            <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i> Riwayat Histori Follow Up</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4 bg-light" id="historyModalBody" style="max-height: 70vh; overflow-y: auto;">
                            <!-- History content inserted here via JS -->
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
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
@endif

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // 🚀 Export PDF Salesplan
        function exportSalesplanPdf() {
            let params = new URLSearchParams(window.location.search);
            let url = "{{ route('admin.salesplan.export-pdf') }}";
            
            // Pass current filters to PDF
            let queryParts = [];
            if (params.get('kelas')) queryParts.push('kelas=' + encodeURIComponent(params.get('kelas')));
            if (params.get('created_by')) queryParts.push('created_by=' + encodeURIComponent(params.get('created_by')));
            if (params.get('status')) queryParts.push('status=' + encodeURIComponent(params.get('status')));
            if (params.get('bulan')) queryParts.push('bulan=' + encodeURIComponent(params.get('bulan')));
            if (params.get('tahun')) queryParts.push('tahun=' + encodeURIComponent(params.get('tahun')));
            if (params.get('type')) queryParts.push('type=' + encodeURIComponent(params.get('type')));
            
            if (queryParts.length > 0) {
                url += '?' + queryParts.join('&');
            }
            
            window.open(url, '_blank');
        }

        // Simpan nilai awal saat fokus
        $(document).on('focus', '.editable', function() {
            // fu-editable punya handler sendiri
            if ($(this).hasClass('fu-editable')) return;
            
            let currentText = $(this).clone().find('.fu-timestamp, .timestamp-input, input').remove().end().text().trim();
            $(this).data('original', currentText);
            
            // UX: Jika isinya hanya strip '-', kosongkan saat user mau ngetik
            if (currentText === '-') {
                // Hanya ubah text node, jangan hapus child elements
                $(this).contents().filter(function() {
                    return this.nodeType === 3; // TEXT_NODE
                }).each(function() {
                    this.textContent = '';
                });
            }
        });

        $(document).on('blur', '.editable', function() {
            // fu-editable punya handler sendiri
            if ($(this).hasClass('fu-editable')) return;
            
            let id = $(this).data('id');
            let field = $(this).data('field');
            let value = $(this).clone().find('.fu-timestamp, .timestamp-input, input').remove().end().text().trim();
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
                    
                    // Update timestamp display real-time
                    if (res.timestamp && field.startsWith('fu')) {
                        let tsElement = $element.find('.fu-timestamp');
                        if (tsElement.length > 0) {
                            tsElement.html('<i class="fas fa-clock"></i> ' + res.timestamp);
                        } else {
                            $element.append('<div class="fu-timestamp text-muted mt-1" contenteditable="false" style="font-size: 10px; line-height: 1;"><i class="fas fa-clock"></i> ' + res.timestamp + '</div>');
                        }
                    }
                     
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

        // 🚀 Handle Manual Timestamp Change (AJAX tanpa refresh)
        function handleTimestampChange(el) {
            // Prevent any form submission
            if (event) event.preventDefault();
            
            let id = $(el).data('id');
            let field = $(el).data('field');
            let value = $(el).val();

            // Disable input selama proses simpan
            $(el).prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.salesplan.inline-update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: field,
                    value: value
                },
                success: function(res) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Tanggal Terupdate'
                    });
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Gagal update tanggal'
                    });
                },
                complete: function() {
                    $(el).prop('disabled', false);
                }
            });
            
            return false; // Pastikan tidak ada refresh
        }

        function resetFu(id) {
            Swal.fire({
                title: 'Refresh Follow Up?',
                text: "Semua data FU dari FU 1 sampai FU 12 pada baris ini akan dikosongkan dan disimpan ke Riwayat Histori. Apakah Anda yakin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f6c23e',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Refresh!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/admin/salesplan/reset-fu') }}/" + id,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Oops!', res.message, 'info');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Gagal memproses refresh FU.', 'error');
                        }
                    });
                }
            });
        }

        function showFuHistory(id, historyDataBase64) {
            try {
                let jsonString = atob(historyDataBase64);
                let history = JSON.parse(jsonString);
                let html = '';
                if(history.length === 0) {
                    html = '<p class="text-center text-muted">Belum ada riwayat histori FU.</p>';
                } else {
                    history.forEach((hist, index) => {
                        let resetDate = '-';
                        if (hist.reset_at) {
                            let parts = hist.reset_at.substring(0, 10).split('-');
                            resetDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        }
                        html += `<div class="card mb-3 border-0 shadow-sm rounded-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt text-info me-1"></i> (${resetDate}) Reset ke-${index + 1}</h6>
                                <small class="text-muted">Direset oleh: ${hist.reset_by}</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="bg-secondary text-white">
                                            <tr>
                                                <th>Tanggal FU</th>
                                                <th>FU</th>
                                                <th>Hasil</th>
                                                <th>Tanggal RTL</th>
                                                <th>Tindak Lanjut</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                        
                        // Loop through FU_1 to FU_12
                        for(let i=1; i<=12; i++) {
                            let fu = hist.data['FU_' + i];
                            if(fu && (fu.hasil || fu.tindak_lanjut)) {
                                html += `<tr>
                                    <td>${fu.at ? fu.at.substring(0, 10).split('-').reverse().join('-') : '-'}</td>
                                    <td class="fw-bold text-center">FU ${i}</td>
                                    <td>${fu.hasil || '-'}</td>
                                    <td>${fu.rtl_at ? fu.rtl_at.substring(0, 10).split('-').reverse().join('-') : '-'}</td>
                                    <td>${fu.tindak_lanjut || '-'}</td>
                                </tr>`;
                            }
                        }

                        html += `       </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;
                    });
                }

                $('#historyModalBody').html(html);
                $('#fuHistoryModal').modal('show');
            } catch(e) {
                console.error("Invalid history data", e);
                Swal.fire('Error', 'Data histori tidak valid.', 'error');
            }
        }
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
                 // Sync data-level to trigger button
                 let triggerBtn = document.querySelector('#month-container-' + id + ' .btn-month-modal-trigger');
                 if (triggerBtn) {
                     $(triggerBtn).data('level', value);
                     triggerBtn.dataset.level = value;
                 }

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

            // Tampilkan pop up langsung jika status menjadi sudah_transfer
            if (value === 'sudah_transfer') {
                let triggerBtn = document.querySelector('#month-container-' + id + ' .btn-month-modal-trigger');
                if (triggerBtn) {
                    let $t = $(triggerBtn);
                    let modalId = $t.data('id');
                    let level = $(`.level-select[data-id="${modalId}"]`).val() || $t.attr('data-level');
                    let tglClosing = $t.data('tanggal-closing');
                    
                    window.showMonthSelectionModal(
                        modalId,
                        $t.data('name'),
                        $t.data('selection'),
                        $t.data('tanggal-masuk'),
                        $t.data('tanggal-selesai'),
                        $t.data('spp-awal'),
                        $t.data('biaya-pendaftaran'),
                        $t.data('pembayaran-spp'),
                        $t.data('total-pembayaran'),
                        level,
                        tglClosing
                    );
                }
            }

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

                    const isSmi = "{{ $kelasFilter }}" === "Start-Up Muslim Indonesia" || "{{ request('type') }}" === "smi";

                    // Row color always updated below
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


@if(strtolower(Auth::user()->role) !== 'administrator' && !in_array(auth()->user()->name, ['Yasmin', 'Linda', 'Shafa Zahra']))
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge bg-warning text-white p-2 me-2 fs-6" style="font-size: 13px">
            Tertarik: {{ $countTertarik }}
        </span>
        <span class="badge bg-success text-white p-2 me-2 fs-6" style="font-size: 13px">
            Assesmen: {{ $countMauTransfer }}
        </span>
        <span class="badge bg-danger text-white p-2 me-2 fs-6" style="font-size: 13px">
            No: {{ $countNo }}
        </span>
        <span class="badge bg-info text-white p-2 me-2 fs-6" style="font-size: 13px">
            Sudah Transfer: {{ $countSudahTransfer }}
        </span>

        <span class="badge bg-secondary text-white p-2 fs-6"style="font-size: 13px">
            Cold: {{ $countCold }}
        </span>
    </div>
    </div>
</div>
@endif
</div>



{{-- Tabel Daftar Peserta --}}
@if(true) 


@if($pesertaTransfer->isNotEmpty())
<div class="d-flex justify-content-between align-items-center mt-5 mb-3">
    <h4 class="fw-bold m-0">
        Daftar Peserta / {{ $kelasFilter }}
        @if(auth()->check() && strtolower(auth()->user()->role) === 'cs-mbc')
            - {{ auth()->user()->name }}
        @endif
    </h4>

    @if($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || (in_array(auth()->user()->name, ['Yasmin', 'Linda', 'Shafa Zahra']) && (request('type') == 'mbc' || request('type') == 'smi')))
    <div class="d-flex gap-3 align-items-center">
        {{-- Filter Bulan (SMI Bawah) --}}
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-muted">Bulan:</span>
            <select class="form-select form-select-sm" style="width: 140px; border-radius: 8px;" 
                onchange="handleAjaxFilter(this, 'bulan')">
                <option value="">-- Semua Bulan --</option>
                @foreach([
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ] as $num => $name)
                    <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Filter Tahun (SMI Bawah) --}}
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-muted">Tahun:</span>
            <select class="form-select form-select-sm" style="width: 110px; border-radius: 8px;" 
                onchange="handleAjaxFilter(this, 'tahun')">
                <option value="semua" {{ request('tahun') == 'semua' ? 'selected' : '' }}>-- Semua --</option>
                @php $currentYear = date('Y'); @endphp
                @for ($i = $currentYear; $i >= $currentYear - 3; $i--)
                    <option value="{{ $i }}" {{ (request('tahun', $currentYear) == $i && request('tahun') != 'semua') ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    @endif
</div>




<div style="overflow-x: auto; white-space: nowrap;">
    <table id="tabelPeserta" style="border-collapse: collapse; width: 100%; text-align: center; font-family: Arial, sans-serif; font-size: 14px; min-width: 500px;">
        <thead>
            <tr style="background: linear-gradient(to right, #376bb9ff, #1c7f91ff); color: white;">
                <th style="padding: 10px; border: 1px solid #ccc;">No</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nama Peserta</th>
                @if($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc')
                <th style="padding: 10px; border: 1px solid #ccc;">{{ (request('type') == 'smi' || $kelasFilter == 'Start-Up Muslim Indonesia') ? 'Level' : 'Kelas' }}</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nominal</th>
                @endif
                <th style="padding: 10px; border: 1px solid #ccc;">Keterangan</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Tanggal Closing</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nama CS Closing</th>
                <th style="padding: 10px; border: 1px solid #ccc; text-align: center;">Detail</th>
            </tr>
        </thead>
   <tbody>
    @php 
        $totalNominal = 0; 
        $totalSppAwal = 0; 
        $currentClosingMonth = null;
    @endphp
     @forelse ($pesertaTransfer ?? collect() as $i => $p)
        @php
            $closingDate = $p->tanggal_closing 
                ? \Carbon\Carbon::parse($p->tanggal_closing) 
                : (($p->pesertaSmi && $p->pesertaSmi->tanggal_masuk) 
                    ? \Carbon\Carbon::parse($p->pesertaSmi->tanggal_masuk) 
                    : ($p->updated_at ? \Carbon\Carbon::parse($p->updated_at) : null));
            $pMonth = $closingDate ? $closingDate->locale('id')->isoFormat('MMMM Y') : 'Tanpa Tanggal';
            $colspanHeader = ($isCsMbc || $kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc') ? 7 : 5;
        @endphp
        
        @if(empty($bulanFilter) && $currentClosingMonth !== $pMonth)
            <tr class="table-info">
                <td colspan="{{ $colspanHeader }}" class="fw-bold text-start ps-4 py-2" style="background-color: #d1ecf1; border: 1px solid #ccc;">
                    🗓️ Bulan Closing: {{ $pMonth }}
                </td>
            </tr>
            @php $currentClosingMonth = $pMonth; @endphp
        @endif

        <tr style="background-color: #e3f2fd;">
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $loop->iteration }}</td>
            <td style="padding: 8px; border: 1px solid #ccc;">{{ $p->nama }}</td>
            @if($isCsMbc || $kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc')
            <td style="padding: 8px; border: 1px solid #ccc;">{{ (request('type') == 'smi' || $kelasFilter == 'Start-Up Muslim Indonesia') ? ($p->level ?? '-') : ($p->kelas->nama_kelas ?? '-') }}</td>
            <td class="fw-bold" style="padding: 8px; border: 1px solid #ccc;">
                @php
                    $nominalAwal = null;
                    $showTotal = $isCsMbc || strtolower(auth()->user()->role) === 'administrator';
                    if ($p->pesertaSmi) {
                        if ($showTotal) {
                            // CS-MBC & Admin: tampilkan total (pendaftaran + SPP)
                            $nominalAwal = $p->pesertaSmi->total_pembayaran ?? ($p->pesertaSmi->pembayaran_spp ?? $p->pesertaSmi->spp_awal);
                        } else {
                            $nominalAwal = $p->pesertaSmi->pembayaran_spp ?? $p->pesertaSmi->spp_awal;
                        }
                    } else {
                        $nominalAwal = $p->nominal;
                    }
                @endphp
                {{ $nominalAwal ? 'Rp ' . number_format((float)str_replace('.', '', (string)$nominalAwal), 0, ',', '.') : '-' }}
            </td>
            @endif
            {{-- KETERANGAN (Read-only) --}}
            <td style="padding: 8px; border: 1px solid #ccc; max-width: 200px; white-space: normal;">
                {{ $p->keterangan ?: '-' }}
            </td>
            {{-- TGL CLOSING (Read-only Display) --}}
            <td style="padding: 8px; border: 1px solid #ccc; width: 150px; text-align: center;">
                {{ $closingDate ? $closingDate->format('d/m/Y') : '-' }}
            </td>
            <td style="padding: 8px; border: 1px solid #ccc;">
                {{ \App\Models\User::find($p->created_by)->name ?? '-' }}
            </td>
            <td style="padding: 8px; border: 1px solid #ccc; text-align: center;">
                @php
                    $customArr2 = [];
                    if ($p->pesertaSmi) {
                        for ($i = 1; $i <= 12; $i++) {
                            $n = $p->pesertaSmi->{"spp_$i"};
                            $t = $p->pesertaSmi->{"tanggal_spp_$i"};
                            if ($n || $t) {
                                $customArr2[] = ['month' => $i, 'nominal' => $n, 'tanggal' => $t];
                            }
                        }
                    }
                @endphp
                <button type="button" class="btn btn-sm btn-info btn-month-detail-trigger"
                    data-name="{{ $p->nama }}"
                    data-custom-payments='@json($customArr2)'
                    data-selection='@json($p->selected_months ?? [])'
                    data-tanggal-masuk="{{ $p->pesertaSmi ? ($p->pesertaSmi->tanggal_masuk ? \Carbon\Carbon::parse($p->pesertaSmi->tanggal_masuk)->format('Y-m-d') : '') : '' }}"
                    data-tanggal-selesai="{{ $p->pesertaSmi ? ($p->pesertaSmi->tanggal_selesai ? \Carbon\Carbon::parse($p->pesertaSmi->tanggal_selesai)->format('Y-m-d') : '') : '' }}"
                    data-spp-awal="{{ $p->pesertaSmi ? $p->pesertaSmi->spp_awal : '' }}"
                    data-biaya-pendaftaran="{{ $p->pesertaSmi ? $p->pesertaSmi->biaya_pendaftaran : '' }}"
                    data-pembayaran-spp="{{ $p->pesertaSmi ? $p->pesertaSmi->pembayaran_spp : '' }}"
                    data-total-pembayaran="{{ $p->pesertaSmi ? $p->pesertaSmi->total_pembayaran : '' }}">
                    <i class="fas fa-eye"></i> Detail
                </button>
            </td>
        </tr>
        @php 
            $totalNominal += $p->nominal; 
            if ($p->pesertaSmi) {
                $showTotal = $isCsMbc || strtolower(auth()->user()->role) === 'administrator';
                if ($showTotal) {
                    // CS-MBC & Admin: total hitung dari total_pembayaran (pendaftaran + SPP)
                    $omsetSpp = $p->pesertaSmi->total_pembayaran ?? ($p->pesertaSmi->pembayaran_spp ?? $p->pesertaSmi->spp_awal);
                } else {
                    $omsetSpp = $p->pesertaSmi->pembayaran_spp ?? $p->pesertaSmi->spp_awal;
                }
                if ($omsetSpp) {
                    $cleanSpp = str_replace('.', '', $omsetSpp);
                    $totalSppAwal += (float) $cleanSpp;
                }
            }
        @endphp
    @empty
        <tr>
            <td colspan="{{ ($isCsMbc || $kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc') ? 7 : 5 }}" style="text-align: center; padding: 15px; color: #999;">
                Prospek belum ada
            </td>
        </tr>
    @endforelse
</tbody>

          <tfoot>
            <tr style="background: #f2f2f2; font-weight: bold; color: #040e0fff;">
                <td colspan="{{ ($isCsMbc || $kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi' || request('type') == 'mbc') ? 3 : 2 }}" style="padding: 10px; border: 1px solid #ccc; text-align: right;">Total Omset</td>
                <td style="padding: 10px; border: 1px solid #ccc;">
                    @if(request('type') == 'smi' || $kelasFilter == 'Start-Up Muslim Indonesia')
                        Rp {{ number_format($totalSppAwal, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    @endif
                </td>
                <td colspan="4" style="border: 1px solid #ccc;"></td>
            </tr>

            <!-- Target Omset -->
            <tr style="background: #d1e7dd; font-weight: bold; color: #0f5132;">
                <td colspan="{{ ($kelasFilter == 'Start-Up Muslim Indonesia' || request('type') == 'smi') ? 2 : 2 }}" style="padding: 10px; border: 1px solid #ccc; text-align: right;">Target Omset</td>
                <td style="padding: 10px; border: 1px solid #ccc;">
                    @php
                        $userRole = strtolower(auth()->user()->role);
                        if ($userRole == 'cs-mbc') {
                            $targetOmsetVal = 50000000;
                        } elseif (!request('created_by')) {
                            $targetOmsetVal = 125000000;
                        } elseif ($kelasFilter == 'Start-Up Muda Indonesia' || $kelasFilter == 'Start-Up Muslim Indonesia') {
                            $targetOmsetVal = isset($targetOmsetSmi) && $targetOmsetSmi > 0 ? $targetOmsetSmi : 50000000;
                        } else {
                            $targetOmsetVal = isset($targetOmsetGlobal) && $targetOmsetGlobal > 0 ? $targetOmsetGlobal : 50000000;
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
@endif

@endif {{-- end hide for administrator on SMI --}}
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

<script>
function filterByStatusHeader(value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set('status', value);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}
</script>

<script>
// 🚀 Manage FU Column Visibility
let currentMaxFu = {{ $initialMaxFu }}; 

function addFuColumn() {
    if (currentMaxFu >= 12) return;
    
    currentMaxFu++;
    
    document.querySelectorAll('.fu-col-' + currentMaxFu).forEach(function(el) {
        el.style.display = '';
    });
    
    let header = document.getElementById('fuGroupHeader');
    if (header) {
        header.setAttribute('colspan', currentMaxFu * 2);
    }
    
    if (currentMaxFu >= 12) {
        document.getElementById('btnAddFu').style.display = 'none';
    }
    
    Toast.fire({
        icon: 'info',
        title: 'FU ' + currentMaxFu + ' ditambahkan'
    });
}

// 🚀 Update FU Results using Textarea
$(document).on('change', '.fu-textarea', function() {
    let id = $(this).data('id');
    let field = $(this).data('field');
    let value = $(this).val().trim();
    let $el = $(this);

    if (value === '') {
        value = '-';
        $el.val('-');
    }

    $.ajax({
        url: "{{ route('admin.salesplan.inline-update') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id,
            field: field,
            value: value
        },
        success: function(res) {
            Toast.fire({
                icon: 'success',
                title: 'Tersimpan'
            });
        },
        error: function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Gagal menyimpan'
            });
        }
    });
});
function saveInline(id, field, value) {
    $.ajax({
        url: "{{ route('admin.salesplan.inline-update') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id,
            field: field,
            value: value
        },
        success: function(res) {
            Toast.fire({
                icon: 'success',
                title: 'Berhasil diperbarui'
            });
            
            // Reload if date changed as it affects grouping
            if (field === 'tanggal_masuk') {
                setTimeout(() => location.reload(), 800);
            }
        },
        error: function(err) {
            console.error(err);
            Toast.fire({
                icon: 'error',
                title: 'Gagal memperbarui data'
            });
        }
    });
}
    const canCheck = @json($canEdit);

    function showTodayTasks(specificDate = null) {
        const modal = $('#todayTasksModal');
        if (!modal.hasClass('show')) {
            modal.modal('show');
        }

        // Determine target date
        let formattedDate = specificDate;
        if (!formattedDate) {
            formattedDate = $('#manualTaskDate').val();
            if (!formattedDate) {
                formattedDate = new Date().toISOString().split('T')[0];
                $('#manualTaskDate').val(formattedDate);
            }
        }

        $('#todayTasksBody').html('<div class="text-center p-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted">Memuat tugas...</p></div>');

        $.get("{{ route('admin.salesplan.tasks-today') }}", { date: formattedDate }, function(data) {
            if (!data || data.length === 0) {
                let dayLabel = formattedDate.split('-').reverse().join('/');
                $('#todayTasksBody').html(`<div class="text-center p-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><p>Tidak ada tindak lanjut yang tertunda untuk ${dayLabel}.</p></div>`);
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 13px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Peserta</th>
                                <th>Kelas</th>
                                <th>FU</th>
                                <th>Tanggal</th>
                                <th>Agenda</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            data.forEach(task => {
                html += `
                    <tr>
                        <td><strong>${task.nama}</strong></td>
                        <td><small class="badge bg-light text-dark border">${task.kelas}</small></td>
                        <td><span class="badge bg-info">FU ${task.fu_index}</span></td>
                        <td><small class="text-muted"><i class="fas fa-clock me-1"></i>${task.tanggal_rtl}</small></td>
                        <td style="white-space: normal; max-width: 250px;">${task.tindak_lanjut}</td>
                        <td class="text-center">
                            ${canCheck ? `
                                <button class="btn btn-sm btn-outline-success btn-task-done" 
                                        data-id="${task.id}" data-field="${task.field}">
                                    <i class="fas fa-check"></i> Checklist
                                </button>
                            ` : `
                                <span class="badge badge-light border text-muted">
                                    <i class="fas fa-clock me-1"></i> Pending
                                </span>
                            `}
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div>';
            $('#todayTasksBody').html(html);
        }).fail(function(xhr) {
            console.error("Task loading error:", xhr.responseText);
            $('#todayTasksBody').html('<div class="alert alert-danger text-center p-4"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><p>Gagal memuat tugas. Silakan pastikan <strong>Database Migration</strong> sudah dijalankan di server helascorp.com.</p></div>');
        });
    }

    function fetchTasksByDate(val) {
        if (!val) return;
        showTodayTasks(val);
    }

    $(document).on('click', '.btn-task-done', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const field = $btn.data('field');
        const $row = $btn.closest('tr');

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('admin.salesplan.inline-update') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                field: field,
                value: 1
            },
            success: function(res) {
                $row.fadeOut(300, function() {
                    $(this).remove();
                    if ($('#todayTasksBody tbody tr').length === 0) {
                        const manualDate = $('#manualTaskDate').val();
                        let label = manualDate ? manualDate.split('-').reverse().join('/') : 'hari ini';
                        $('#todayTasksBody').html(`<div class="text-center p-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success"></i><p>Hebat! Semua tugas untuk ${label} telah selesai.</p></div>`);
                    }
                });
                Toast.fire({ icon: 'success', title: 'Tugas diselesaikan' });
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Checklist');
                Toast.fire({ icon: 'error', title: 'Gagal memperbarui status' });
            }
        });
    });

    // --- Month & Year Selection Logic (REVISED for Multi-Year Support) ---
    let tempModalSelections = {}; // Store selections internally as { year: [months...] }
    const monthsNamesArr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $(document).on('click', '.btn-month-modal-trigger', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let currentSelection = $(this).data('selection');
        let tglMasuk = $(this).data('tanggal-masuk');
        let tglSelesai = $(this).data('tanggal-selesai');
        let sppAwal = $(this).data('spp-awal');
        let biayaPendaftaran = $(this).data('biaya-pendaftaran');
        let pembayaranSpp = $(this).data('pembayaran-spp');
        let totalPembayaran = $(this).data('total-pembayaran');
        let level = $(`.level-select[data-id="${id}"]`).val() || $(this).attr('data-level');
        let tglClosing = $(this).data('tanggal-closing');
        
        window.showMonthSelectionModal(id, name, currentSelection, tglMasuk, tglSelesai, sppAwal, biayaPendaftaran, pembayaranSpp, totalPembayaran, level, tglClosing);
    });

    $(document).on('click', '.btn-month-detail-trigger', function() {
        let name = $(this).data('name');
        let selection = $(this).data('selection');
        let tglMasuk = $(this).data('tanggal-masuk');
        let tglSelesai = $(this).data('tanggal-selesai');
        let sppAwal = $(this).data('spp-awal');
        let biayaPendaftaran = $(this).data('biaya-pendaftaran');
        let pembayaranSpp = $(this).data('pembayaran-spp');
        let totalPembayaran = $(this).data('total-pembayaran');
        let customPayments = $(this).data('custom-payments');
        
        window.showCoveringMonthsDetail(name, selection, tglMasuk, tglSelesai, sppAwal, biayaPendaftaran, pembayaranSpp, totalPembayaran, customPayments);
    });

    function formatCurrencyValue(value) {
        if (!value) return '';
        let num = value.toString().replace(/[^0-9]/g, '');
        if (!num) return '';
        return parseInt(num).toLocaleString('id-ID');
    }

    window.showMonthSelectionModal = function(id, name, currentSelection, tglMasuk, tglSelesai, sppAwal, biayaPendaftaran, pembayaranSpp, totalPembayaran, level, tglClosing) {
        $('#modalPlanId').val(id);
        $('#modalPlanNameDisplay').text(name);
        $('#modalPlanLevelDisplay').text(level || '-');
        $('#modalTanggalClosingHidden').val(tglClosing || '');
        
        // Reset selections
        $('.month-cb').prop('checked', false);
        $('#cb-pay-full').prop('checked', false).trigger('change');
        $('#btn-month-all').prop('checked', false);
        tempModalSelections = {};
        $('#modalTanggalMasuk').val(tglMasuk || tglClosing || ''); // Default to closing date if new
        $('#modalTanggalSelesai').val(tglSelesai || '');
        $('#modalBiayaPendaftaran').val(formatCurrencyValue(biayaPendaftaran || ''));
        $('#modalSppPertama').val(formatCurrencyValue(pembayaranSpp || ''));
        $('#modalSppAwal').val(formatCurrencyValue(totalPembayaran || sppAwal || ''));
        
        // Default to Template method and trigger logic
        $('#method-template').prop('checked', true);
        $('input[name="pay_method"]:checked').trigger('change');
        
        // Trigger automatic Tanggal Selesai if new
        if (!tglSelesai && (tglMasuk || tglClosing)) {
            $('#modalTanggalMasuk').trigger('change');
        }
        
        let firstYear = '';
        if (currentSelection) {
            if (typeof currentSelection === 'string') {
                try { currentSelection = JSON.parse(currentSelection); } catch(e) {}
            }
            // Check if it's new multi-year format or old format
            if (currentSelection.months && currentSelection.years) {
                // Backward compatibility (Old format)
                let y = currentSelection.years[0] || '';
                if (y) {
                    tempModalSelections[y] = currentSelection.months;
                    firstYear = y;
                }
            } else if (typeof currentSelection === 'object' && !Array.isArray(currentSelection)) {
                // New multi-year format: { '2026': [1,2], '2027': [3,4] }
                tempModalSelections = currentSelection;
                let yearsInSelection = Object.keys(tempModalSelections);
                if (yearsInSelection.length > 0) {
                    firstYear = yearsInSelection[0];
                }
            }
        }
        
        // Default to current year or 2026 if nothing selected
        if (!firstYear) {
            let today = new Date();
            firstYear = today.getFullYear();
            if (firstYear < 2026) firstYear = 2026;
        }

        $('#yearSelect').val(firstYear);
        loadMonthsForYear(firstYear);
        
        // Show/hide fields
        $('#dateSelectionFields').toggle(!!firstYear);
        $('#monthSelectionModal').modal('show');
    };

    function loadMonthsForYear(year) {
        $('.month-cb').prop('checked', false);
        if (year && tempModalSelections[year]) {
            tempModalSelections[year].forEach(m => {
                $('#cb-month-' + m).prop('checked', true);
            });
        }
    }

    $(document).on('change', '#yearSelect', function() {
        let year = $(this).val();
        loadMonthsForYear(year);
        $('#dateSelectionFields').toggle(!!year);
    });

    // --- Template vs Custom Logic ---
    $(document).on('change', 'input[name="pay_method"]', function() {
        let method = $(this).val();
        let level = $('#modalPlanLevelDisplay').text().toLowerCase();
        
        if (method === 'template') {
            $('#modalSppPertama').prop('readonly', true).addClass('bg-light');
            $('#modalBiayaPendaftaran').val(formatCurrencyValue('500000'));
            
            if (level.includes('grow up')) {
                $('#modalSppPertama').val(formatCurrencyValue('1500000'));
            } else if (level.includes('start up')) {
                $('#modalSppPertama').val(formatCurrencyValue('1000000'));
            } else {
                $('#modalSppPertama').val(formatCurrencyValue('1000000')); // Default
            }
            $('#customPaymentsContainer').addClass('d-none');
        } else {
            $('#modalSppPertama').prop('readonly', false).removeClass('bg-light');
            // If pay-full is checked, we might want to keep SPP value 0 initially as it covers everything?
            // User requested pendaftaran 500k, so SPP usually would be the bulk.
            // But let's keep it 0 as default for custom.
            $('#modalSppPertama').val('0'); 
            $('#customPaymentsContainer').removeClass('d-none');
        }
        updateModalTotal();
    });

    $(document).on('change', '#cb-pay-full', function() {
        let isChecked = $(this).is(':checked');
        let level = $('#modalPlanLevelDisplay').text().toLowerCase();

        if (isChecked) {
            $('#monthChecklist').hide();
            $('#all-month-wrapper').hide();
            $('#wrapperTanggalSelesai').show();
            
            // Set SPP to 15M for Pay in Full
            $('#modalBiayaPendaftaran').val(formatCurrencyValue('500000'));
            $('#modalSppPertama').val(formatCurrencyValue('15000000'));
            
            calculateEndDate();
        } else {
            $('#monthChecklist').show();
            $('#all-month-wrapper').show();
            $('#wrapperTanggalSelesai').hide();

            // Revert to default based on level
            $('#modalBiayaPendaftaran').val(formatCurrencyValue('500000'));
            if (level.includes('grow up')) {
                $('#modalSppPertama').val(formatCurrencyValue('1500000'));
            } else {
                $('#modalSppPertama').val(formatCurrencyValue('1000000'));
            }
        }
        updateModalTotal();
    });

    function calculateEndDate() {
        let entryVal = $('#modalTanggalMasuk').val();
        if (entryVal && $('#cb-pay-full').is(':checked')) {
            let date = new Date(entryVal);
            // 11 months after start month covers total 12 months
            date.setMonth(date.getMonth() + 11);
            
            let y = date.getFullYear();
            let m = String(date.getMonth() + 1).padStart(2, '0');
            let d = String(date.getDate()).padStart(2, '0');
            $('#modalTanggalSelesai').val(`${y}-${m}-${d}`);
        }
    }

    $(document).on('change', '#modalTanggalMasuk', function() {
        calculateEndDate();
    });

    $(document).on('input', '#modalBiayaPendaftaran, #modalSppPertama', function() {
        updateModalTotal();
    });

    function updateModalTotal() {
        let pendaftaran = parseInt($('#modalBiayaPendaftaran').val().replace(/[^0-9]/g, '')) || 0;
        let spp = parseInt($('#modalSppPertama').val().replace(/[^0-9]/g, '')) || 0;
        $('#modalSppAwal').val(formatCurrencyValue(pendaftaran + spp));
    }

    // Save checkbox changes into temp state immediately
    $(document).on('change', '.month-cb', function() {
        let year = $('#yearSelect').val();
        if (!year) {
            Swal.fire('Oops!', 'Pilih tahun terlebih dahulu.', 'warning');
            $(this).prop('checked', false);
            return;
        }
        
        if (!tempModalSelections[year]) tempModalSelections[year] = [];
        
        let checkedMonths = [];
        $('.month-cb:checked').each(function() {
            checkedMonths.push(parseInt($(this).data('value')));
        });
        tempModalSelections[year] = checkedMonths.sort((a,b) => a-b);
        updateAllCheckState();
    });

    $(document).on('click', '#btn-month-all', function() {
        let isChecked = $(this).is(':checked');
        $('.month-cb').prop('checked', isChecked).trigger('change');
    });

    function updateAllCheckState() {
        let allChecked = $('.month-cb:checked').length === 12;
        $('#btn-month-all').prop('checked', allChecked);
    }

    window.saveSelectedMonths = function() {
        let id = $('#modalPlanId').val();
        let tanggalMasuk = $('#modalTanggalMasuk').val();
        let tanggalSelesai = $('#modalTanggalSelesai').val();
        let pendaftaran = $('#modalBiayaPendaftaran').val().replace(/[^0-9]/g, '');
        let sppPertama = $('#modalSppPertama').val().replace(/[^0-9]/g, '');
        let sppAwal = $('#modalSppAwal').val().replace(/[^0-9]/g, '');
        
        // Filter out empty years
        let finalSelections = {};
        
        if ($('#cb-pay-full').is(':checked')) {
            if (!tanggalMasuk) {
                Swal.fire('Oops!', 'Mohon isi Tanggal Masuk untuk menghitung periode lunas.', 'warning');
                return;
            }
            let dateMasuk = new Date(tanggalMasuk);
            let startYear = dateMasuk.getFullYear();
            let startMonth = dateMasuk.getMonth() + 1;
            
            for (let i = 0; i < 12; i++) {
                let current = new Date(startYear, (startMonth - 1) + i, 1);
                let curY = current.getFullYear();
                let curM = current.getMonth() + 1;
                
                if (!finalSelections[curY]) finalSelections[curY] = [];
                finalSelections[curY].push(curM);
            }
        } else {
            for(let y in tempModalSelections) {
                if (Array.isArray(tempModalSelections[y]) && tempModalSelections[y].length > 0) {
                    finalSelections[y] = tempModalSelections[y];
                }
            }
        }
        
        if (Object.keys(finalSelections).length === 0 && !tanggalMasuk && !tanggalSelesai && !sppAwal) {
             Swal.fire('Perhatian', 'Pilih setidaknya satu bulan bayar atau isi data yang diperlukan.', 'warning');
             return;
        }
        let methodSelection = $('input[name="pay_method"]:checked').val();
        let customPayments = [];
        if (methodSelection === 'custom') {
            $('#customPaymentsList .custom-payment-row').each(function() {
                let date = $(this).find('.cp-date').val();
                let nominal = $(this).find('.cp-nominal').val().replace(/[^0-9]/g, '');
                if (date && nominal) {
                    customPayments.push({ date: date, nominal: nominal });
                }
            });
        }

        $.ajax({
            url: "{{ route('admin.salesplan.update-selected-months') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                selected_months: JSON.stringify(finalSelections),
                tanggal_masuk: tanggalMasuk,
                tanggal_selesai: tanggalSelesai,
                spp_awal: sppAwal,
                biaya_pendaftaran: pendaftaran,
                pembayaran_spp: sppPertama,
                custom_payments: customPayments
            },
            success: function(res) {
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Berhasil Disimpan', 
                    text: 'Jadwal pembayaran diperbarui.', 
                    timer: 1500 
                });
                $('#monthSelectionModal').modal('hide');
                
                // Redirect logic for Reseller/Chapter
                let role = "{{ $userRole }}";
                if (role === 'reseller' || role === 'chapter') {
                    window.location.href = "{{ route('peserta-smi.index') }}";
                } else {
                    location.reload();
                }
            },
            error: function(err) {
                Swal.fire('Error', 'Gagal menyimpan.', 'error');
            }
        });
    };

    window.addCustomPaymentRow = function() {
        let count = $('#customPaymentsList .custom-payment-row').length + 1;
        let html = `
            <div class="custom-payment-row mb-2 border-bottom pb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-weight-bold text-secondary" style="font-size: 0.7rem;">Pembayaran ke-${count + 1}</span>
                    <i class="fas fa-times text-danger" onclick="$(this).closest('.custom-payment-row').remove(); updateCustomPaymentLabels();" style="cursor: pointer;" title="Hapus"></i>
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-1 pr-1">
                        <input type="date" class="form-control form-control-sm cp-date" style="border-radius: 6px;">
                    </div>
                    <div class="col-sm-6 mb-1 pl-1">
                        <input type="text" class="form-control form-control-sm cp-nominal" placeholder="Nominal Rp" style="border-radius: 6px;" oninput="this.value = formatCurrencyValue(this.value)">
                    </div>
                </div>
            </div>
        `;
        $('#customPaymentsList').append(html);
    };

    window.updateCustomPaymentLabels = function() {
        $('#customPaymentsList .custom-payment-row').each(function(index) {
            $(this).find('span').text('Pembayaran ke-' + (index + 2));
        });
    };
    window.showCoveringMonthsDetail = function(name, selection, tglMasuk, tglSelesai, sppAwal, biayaPendaftaran, pembayaranSpp, totalPembayaran, customPayments) {
        if (typeof selection === 'string') {
            try { selection = JSON.parse(selection); } catch(e) {}
        }
        if (typeof customPayments === 'string') {
            try { customPayments = JSON.parse(customPayments); } catch(e) {}
        }
        $('#detailPlanName').text(name);
        let content = "";
        if (tglMasuk || tglSelesai || sppAwal || biayaPendaftaran || pembayaranSpp || totalPembayaran || (customPayments && customPayments.length > 0)) {
            content += "<div class='alert alert-light border mb-3 p-3 shadow-sm' style='border-radius: 12px; background-color: #f8f9fa;'>";
            if (tglMasuk) content += "<div><small class='text-muted text-uppercase fw-bold' style='font-size: 10px; letter-spacing: 0.5px;'>Tgl Masuk Program:</small><br><span class='h6 fw-bold text-info'>" + formatDate(tglMasuk) + "</span></div>";
            if (tglSelesai) content += "<div class='mt-2'><small class='text-muted text-uppercase fw-bold' style='font-size: 10px; letter-spacing: 0.5px;'>Tgl Selesai Program:</small><br><span class='h6 fw-bold text-success'>" + formatDate(tglSelesai) + "</span></div>";
            
            if (biayaPendaftaran) content += "<div class='mt-2'><small class='text-muted text-uppercase fw-bold' style='font-size: 10px; letter-spacing: 0.5px;'>Pendaftaran:</small><br><span class='h6 fw-bold text-dark'>Rp " + formatCurrencyValue(biayaPendaftaran) + "</span></div>";
            if (pembayaranSpp) content += "<div class='mt-2'><small class='text-muted text-uppercase fw-bold' style='font-size: 10px; letter-spacing: 0.5px;'>Pembayaran SPP:</small><br><span class='h6 fw-bold text-dark'>Rp " + formatCurrencyValue(pembayaranSpp) + "</span></div>";
            
            let displayTotal = totalPembayaran || sppAwal;
            if (!displayTotal && (biayaPendaftaran || pembayaranSpp)) {
                let pd = biayaPendaftaran ? parseFloat(biayaPendaftaran.toString().replace(/[^0-9]/g, '')) : 0;
                let sp = pembayaranSpp ? parseFloat(pembayaranSpp.toString().replace(/[^0-9]/g, '')) : 0;
                displayTotal = pd + sp;
            }
            if (displayTotal) content += "<div class='mt-2 pt-2 border-top'><small class='text-muted text-uppercase fw-bold' style='font-size: 10px; letter-spacing: 0.5px;'>Total Pembayaran Pertama:</small><br><span class='h6 fw-bold text-danger'>Rp " + formatCurrencyValue(displayTotal) + "</span></div>";
            content += "</div>";
        }
        
        if (selection) {
            let hasAtLeastOneYear = false;
            // Check if it's old format or new
            if (selection.years && selection.months) {
                // Old format fallback with .years and .months
                let y = selection.years[0];
                if (y) {
                    content += "<div class='mt-3 mb-2 shadow-sm p-2 rounded bg-info text-white text-center fw-bold' style='font-size: 12px;'>PERIODE " + y + "</div>";
                    content += "<div class='d-flex flex-wrap gap-1'>";
                    if (Array.isArray(selection.months)) {
                        selection.months.sort((a,b) => a-b).forEach(m => {
                            content += "<span class='badge bg-light text-dark border p-2 m-1' style='font-size: 11px;'>" + monthsNamesArr[m-1] + "</span>";
                        });
                    }
                    content += "</div>";
                    hasAtLeastOneYear = true;
                }
            } else if (Array.isArray(selection)) {
                // Extreme legacy format: pure array of months, eg: [1, 2, 3] or ["1", "2"]
                let defaultYear = new Date().getFullYear();
                if (defaultYear < 2026) defaultYear = 2026;
                if (selection.length > 0) {
                    content += "<div class='mt-3 mb-2 shadow-sm p-2 rounded bg-info text-white text-center fw-bold' style='font-size: 12px;'>PERIODE " + defaultYear + "</div>";
                    content += "<div class='d-flex flex-wrap gap-1 mb-3'>";
                    selection.sort((a,b) => a-b).forEach(m => {
                        content += "<span class='badge bg-light text-dark border p-2 m-1' style='font-size: 11px;'>" + monthsNamesArr[m-1] + "</span>";
                    });
                    content += "</div>";
                    hasAtLeastOneYear = true;
                }
            } else {
                // New format: selection is { '2026': [1,2], '2027': [3,4] }
                let sortedYears = Object.keys(selection).sort();
                sortedYears.forEach(year => {
                    let months = selection[year];
                    if (typeof months === 'string') {
                        try { months = JSON.parse(months); } catch(e) {}
                    }
                    if (Array.isArray(months) && months.length > 0) {
                        content += "<div class='mt-3 mb-2 shadow-sm p-2 rounded bg-info text-white text-center fw-bold' style='font-size: 12px;'>TAHUN " + year + "</div>";
                        content += "<div class='d-flex flex-wrap gap-1 mb-3'>";
                        months.sort((a,b) => a-b).forEach(m => {
                            content += "<span class='badge bg-light text-dark border p-2 m-1' style='font-size: 11px;'>" + monthsNamesArr[m-1] + "</span>";
                        });
                        content += "</div>";
                        hasAtLeastOneYear = true;
                    }

                });
            }
            
            if (!hasAtLeastOneYear && !tglMasuk && !tglSelesai && (!customPayments || customPayments.length === 0)) {
                content = "<div class='text-center p-4 text-muted font-italic'><i class='fas fa-calendar-times mb-2 fa-2x'></i><br>Belum ada data.</div>";
            }
        }
        
        if (customPayments && customPayments.length > 0) {
            content += "<div class='mt-3 bg-light p-3 shadow-sm' style='border-radius: 12px; border: 1px solid #17a2b8;'>";
            content += "<h6 class='font-weight-bold text-info mb-3 text-uppercase' style='font-size: 0.8rem;'><i class='fas fa-list-ol me-1'></i> Pembayaran Selanjutnya</h6>";
            customPayments.forEach((p, index) => {
                let formattedDate = p.tanggal ? formatDate(p.tanggal) : '-';
                let formattedNominal = p.nominal ? "Rp " + formatCurrencyValue(p.nominal) : 'Rp 0';
                content += "<div class='d-flex justify-content-between align-items-center mb-2 pb-2' style='" + (index < customPayments.length - 1 ? "border-bottom: 1px dashed #bee5eb;" : "") + "'>";
                content += "<div><div style='font-size: 0.75rem;' class='font-weight-bold text-dark'>Pembayaran ke-" + (index + 2) + "</div>";
                content += "<div style='font-size: 0.7rem;' class='text-secondary'><i class='fas fa-calendar-day text-info mr-1'></i> Jatuh Tempo: " + formattedDate + "</div></div>";
                content += "<div class='h6 mb-0 font-weight-bold text-success'>" + formattedNominal + "</div>";
                content += "</div>";
            });
            content += "</div>";
        }
        
        $('#detailMonthsContent').html(content);
        $('#monthDetailModal').modal('show');
    };

    function formatDate(dateStr) {
        if (!dateStr) return "-";
        try {
            let pts = dateStr.split('-');
            if (pts.length === 3) return pts[2] + "/" + pts[1] + "/" + pts[0];
        } catch(e) {}
        return dateStr;
    }
</script>

{{-- Modal Daftar Tugas Hari Ini (Tindak Lanjut) --}}
<div class="modal fade" id="todayTasksModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-warning text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title fw-bold m-0"><i class="fas fa-calendar-check me-2"></i> To Do List</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-4 bg-white border-bottom shadow-sm">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <label for="manualTaskDate" class="fw-bold text-muted text-uppercase mb-0" style="font-size: 0.85rem; letter-spacing: 1px;">
                            <i class="fas fa-calendar-alt text-warning me-1"></i> Silakan Pilih Tanggal:
                        </label>
                        <div class="position-relative">
                            <input type="date" id="manualTaskDate" class="form-control shadow-sm border-warning" 
                                   value="{{ date('Y-m-d') }}"
                                   style="width: 200px; border-radius: 12px; font-weight: 600; color: #444;" 
                                   onchange="fetchTasksByDate(this.value)">
                        </div>
                    </div>
                </div>
                <div id="todayTasksBody" class="p-3">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Pilih Bulan --}}
<div class="modal fade" id="monthSelectionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold m-0"><i class="fas fa-wallet me-2"></i> Setting Pembayaran</h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                {{-- Nama dan Status Section --}}
                <div class="row mb-3 pb-3 border-bottom">
                    <div class="col-12">
                        <label class="font-weight-bold d-block mb-0 text-muted" style="font-size: 0.7rem;">NAMA PESERTA</label>
                        <span id="modalPlanNameDisplay" class="h6 fw-bold text-dark">-</span>
                        <span id="modalPlanLevelDisplay" class="d-none"></span>
                    </div>
                </div>

                {{-- Opsi Pembayaran --}}
                <div class="mb-3">
                    <label class="font-weight-bold d-block mb-2 text-uppercase text-secondary" style="font-size: 0.75rem;">Metode Pembayaran</label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-info btn-sm active flex-fill">
                            <input type="radio" name="pay_method" id="method-template" value="template" checked> Pembayaran Template
                        </label>
                        <label class="btn btn-outline-info btn-sm flex-fill">
                            <input type="radio" name="pay_method" id="method-custom" value="custom"> Pembayaran Custom
                        </label>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-4" style="border: 1px solid #e0e0e0;">
                    <h6 class="font-weight-bold text-info mb-3" style="font-size: 0.85rem;"><i class="fas fa-money-bill-wave me-1"></i> Detail Pembayaran Pertama</h6>
                    <div class="row">
                        <div class="col-sm-4 mb-2">
                            <label class="font-weight-bold d-block mb-1 text-uppercase text-secondary" style="font-size: 0.7rem;">Pendaftaran</label>
                            <input type="text" id="modalBiayaPendaftaran" class="form-control form-control-sm currency-input" style="border-radius: 8px;" placeholder="0">
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="font-weight-bold d-block mb-1 text-uppercase text-secondary" style="font-size: 0.7rem;">Pembayaran SPP</label>
                            <input type="text" id="modalSppPertama" class="form-control form-control-sm currency-input" style="border-radius: 8px;" placeholder="0">
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label class="font-weight-bold d-block mb-1 text-uppercase text-danger" style="font-size: 0.7rem;">Total Saja</label>
                            <input type="text" id="modalSppAwal" class="form-control form-control-sm bg-white fw-bold text-danger" style="border-radius: 8px; border-color: #ffcccc;" readonly>
                        </div>
                    </div>
                </div>

                <p class="text-muted mb-2">Pilih periode yang dichecklist untuk: <strong id="modalPlanName" class="text-info d-none"></strong></p>
                <input type="hidden" id="modalPlanId">
                <input type="hidden" id="modalTanggalClosingHidden">
                <div class="row">
                    <div class="col-md-6 border-right">
                        <div class="d-flex align-items-center mb-2" style="gap: 15px;">
                            <label class="font-weight-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Bulan</label>
                            <div class="custom-control custom-checkbox" id="all-month-wrapper">
                                <input type="checkbox" class="custom-control-input" id="btn-month-all">
                                <label class="custom-control-label font-weight-bold text-info" for="btn-month-all" style="font-size: 0.7rem;">ALL</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input text-success" id="cb-pay-full">
                                <label class="custom-control-label font-weight-bold text-success" for="cb-pay-full" style="font-size: 0.7rem;">Bayar Lunas Langsung</label>
                            </div>
                        </div>
                        <div class="text-muted italic mb-2" style="font-size: 0.65rem;">(Klik ALL jika langsung membayar lunas)</div>
                        <div id="monthChecklist" style="max-height: 250px; overflow-y: auto;">
                            @php
                                $monthsNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            @endphp
                            @foreach($monthsNames as $index => $month)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input month-cb" id="cb-month-{{ $index + 1 }}" data-value="{{ $index + 1 }}">
                                    <label class="custom-control-label font-weight-normal" for="cb-month-{{ $index + 1 }}">{{ $month }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6 pl-3">
                        <label class="font-weight-bold d-block mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Tahun</label>
                        <select id="yearSelect" class="form-control form-control-sm mb-3" style="border-radius: 8px;">
                            <option value="">-- Pilih Tahun --</option>
                            @for($y = 2026; $y <= 2030; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                        
                        <div id="dateSelectionFields" class="mt-3">
                            <div class="mb-3">
                                <label class="font-weight-bold d-block mb-1 text-uppercase text-info" style="font-size: 0.7rem;">Tanggal Masuk</label>
                                <input type="date" id="modalTanggalMasuk" class="form-control form-control-sm" style="border-radius: 8px;">
                            </div>
                            
                            <div class="mb-2" id="wrapperTanggalSelesai" style="display: none;">
                                <label class="font-weight-bold d-block mb-1 text-uppercase text-info" style="font-size: 0.7rem;">Tanggal Selesai</label>
                                <input type="date" id="modalTanggalSelesai" class="form-control form-control-sm" style="border-radius: 8px;">
                            </div>

                            {{-- Pembayaran Selanjutnya (Khusus Custom) --}}
                            <div id="customPaymentsContainer" class="d-none mt-3 p-3 bg-white rounded border border-info shadow-sm">
                                <label class="font-weight-bold d-block mb-3 text-uppercase text-info border-bottom pb-2" style="font-size: 0.75rem;"><i class="fas fa-plus-circle me-1"></i> Pembayaran Selanjutnya</label>
                                <div id="customPaymentsList">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info w-100 mt-2 font-weight-bold" style="border-radius: 8px; border-style: dashed;" onclick="addCustomPaymentRow()">
                                    <i class="fas fa-plus mr-1"></i> Tambah Pembayaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info rounded-pill px-4" onclick="saveSelectedMonths()">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail Bulan --}}
<div class="modal fade" id="monthDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-info text-white py-2" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h6 class="modal-title fw-bold m-0"><i class="fas fa-info-circle me-1"></i> Detail Periode</h6>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3 pb-2 border-bottom">Peserta: <strong id="detailPlanName" class="text-dark"></strong></p>
                <div id="detailMonthsContent"></div>
            </div>
            <div class="modal-footer p-2 text-center" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-sm btn-secondary w-100" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    function calculateTotal() {
        let pendaftaran = document.getElementById('modalBiayaPendaftaran').value.replace(/[^0-9]/g, '');
        let sppPertama = document.getElementById('modalSppPertama').value.replace(/[^0-9]/g, '');
        
        let total = (parseInt(pendaftaran) || 0) + (parseInt(sppPertama) || 0);
        
        if (total > 0) {
            document.getElementById('modalSppAwal').value = total.toLocaleString('id-ID');
        } else {
            document.getElementById('modalSppAwal').value = '';
        }
    }

    $(document).on('input', '.currency-input', function () {
        let val = $(this).val().replace(/[^0-9]/g, '');
        if (val) {
            $(this).val(parseInt(val).toLocaleString('id-ID'));
        } else {
            $(this).val('');
        }
        calculateTotal();
    });
});
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
