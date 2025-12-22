@extends('layouts.masteradmin')
@section('content')
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-scroll {
        max-height: calc(100vh - 50px);
        overflow-y: auto;
        position: relative; /* Ensure relative positioning for child absolute/sticky */
    }
    
    thead th {
        position: sticky;
        top: 0;
        background-color: #25799E !important; /* Force background color to avoid transparency */
        color: white;
        z-index: 10;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    

    th {
        font-size: 14px;
        padding: 6px;
        text-align: left;
    }

    td {
        font-size: 14px;
        padding: 6px;
        text-align: left;
        color: #000 !important;
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

    // Hitung total keseluruhan
    $totalSeluruhCS = $salesplans->sum('nominal');
    $totalTargetSemua = $targetOmset * count($groupedByCS);
    $totalKekurangan = max(0, $totalTargetSemua - $totalSeluruhCS);
    $persentaseTotal = $totalTargetSemua > 0 ? round(($totalSeluruhCS / $totalTargetSemua) * 100, 1) : 0;
@endphp

<!-- Filter hanya administrator, Manager area, dan CS SMI dan Agus Setyo -->
@if(auth()->id() == 1 || auth()->id() == 13 || strtolower(auth()->user()->role) == 'cs-smi' || in_array(auth()->user()->name, ['Tursia', 'Latifah']) || auth()->user()->name == 'Agus Setyo')
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

    .table-scroll {
        max-height: calc(100vh - 100px); /* Adjust height slightly to ensure it fits in viewport */
        overflow-y: auto;
        position: relative;
        z-index: 1; /* New Stacking Context */
        border-bottom: 2px solid #25799E;
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

<form method="GET" action="{{ route('admin.salesplan.index') }}" class="filter-container">
@if(auth()->id() == 1 || auth()->id() == 13 || auth()->user()->name == 'Agus Setyo')
{{-- ✅ Filter CS --}}
<div class="filter-group">
    <label for="cs_filter" class="filter-label"><i class="fas fa-user-tie text-primary"></i> CS:</label>
    <select name="created_by" id="cs_filter" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">-- Semua CS --</option>
        @foreach($csList as $cs)
            @if(
                (auth()->id() == 1 && !in_array($cs->name, ['Latifah', 'Tursia'])) ||
                (auth()->id() == 13 && in_array($cs->name, ['Latifah', 'Tursia'])) ||
                (auth()->user()->name == 'Agus Setyo')
            )
                <option value="{{ $cs->id }}" {{ request('created_by') == $cs->id ? 'selected' : '' }}>
                    {{ $cs->name }}
                </option>
            @endif
        @endforeach
    </select>
</div>

{{-- ✅ Filter Kelas --}}
<div class="filter-group">
    <label for="kelas_filter" class="filter-label"><i class="fas fa-chalkboard-teacher text-success"></i> Kelas:</label>
    <select name="kelas" id="kelas_filter" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">-- Semua Kelas --</option>
        @foreach($kelasList as $kelas)
            @if(
                (auth()->id() == 1 && !in_array($kelas->nama_kelas, ['Start-Up Muda Indonesia', 'Sekolah Kaya'])) ||
                (auth()->id() == 13 && $kelas->nama_kelas == 'Start-Up Muda Indonesia')
            )
                <option value="{{ $kelas->nama_kelas }}" {{ request('kelas') == $kelas->nama_kelas ? 'selected' : '' }}>
                    {{ $kelas->nama_kelas }}
                </option>
            @endif
        @endforeach
    </select>
</div>
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



@php
    // Ambil nilai dari filter
    $bulanFilter = request('bulan');
    $kelasFilter = request('kelas');

    // 🔹 Logika Target Omset
    if (!empty($bulanFilter)) {
        // Jika filter bulan aktif → target 50 juta per CS
        $targetOmset = 50000000;
    } elseif (!empty($kelasFilter)) {
        // Jika hanya filter kelas → target 25 juta per CS
        $targetOmset = 25000000;
    } else {
        // Jika tanpa filter apapun → default bisa diset 0 atau 25 juta
        $targetOmset = 25000000;
    }
@endphp

<h4 class="mt-5 mb-4 fw-bold text-center text-dark">🎯 Pencapaian Target Per CS</h4>

<div class="table-responsive">
    <table class="table table-hover table-striped align-middle shadow-sm">
        <thead class="table-primary text-center">
            <tr>
                <th>Nama CS</th>
                <th>Target Omset</th>
                <th>Tercapai</th>
                <th>Kekurangan</th>
                <th>Persentase</th>
                <th>Status</th>
            </tr>
        </thead>
    <tbody>
@foreach ($salesplansByCS as $csId => $items)
    @php
        // Ambil data CS
        $cs = \App\Models\User::find($csId);

        if (!$cs) continue;

        if (strtolower($cs->name) === 'administrator') continue;

        if (auth()->user()->role !== 'administrator' && $cs->id !== auth()->id()) continue;

        // Hitung nominal
        $totalNominal = $items->sum('nominal');

        $targetOmset = 25000000;
        $selisih = max(0, $targetOmset - $totalNominal);
        $tercapai = $totalNominal >= $targetOmset;
        $persentase = $targetOmset > 0 ? round(($totalNominal / $targetOmset) * 100, 1) : 0;
    @endphp

    <tr>
        <td class="fw-semibold">{{ $cs->name }}</td>
        <td class="text-end">Rp {{ number_format($targetOmset, 0, ',', '.') }}</td>
        <td class="text-end text-success fw-semibold">
            Rp {{ number_format($totalNominal, 0, ',', '.') }}
        </td>
        <td class="text-end text-danger">
            Rp {{ number_format($selisih, 0, ',', '.') }}
        </td>
        <td style="min-width: 150px;">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar {{ $tercapai ? 'bg-success' : 'bg-warning' }}"
                    role="progressbar"
                    style="width: {{ min($persentase, 100) }}%;"
                    aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <small class="d-block text-center mt-1 fw-semibold">{{ $persentase }}%</small>
        </td>
        <td class="text-center">
            @if($tercapai)
                <span class="badge bg-success">Tercapai</span>
            @else
                <span class="badge bg-warning text-dark">Belum Tercapai</span>
            @endif
        </td>
    </tr>
@endforeach

</tbody>




        
        
        
        <tfoot class="table-light fw-bold">
            <tr>
                <td class="text-center">TOTAL</td>
                <td class="text-end">
                    Rp {{ number_format($totalTargetSemua, 0, ',', '.') }}
                </td>
                <td class="text-end text-success">
                    Rp {{ number_format($totalSeluruhCS, 0, ',', '.') }}
                </td>
                <td class="text-end text-danger">
                    Rp {{ number_format($totalKekurangan, 0, ',', '.') }}
                </td>
                <td>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar {{ $persentaseTotal >= 100 ? 'bg-success' : 'bg-info' }}"
                             role="progressbar"
                             style="width: {{ min($persentaseTotal, 100) }}%;"
                             aria-valuenow="{{ $persentaseTotal }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="d-block text-center mt-1 fw-semibold">{{ $persentaseTotal }}%</small>
                </td>
            
            </tr>
        </tfoot>
    </table>
</div>



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
                Closing Paket
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



        <form method="GET" class="d-flex">

            <input type="hidden" name="kelas" value="{{ request('kelas') }}">

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
    $('#searchSalesPlan').on('keyup', function() {
        let query = $(this).val().toLowerCase();

        $('table tbody tr').each(function() {
            let nama = $(this).find('td:nth-child(2)').text().toLowerCase(); // kolom Nama
            if (nama.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>


</div>

            <div class="table-responsive table-scroll">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="text-white" style="background-color:#25799E;">


                        <tr>
                            <th rowspan="3" style="top: 0;">No</th>
                            <th rowspan="3" style="top: 0;">Nama</th>
                            <th rowspan="3" style="top: 0;">Sumber Leads</th>
                         <th rowspan="3" style="top: 0;">
    {{ $kelasFilter == 'Start-Up Muda Indonesia' ? 'Situasi Anak' : 'Situasi Bisnis' }}
</th>
                            <th rowspan="3" style="top: 0;">Kendala</th>

                            {{-- Header grup untuk FU --}}
                            <th colspan="10" class="text-center" style="top: 0;">Follow Up</th>

                            <th rowspan="3" style="top: 0;">Potensi</th>
                            <th rowspan="3" style="top: 0;">Closing Paket</th>
                            <th rowspan="5" style="top: 0;">Status</th>
                        
                            @if(Auth::user()->email == "mbchamasah@gmail.com")
                            <th rowspan="3" style="top: 0;">Input Oleh</th>
                            @endif
                            <th rowspan="3" style="top: 0;">Aksi</th>
                        </tr>
                        <tr>
                            {{-- Header FU 1 - 5 --}}
                            @for ($i = 1; $i <= 5; $i++)
                                <th colspan="2" class="text-center" style="top: 36px;">FU {{ $i }}</th>
                                @endfor
                        </tr>
                        <tr>
                            {{-- Sub kolom Hasil & Tindak Lanjut --}}
                            @for ($i = 1; $i <= 5; $i++)
                                <th style="top: 72px;">Hasil</th>
                                <th style="top: 72px;">Tindak Lanjut</th>
                                @endfor
                        </tr>
                    </thead>



                    <tbody>
                        @forelse ($salesplans as $plan)
                        @php
                        $rowColors = [
                        'ok' => 'table-info',
                        'hot' => 'table-success', // hijau
                        'warm' => 'table-warning', // kuning
                        'No' => 'table-danger', // merah
                        'Cold' => 'table-white' // abu
                        ];

                        $statusTexts = [
                        'ok'=> 'Sudah Transfer',
                        'hot' => 'Mau Transfer',
                        'warm' => 'Tertarik',
                        'No' => 'Tidak Transfer',
                        'Cold' => 'Belum Transfer',
                        ];

                        $rowClass = $rowColors[$plan->status] ?? '';
                        $badgeText = $statusTexts[$plan->status] ?? ucfirst($plan->status);
                        @endphp

                        <tr class="{{ $rowClass }}
                            @if($plan->status == 'sudah_transfer') table-info
    @elseif($plan->status == 'mau_transfer') table-success
    @elseif($plan->status == 'tertarik') table-warning
    @elseif($plan->status == 'no') table-danger
    @elseif($plan->status == 'cold') table-secondary
    @endif">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $plan->nama ?? '-' }}</td>
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
                            <td>{{ $plan->situasi_bisnis ?? '-' }}</td>
                            <td>{{ $plan->kendala ?? '-' }}</td>


                            @for ($i = 1; $i <= 5; $i++)
                                <td contenteditable="true" class="editable bg-light"
                                data-id="{{ $plan->id }}"
                                data-field="fu{{ $i }}_hasil">
                                {{ $plan->{'fu'.$i.'_hasil'} ?? '-' }}
                                </td>

                                <td contenteditable="true" class="editable text-dark"
                                    data-id="{{ $plan->id }}"
                                    data-field="fu{{ $i }}_tindak_lanjut">
                                    {{ $plan->{'fu'.$i.'_tindak_lanjut'} ?? '-' }}
                                </td>
                                @endfor

                                <td contenteditable="true" class="editable fw-bold text-dark text-bold"
                                    data-id="{{ $plan->id }}"
                                    data-field="nominal">
                                    {{ number_format($plan->nominal, 0, ',', '.') }}
                                </td>

                         <td class="text-center">
    <button class="btn btn-sm btn-checklist"
        data-id="{{ $plan->id }}"
        data-field="keterangan"
        data-value="{{ $plan->keterangan == 'done' ? 'done' : 'pending' }}"
        style="font-size:18px;">
        @if($plan->keterangan == 'done')
            ✔
        @else
            ☐
        @endif
    </button>
</td>

<style>
.btn-checklist {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    font-weight: bold;
    border: 1px solid #ccc;
}

.btn-checklist.done {
    background: #2ecc71;
    color: white;
    border-color: #27ae60;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-checklist').forEach(btn => {
        btn.addEventListener('click', function() {

            let id = this.dataset.id;
            let field = this.dataset.field;
            let current = this.dataset.value;

            // Toggle value
            let newValue = (current === "done") ? "pending" : "done";

            // Update tampilan checklist
            if (newValue === "done") {
                this.innerHTML = "✔";
                this.classList.add("done");
            } else {
                this.innerHTML = "☐";
                this.classList.remove("done");
            }

            this.dataset.value = newValue;

            // Kirim ke server (controller updateInline)
           fetch("{{ route('admin.salesplan.inline-update') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({
        id: id,
        field: field,
        value: newValue
    })
});

        });
    });
});
</script>

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


                                <style>
                                    /* Style default */
                                    .status-dropdown {
                                        min-width: 160px;
                                        padding: 4px 8px;
                                        font-size: 14px;
                                        font-weight: bold;
                                        color: #fff;
                                        /* teks default putih */
                                    }

                                    /* Warna sesuai status */
                                    .status-sudah_transfer {
                                        background-color: #48e7ecff;
                                        color: #030303ff;
                                    }

                                    /* Hijau */
                                    .status-mau_transfer {
                                        background-color: #1cff07ff;
                                        color: #000;
                                    }

                                    /* Kuning */
                                    .status-tertarik {
                                        background-color: #ffd900ff;
                                        color: #000;
                                    }

                                    /* Biru */
                                    .status-cold {
                                        background-color: #6c757d;
                                    }

                                    /* Abu gelap */
                                    .status-no {
                                        background-color: #d12020ff;
                                        color: #faf3f3ff;
                                    }

                                    /* Abu terang */
                                </style>
<script>
$(document).on('change', '.status-dropdown', function() {

    let id = $(this).data('id');
    let value = $(this).val();
    let dropdown = $(this);

    $.ajax({
        url: "/admin/salesplan/update-status/" + id,   // ✔️ URL benar
        type: "POST",                                  // ✔️ POST bukan PUT
        data: {
            _token: "{{ csrf_token() }}",
            status: value
        },

        success: function(res) {

            // 🔥 Replace CSS class status dropdown
            dropdown.removeClass("status-sudah_transfer status-mau_transfer status-tertarik status-cold status-no")
                    .addClass("status-" + value);

            // 🔥 Replace warna row tabel
            let row = dropdown.closest('tr');
            row.removeClass("table-info table-success table-warning table-danger table-secondary");

            if (value === "sudah_transfer") row.addClass("table-info");
            if (value === "mau_transfer")    row.addClass("table-success");
            if (value === "tertarik")        row.addClass("table-warning");
            if (value === "no")              row.addClass("table-danger");
            if (value === "cold")            row.addClass("table-secondary");

            console.log("Status berhasil diupdate", res);
        },

       
    });

});
</script>



                                <!--Form Hapus-->
                                <td>
                                                        <form id="delete-form-{{ $plan->id }}"
      action="{{ route('admin.salesplan.destroy', $plan->id) }}"
      method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">
        <i class="fas fa-trash"></i> Hapus
    </button>
</form>
                                </td>


                                <!-- Script Hapus -->
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const deleteButtons = document.querySelectorAll('.btn-delete');

                                        deleteButtons.forEach(button => {
                                            button.addEventListener('click', function() {
                                                let id = this.getAttribute('data-id');

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
                                                        document.getElementById('delete-form-' + id).submit();
                                                    }
                                                });
                                            });
                                        });
                                    });
                                </script>

                                @if(Auth::user()->email == "mbchamasah@gmail.com")
                                <td>
                                    @switch($plan->created_by)
                                    @case(1)
                                    Administrator
                                    @break
                                    @case(2)
                                    Linda
                                    @break
                                    @case(3)
                                    Yasmin
                                    @break
                                    @case(4)
                                    Tursia
                                    @break
                                    @case(5)
                                    Livia
                                    @break
                                    @case(6)
                                    Shafa
                                    @break
                                    @default
                                    -
                                    @endswitch
                                </td>
                                @endif
                                </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="22" class="text-center text-muted">
                                Tidak ada data sales plan ditemukan.
                            </td>
                        </tr>

                        @endforelse
                    </tbody>
                </table>

            </div>
            <div class="d-flex justify-content-center mt-3">
    @if(method_exists($salesplans, 'links'))
    {{ $salesplans->links() }}
