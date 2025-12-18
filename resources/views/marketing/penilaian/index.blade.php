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

<div class="row">

    <!-- Kolom Kiri: Filter & Input Atasan -->
    <div class="col-lg-6 mb-4">

        <!-- Card Filter -->
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
                    
                    <a href="{{ route('gantt.index') }}" class="btn btn-info btn-block w-100 mt-2">
                        <i class="fas fa-project-diagram"></i> Monitoring Gantt Chart
                    </a>
                </form>
            </div>
        </div>

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

    </div>

    <!-- Kolom Kanan: Statistik System -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Statistik Sistem (Otomatis)</h6>
            </div>
            <div class="card-body">
                @php 
                    $colClass = isset($roas) ? 'col-md-6' : 'col-md-4'; 
                @endphp
                
                <div class="row g-3">
                    @if(isset($roas))
                    {{-- Card ROAS (Only for Eko Sulis) --}}
                    <div class="col-12 {{ $colClass }}">
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
                    @endif

                    {{-- Card 1: Leads MBC --}}
                    <div class="col-12 {{ $colClass }}">
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

                    {{-- Card 2: Leads SMI --}}
                    <div class="col-12 {{ $colClass }}">
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

                    {{-- Card 3: Penilaian Atasan --}}
                    <div class="col-12 {{ $colClass }}">
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
                            @if(isset($roas))
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
                                <td>Leads MBC</td>
                                <td>Target {{ $targetLeadsMBC }}/bulan</td>
                                <td>30%</td>
                                <td>{{ $leadsMBC }}</td>
                                <td>{{ $nilaiLeadsMBC }}</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Leads SMI</td>
                                <td>Target {{ $targetLeadsSMI }}/bulan</td>
                                <td>30%</td>
                                <td>{{ $leadsSMI }}</td>
                                <td>{{ $nilaiLeadsSMI }}</td>
                            </tr>
                            @else
                            {{-- FELMI / NISA --}}
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
                                <td>{{ isset($roas) ? 4 : 3 }}</td>
                                <td>Penilaian Atasan</td>
                                <td>Input Oleh Atasan</td>
                                <td>10%</td>
                                <td>{{ $persenManual }}%</td>
                                <td>{{ $nilaiManualPart }}</td>
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

    </div>
</div>

@endsection
