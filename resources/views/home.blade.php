@extends('layouts.masteradmin')

@section('content')
@php
    use App\Models\Data;
@endphp

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
    
</style>

<div class="container-fluid px-4">

    {{-- ALERT MODE READ ONLY (ADMIN) --}}
    @if(isset($user) && $readonly)
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
            <div>
                <strong>Dashboard CS:</strong> <strong>{{ $user->name }} </strong> <br>
                <span class="text-muted small">Email: {{ $user->email }} | Role: {{ ucfirst($user->role) }}</span>
            </div>
            <div>
                <span class="text-white badge bg-primary p-2">Mode Read-Only</span>
            </div>
        </div>
    @endif
    
    {{-- ✨ KOMENTAR ADMIN KE CS ✨ --}}
@if(isset($user) && $readonly)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-warning text-dark fw-bold">
        <i class="fas fa-comments me-2"></i> Komentar untuk {{ $user->name }}
    </div>
    <div class="card-body">
        {{-- Form Kirim Komentar --}}
        <form id="formKomentar" method="POST" action="{{ route('komentar.store') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <div class="input-group mb-3">
                <input type="text" name="pesan" class="form-control" placeholder="Tulis komentar untuk CS ini..." required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Kirim
                </button>
            </div>
        </form>
@if(session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif


<button class="btn btn-outline-secondary btn-sm mb-2" data-toggle="modal" data-target="#modalKomentar">
    <i class="fas fa-history"></i> Lihat Riwayat Komentar
</button>

<div class="modal fade" id="modalKomentar" tabindex="-1" role="dialog" aria-labelledby="modalKomentarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="modalKomentarLabel">
            <i class="fas fa-comments me-2"></i> Riwayat Komentar
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          @foreach($komentar as $msg)
              <div class="alert alert-light border d-flex justify-content-between align-items-start mb-2">
                  <div>
                      <strong>{{ $msg->admin->name ?? 'Admin' }}</strong><br>
                      <span class="text-dark">{{ $msg->pesan }}</span><br>
                      <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                  </div>
                  <i class="fas fa-comment-dots text-warning"></i>
              </div>
          @endforeach
      </div>
    </div>
  </div>
</div>


    </div>
</div>
@endif



@php
    $bulanDipilih = request('bulan', now()->format('Y-m'));
    $bulanParse   = \Carbon\Carbon::parse($bulanDipilih . '-01');
    $namaBulan    = $bulanParse->translatedFormat('F');
    $tahun        = $bulanParse->year;

    use App\Models\Kelas;


    $jadwalKelas = Kelas::whereYear('tanggal_mulai', $tahun)
                        ->whereMonth('tanggal_mulai', $bulanParse->month)
                        ->pluck('tanggal_mulai','nama_kelas')
                        ->toArray();
@endphp


    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="bulan" class="form-label fw-semibold">
                         Pilih Bulan Kelas:
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

    {{-- ================== OMSET PER KELAS ================== --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-success text-white fw-bold">
        OMSET KELAS ({{ strtoupper($namaBulan) }} {{ $tahun }})
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Nama Kelas</th>
                    <th>Tanggal</th>
                    <th>Omset</th>
                    <th>Target</th>
                    <th>% Tercapai</th>
                    <th>Insentif</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kelasOmsetFiltered as $k)
                    @php
                        $komisiSementara = $k['omset'] * 0.01;
                        $komisiTotal = $k['omset'] >= $k['target'] ? $komisiSementara + 300000 : $komisiSementara;
                        $persen = $k['target'] > 0 ? round(($k['omset'] / $k['target']) * 100, 2) : 0;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $k['nama_kelas'] }}</td>
                        <td>{{ $jadwalKelas[$k['nama_kelas']] ?? '-' }}</td>
                        <td class="text-success fw-bold">
                            Rp {{ number_format($k['omset'], 0, ',', '.') }}
                        </td>
                        <td>Rp {{ number_format($k['target'], 0, ',', '.') }}</td>
                        <td class="{{ $persen >= 100 ? 'text-success fw-bold' : ($persen >= 75 ? 'text-warning fw-bold' : 'text-danger fw-bold') }}">
                            {{ $persen }}%
                        </td>
                        <td class="text-primary fw-bold">
                            Rp {{ number_format($komisiTotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted fst-italic">Tidak ada data untuk bulan ini</td>
                    </tr>
                @endforelse
            </tbody>

    @php
    $totalOmset = $kelasOmsetFiltered->sum('omset');
    $targetBulanan = 50000000; // 50 juta
    $persenTercapai = $targetBulanan > 0 ? round(($totalOmset / $targetBulanan) * 100, 2) : 0;

    // 🔹 Logika Reward Bulanan (nilai numerik + teks)
  if ($persenTercapai >= 100) {
    $rewardBulanan = 600000;
    $reward = "Rp " . number_format($rewardBulanan, 0, ',', '.');
    $keterangan = "🏆 Luar biasa! Anda mencapai 100%! Terus pertahankan performa hebat ini!";
} elseif ($persenTercapai >= 90) {
    $rewardBulanan = 500000;
    $reward = "Rp " . number_format($rewardBulanan, 0, ',', '.');
    $keterangan = "🔥 Hampir sempurna! Tingkatkan performa sedikit lagi untuk mencapai 100%!";
} elseif ($persenTercapai >= 50) {
    $rewardBulanan = 300000;
    $reward = "Rp " . number_format($rewardBulanan, 0, ',', '.');
    $keterangan = "💪 Performa Anda bagus! Ayo semangat, masih bisa ditingkatkan!";
} else {
    $rewardBulanan = 0;
    $reward = "-";
    $keterangan = "😔 Mohon maaf, Anda belum mendapat reward. Tetap semangat untuk bulan depan!";
}

@endphp


            <tfoot>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-end text-dark">Total Omset</td>
                    <td colspan="3" class="text-start text-success">
                        Rp {{ number_format($totalOmset, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-end text-dark">Target Omset Bulanan</td>
                    <td colspan="3" class="text-start text-dark">
                        Rp {{ number_format($targetBulanan, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-end text-dark">Persentase Tercapai</td>
                    <td colspan="3" class="text-start 
                        {{ $persenTercapai >= 100 ? 'text-success' : ($persenTercapai >= 75 ? 'text-warning' : 'text-danger') }}">
                        {{ $persenTercapai }}%
                    </td>
                </tr>
                <tr class="bg-light fw-bold">
                    <td colspan="3" class="text-end text-dark">Reward Bulanan</td>
                    <td colspan="3" class="text-start text-success">
                        {{ $reward }}
                    </td>
                </tr>
<tr class="bg-light fw-bold">
    <td colspan="3" class="text-end text-dark">Reminder</td>
    <td colspan="3" class="text-start">
        <div class="reminder-cell">
            <i class="fa-solid fa-bell reminder-icon"></i>
            {{ $keterangan }}
        </div>
    </td>
</tr>
            </tfoot>
        </table>
    </div>
</div>



    {{-- ================== CARD DATABASE & KOMISI ================== --}}
    @php
        $namaUserData = isset($user) && $readonly ? $user->name : auth()->user()->name;

        $databaseBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $bulanParse->year)
            ->whereMonth('created_at', $bulanParse->month)
            ->count();

        $totalDatabase = Data::where('created_by', $namaUserData)->count();
        $target = 50;

        $sumberDatabase = Data::select('leads', \DB::raw('COUNT(*) as total'))
            ->where('created_by', $namaUserData)
            ->whereYear('created_at', $bulanParse->year)
            ->whereMonth('created_at', $bulanParse->month)
            ->groupBy('leads')
            ->pluck('total','leads')
            ->toArray();

        $labels = array_keys($sumberDatabase);
        $values = array_values($sumberDatabase);

     $totalKomisi = collect($kelasOmsetFiltered)->sum(function($k) {
    $komisiSementara = $k['omset'] * 0.01;
    return $k['omset'] >= $k['target'] ? $komisiSementara + 300000 : $komisiSementara;
});

    @endphp

    <div class="row g-4">
        {{-- Card Database --}}
        <div class="col-12 col-md-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-info text-white fw-bold py-2">
                    <i class="fas fa-database me-2"></i> JUMLAH DATABASE
                </div>
                <div class="card-body text-center">
                    <h2 class="fw-bold text-dark mb-2" style="font-size: 2.5rem;">{{ $databaseBaru }}</h2>
                    <p class="text-muted mb-3">Periode: {{ $bulanParse->translatedFormat('F') }} {{ $bulanParse->year }}</p>
                    <div class="progress mb-3" style="height: 18px; border-radius: 10px;">
                        <div class="progress-bar bg-success fw-bold text-white" role="progressbar" style="width: {{ min(($databaseBaru / $target) * 100, 100) }}%">
                            {{ number_format(($databaseBaru / $target) * 100, 0) }}%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-primary text-white px-3 py-2">Target: {{ $target }}</span>
                        <span class="badge bg-secondary text-white px-3 py-2">Total: {{ $totalDatabase }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Pie Chart --}}
        <div class="col-12 col-md-4">
            <div class="card shadow-lg border-0 h-100">
                <div class="card-header bg-primary text-white fw-bold py-2">
                    <i class="fas fa-chart-pie me-2"></i> SUMBER LEADS
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="pieSumberDbSmall" width="200" height="200"></canvas>
                </div>
            </div>
        </div>

{{-- Card Total Komisi + Reward --}}
<div class="col-12 col-md-4">
    <div class="card shadow-lg border-0 h-100">
        <div class="card-header bg-secondary text-white fw-bold py-2">
            <i class="fas fa-file-invoice-dollar me-2"></i> TOTAL KOMISI + REWARD
        </div>
        <div class="card-body text-center">
            @php
                $totalDenganReward = $totalKomisi + $rewardBulanan;
            @endphp

            <h2 class="fw-bold text-success mb-2" style="font-size: 2.5rem;">
                Rp {{ number_format($totalDenganReward, 0, ',', '.') }}
            </h2>

            <p class="text-muted mb-1">Komisi: Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
            <p class="text-muted mb-1">Reward: Rp {{ number_format($rewardBulanan, 0, ',', '.') }}</p>
            
            <hr>
            <p class="text-muted mb-0">Periode: {{ $namaBulan }} {{ $tahun }}</p>
        </div>
    </div>
</div>


    </div>

    {{-- ================== PIE CHART ================== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxSmall = document.getElementById('pieSumberDbSmall').getContext('2d');
        new Chart(ctxSmall, {
            type: 'pie',
            data: {
                labels: @json($labels),
                datasets: [{
                    data: @json($values),
                    backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#17a2b8','#fd7e14','#20c997','#6610f2','#e83e8c'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 12 } }
                    }
                }
            }
        });
    </script>

    {{-- ================== KPI BULANAN ================== --}}
    <div class="card shadow-lg border-0 mt-5">
        <div class="card-header bg-primary text-white text-center fw-bold fs-5">
          PENILAIAN AKTIVITAS (CS MBC)
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped table-hover mb-0 text-center align-middle">
                <thead class="table-info">
                    <tr>
                        <th>No</th>
                        <th>Aktivitas</th>
                        <th>Target</th>
                        <th>Bobot</th>
                        <th>Presentase</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kpiData as $i => $row)
                        <tr>
                            <td class="fw-bold text-dark">{{ $i+1 }}</td>
                            <td class="fw-bold text-start text-dark">{{ $row['nama'] }}</td>
                            <td class="fw-bold text-dark">{{ $row['target'] }}</td>
                            <td class="fw-bold text-dark">{{ $row['bobot'] }}</td>
                            <td class="fw-bold text-dark">{{ $row['persentase'] }}%</td>
                            <td class="fw-bold text-dark">{{ number_format($row['nilai'],2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-success fw-bold fs-6">
                        <td colspan="3" class="text-center text-dark fw-bold">TOTAL</td>
                        <td class="text-dark fw-bold">{{ $totalBobot }}</td>
                        <td>â€”</td>
                        <td class="text-dark fw-bold">{{ number_format($totalNilai,2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
  {{-- ================== PENILAIAN HASIL ================== --}}
<!--<div class="card shadow-lg border-0 mt-4">-->
<!--    <div class="card-header bg-success text-white text-center fw-bold fs-5">-->
<!--        PENILAIAN HASIL (CS MBC)-->
<!--    </div>-->

<!--    <div class="card-body p-0">-->
<!--        <table class="table table-bordered table-striped table-hover mb-0 align-middle">-->
<!--            <thead class="table-warning text-center">-->
<!--                <tr>-->
<!--                    <th>No</th>-->
<!--                    <th>Aspek Kinerja</th>-->
<!--                    <th>Indikator</th>-->
<!--                    <th>Bobot</th>-->
<!--                    <th>Pencapaian</th>-->
<!--                    <th>Nilai</th>-->
<!--                </tr>-->
<!--            </thead>-->

<!--            <tbody class="text-dark">-->

<!--                {{-- 1. Penjualan & Omset --}}-->
<!--                <tr>-->
<!--                    <td class="text-center fw-bold">1</td>-->
<!--                    <td class="fw-bold">Penjualan & Omset</td>-->
<!--                    <td>Omset per kelas Rp 25 juta, total Rp 50 juta/bulan dari 2 kelas</td>-->
<!--                    <td class="text-center fw-bold">40%</td>-->
<!--                    <td class="fw-bold">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>-->
<!--                    <td class="text-center fw-bold">{{ $nilaiOmset ?? 0 }}</td>-->
<!--                </tr>-->

<!--                {{-- 2. Closing Paket --}}-->
<!--                <tr>-->
<!--                    <td class="text-center fw-bold">2</td>-->
<!--                    <td class="fw-bold">Closing Paket</td>-->
<!--                    <td>Mendapatkan minimal 1 closing peserta yang ambil paket kelas per bulan</td>-->
<!--                    <td class="text-center fw-bold">30%</td>-->

<!--                    {{-- PENCAPAIAN --}}-->
<!--                    <td class="fw-bold">{{ $closingPaket ?? 0 }} peserta</td>-->

<!--                    {{-- NILAI --}}-->
<!--                    <td class="text-center fw-bold">{{ $nilaiClosingPaket ?? 0 }}</td>-->
<!--                </tr>-->

<!--                {{-- 3. Database Baru --}}-->
<!--                <tr>-->
<!--                    <td class="text-center fw-bold">3</td>-->
<!--                    <td class="fw-bold">Database Baru</td>-->
<!--                    <td>50 database baru</td>-->
<!--                    <td class="text-center fw-bold">30%</td>-->

<!--                    {{-- PENCAPAIAN --}}-->
<!--                    <td class="fw-bold">{{ $databaseBaru }}</td>-->

<!--                    {{-- NILAI --}}-->
<!--                    <td class="text-center fw-bold">{{ $nilaiDatabaseBaru ?? 0 }}</td>-->
<!--                </tr>-->

<!--            </tbody>-->

<!--            {{-- TOTAL NILAI --}}-->
<!--       <tfoot>-->
<!--    <tr class="table-success fw-bold">-->
<!--        <td colspan="5" class="text-end">TOTAL NILAI</td>-->
<!--        <td class="text-center">-->
<!--            {{ ($nilaiOmset ?? 0) + ($nilaiClosingPaket ?? 0) + ($nilaiDatabaseBaru ?? 0) }}-->
<!--        </td>-->
<!--    </tr>-->
<!--</tfoot>-->

<!--        </table>-->
<!--    </div>-->
<!--</div>-->


    <br>
    <br>

</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

