@extends('layouts.masteradmin')

@section('content')

{{-- Font Awesome --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .badge-lg { font-size: 1.1rem; padding: 0.8rem 1.4rem; }
    .card-header { font-size: 1rem; }
    .progress-bar { font-size: 0.9rem; }
    
    /* 🔵 Efek berdenyut lembut (pulse) */
    @keyframes pulseGlow {
        0% {
            box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
            transform: scale(1.03);
        }
        100% {
            box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
            transform: scale(1);
        }
    }

    /* 🌑 Border tabel lebih jelas */
    .table-bordered, 
    .table-bordered th, 
    .table-bordered td {
        border: 1px solid #000 !important;
    }
    
    /* Card Hover Effect */
    .card-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .card-hover:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
</style>

@php
    $isEkoSulis = isset($targetUser) && trim($targetUser->name) === 'Eko Sulis';
    $isFelmi = isset($targetUser) && trim($targetUser->name) === 'Felmi';
    $mainColClass = $isFelmi ? 'col-lg-12' : 'col-lg-6';
@endphp

<div class="row">

    <!-- Kolom Kiri: Filter & Input Atasan -->
    <div class="{{ $mainColClass }} mb-4">

        <!-- Card Filter -->
        @if(!$isFelmi)
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Filter Karyawan & Periode</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route($routeAction ?? 'marketing.penilaian.index') }}">
                    
                    @if(isset($daftarCs) && count($daftarCs) > 0)
                        <div class="form-group mb-3">
                            <label class="font-weight-bold">Pilih CS:</label>
                            <select name="user_id" class="form-control" onchange="this.form.submit()">
                                @foreach($daftarCs as $cs)
                                    <option value="{{ $cs->id }}" {{ (isset($userId) && $userId == $cs->id) ? 'selected' : '' }}>
                                        {{ $cs->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @elseif(isset($userId))
                        <input type="hidden" name="user_id" value="{{ $userId }}">
                         <div class="form-group mb-3">
                            <label class="font-weight-bold">CS:</label>
                            <input type="text" class="form-control" value="{{ $targetUser->name ?? auth()->user()->name }}" readonly disabled>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col">
                            <label class="font-weight-bold">Bulan</label>
                            <select name="bulan" class="form-control">
                                @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $k => $v)
                                    <option value="{{ $k }}" {{ (request('bulan') ?? date('m')) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label class="font-weight-bold">Tahun</label>
                            <select name="tahun" class="form-control">
                                @for($t = date('Y'); $t >= 2023; $t--)
                                    <option value="{{ $t }}" {{ (request('tahun') ?? date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block w-100">
                        <i class="fas fa-search"></i> Tampilkan Data
                    </button>
                    
                    {{-- Gantt Chart & Program Kerja (KPI) --}}
                    <div class="row mt-2">
                        <div class="col-6">
                            <a href="{{ route('gantt.index', ['user_id' => $userId]) }}" class="btn btn-info btn-block shadow-sm">
                                <i class="fas fa-project-diagram"></i> Ganchart
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('programkerja.index', ['user_id' => $userId]) }}" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-tasks"></i> KPI
                            </a>
                        </div>
                    </div>

                    @if(isset($targetUser) && !$isFelmi)
                        @if($isEkoSulis)
                            <a href="{{ route('admin.ads-activity.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'user_id' => $userId]) }}"
                               class="btn btn-danger btn-block w-100 mt-2"
                               target="_blank">
                                <i class="fas fa-file-pdf"></i> Export PDF Daily Activity
                            </a>
                        @else
                            <a href="{{ route('admin.activity-cs.viewPdfBulanan', ['cs_id' => $userId, 'bulan' => $tahun . '-' . $bulan]) }}"
                               class="btn btn-danger btn-block w-100 mt-2"
                               target="_blank">
                                <i class="fas fa-file-pdf"></i> Export PDF Daily Activity
                            </a>
                        @endif
                    @endif

                </form>
            </div>
        </div>
        @endif

        @if($isFelmi)
            <!-- Title & Simple Filter -->
            <div class="text-center mb-4 mt-2">
                <h4 class="font-weight-bold" style="color: #5a5c69;">Penilaian Kinerja (KPI) Marketing Event</h4>
                <form method="GET" action="{{ route('marketing.penilaian.index') }}" class="d-inline-flex align-items-center justify-content-center mt-2">
                    @if(isset($userId))
                        <input type="hidden" name="user_id" value="{{ $userId }}">
                    @endif
                    
                    <select name="bulan" class="form-control mr-2 shadow-sm" style="width: auto; border-radius: 5px; border: 1px solid #d1d3e2;" onchange="this.form.submit()">
                        @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $k => $v)
                            <option value="{{ $k }}" {{ (request('bulan') ?? date('m')) == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>

                    <select name="tahun" class="form-control shadow-sm" style="width: auto; border-radius: 5px; border: 1px solid #d1d3e2;" onchange="this.form.submit()">
                        @for($t = date('Y'); $t >= 2023; $t--)
                            <option value="{{ $t }}" {{ (request('tahun') ?? date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                </form>

                {{-- Buttons for Felmi --}}
                <div class="mt-3 d-flex justify-content-center">
                    <a href="{{ route('gantt.index', ['user_id' => $userId]) }}" class="btn btn-info shadow-sm mr-2">
                        <i class="fas fa-project-diagram mr-1"></i> Ganchart
                    </a>
                    <a href="{{ route('programkerja.index', ['user_id' => $userId]) }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-tasks mr-1"></i> KPI
                    </a>
                </div>
            </div>



            <!-- TABLE KPI (FELMI) -->
            <div class="card shadow mb-4">
                <div class="card-header py-2 text-dark font-weight-bold text-center" style="background-color: #ffff00; font-size: 1.2rem; border: 1px solid #000; border-bottom: none;">
                    Key Performance Indicator (KPI)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 w-100" style="border-color: #000;">
                            <thead style="background-color: #dae8fc;">
                                <tr class="text-center">
                                    <th style="width: 5%; border: 1px solid #000;">NO</th>
                                    <th style="width: 35%; border: 1px solid #000;">INDIKATOR</th>
                                    <th style="width: 15%; border: 1px solid #000;">TARGET</th>
                                    <th style="width: 15%; border: 1px solid #000;">BOBOT</th>
                                    <th style="width: 15%; border: 1px solid #000;">REALISASI</th>
                                    <th style="width: 15%; border: 1px solid #000;">NILAI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($felmiKpi ?? [] as $index => $kpi)
                                <tr class="text-center">
                                    <td class="align-middle" style="border: 1px solid #000;">{{ $index + 1 }}</td>
                                    <td class="text-left font-weight-bold align-middle" style="border: 1px solid #000;">{{ $kpi['nama'] }}</td>
                                    <td class="align-middle" style="border: 1px solid #000;">{{ $kpi['target'] }}</td>
                                    <td class="align-middle" style="border: 1px solid #000;">{{ number_format($kpi['bobot'], 0) }}%</td>
                                    <td class="align-middle" style="border: 1px solid #000;">
                                        {{ is_numeric($kpi['real']) ? number_format($kpi['real'], 0) : $kpi['real'] }}
                                    </td>
                                    <td class="align-middle font-weight-bold" style="border: 1px solid #000; background-color: #f8f9fc;">{{ number_format($kpi['nilai'], 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-dark text-white font-weight-bold">
                                <tr class="text-center">
                                    <td colspan="3" class="text-right align-middle py-2">TOTAL KPI</td>
                                    <td class="align-middle text-warning">100%</td>
                                    <td class="align-middle">—</td>
                                    <td class="align-middle bg-primary text-white" style="font-size: 1.1rem;">{{ number_format($totalFelmiKpiScore ?? 0, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- OVERALL PERFORMANCE SCORE (FELMI) -->
            <div class="card shadow mb-4" style="background-color: #0d1225; border-radius: 8px; border: none;">
                <div class="card-body py-3">
                    <div class="row align-items-center text-center">
                        <div class="col-md-5 text-left pl-4">
                            <h5 class="m-0 font-weight-bold text-white" style="letter-spacing: 2px; font-size: 1.1rem;">
                                OVERALL PERFORMANCE SCORE
                            </h5>
                        </div>
                        <div class="col-md-2">
                            <span class="font-weight-bold" style="color: #f6c23e; font-size: 1.2rem;">100</span>
                        </div>
                        <div class="col-md-2">
                            <span class="text-white" style="font-size: 1.2rem;">—</span>
                        </div>
                        <div class="col-md-3">
                            <div class="d-inline-flex flex-column align-items-center justify-content-center bg-white rounded-circle shadow-sm" style="width: 85px; height: 85px; border: 4px solid #2c5cc5;">
                                <span class="font-weight-bold text-primary" style="font-size: 1.4rem; line-height: 1;">
                                    {{ number_format($overallPerformanceScore ?? 0, 0) }}
                                </span>
                                <small class="text-muted font-weight-bold" style="font-size: 0.6rem; letter-spacing: 0;">TOTAL POINT</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Card Input Penilaian Atasan -->
            @if(isset($routeAction))
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Input Penilaian Atasan</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.penilaian-cs.store') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $userId }}">
                        <input type="hidden" name="bulan" value="{{ request('bulan') ?? date('m') }}">
                        <input type="hidden" name="tahun" value="{{ request('tahun') ?? date('Y') }}">

                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Kerajinan (0-100)</label>
                            <div class="col-sm-8">
                                <input type="number" name="kerajinan" class="form-control" required min="0" max="100" value="{{ $manual->kerajinan ?? 0 }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Kerjasama (0-100)</label>
                            <div class="col-sm-8">
                                <input type="number" name="kerjasama" class="form-control" required min="0" max="100" value="{{ $manual->kerjasama ?? 0 }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Tanggung Jawab (0-100)</label>
                            <div class="col-sm-8">
                                <input type="number" name="tanggung_jawab" class="form-control" required min="0" max="100" value="{{ $manual->tanggung_jawab ?? 0 }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Inisiatif (0-100)</label>
                            <div class="col-sm-8">
                                <input type="number" name="inisiatif" class="form-control" required min="0" max="100" value="{{ $manual->inisiatif ?? 0 }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Komunikasi (0-100)</label>
                            <div class="col-sm-8">
                                <input type="number" name="komunikasi" class="form-control" required min="0" max="100" value="{{ $manual->komunikasi ?? 0 }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="3">{{ $manual->catatan ?? '' }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger btn-block w-100">💾 Simpan Penilaian</button>
                    </form>
                </div>
            </div>
            @endif
        @endif

    </div>

    <!-- Kolom Kanan: Statistik System -->
    <div class="{{ $mainColClass }}">
        @if(!$isFelmi)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Statistik Sistem (Otomatis)</h6>
            </div>
            <div class="card-body">
                @php 
                    $colClass = isset($roas) ? 'col-md-6' : 'col-md-4'; 
                @endphp
                
                <div class="row g-3">
                    @if($targetUser->name === 'Eko Sulis')
                    {{-- CARDS EKO SULIS --}}
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-primary text-white fw-bold py-2">
                                <i class="fas fa-chart-line me-2"></i> ROAS
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-primary mb-1">{{ $roas }}X</h3>
                                <p class="text-muted small mb-1">Target: {{ $targetRoas }}X</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $persenRoas }}%"></div>
                                </div>
                                <span class="badge bg-primary text-white">Score: {{ $nilaiAkhirRoas }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-success text-white fw-bold py-2">
                                <i class="fas fa-ad me-2"></i> LEADS ADS
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-success mb-1">{{ $leadsAds }}</h3>
                                <p class="text-muted small mb-1">Target: {{ $targetLeadsAds }}</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persenLeadsAds }}%"></div>
                                </div>
                                <span class="badge bg-success text-white">Score: {{ $nilaiLeadsAds }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-dark text-white fw-bold py-2">
                                <i class="fas fa-walking me-2"></i> LEADS OFFLINE (FELMI)
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-dark mb-1">{{ $leadsFelmi ?? 0 }}</h3>
                                <p class="text-muted small mb-1">Target: {{ $targetLeadsFelmi ?? 0 }}</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $persenLeadsFelmi ?? 0 }}%"></div>
                                </div>
                                <span class="badge bg-dark text-white">Score: {{ $nilaiLeadsFelmi ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-warning text-white fw-bold py-2">
                                <i class="fas fa-laptop me-2"></i> LEADS ONLINE (NISA)
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-warning mb-1">{{ $leadsNisa ?? 0 }}</h3>
                                <p class="text-muted small mb-1">Target: {{ $targetLeadsNisa ?? 0 }}</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenLeadsNisa ?? 0 }}%"></div>
                                </div>
                                <span class="badge bg-warning text-white">Score: {{ $nilaiLeadsNisa ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    @elseif($targetUser->name === 'Felmi')
                    {{-- CARDS FELMI --}}
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-success text-white fw-bold py-2">
                                <i class="fas fa-users me-2"></i> TOTAL LEADS BARU
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-success mb-1">{{ $leadsFelmiCount ?? 0 }}</h3>
                                <p class="text-muted small mb-1">Target: 100</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persenLeadsFelmi ?? 0 }}%"></div>
                                </div>
                                <span class="badge bg-success text-white">Score: {{ $nilaiLeadsFelmiPart ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-info text-white fw-bold py-2">
                                <i class="fas fa-calendar-check me-2"></i> E-FEST / FORUM
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-info mb-1">{{ $efestCount ?? 0 }}</h3>
                                <p class="text-muted small mb-1">Target: 50</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $persenEfest ?? 0 }}%"></div>
                                </div>
                                <span class="badge bg-info text-white">Score: {{ $nilaiEfest ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-warning text-white fw-bold py-2">
                                <i class="fas fa-store me-2"></i> BISNIS VISIT
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-warning mb-1">{{ $visitCount ?? 0 }}</h3>
                                <p class="text-muted small mb-1">Target: 50</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenVisit ?? 0 }}%"></div>
                                </div>
                                <span class="badge bg-warning text-white">Score: {{ $nilaiVisit ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    @else
                    {{-- DEFAULT CARDS --}}
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-success text-white fw-bold py-2">
                                <i class="fas fa-users me-2"></i> LEADS MBC
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-success mb-1">{{ $leadsMBC }}</h3>
                                <p class="text-muted small mb-1">Target: {{ $targetLeadsMBC }}</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persenLeadsMBC }}%"></div>
                                </div>
                                <span class="badge bg-success text-white">Score: {{ $nilaiLeadsMBC }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-warning text-white fw-bold py-2">
                                <i class="fas fa-user-graduate me-2"></i> LEADS SMI
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-warning mb-1">{{ $leadsSMI }}</h3>
                                <p class="text-muted small mb-1">Target: {{ $targetLeadsSMI }}</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenLeadsSMI }}%"></div>
                                </div>
                                <span class="badge bg-warning text-white">Score: {{ $nilaiLeadsSMI }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- CARD ATASAN (FOR ALL) --}}
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 h-100 card-hover">
                            <div class="card-header bg-info text-white fw-bold py-2">
                                <i class="fas fa-star me-2"></i> ATASAN
                            </div>
                            <div class="card-body text-center p-2">
                                <h3 class="fw-bold text-info mb-1">{{ $persenManual }}%</h3>
                                <p class="text-muted small mb-1">Manual</p>
                                <div class="progress mb-1" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $persenManual }}%"></div>
                                </div>
                                <span class="badge bg-info text-white">Score: {{ $nilaiManualPart }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Card Tabel Penilaian Hasil -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                 <h6 class="m-0 font-weight-bold text-white text-center">PENILAIAN HASIL (MARKETING {{ strtoupper($targetUser->name ?? auth()->user()->name) }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="bg-warning text-dark">
                            <tr>
                                <th>No</th>
                                <th>Aspek Kinerja</th>
                                <th>Indikator</th>
                                <th>Bobot</th>
                                <th>Pencapaian</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($targetUser->name === 'Eko Sulis')
                            {{-- TABEL EKO SULIS --}}
                            <tr>
                                <td>1</td>
                                <td>ROAS</td>
                                <td>Target {{ $targetRoas }}X</td>
                                <td>30%</td>
                                <td>{{ $roas }}X</td>
                                <td>{{ $nilaiAkhirRoas }}</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Jumlah Leads ADS</td>
                                <td>Target {{ $targetLeadsAds }}/bulan</td>
                                <td>20%</td>
                                <td>{{ $leadsAds }}</td>
                                <td>{{ $nilaiLeadsAds }}</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Jumlah Leads Event Offline (Felmi)</td>
                                <td>Target {{ $targetLeadsFelmi }}/bulan</td>
                                <td>20%</td>
                                <td>{{ $leadsFelmi }}</td>
                                <td>{{ $nilaiLeadsFelmi }}</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Jumlah Leads Event Online (Nisa)</td>
                                <td>Target {{ $targetLeadsNisa }}/bulan</td>
                                <td>20%</td>
                                <td>{{ $leadsNisa }}</td>
                                <td>{{ $nilaiLeadsNisa }}</td>
                            </tr>
                            @elseif($targetUser->name === 'Felmi')
                            {{-- TABEL FELMI --}}
                            <tr>
                                <td>1</td>
                                <td>Total Leads Baru/Bulan</td>
                                <td>Target 100</td>
                                <td>40%</td>
                                <td>{{ $leadsFelmiCount }}</td>
                                <td>{{ $nilaiLeadsFelmiPart }}</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Entrepreneur Forum / E-Fest</td>
                                <td>Target 50</td>
                                <td>30%</td>
                                <td>{{ $efestCount }}</td>
                                <td>{{ $nilaiEfest }}</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Bisnis Visit / UpRev</td>
                                <td>Target 50</td>
                                <td>30%</td>
                                <td>{{ $visitCount }}</td>
                                <td>{{ $nilaiVisit }}</td>
                            </tr>
                            @else
                            {{-- DEFAULT --}}
                            <tr>
                                <td>1</td>
                                <td>Leads MBC</td>
                                <td>Target {{ $targetLeadsMBC }}/bulan</td>
                                <td>45%</td>
                                <td>{{ $leadsMBC }}</td>
                                <td>{{ $nilaiLeadsMBC }}</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Leads SMI</td>
                                <td>Target {{ $targetLeadsSMI }}/bulan</td>
                                <td>45%</td>
                                <td>{{ $leadsSMI }}</td>
                                <td>{{ $nilaiLeadsSMI }}</td>
                            </tr>
                            @endif

                            <tr>
                                <td>{{ ($targetUser->name === 'Eko Sulis') ? 5 : (($targetUser->name === 'Felmi') ? 4 : 3) }}</td>
                                <td>Penilaian Atasan</td>
                                <td>Input Oleh Atasan</td>
                                <td>10%</td>
                                <td>{{ $persenManual }}%</td>
                                <td>{{ $nilaiManualPart }}</td>
                            </tr>
                            <tr>
                                <td>{{ ($targetUser->name === 'Eko Sulis') ? 6 : (($targetUser->name === 'Felmi') ? 5 : 4) }}</td>
                                <td>Daily Activity</td>
                                <td>Pencapaian KPI Harian</td>
                                <td>(Ref)</td>
                                <td>{{ number_format($dailyTotalKpi ?? 0, 0) }}%</td>
                                <td>{{ number_format($dailyTotalKpi ?? 0, 0) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                             <tr style="background-color: #d1f7d6;">
                                 <td colspan="5" class="text-right">TOTAL NILAI AKHIR</td>
                                 <td>{{ $totalNilai }}</td>
                             </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer Alert -->
        <div class="card shadow mb-4">
             <div class="card-body {{ ($totalNilai ?? 0) < 70 ? 'bg-danger' : 'bg-success' }} text-white text-center">
                <h3 class="font-weight-bold m-0">{{ ($totalNilai ?? 0) < 70 ? 'Underperformance' : 'Good Performance' }} ({{ $totalNilai ?? 0 }})</h3>
                <p class="m-0 mt-2 font-italic small">
                    @if($totalNilai >= 100)
                        "Luar biasa!"
                    @elseif($totalNilai >= 80)
                        "Kerja bagus!"
                    @elseif($totalNilai >= 60)
                        "Cukup baik."
                    @elseif($totalNilai >= 40)
                        "Ayo bangkit!"
                    @else
                        "Jangan patah semangat."
                    @endif
                </p>
            </div>
        </div>

        <!-- History -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                 <h6 class="m-0 font-weight-bold text-primary">History Kinerja Per Bulan</h6>
            </div>
            <div class="card-body">
                <div style="display:flex; gap:10px; overflow-x:auto; white-space:nowrap; padding-bottom:10px;">
                    @foreach(range(1,12) as $m)
                        @php
                            $nilai = $historyNilai[$m] ?? 0;
                            $warna = $nilai > 100 ? "#009300" : ($nilai >= 80 ? "#22b122" : ($nilai >= 60 ? "#ffe75c" : ($nilai >= 40 ? "#ff9933" : "#e53935")));
                            if($nilai == 0) $warna = "#e5e7eb";
                        @endphp

                        <div style="width:70px; padding:5px; border:1px solid #e5e7eb; border-radius:5px; background:#fff; text-align:center;">
                            <div style="font-weight:700; font-size:12px;">{{ DateTime::createFromFormat('!m', $m)->format('M') }}</div>
                            <div style="height:5px; background:{{ $warna }}; margin:5px 0; border-radius:3px;"></div>
                            <div style="font-size:11px;">{{ $nilai }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
