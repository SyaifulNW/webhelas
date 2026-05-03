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

    /* 🎨 Tampilan cell reminder */
    .reminder-cell {
        background: linear-gradient(90deg, #e3f2fd, #bbdefb);
        border-radius: 10px;
        padding: 10px 14px;
        font-weight: 600;
        color: #0d47a1;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: pulseGlow 2s infinite ease-in-out;
        transition: transform 0.3s ease;
    }

    /* 🔔 Ikon lonceng bergetar ringan */
    .reminder-icon {
        color: #2196f3;
        animation: ring 2s infinite;
        font-size: 1.3rem;
    }

    @keyframes ring {
        0% { transform: rotate(0); }
        10% { transform: rotate(15deg); }
        20% { transform: rotate(-10deg); }
        30% { transform: rotate(5deg); }
        40% { transform: rotate(-5deg); }
        50%, 100% { transform: rotate(0); }
    }

    /* 🌑 Border tabel lebih jelas */
    .table-bordered, 
    .table-bordered th, 
    .table-bordered td {
        border: 1px solid #000 !important;
    }
    
</style>

<div class="container-fluid px-4">

    @php
        $bulanDipilih = request('bulan', now()->format('Y-m'));
        $bulanParse   = \Carbon\Carbon::parse($bulanDipilih . '-01');
        $namaBulan    = $bulanParse->translatedFormat('F');
        $tahun        = $bulanParse->year;
    @endphp

    {{-- Header Dashboard --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="mb-3"><i class="fas fa-bullhorn me-2 text-primary"></i>Dashboard Advertising - {{ $userName }}</h3>
                    <form method="GET" action="{{ route('advertising') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="bulan" class="form-label fw-semibold">
                                📅 Pilih Bulan:
                            </label>
                            <input type="month" id="bulan" name="bulan" class="form-control" value="{{ $bulanDipilih }}">
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== CARD STATISTIK ================== --}}
    <div class="row g-4 mb-4">
        {{-- Card ROAS --}}
        @if(!$isEkoSulis)
        <div class="col-12 col-md-3">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-primary text-white fw-bold py-2">
                    <i class="fas fa-chart-line me-2"></i> ROAS
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-primary mb-2" style="font-size: 2.5rem;">{{ $roas }}X</h2>
                    <p class="text-muted mb-1">Target: 10X</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-primary fw-bold text-white" role="progressbar" style="width: {{ min(($roas / 10) * 100, 100) }}%">
                            {{ number_format(($roas / 10) * 100, 0) }}%
                        </div>
                    </div>
                    <small class="text-muted">Omset: Rp {{ number_format($totalOmset, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        @endif

        {{-- Card Leads MBC --}}
        <div class="col-12 {{ $isEkoSulis ? 'col-md-4' : 'col-md-3' }}">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-success text-white fw-bold py-2">
                    <i class="fas fa-users me-2"></i> LEADS MBC
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-success mb-2" style="font-size: 2.5rem;">{{ $leadsMBC }}</h2>
                    <p class="text-muted mb-1">Target: 300/bulan</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-success fw-bold text-white" role="progressbar" style="width: {{ min(($leadsMBC / 300) * 100, 100) }}%">
                            {{ number_format(($leadsMBC / 300) * 100, 0) }}%
                        </div>
                    </div>
                    <span class="badge bg-success text-white px-3 py-2">Periode: {{ $namaBulan }} {{ $tahun }}</span>
                </div>
            </div>
        </div>

        {{-- Card Leads SMI --}}
        <div class="col-12 {{ $isEkoSulis ? 'col-md-4' : 'col-md-3' }}">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-warning text-dark fw-bold py-2">
                    <i class="fas fa-user-graduate me-2"></i> LEADS SMI
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-warning mb-2" style="font-size: 2.5rem;">{{ $leadsSMI }}</h2>
                    <p class="text-muted mb-1">Target: 200/bulan</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-warning fw-bold text-dark" role="progressbar" style="width: {{ min(($leadsSMI / 100) * 100, 100) }}%">
                            {{ number_format(($leadsSMI / 100) * 100, 0) }}%
                        </div>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2">Periode: {{ $namaBulan }} {{ $tahun }}</span>
                </div>
            </div>
        </div>

        {{-- Card Penilaian Atasan --}}
        <div class="col-12 {{ $isEkoSulis ? 'col-md-4' : 'col-md-3' }}">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-info text-white fw-bold py-2">
                    <i class="fas fa-star me-2"></i> PENILAIAN ATASAN
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-info mb-2" style="font-size: 2.5rem;">{{ $penilaianAtasan }}%</h2>
                    <p class="text-muted mb-1">Input Oleh Atasan</p>
                    <div class="progress mb-2" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-info fw-bold text-white" role="progressbar" style="width: {{ $penilaianAtasan }}%">
                            {{ $penilaianAtasan }}%
                        </div>
                    </div>
                    <span class="badge bg-info text-white px-3 py-2">Bobot: 10%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================== TABEL KPI ADVERTISING ================== --}}
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-gradient text-white text-center fw-bold fs-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            📊 PENILAIAN KINERJA ADVERTISING - {{ strtoupper($namaBulan) }} {{ $tahun }}
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
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpiData as $item)
                        <tr>
                            <td class="fw-bold text-dark">{{ $item['no'] }}</td>
                            <td class="fw-bold text-start text-dark">{{ $item['aspek'] }}</td>
                            <td class="text-dark">{{ $item['indikator'] }}</td>
                            <td class="fw-bold text-dark">{{ $item['bobot'] }}</td>
                            <td class="fw-bold text-dark">{{ $item['pencapaian'] }}</td>
                            <td class="fw-bold text-dark">{{ number_format($item['nilai'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-success fw-bold fs-6">
                        <td colspan="5" class="text-end text-dark fw-bold">TOTAL NILAI</td>
                        <td class="text-dark fw-bold">{{ number_format($totalNilai, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>



    {{-- ================== KETERANGAN NILAI (SAMA SEPERTI PENILAIAN/INDEX) ================== --}}
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
                <tr style="background:#009300; color:white;">
                    <td style="padding:14px;">&gt; 100</td>
                    <td style="padding:14px;">Sangat Baik</td>
                </tr>

                <tr style="background:#22b122; color:white;">
                    <td style="padding:14px;">80 – 99</td>
                    <td style="padding:14px;">Baik</td>
                </tr>

                <tr style="background:#ffe75c;">
                    <td style="padding:14px;">60 – 79</td>
                    <td style="padding:14px;">Cukup</td>
                </tr>

                <tr style="background:#ff9933; color:#222;">
                    <td style="padding:14px;">40 – 59</td>
                    <td style="padding:14px;">Pembinaan</td>
                </tr>

                <tr style="background:#e53935; color:white;">
                    <td style="padding:14px;">&lt; 40</td>
                    <td style="padding:14px;">Underperformance</td>
                </tr>
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ambil total nilai dari backend
        let totalNilai = {{ $totalNilai ?? 0 }};

        // Elemen target
        const box = document.getElementById("kategoriBox");
        const motivasi = document.getElementById("motivasiBox");

        if (!box || !motivasi) return;

        // =============================
        // SKALA & MOTIVASI
        // =============================
        const kategori = [
            {
                min: 100,
                label: "Sangat Baik",
                bg: "#009300",
                border: "#007a00",
                color: "#ffffff",
                motivasi: [
                    "Luar biasa! Konsistensi kinerjamu sangat menginspirasi!",
                    "Kamu membuktikan bahwa kerja keras selalu membuahkan hasil.",
                    "Pertahankan kualitas terbaik ini, kamu sedang berada di level puncak!",
                    "Sangat mengesankan! Teruskan performa luar biasa ini!",
                    "Kerjamu jernih, rapih, dan sangat optimal. Hebat!",
                    "Ini adalah pencapaian premium! Kamu luar biasa!",
                    "Standar kerjamu sangat tinggi—pertahankan!",
                    "Hasilmu mencerminkan dedikasi yang kuat!",
                    "Kamu berada di jalur yang sangat tepat!",
                    "Performa terbaik! Lanjutkan ritme positif ini!"
                ]
            },
            {
                min: 80,
                label: "Baik",
                bg: "#22b122",
                border: "#1a8a1a",
                color: "#ffffff",
                motivasi: [
                    "Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik.",
                    "Performa stabil, terus tingkatkan ya!",
                    "Kamu sudah berada di jalur yang benar!",
                    "Hasil yang baik! Tingkatkan konsistensi!",
                    "Semangat, kamu hampir mencapai kategori unggul!",
                    "Teruskan kerja positif ini!",
                    "Kamu punya potensi besar untuk naik level!",
                    "Kemajuanmu jelas terlihat!",
                    "Baik! Tapi kamu bisa lebih baik lagi.",
                    "Pertahankan dan tingkatkan!"
                ]
            },
            {
                min: 60,
                label: "Cukup",
                bg: "#ffe75c",
                border: "#e6d053",
                color: "#333333",
                motivasi: [
                    "Cukup baik, tapi masih banyak ruang untuk berkembang.",
                    "Ayo naikkan levelnya, kamu pasti bisa!",
                    "Kerjamu sudah masuk standar, tinggal ditingkatkan saja.",
                    "Jangan puas dulu, masih bisa lebih maksimal!",
                    "Fokus dan perbaikan kecil akan membuatmu naik kelas.",
                    "Ambil satu langkah lebih konsisten.",
                    "Cukup, tapi belum memenuhi potensi terbaikmu.",
                    "Ayo tingkatkan sedikit demi sedikit!",
                    "Masih banyak ruang untuk berkembang.",
                    "Tetap semangat, kamu mampu lebih baik!"
                ]
            },
            {
                min: 40,
                label: "Pembinaan",
                bg: "#ff9933",
                border: "#e68a2e",
                color: "#000000",
                motivasi: [
                    "Jangan menyerah, ini saatnya bangkit!",
                    "Kamu butuh fokus lebih, tapi kamu bisa mengejarnya.",
                    "Evaluasi kembali dan tingkatkan secara bertahap.",
                    "Ayo perbaiki pelan-pelan! Mulai dari hal kecil.",
                    "Konsistensi adalah kunci. Kamu pasti bisa.",
                    "Saatnya memperbaiki strategi kerja.",
                    "Tetap berusaha, jangan putus asa.",
                    "Bangkit! Kamu bisa mengejar ketertinggalan.",
                    "Ayo perbaiki satu per satu, jangan sekaligus.",
                    "Ini bukan akhir, ini awal untuk peningkatan!"
                ]
            },
            {
                min: 0,
                label: "Underperformance",
                bg: "#e53935",
                border: "#c62828",
                color: "#ffffff",
                motivasi: [
                    "Jangan patah semangat, semua orang pernah mulai dari bawah.",
                    "Ini saatnya memperbaiki ritme kerja.",
                    "Kamu mampu! Mulai dari target paling kecil dulu.",
                    "Fokus pada satu peningkatan sederhana setiap hari.",
                    "Gagal itu biasa, bangkit itu luar biasa.",
                    "Perjalanan panjang dimulai dari satu langkah.",
                    "Jangan bandingkan diri dengan orang lain, fokus ke progresmu!",
                    "Setiap hari adalah kesempatan untuk mulai kembali.",
                    "Ayo bangkit! Kamu belum terlambat untuk mengejar.",
                    "Kamu hanya butuh konsistensi kecil yang dilakukan setiap hari."
                ]
            }
        ];

        // =============================
        // Tentukan kategori berdasarkan nilai
        // =============================
        let hasil = kategori.find(k => totalNilai >= k.min);

        // Tampilkan styling & teks
        box.style.background = hasil.bg;
        box.style.borderColor = hasil.border;
        box.style.color = hasil.color;
        box.innerHTML = `${hasil.label} (${totalNilai})`;

        if (hasil.label === "Pembinaan" || hasil.label === "Underperformance") {
            box.classList.add("pulse");
        }

        // Tampilkan motivasi acak sesuai kategori
        motivasi.innerHTML = `
            <p style="padding:12px; border-left:5px solid ${hasil.color}">
                💬 <em>${hasil.motivasi[Math.floor(Math.random() * 10)]}</em>
            </p>
        `;
    });
    </script>
    <br>
    <br>
    
    <h2 style="margin-top:40px; font-weight:bold;">G. HISTORY KINERJA PER BULAN</h2>

    <div style="
        margin-top:20px;
        display:flex;
        gap:10px;
        overflow-x:auto;
        white-space:nowrap;
        padding-bottom:10px;
    ">

        @foreach(range(1,12) as $m)
            @php
                $nilai = $historyNilai[$m] ?? 0;

                // Kategori warna sesuai Keterangan Skala Nilai
                if($nilai > 100){
                    $warna = "#009300"; // Sangat Baik
                } elseif($nilai >= 80){
                    $warna = "#22b122"; // Baik
                } elseif($nilai >= 60){
                    $warna = "#ffe75c"; // Cukup
                } elseif($nilai >= 40){
                    $warna = "#ff9933"; // Pembinaan
                } elseif($nilai > 0){
                    $warna = "#e53935"; // Underperformance
                } else {
                    $warna = "#e5e7eb"; // Belum dinilai (0)
                }
            @endphp

            <div style="
                width:90px;
                padding:8px 10px;
                border:1px solid #e5e7eb;
                border-radius:10px;
                background:#ffffff;
                box-shadow:0 1px 3px rgba(0,0,0,0.05);
                flex:none;
                text-align:center;
            ">
                <div style="font-weight:700; font-size:14px; margin-bottom:6px;">
                    {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                </div>

                <div style="width:100%; height:10px; background:#e5e7eb; border-radius:5px;">
                    <div style="
                        width:100%;
                        height:100%;
                        background:{{ $warna }};
                        border-radius:5px;
                    "></div>
                </div>

                <div style="font-size:13px; margin-top:4px; font-weight:600;">
                    {{ $nilai }}
                </div>
            </div>

        @endforeach

    </div>

    <br>
</div>
    {{-- ================== RUMUS ROAS ================== --}}
    @if(!$isEkoSulis)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="fas fa-calculator me-2"></i> {{ $rumusRoas['label'] }}
        </div>
        <div class="card-body">
            <div class="reminder-cell">
                <i class="fas fa-info-circle reminder-icon"></i>
                <span>{{ $rumusRoas['formula'] }}</span>
            </div>
            <div class="mt-3">
                <p class="mb-1"><strong>Biaya Iklan:</strong> Rp {{ number_format($biayaIklan, 0, ',', '.') }}</p>
                <p class="mb-1"><strong>Total Omset:</strong> Rp {{ number_format($totalOmset, 0, ',', '.') }}</p>
                <p class="mb-0"><strong>ROAS:</strong> {{ $roas }}X</p>
            </div>
        </div>
    </div>
    @endif


@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
