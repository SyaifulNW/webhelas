@extends('layouts.masteradmin')

@section('content')

<style>
    /* Popup Motivasi */


    @keyframes popIn {
        from { transform: translate(-50%, -40%) scale(0.5); opacity: 0; }
        to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }

    /* Background blur saat popup muncul */
    #popupOverlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        z-index: 9998;
    }

    /* Progress bar */
    .progress {
        height: 22px;
        font-size: 12px;
        font-weight: bold;
    }

    /* Chart Card */
    .chart-container {
        width: 100%;
        height: 320px;
    }
</style>

<div class="container mt-4">

    {{-- ================== JUDUL ================== --}}
    <h3 class="fw-bold mb-3 text-center">Penilaian Hasil CS</h3>
    {{-- ================== FILTER BULAN ================== --}}
<form method="GET" class="mb-3 d-flex justify-content-center" style="gap: 10px;">
    <select name="bulan" class="form-select w-auto" onchange="this.form.submit()">
        @php
            $daftarBulan = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            $bulanDipilih = request('bulan') ?? date('m');
        @endphp

        @foreach ($daftarBulan as $key => $nama)
            <option value="{{ $key }}" {{ $bulanDipilih == $key ? 'selected' : '' }}>
                {{ $nama }}
            </option>
        @endforeach
    </select>

    <select name="tahun" class="form-select w-auto" onchange="this.form.submit()">
        @php
            $tahunSekarang = date('Y');
            $tahunDipilih = request('tahun') ?? $tahunSekarang;
        @endphp

        @for ($t = $tahunSekarang; $t >= 2023; $t--)
            <option value="{{ $t }}" {{ $tahunDipilih == $t ? 'selected' : '' }}>
                {{ $t }}
            </option>
        @endfor
    </select>
