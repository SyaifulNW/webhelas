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

<div class="container-fluid px-4">

    @php
        $bulanDipilih = request('bulan') ?? date('m');
        $tahunDipilih = request('tahun') ?? date('Y');
        $namaBulan = \Carbon\Carbon::createFromFormat('m', $bulanDipilih)->translatedFormat('F');
    @endphp

    {{-- Header Dashboard --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="mb-3"><i class="fas fa-chart-line me-2 text-primary"></i>Penilaian Kinerja Marketing - {{ auth()->user()->name }}</h3>
                    <form method="GET" action="{{ route('marketing.penilaian.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="bulan" class="form-label fw-semibold">📅 Pilih Bulan:</label>
                            <select name="bulan" class="form-select">
                                @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $k => $v)
                                    <option value="{{ $k }}" {{ $bulanDipilih == $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun" class="form-label fw-semibold">📅 Pilih Tahun:</label>
                            <select name="tahun" class="form-select">
                                @for($t = date('Y'); $t >= 2023; $t--)
                                    <option value="{{ $t }}" {{ $tahunDipilih == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-1"></i> Tampilkan
                            </button>
                            <!-- <a href="{{ route('marketing.penilaian.exportPdf', ['bulan' => $bulanDipilih, 'tahun' => $tahunDipilih]) }}" class="btn btn-danger px-4 ms-2">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== CARD STATISTIK (3 KARTU) ================== --}}
    <div class="row g-4 mb-4">
        {{-- Card 1: Leads MBC --}}
        <div class="col-12 col-md-4">
            <div class="card shadow-lg border-0 h-100 card-hover">
                <div class="card-header bg-success text-white fw-bold py-2">
                    <i class="fas fa-users me-2"></i> LEADS MBC
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-success mb-2" style="font-size: 3rem;">{{ $leadsMBC }}</h2>
                    <p class="text-muted mb-1">Target: {{ $targetLeadsMBC }}/bulan</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-success fw-bold text-white" role="progressbar" style="width: {{ $persenLeadsMBC }}%">
                            {{ number_format($persenLeadsMBC, 0) }}%
                        </div>
                    </div>
                    <span class="badge bg-success text-white px-3 py-2">Periode: {{ $namaBulan }} {{ $tahunDipilih }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Leads SMI --}}
        <div class="col-12 col-md-4">
            <div class="card shadow-lg border-0 h-100 card-hover">
                <div class="card-header bg-warning text-white fw-bold py-2" style="color: white !important;">
                    <i class="fas fa-user-graduate me-2"></i> LEADS SMI
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-warning mb-2" style="font-size: 3rem;">{{ $leadsSMI }}</h2>
                    <p class="text-muted mb-1">Target: {{ $targetLeadsSMI }}/bulan</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-warning fw-bold text-white" role="progressbar" style="width: {{ $persenLeadsSMI }}%">
                            {{ number_format($persenLeadsSMI, 0) }}%
                        </div>
                    </div>
                    <span class="badge bg-warning text-white px-3 py-2">Periode: {{ $namaBulan }} {{ $tahunDipilih }}</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Penilaian Atasan --}}
        <div class="col-12 col-md-4">
            <div class="card shadow-lg border-0 h-100 card-hover">
                <div class="card-header bg-info text-white fw-bold py-2">
                    <i class="fas fa-star me-2"></i> PENILAIAN ATASAN
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-info mb-2" style="font-size: 3rem;">{{ $persenManual }}%</h2>
                    <p class="text-muted mb-1">Input Oleh Atasan</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-info fw-bold text-white" role="progressbar" style="width: {{ $persenManual }}%">
                            {{ number_format($persenManual, 0) }}%
                        </div>
                    </div>
                    <span class="badge bg-info text-white px-3 py-2">Bobot: 10%</span>
                </div>
            </div>
        </div>
    </div>


    {{-- ================== TABEL KPI MARKETING ================== --}}
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header text-white text-center fw-bold fs-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            📊 DETAIL PENILAIAN KINERJA - {{ strtoupper($namaBulan) }} {{ $tahunDipilih }}
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped table-hover mb-0 text-center align-middle">
                <thead style="background-color: #FFFF00;">
                    <tr>
                        <th>No</th>
                        <th>Aspek Kinerja</th>
                        <th>Indikator</th>
                        <th>Bobot</th>
                        <th>Pencapaian</th>
                        <th>Nilai (Points)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. Leads MBC --}}
                    <tr>
                        <td class="fw-bold">1</td>
                        <td class="text-start fw-bold">Leads MBC</td>
                        <td>Target {{ $targetLeadsMBC }}/bulan</td>
                        <td class="fw-bold">45%</td>
                        <td class="fw-bold">{{ $leadsMBC }}</td>
                        <td class="fw-bold">{{ $nilaiLeadsMBC }}</td>
                    </tr>
                    
                    {{-- 2. Leads SMI --}}
                    <tr>
                        <td class="fw-bold">2</td>
                        <td class="text-start fw-bold">Leads SMI</td>
                        <td>Target {{ $targetLeadsSMI }}/bulan</td>
                        <td class="fw-bold">45%</td>
                        <td class="fw-bold">{{ $leadsSMI }}</td>
                        <td class="fw-bold">{{ $nilaiLeadsSMI }}</td>
                    </tr>

                    {{-- 3. Penilaian Atasan --}}
                    <tr>
                        <td class="fw-bold">3</td>
                        <td class="text-start fw-bold">Penilaian Atasan</td>
                        <td>Input Oleh Atasan</td>
                        <td class="fw-bold">10%</td>
                        <td class="fw-bold">{{ $persenManual }}%</td>
                        <td class="fw-bold">{{ $nilaiManualPart }}</td>
                    </tr>

                    {{-- TOTAL --}}
                    <tr class="table-success fw-bold fs-5">
                        <td colspan="5" class="text-end text-dark">TOTAL NILAI AKHIR</td>
                        <td class="text-dark">{{ $totalNilai }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================== KETERANGAN SKALA NILAI & HISTORY (REUSED) ================== --}}
    
    <div class="nilai-wrapper" style="
        background:#fff;
        border:1px solid #e3e8f3;
        border-radius:16px;
        padding:25px;
        margin-top:30px;
        box-shadow:0 6px 14px rgba(0,0,0,0.06);
    ">

        <!-- ===== BOX TOTAL NILAI (otomatis berubah warna via JS) ===== -->
        <div id="kategoriBox" style="
            padding:18px;
            border-radius:14px;
            font-size:22px;
            font-weight:800;
            text-align:center;
            margin-bottom:22px;
            background:#f7f9fc;
            border:1px solid #d7dcec;
            color:#333;
            transition:0.3s ease;
        ">
            Belum dihitung
        </div>

        <!-- ===== MOTIVASI ===== -->
        <div id="motivasiBox" style="
            margin-top:10px;
            margin-bottom:25px;
            font-size:17px;
            font-weight:500;
            color:#444;
        "></div>

        <!-- JUDUL -->
        <h4 class="fw-bold mt-3" style="margin-bottom:14px; color:#2b2b2b;"> Keterangan Skala Nilai</h4>

        <!-- ===== TABEL KONVERSI ===== -->
        <table style="
            width:100%;
            border-collapse:collapse;
            overflow:hidden;
            border-radius:12px;
            font-size:16px;
            font-weight:600;
            text-align:center;
            box-shadow:0 3px 10px rgba(0,0,0,0.05);
        ">
            <thead>
                <tr style="background:#2f3b52; color:white;">
                    <th style="padding:12px;">Rentang Nilai</th>
                    <th style="padding:12px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background:#009300; color:white;"><td style="padding:14px;">&gt; 100</td><td style="padding:14px;">Sangat Baik</td></tr>
                <tr style="background:#22b122; color:white;"><td style="padding:14px;">80 – 99</td><td style="padding:14px;">Baik</td></tr>
                <tr style="background:#ffe75c;"><td style="padding:14px;">60 – 79</td><td style="padding:14px;">Cukup</td></tr>
                <tr style="background:#ff9933; color:#222;"><td style="padding:14px;">40 – 59</td><td style="padding:14px;">Pembinaan</td></tr>
                <tr style="background:#e53935; color:white;"><td style="padding:14px;">&lt; 40</td><td style="padding:14px;">Underperformance</td></tr>
            </tbody>
        </table>

    </div>

    <style>
    #kategoriBox.pulse {
        animation: pulseBox 1.2s infinite;
    }

    @keyframes pulseBox {
        0% { transform: scale(1); }
        50% { transform: scale(1.04); }
        100% { transform: scale(1); }
    }
    </style>


    <h2 style="margin-top:40px; font-weight:bold;">G. HISTORY KINERJA PER BULAN</h2>
    <div style="margin-top:20px; display:flex; gap:10px; overflow-x:auto; white-space:nowrap; padding-bottom:10px;">
        @foreach(range(1,12) as $m)
            @php
                $nilai = $historyNilai[$m] ?? 0;
                if($nilai > 100) $warna = "#009300";
                elseif($nilai >= 80) $warna = "#22b122";
                elseif($nilai >= 60) $warna = "#ffe75c";
                elseif($nilai >= 40) $warna = "#ff9933";
                elseif($nilai > 0) $warna = "#e53935";
                else $warna = "#e5e7eb";
            @endphp

            <div style="width:90px; padding:8px 10px; border:1px solid #e5e7eb; border-radius:10px; background:#ffffff; box-shadow:0 1px 3px rgba(0,0,0,0.05); flex:none; text-align:center;">
                <div style="font-weight:700; font-size:14px; margin-bottom:6px;">
                    {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                </div>
                <div style="width:100%; height:10px; background:#e5e7eb; border-radius:5px;">
                    <div style="width:100%; height:100%; background:{{ $warna }}; border-radius:5px;"></div>
                </div>
                <div style="font-size:13px; margin-top:4px; font-weight:600;">
                    {{ $nilai }}
                </div>
            </div>
        @endforeach
    </div>

    <br><br>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let totalNilai = {{ $totalNilai ?? 0 }};
    const box = document.getElementById("kategoriBox");
    const motivasi = document.getElementById("motivasiBox");

    if (!box || !motivasi) return;

    const kategori = [
        { min: 100, label: "Sangat Baik", bg: "#009300", border: "#007a00", color: "#ffffff", motivasi: ["Luar biasa!"] },
        { min: 80, label: "Baik", bg: "#22b122", border: "#1a8a1a", color: "#ffffff", motivasi: ["Kerja bagus!"] },
        { min: 60, label: "Cukup", bg: "#ffe75c", border: "#e6d053", color: "#333333", motivasi: ["Cukup baik."] },
        { min: 40, label: "Pembinaan", bg: "#ff9933", border: "#e68a2e", color: "#000000", motivasi: ["Ayo bangkit!"] },
        { min: 0, label: "Underperformance", bg: "#e53935", border: "#c62828", color: "#ffffff", motivasi: ["Jangan patah semangat."] }
    ];

    let hasil = kategori.find(k => totalNilai >= k.min);

    box.style.background = hasil.bg;
    box.style.borderColor = hasil.border;
    box.style.color = hasil.color;
    box.innerHTML = `${hasil.label} (${totalNilai})`;

    if (hasil.label === "Pembinaan" || hasil.label === "Underperformance") {
        box.classList.add("pulse");
    }

    motivasi.innerHTML = `
        <p style="padding:12px; border-left:5px solid ${hasil.color}">
            💬 <em>${hasil.motivasi[0]}</em>
        </p>
    `;
});
</script>

@endsection