@endif

            </div>


        </div>
    </div>

    <script>
        $(document).on('blur', '.editable', function() {
            let id = $(this).data('id');
            let field = $(this).data('field');
            let value = $(this).text().trim();

            $.ajax({
                url: "/admin/salesplan/inline-update", // relative URL, auto ikut domain aktif
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    field: field,
                    value: value
                },
                success: function(res) {
                    console.log("✅ Update sukses:", res);
                },
                error: function(xhr, status, error) {
                    console.error("❌ Gagal update:", xhr.responseText);
                    alert("Gagal update data!");
                }
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
                <th style="padding: 10px; border: 1px solid #ccc;">Nama</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nominal</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Nama CS</th>
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
            <td colspan="4" style="text-align: center; padding: 15px; color: #999;">
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
                    Rp 25.000.000
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

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        function adjustStickyHeader() {
            const table = document.querySelector(".table-scroll table");
            const thead = table ? table.querySelector("thead") : null;
            
            if (!thead) return;

            const rows = thead.querySelectorAll("tr");
            if (rows.length < 3) return;

            // Row 0 is the main row ("No", "Nama", "Follow Up"...)
            // The "Follow Up" cell in Row 0 is what we care about for stacking height
            // But we can't easily select "Follow Up" specifically without class. 
            // However, we know "Follow Up" is in the first row. 
            // Let's assume the height of the first row (excluding rowspans) is roughly the height of "Follow Up".
            // Actually, querying the 'th' that has visible content in the row is safer.

            const row0 = rows[0];
            const row1 = rows[1];
            const row2 = rows[2];

            // Get height of first row's content (e.g. "Follow Up" header)
            // We can check the height of a non-rowspan cell in row 0? 
            // "Follow Up" is the 6th cell (index 5) roughly.
            // Let's just use the bounding rect of the "Follow Up" cell.
            // It is the cell with "Follow Up" text.
            let h1 = 0;
            const followUpCell = Array.from(row0.children).find(td => td.innerText.trim() === "Follow Up");
            if (followUpCell) {
                h1 = followUpCell.offsetHeight;
            } else {
                h1 = row0.offsetHeight; // Fallback
            }

            // Get height of second row (FU 1..5)
            let h2 = 0;
            // The cells in row 1 are just the "FU x" headers
            if (row1.children.length > 0) {
                h2 = row1.children[0].offsetHeight;
            } else {
                h2 = row1.offsetHeight;
            }

            // Apply tops
            // Row 0 cells already have top: 0 via inline/css
            
            // Row 1 cells:
            Array.from(row1.children).forEach(th => {
                th.style.top = h1 + "px";
            });

            // Row 2 cells:
            Array.from(row2.children).forEach(th => {
                th.style.top = (h1 + h2) + "px";
            });
        }

        // Run on load and resize
        adjustStickyHeader();
        window.addEventListener("resize", adjustStickyHeader);
        
        // Also run after a short delay in case of font loading
        setTimeout(adjustStickyHeader, 500);
    });
</script>
@endsection
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>