</form>


    {{-- ================== PROGRESS BAR TOTAL ================== --}}
    @php
        // Gunakan variable totalNilai dari controller
        $persen = ($totalNilai / 100) * 100;
        $warna = $totalNilai >= 80 ? 'bg-success' : ($totalNilai >= 60 ? 'bg-warning' : 'bg-danger');
    @endphp

    <div class="card border-0 shadow-sm p-3 mb-4">
        <h5 class="fw-bold mb-2">Total Pencapaian: {{ $totalNilai }}/100</h5>

        <div class="progress">
            <div class="progress-bar {{ $warna }}" style="width: {{ $totalNilai }}%;">
                {{ $totalNilai }}%
            </div>
        </div>
    </div>



    {{-- ================== TABEL PENILAIAN ================== --}}
    <div class="card shadow-lg border-0 mt-4">
        <div class="card-header bg-success text-white text-center fw-bold fs-5">
            PENILAIAN HASIL cs
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                <thead class="table-warning text-center">
                    <tr>
                        <th>No</th>
                        <th>Aspek Kinerja</th>
                        <th>Indikator</th>
                        <th>Bobot</th>
                        <th>Pencapaian</th>
                        <th>Nilai</th>
                    </tr>
                </thead>

                <tbody class="text-dark">
                    @php $no = 1; @endphp
                    {{-- 1 --}}
                    <tr>
                        <td class="text-center fw-bold">{{ $no++ }}</td>
                        <td class="fw-bold">Penjualan & Omset</td>
                        <td>Target Rp 50 juta/bulan</td>
                        <td class="text-center fw-bold">40%</td>
                        <td class="fw-bold">Rp {{ number_format($totalOmset) }}</td>
                        <td class="text-center fw-bold">{{ $nilaiOmset }}</td>
                    </tr>

                    {{-- 2 (Closing Paket - Hide for cs-smi) --}}
                    @if(auth()->user()->role !== 'cs-smi')
                    <tr>
                        <td class="text-center fw-bold">{{ $no++ }}</td>
                        <td class="fw-bold">Closing Paket</td>
                        <td>Target 1 closing paket per bulan</td>
                        <td class="text-center fw-bold">20%</td>
                        <td class="fw-bold">{{ $closingPaket }} peserta</td>
                        <td class="text-center fw-bold">{{ $nilaiClosingPaket }}</td>
                    </tr>
                    @endif

                    {{-- 3 --}}
                    <tr>
                        <td class="text-center fw-bold">{{ $no++ }}</td>
                        <td class="fw-bold">Database Baru</td>
                        <td>Target 50 database baru</td>
                        <td class="text-center fw-bold">{{ auth()->user()->role == 'cs-smi' ? '30%' : '20%' }}</td>
                        <td class="fw-bold">{{ $databaseBaru }}</td>
                        <td class="text-center fw-bold">{{ $nilaiDatabaseBaru }}</td>
                    </tr>

                    {{-- 4 --}}
                    @php
                        $totalSumManual = 0;
                        if(isset($manual)){
                            $totalSumManual = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
                        }
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $no++ }}</td>
                        <td class="fw-bold">Penilaian Atasan</td>
                        <td>Total Skor Kualitatif (Max 500)</td>
                        <td class="text-center fw-bold">{{ auth()->user()->role == 'cs-smi' ? '30%' : '20%' }}</td>
                        <td class="fw-bold">{{ $totalSumManual }}</td>
                        <td class="text-center fw-bold">{{ $nilaiManualPart ?? 0 }}</td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr class="table-success fw-bold">
                        <td colspan="5" class="text-end">TOTAL NILAI</td>
                        <td class="text-center">{{ $totalNilai }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ================== TABEL PENILAIAN MANUAL (ATASAN) ================== --}}
    @if(isset($manual))
    <div class="card shadow-lg border-0 mt-4">
        <div class="card-header bg-primary text-white text-center fw-bold fs-5">
            PENILAIAN KUALITATIF (OLEH ATASAN)
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Aspek</th>
                        <th>Nilai (0-100)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">Kerajinan</td>
                        <td class="text-center">{{ $manual->kerajinan }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Kerjasama</td>
                        <td class="text-center">{{ $manual->kerjasama }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Tanggung Jawab</td>
                        <td class="text-center">{{ $manual->tanggung_jawab }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Inisiatif</td>
                        <td class="text-center">{{ $manual->inisiatif }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Komunikasi</td>
                        <td class="text-center">{{ $manual->komunikasi }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-info fw-bold">
                        <td class="text-end">TOTAL SKOR</td>
                        <td class="text-center">{{ $totalSumManual }}</td>
                    </tr>
                    <tr class="table-warning fw-bold">
                        <td class="text-end">RATA-RATA NILAI</td>
                        <td class="text-center">{{ $manual->total_nilai }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="p-3">
                            <strong>Catatan Atasan:</strong><br>
                            <em>{{ $manual->catatan ?? '-' }}</em>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>



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
                <td style="padding:14px;">80 â€“ 99</td>
                <td style="padding:14px;">Baik</td>
            </tr>

            <tr style="background:#ffe75c;">
                <td style="padding:14px;">60 â€“ 79</td>
                <td style="padding:14px;">Cukup</td>
            </tr>

            <tr style="background:#ff9933; color:#222;">
                <td style="padding:14px;">40 â€“ 59</td>
                <td style="padding:14px;">Pembinaan</td>
            </tr>

            <tr style="background:#e53935; color:white;">
                <td style="padding:14px;">&lt; 40</td>
                <td style="padding:14px;">Underperformance</td>
            </tr>
        </tbody>
    </table>

</div>


<!-- ====== STYLE ANIMASI ===== -->
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


<!--script nilai-->


<script>
// Ambil total nilai dari backend
let totalNilai = {{ $totalNilai ?? 0 }};

// Elemen target
const box = document.getElementById("kategoriBox");
const motivasi = document.getElementById("motivasiBox");

// =============================
// SKALA & MOTIVASI
// =============================
const kategori = [
    {
        min: 100,
        label: "Sangat Baik",
        bg: "#d1f7d3",
        border: "#8edb92",
        color: "#155724",
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
        bg: "#e9ffd6",
        border: "#c8eca2",
        color: "#35630a",
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
        bg: "#fff7d1",
        border: "#f0dc8a",
        color: "#8a6d00",
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
        bg: "#ffe4d1",
        border: "#f3b693",
        color: "#7a2f00",
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
        bg: "#fcd2d0",
        border: "#e39a96",
        color: "#811d1a",
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
</script>




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

</div>



{{-- ================== SCRIPT ================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // === POPUP MOTIVASI ===
    const motivasi = [
        "Kerja kerasmu hari ini adalah kesuksesanmu besok!",
        "Tetap fokus, kamu sudah sangat dekat dengan target!",
        "Percaya proses, hasil terbaik sedang menunggumu!",
        "Sedikit lagi! Kamu pasti bisa!",
        "Lakukan yang terbaik, Tuhan yang menyempurnakan!"
    ];

    function tampilMotivasi() {
        document.getElementById('motivasiText').innerText =
            motivasi[Math.floor(Math.random() * motivasi.length)];

        document.getElementById('popupOverlay').style.display = 'block';
        document.getElementById('motivasiPopup').style.display = 'block';
    }

    function tutupMotivasi() {
        document.getElementById('popupOverlay').style.display = 'none';
        document.getElementById('motivasiPopup').style.display = 'none';
    }

    // Muncul otomatis setelah 2 detik
    setTimeout(tampilMotivasi, 1500);



    // === CHART PENILAIAN ===
</script>

@endsection
