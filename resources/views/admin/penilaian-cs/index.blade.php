@extends('layouts.masteradmin')

@section('content')

<style>
    body { background: #eef1f6; font-family: Arial, sans-serif; }
    .container-penilaian {
        max-width: 1100px; margin: 40px auto; background: #fff;
        padding: 35px; border-radius: 14px; box-shadow: 0 5px 18px rgba(0,0,0,0.08);
    }
    h1 { font-size: 28px; font-weight: bold; margin-bottom: 25px; text-align: center; }
    h2 { font-size: 20px; margin-top: 40px; font-weight: bold; color:#2b3a55; padding-bottom:8px; border-bottom:3px solid #dfe6f1; }
    .form-label { margin-top: 15px; font-weight: bold; }
    .form-control { margin-bottom: 10px; padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; overflow: hidden; }
    table th { background: #2b3a55; color:#fff; text-align:center; padding:12px; }
    table td { padding:10px; background:#fafafa; border:1px solid #eee; vertical-align: middle; }
    textarea { width:100%; height:65px; padding:8px; border-radius:8px; border:1px solid #ccc; resize:vertical; }
.summary-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    font-size: 16px;
}

.summary-table thead th {
    background: linear-gradient(90deg, #1d4ed8, #3b82f6);
    color: #fff;
    padding: 12px;
    text-align: center;
    font-size: 17px;
    font-weight: 600;
}

.summary-table tbody tr td {
    padding: 12px 14px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fbff;
}

.summary-table tbody tr:hover td {
    background: #eef3ff;
    transition: 0.25s;
}

.summary-final-row td {
    background: linear-gradient(90deg, #fef3c7, #fde68a);
    padding: 14px;
    font-size: 18px;
    font-weight: 700;
    border-top: 2px solid #fcd34d;
    color: #92400e;
}

    .btn-submit { display:none; } /* tombol dihilangkan, otomatis */
    .small-muted { font-size:13px; color:#6b7280; }
    .total-box { margin-top:12px; padding:10px; border-radius:8px; background:#f6f9ff; border:1px solid #e6eefc; font-weight:700; }
</style>

<div class="container-penilaian">

    <h1 class="text-black">FORM PENILAIAN KINERJA KARYAWAN HELAS CORPORATION</h1>

    {{-- Periode & Pilih CS --}}
    <label class="form-label">Periode Penilaian</label>
    <form action="{{ route('admin.penilaian-cs.index') }}" method="GET" id="filterForm">
        <div style="display:flex; gap:10px; align-items:center;">
            <div style="width:160px;">
                <select name="bulan" class="form-control" required onchange="document.getElementById('filterForm').submit()">
                    <option value="">Pilih Bulan</option>
                    @foreach(range(1,12) as $m)
                        @php $value = sprintf('%02d',$m); @endphp
                        <option value="{{ $value }}" {{ request('bulan') == $value ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="width:260px;">
                <select name="user_id" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    <option value="">-- Pilih Karyawan (CS) --</option>

                    @foreach($daftarCs->filter(fn($cs) => in_array($cs->role, ['cs-mbc', 'cs-smi'])) as $cs)
                        <option value="{{ $cs->id }}"
                            {{ (int)request('user_id') === $cs->id || (isset($userId) && $userId == $cs->id) ? 'selected' : '' }}>
                            {{ $cs->name }}
                        </option>
                    @endforeach

                </select>
            </div>
        </div>
    </form>

{{-- ============== ASPEK A (otomatis & readonly) ============== --}}

@php
use App\Models\SalesPlan;
use Carbon\Carbon;

$bulan = request('bulan') ?? Carbon::now()->format('m');
$tahun = Carbon::now()->format('Y');
$userId = request('user_id') ?: 0;

// Mulai dan akhir bulan
$start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
$end   = Carbon::create($tahun, $bulan, 1)->endOfMonth();

// CEK kolom tanggal yang benar — fallback ke created_at
$kolomTanggal = Schema::hasColumn('salesplans', 'updated_at')
    ? 'updated_at'
    : 'created_at';


// CEK kolom user yang benar — fallback ke user_id
$kolomUser = Schema::hasColumn('salesplans', 'created_by') ? 'created_by' : 'user_id';

// Ambil data
$sales = SalesPlan::where($kolomUser, $userId)
    ->whereBetween($kolomTanggal, [$start, $end])
    ->get();
    
    

// Normalisasi status ke huruf kecil
$sales->transform(function($row){
    $row->status = strtolower(trim($row->status));
    return $row;
});

$countSudahTransfer = $sales->where('status', 'sudah_transfer')->count();
$countNo = $sales->where('status', 'no')->count();
$countCold = $sales->where('status', 'cold')->count();
$countTertarik = $sales->where('status', 'tertarik')->count();
$countMauTransfer = $sales->where('status', 'mau_transfer')->count();

$totalTidakClosing  = $countNo + $countCold;
$totalDatabase      = $sales->count();
$dbBaru             = $countTertarik + $countMauTransfer + $countCold + $countSudahTransfer + $countNo;

// Hitung pencapaian closing
$pencapaianClosing = $countSudahTransfer > 0 
    ? min(100, round(($countSudahTransfer / 30) * 100))
    : 0;
@endphp



<!--<h2 class="mt-4"><strong>A. ASPEK KINERJA PENJUALAN</strong></h2>-->

<!--<table class="table table-bordered" id="tableA">-->
<!--    <thead class="bg-primary text-white">-->
<!--        <tr>-->
<!--            <th width="50">No</th>-->
<!--            <th width="220">Aspek Dinilai</th>-->
<!--            <th>Indikator</th>-->
<!--            <th width="220">Keterangan</th>-->
<!--            <th width="120">Nilai</th>-->
<!--            <th>Catatan</th>-->
<!--        </tr>-->
<!--    </thead>-->

<!--    <tbody>-->
<!--        {{-- 1. Pencapaian Target Closing --}}-->
<!--        <tr>-->
<!--            <td class="text-center">1</td>-->
<!--            <td>Pencapaian Target Closing</td>-->
<!--            <td>Closing / Target 30</td>-->
<!--            <td>Closing bulan ini: <strong>{{ $countSudahTransfer }}</strong><br>Target: 30</td>-->
<!--            <td>-->
<!--                <input type="number" readonly value="{{ $pencapaianClosing }}"-->
<!--                       class="form-control scoreA" data-key="pencapaianClosing">-->
    
<!--            </td>-->
<!--            <td><textarea class="form-control" placeholder="Catatan..."></textarea></td>-->
<!--        </tr>-->

<!--        {{-- 2. Jumlah Tidak Closing --}}-->
<!--        <tr>-->
<!--            <td class="text-center">2</td>-->
<!--            <td>Jumlah Tidak Closing</td>-->
<!--            <td>Prospect status 'no' / 'cold'</td>-->
<!--            <td>Total Tidak Closing: <strong>{{ $totalTidakClosing }}</strong><br>Cold: {{ $countCold }}<br>No: {{ $countNo }}</td>-->
<!--            <td>-->
<!--                <input type="number" readonly value="{{ $totalTidakClosing }}"-->
<!--                       class="form-control scoreA" data-key="totalTidakClosing">-->
   
<!--            </td>-->
<!--            <td><textarea class="form-control" placeholder="Catatan..."></textarea></td>-->
<!--        </tr>-->

<!--        {{-- 3. Pertambahan Database Baru --}}-->
<!--        <tr>-->
<!--            <td class="text-center">3</td>-->
<!--            <td>Pertambahan Database Baru</td>-->
<!--            <td>DB baru (tertarik / mau_transfer / cold)</td>-->
<!--        <td>-->
<!--    <div>Tertarik: <strong>{{ $countTertarik }}</strong></div>-->
<!--    <div>Mau Transfer: <strong>{{ $countMauTransfer }}</strong></div>-->
<!--    <div>Cold: <strong>{{ $countCold }}</strong></div>-->
<!--    <div>No: <strong>{{ $countNo }}</strong></div>-->
<!--    <div>Sudah Transfer: <strong>{{ $countSudahTransfer }}</strong></div>-->
<!--    <div style="margin-top:4px;">-->
<!--        <strong>Total DB Baru: {{ $dbBaru }}</strong>-->
<!--    </div>-->
<!--</td>-->

<!--            <td>-->
<!--                <input type="number" readonly value="{{ $dbBaru }}"-->
<!--                       class="form-control scoreA" data-key="dbBaru">-->

<!--            </td>-->
<!--            <td><textarea class="form-control" placeholder="Catatan..."></textarea></td>-->
<!--        </tr>-->

<!--        {{-- 4. Konversi Closing --}}-->

<!--    </tbody>-->

<!--<tfoot>-->
<!--    <tr style="background:#eef5ff; font-weight:700;">-->
<!--        <td colspan="4" style="text-align:right;">Total Nilai A</td>-->
<!--        <td id="total_A">0</td>-->
<!--        <td></td>-->
<!--    </tr>-->
<!--    <tr style="background:#e8f3ff; font-weight:700;">-->
<!--        <td colspan="4" style="text-align:right;">Rata-rata Nilai A</td>-->
<!--        <td id="avg_A">0</td>-->
<!--        <td></td>-->
<!--    </tr>-->
<!--    <tr style="background:#fff4e6; font-weight:700;">-->
<!--        <td colspan="4" style="text-align:right;">Skor A Terkonversi</td>-->
<!--        <td id="scoreA_converted">0</td>-->
<!--        <td></td>-->
<!--    </tr>-->
<!--</tfoot>-->

<!--</table>-->



    {{-- ============== GENERATE B,C,D (manual inputs) ============== --}}
    <div id="sections"></div>

<h2 class="mt-4" style="font-weight: 800;"><strong>E. RINGKASAN NILAI</strong></h2>

<table class="summary-table" style="font-weight:600;">
    <thead>
        <tr>
            <th style="font-weight:700;">Aspek</th>
            <th style="font-weight:700;">Rata-rata</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td style="font-weight:600;">A. Kinerja Penjualan (terkonversi)</td>
            <td id="sumA" style="font-weight:600;">0</td>
        </tr>
        <tr>
            <td style="font-weight:600;">B. Komunikasi & Attitude</td>
            <td id="sumB" style="font-weight:600;">0</td>
        </tr>
        <tr>
            <td style="font-weight:600;">C. Kedisiplinan</td>
            <td id="sumC" style="font-weight:600;">0</td>
        </tr>
        <tr>
            <td style="font-weight:600;">D. Karakter & Semangat Kerja</td>
            <td id="sumD" style="font-weight:600;">0</td>
        </tr>
    </tbody>

    <tfoot>
        <tr class="summary-final-row">
            <td style="font-weight:700;"><strong>TOTAL NILAI AKHIR</strong></td>
            <td id="totalFinal" style="font-weight:700;"><strong>0</strong></td>
        </tr>
    </tfoot>
</table>


<!-- BLOK NILAI, MOTIVASI & KETERANGAN RENTANG NILAI -->
<div class="nilai-wrapper" style="
    background:#fff;
    border:1px solid #dce3f1;
    border-radius:14px;
    padding:22px;
    margin-top:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.07);
">

    <!-- TOTAL NILAI -->
    <div id="kategoriBox" style="
        padding:16px;
        border-radius:12px;
        font-size:20px;
        font-weight:700;
        text-align:center;
        margin-bottom:18px;
        background:#f6f7fb;
        border:1px solid #d0d4e4;
        color:#333;
    ">
        Belum dihitung
    </div>

    <!-- MOTIVASI -->
    <div id="motivasiBox" style="
        margin-top:14px;
        margin-bottom:25px;
    "></div>


   <h3 class="mt-4">Keterangan Nilai Bulan Ini</h3>
<table class="conversion-table">
    <tr><th>Rentang Nilai</th><th>Keterangan</th></tr>

    <tr class="conv-sangatbaik">
        <td>> 100</td>
        <td>Sangat Baik</td>
    </tr>

    <tr class="conv-baik">
        <td>80 - 99</td>
        <td>Baik</td>
    </tr>

    <tr class="conv-cukup">
        <td>60 - 79</td>
        <td>Cukup</td>
    </tr>

    <tr class="conv-pembinaan">
        <td>40 - 59</td>
        <td>Pembinaan</td>
    </tr>

    <tr class="conv-under">
        <td>< 40</td>
        <td>Underperformance</td>
    </tr>
</table>

<style>
.conversion-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
    font-family: 'Segoe UI', sans-serif;
}
.conversion-table th {
    background: #2c3e50 !important;
    color: #fff !important;
    font-size: 16px;
    font-weight: 700;
    padding: 12px;
    letter-spacing: 0.5px;
}
.conversion-table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
    font-weight: 700;
    font-size: 15px;
}
.conv-sangatbaik td  { background: #007f00 !important; color: #ffffff !important; text-shadow: 0 0 3px rgba(0,0,0,0.4); }
.conv-baik td { background: #22aa22 !important; color: #ffffff !important; text-shadow: 0 0 3px rgba(0,0,0,0.4); }
.conv-cukup td { background: #ffe84f !important; color: #333333 !important; text-shadow: 0 0 2px rgba(255,255,255,0.5); }
.conv-pembinaan td { background: #ff9d29 !important; color: #2c2c2c !important; text-shadow: 0 0 2px rgba(255,255,255,0.5); }
.conv-under td { background: #e53935 !important; color: #ffffff !important; text-shadow: 0 0 3px rgba(0,0,0,0.4); }
</style>

<h2 style="margin-top:40px; font-weight:bold;">G. HISTORY NILAI PER BULAN</h2>

@php
    // Contoh nilai langsung dari Blade
    $historyNilai = [
        11 => 92 // November
    ];
@endphp

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

            if($nilai >= 85){
                $warna = "#22c55e"; // hijau
            } elseif($nilai >= 70){
                $warna = "#eab308"; // kuning
            } elseif($nilai >= 50){
                $warna = "#fb923c"; // orange
            } else {
                $warna = "#e5e7eb"; // putih/abu (belum dinilai)
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


<script>
/* =========================
   Data sections (B,C,D)
   ========================= */
const sections = {
    B:{ title:"ASPEK KOMUNIKASI & ATTITUDE", items:[
        ["Kemampuan Berkomunikasi","Bahasa sopan, persuasif, empati"],
        ["Sikap saat Menangani Penolakan","Tetap tenang & profesional"],
        ["Kerjasama Tim","Mendukung dan komunikasi terbuka"],
        ["Penerimaan Feedback","Tidak defensif, mau belajar"],
        ["Senyum & Energi Positif","Menjaga vibes positif"]
    ]},
    C:{ title:"ASPEK KEDISIPLINAN", items:[
        ["Laporan Harian & CRM","Mengisi data dengan disiplin"],
        ["Tanggung Jawab Follow-up","Tidak ada prospek terabaikan"],
        ["Ketaatan SOP & Script","Mengikuti alur standar"]
    ]},
    D:{ title:"ASPEK KARAKTER & SEMANGAT KERJA", items:[
        ["Semangat & Antusiasme","Energi tinggi & konsisten"],
        ["Kejujuran & Integritas","Jujur dalam data & proses"],
        ["Tanggung Jawab","Bekerja sebagai ibadah"],
        ["Inisiatif & Kepedulian","Membantu tanpa diminta"],
        ["Konsistensi Doa","Menjaga niat dan spiritualitas"]
    ]}
};

/* =========================
   Render sections B,C,D
   ========================= */
function renderSections() {
    const container = document.getElementById("sections");
    container.innerHTML = "";

    Object.entries(sections).forEach(([key, section]) => {
        let html = `
            <h2 class="mt-4"><strong>${key}. ${section.title}</strong></h2>
            <table class="table table-bordered" id="table_${key}">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="50">No</th>
                        <th width="220">Aspek Dinilai</th>
                        <th>Indikator</th>
                        <th width="120">Nilai (0-100)</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
        `;

        section.items.forEach((item, index) => {
            html += `
                <tr>
                    <td class="text-center">${index+1}</td>
                    <td>${item[0]}</td>
                    <td>${item[1]}</td>
                    <td>
                        <input type="number" min="0" max="100" class="form-control score" data-section="${key}" value="0">
                    </td>
                    <td><textarea class="form-control" placeholder="Catatan..."></textarea></td>
                </tr>
            `;
        });

        // tfoot: total & avg
        html += `
                </tbody>
<tfoot>

    <!-- TOTAL -->
    <tr style="
        background: linear-gradient(90deg, #dbeafe, #eff6ff) !important;
        font-weight:700;
        border-top:2px solid #bfdbfe;
    ">
        <td colspan="3" style="text-align:right; padding:10px; color:#1e3a8a;">
            Total Nilai ${key}
        </td>
        <td id="total_${key}" style="color:#1e40af; font-size:16px;">0</td>
        <td></td>
    </tr>

    <!-- RATA-RATA -->
    <tr style="
        background: linear-gradient(90deg, #f0f7ff, #e0efff) !important;
        font-weight:700;
        border-bottom:2px solid #93c5fd;
    ">
        <td colspan="3" style="text-align:right; padding:10px; color:#1e3a8a;">
            Rata-rata Nilai ${key}
        </td>
        <td id="avg_${key}" style="color:#1e40af; font-size:16px;">0</td>
        <td></td>
    </tr>

</tfoot>


            </table>
        `;

        container.innerHTML += html;
    });
}

renderSections();

/* =========================
   Motivasi text banks
   ========================= */
const motivasiText = {
    sangatBaik: [ "Terus pertahankan prestasi ini, Anda luar biasa!", "Kerja keras Anda menjadi inspirasi bagi tim.", "Konsistensi Anda adalah kekuatan terbesar Anda." ],
    baik: [ "Kinerja Anda sangat baik, terus tingkatkan!", "Anda menunjukkan potensi besar untuk berkembang.", "Terus asah kemampuan Anda setiap hari." ],
    cukup: [ "Terus berusaha, Anda memiliki potensi besar.", "Sedikit peningkatan akan membawa Anda lebih baik.", "Jangan ragu untuk belajar hal baru setiap hari." ],
    pembinaan: [ "Tetap semangat, masih banyak ruang untuk berkembang.", "Jangan berkecil hati, ini adalah awal perbaikan.", "Manfaatkan bimbingan dari atasan untuk berkembang." ],
    unqualified: [ "Ini bukan akhir, tetapi awal untuk bangkit.", "Evaluasi adalah kesempatan untuk memperbaiki diri.", "Anda masih bisa menorehkan perubahan besar." ]
};

/* =========================
   Utility helpers
   ========================= */
function toNumber(v) { return (v === null || v === undefined || v === "") ? 0 : Number(v); }

/* =========================
   Compute conversions for Aspek A
   - returns object { totalRaw, avgRaw, convertedScore }
   ========================= */
function computeAConversion() {
    // collect A inputs (readonly)
    const inputs = document.querySelectorAll('.scoreA');
    let rawVals = {};
    inputs.forEach(inp => {
        const key = inp.dataset.key; // pencapaianClosing, totalTidakClosing, dbBaru, konversiClosing
        rawVals[key] = toNumber(inp.value);
    });

    // Raw total & avg (mentah)
    const rawArray = Object.values(rawVals);
    const totalRaw = rawArray.reduce((s, n) => s + n, 0);
    const avgRaw = rawArray.length ? (totalRaw / rawArray.length) : 0;

    // Convert to 0-100 scale:
    // 1) pencapaianClosing: already 0-100 from server
    const pencapaianScore = Math.max(0, Math.min(100, toNumber(rawVals.pencapaianClosing || 0)));

    // 2) totalTidakClosing: lower is better -> use proportion against totalDatabase
    const totalTidakClosing = toNumber(rawVals.totalTidakClosing || 0);
    // read totalDatabase from server-rendered element (we'll store it as data attr on tableA)
    const totalDatabase = Number(@json($totalDatabase ?? 0));
    let notClosingScore = 0;
    if (totalDatabase > 0) {
        const prop = Math.min(1, totalTidakClosing / totalDatabase);
        notClosingScore = Math.round((1 - prop) * 100);
    } else {
        // jika tidak ada DB, treat no not-closing as perfect if totalTidakClosing == 0
        notClosingScore = totalTidakClosing > 0 ? 0 : 100;
    }

    // 3) dbBaru: more is better relative to totalDatabase
    const dbBaru = toNumber(rawVals.dbBaru || 0);
    let dbBaruScore = 0;
    if (totalDatabase > 0) {
        dbBaruScore = Math.round(Math.min(1, dbBaru / totalDatabase) * 100);
    } else {
        dbBaruScore = dbBaru > 0 ? 100 : 0;
    }

    // 4) konversiClosing: already 0-100
    const konversiScore = Math.max(0, Math.min(100, toNumber(rawVals.konversiClosing || 0)));

    // final converted A score = average of the 4 converted components
    const components = [pencapaianScore, notClosingScore, dbBaruScore, konversiScore];
    const convertedScore = components.reduce((s, n) => s + n, 0) / components.length;

    return {
        totalRaw,
        avgRaw,
        convertedScore: Number(convertedScore.toFixed(1)),
        components: { pencapaianScore, notClosingScore, dbBaruScore, konversiScore }
    };
}

/* =========================
   Calculate B/C/D totals & averages
   ========================= */
function calculateSectionTotals() {
    const totals = { B:0, C:0, D:0 };
    const counts = { B:0, C:0, D:0 };

    document.querySelectorAll('.score').forEach(input => {
        const sec = input.dataset.section;
        const val = toNumber(input.value);
        totals[sec] += val;
        counts[sec] += 1;
    });

    // update table footers and summary placeholders
    ['B','C','D'].forEach(sec => {
        const totalEl = document.getElementById(`total_${sec}`);
        const avgEl = document.getElementById(`avg_${sec}`);
        const sumEl = document.getElementById(`sum${sec}`);

        const total = totals[sec] || 0;
        const avg = counts[sec] ? (total / counts[sec]) : 0;

        if (totalEl) totalEl.textContent = Math.round(total);
        if (avgEl) avgEl.textContent = Math.round(avg);
        if (sumEl) sumEl.textContent = Math.round(avg); // ringkasan menampilkan rata-rata tiap aspek
    });

    return { totals, counts };
}

/* =========================
   MAIN: calculate all and update UI (runs on load & on input)
   ========================= */
function recalcAll() {
    // A computations
    const aResult = computeAConversion();
    // update A tfoot
    const totalAEl = document.getElementById('total_A');
    const avgAEl = document.getElementById('avg_A');
    const scoreAConvEl = document.getElementById('scoreA_converted');

    if (totalAEl) totalAEl.textContent = Math.round(aResult.totalRaw);
    if (avgAEl) avgAEl.textContent = Math.round(aResult.avgRaw);
    if (scoreAConvEl) scoreAConvEl.textContent = aResult.convertedScore;

    // set ringkasan A (use converted score for summary)
    const sumAEl = document.getElementById('sumA');
    if (sumAEl) sumAEl.textContent = Math.round(aResult.convertedScore);

    // B/C/D
    const { totals, counts } = calculateSectionTotals();

    // compute final: average of (A_converted + B_avg + C_avg + D_avg)
    const avgB = counts.B ? (totals.B / counts.B) : 0;
    const avgC = counts.C ? (totals.C / counts.C) : 0;
    const avgD = counts.D ? (totals.D / counts.D) : 0;

    const finalAvg = (aResult.convertedScore + avgB + avgC + avgD) / 4;
    const finalRounded = Number(finalAvg.toFixed(1));

    // update final summary
    const totalFinalEl = document.getElementById('totalFinal');
    if (totalFinalEl) totalFinalEl.textContent = finalRounded;

    // kategori & coloring
    const box = document.getElementById('kategoriBox');
    let kategori = "";
    if (finalRounded >= 90) kategori = "Sangat Baik";
    else if (finalRounded >= 80) kategori = "Baik";
    else if (finalRounded >= 60) kategori = "Cukup";
    else if (finalRounded >= 40) kategori = "Perlu Pembinaan";
    else kategori = "Underperformance";

    box.textContent = `${kategori} (${finalRounded})`;

    if (kategori === "Sangat Baik" || kategori === "Baik") {
        box.style.background = "#d5f8d5";
        box.style.borderColor = "#27ae60";
        box.style.color = "#145a32";
    } else if (kategori === "Cukup") {
        box.style.background = "#fff6c7";
        box.style.borderColor = "#f1c40f";
        box.style.color = "#7d6608";
    } else {
        box.style.background = "#ffd6d6";
        box.style.borderColor = "#e74c3c";
        box.style.color = "#7b241c";
    }

    // show motivational sentence based on kategori
    let motivasiList = motivasiText.unqualified;
    if (kategori === "Sangat Baik") motivasiList = motivasiText.sangatBaik;
    else if (kategori === "Baik") motivasiList = motivasiText.baik;
    else if (kategori === "Cukup") motivasiList = motivasiText.cukup;
    else if (kategori === "Perlu Pembinaan") motivasiList = motivasiText.pembinaan;

    const motivasiBox = document.getElementById('motivasiBox');
    const randomKalimat = motivasiList[Math.floor(Math.random() * motivasiList.length)];
    motivasiBox.innerHTML = `
        <div class="motivasi-wrapper">
            <div class="motivasi-title">Motivasi Bulanan:</div>
            <div class="motivasi-text">${randomKalimat}</div>
        </div>
    `;
}

/* =========================
   Event listeners
   - Recalc on input change (B/C/D inputs)
   - Recalc on load
   ========================= */
document.addEventListener('input', function(e){
    // only recalc when relevant input changes (score inputs)
    if (e.target.matches('.score') || e.target.matches('.scoreA')) {
        recalcAll();
    }
});

// initial run on load (DOM ready)
document.addEventListener('DOMContentLoaded', function(){
    // ensure sections rendered values are considered
    recalcAll();
});
</script>

<style>
.motivasi-wrapper {
    background: #fff5d9;
    padding: 15px 20px;
    border-radius: 12px;
    border-left: 6px solid #ff9800;
    margin-top: 10px;
    animation: pulseGlow 1.7s infinite;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}
.motivasi-title {
    font-weight: bold;
    font-size: 17px;
    color: #e65100;
    margin-bottom: 8px;
}
.motivasi-text {
    font-size: 16px;
    font-weight: 600;
    color: #6d4800;
}
@keyframes pulseGlow {
    0% { transform: scale(1); box-shadow: 0 0 10px rgba(255,145,0,0.4); }
    50% { transform: scale(1.02); box-shadow: 0 0 18px rgba(255,145,0,0.7); }
    100% { transform: scale(1); box-shadow: 0 0 10px rgba(255,145,0,0.4); }
}
</style>

@endsection
