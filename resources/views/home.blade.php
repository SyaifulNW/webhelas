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
    
    /* Popup Motivasi */
    @keyframes popIn {
        from { transform: translate(-50%, -40%) scale(0.5); opacity: 0; }
        to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    }

    #popupOverlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        z-index: 9998;
    }

    #motivasiPopup {
        display: none; 
        position: fixed; 
        top: 50%; left: 50%; 
        transform: translate(-50%, -50%); 
        z-index: 9999; 
        background: white; 
        padding: 20px; 
        border-radius: 15px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
        text-align: center;
        width: 90%; 
        max-width: 400px;
        animation: popIn 0.5s ease-out;
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

    <!-- Popup Motivasi HTML -->
    <div id="popupOverlay" onclick="tutupMotivasi()"></div>
    <div id="motivasiPopup">
        <h4 class="fw-bold text-primary mb-3">🌟 Motivasi Hari Ini</h4>
        <p id="motivasiText" class="fs-5 text-dark" style="font-style: italic;"></p>
        <button class="btn btn-primary mt-3 px-4" onclick="tutupMotivasi()">Semangat! 🚀</button>
    </div>

    <!-- Month Filter Card -->
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

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="dashboard-tab-link" data-toggle="tab" data-target="#dashboard-tab" type="button" role="tab" aria-controls="dashboard-tab" aria-selected="true">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard Panel 1
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="performance-tab-link" data-toggle="tab" data-target="#performance-tab" type="button" role="tab" aria-controls="performance-tab" aria-selected="false">
                <i class="fas fa-star me-2"></i> Penilaian Kinerja Saya
            </button>
        </li>
    </ul>

    <div class="tab-content" id="dashboardTabsContent">
        <!-- ================== TAB 1: DASHBOARD ================== -->
        <div class="tab-pane fade show active" id="dashboard-tab" role="tabpanel" aria-labelledby="dashboard-tab-link">
            
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
                        $targetBulanan = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
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
                                <td colspan="3" class="text-start {{ $persenTercapai >= 100 ? 'text-success' : ($persenTercapai >= 75 ? 'text-warning' : 'text-danger') }}">
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

            <div class="row g-4 mb-4">
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

            {{-- ================== PIE CHART SCRIPT ================== --}}
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
            <div class="card shadow-lg border-0 mt-5 mb-5">
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
                                <td>—</td>
                                <td class="text-dark fw-bold">{{ number_format($totalNilai,2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================== TAB 2: PENILAIAN KINERJA SAYA ================== -->
        <div class="tab-pane fade" id="performance-tab" role="tabpanel" aria-labelledby="performance-tab-link">
            
            <div class="container-fluid mt-4">
                
                {{-- JUDUL --}}
                <div class="text-center mb-3">
                    <h3 class="fw-bold" style="color: #5a5c69;">Penilaian Hasil CS</h3>
                </div>

                {{-- FILTER BULAN & TAHUN --}}
                <form method="GET" action="{{ route('home') }}" class="d-flex justify-content-center align-items-center mb-4" style="gap: 10px;">
                    <input type="hidden" name="active_tab" value="performance">
                    
                    <select name="bulan" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}" {{ $bulanParse->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select name="tahun" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                        @foreach(range(date('Y'), 2023) as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- PROGRESS BAR TOTAL PENCAPAIAN --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold text-secondary mb-2">Total Pencapaian: {{ $totalNilaiHasil ?? 0 }}/100</h5>
                        <div class="progress" style="height: 25px; background-color: #e9ecef; border-radius: 5px;">
                            <div class="progress-bar fw-bold" role="progressbar" 
                                style="width: {{ $totalNilaiHasil ?? 0 }}%; background-color: #dc3545; font-size: 14px;" 
                                aria-valuenow="{{ $totalNilaiHasil ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $totalNilaiHasil ?? 0 }}%
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABEL PENILAIAN UTAMA --}}
                <div class="card shadow border-0 mb-4">
                    <div class="card-header text-white text-center fw-bold" style="background-color: #00c0ef;">
                        PENILAIAN HASIL cs
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0 text-center align-middle">
                            <thead style="background-color: #ffed8b;">
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
                                {{-- 1. Penjualan & Omset --}}
                                <tr>
                                    <td>1</td>
                                    <td class="text-start">Penjualan & Omset</td>
                                    <td class="text-start">Target Rp 50 juta/bulan</td>
                                    <td>40%</td>
                                    <td>Rp {{ number_format($totalOmset ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $nilaiOmset ?? 0 }}</td>
                                </tr>
                                {{-- 2. Closing Paket --}}
                                <tr>
                                    <td>2</td>
                                    <td class="text-start">Closing Paket</td>
                                    <td class="text-start">Target 1 closing paket per bulan</td>
                                    <td>20%</td>
                                    <td>{{ $closingPaket ?? 0 }} peserta</td>
                                    <td>{{ $nilaiClosingPaket ?? 0 }}</td>
                                </tr>
                                {{-- 3. Database Baru --}}
                                <tr>
                                    <td>3</td>
                                    <td class="text-start">Database Baru</td>
                                    <td class="text-start">Target 50 database baru</td>
                                    <td>20%</td>
                                    <td>{{ $databaseBaru ?? 0 }}</td>
                                    <td>{{ $nilaiDatabaseBaru ?? 0 }}</td>
                                </tr>
                                {{-- 4. Penilaian Atasan --}}
                                @php
                                    $manualSum = isset($manual) ? ($manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi) : 0;
                                @endphp
                                <tr>
                                    <td>4</td>
                                    <td class="text-start">Penilaian Atasan</td>
                                    <td class="text-start">Total Skor Kualitatif (Max 500)</td>
                                    <td>20%</td>
                                    <td>{{ $manualSum }}</td>
                                    <td>{{ $nilaiManualPart ?? 0 }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #dff0d8;">
                                    <td colspan="5" class="text-start fw-bold ps-4">TOTAL NILAI</td>
                                    <td class="fw-bold">{{ $totalNilaiHasil ?? 0 }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- STATUS BOX & LEGEND --}}
                <div class="card shadow border-0 p-4 mb-4">
                    
                    {{-- Dinamic Status Box --}}
                    <div id="statusBoxContainer" class="p-3 text-center text-white fw-bold fs-4 mb-3" 
                         style="border-radius: 5px; background-color: #dc3545;">
                         Underperformance ({{ $totalNilaiHasil ?? 0 }})
                    </div>

                    {{-- Motivasi Text --}}
                    <div class="d-flex align-items-start mb-4">
                        <i class="fas fa-comment-dots fa-lg me-2 mt-1" style="color: #aaa;"></i>
                        <em id="motivasiTextInline" style="color: #555;">
                            Ayo bangkit! Kamu belum terlambat untuk mengejar.
                        </em>
                    </div>

                    <h5 class="fw-bold mb-3">Keterangan Skala Nilai</h5>
                    <div class="table-responsive">
                        <table class="table text-center text-white fw-bold mb-0">
                            <thead style="background-color: #2c3e50;">
                                <tr>
                                    <th>Rentang Nilai</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background-color: #008000;">
                                    <td>> 100</td>
                                    <td>Sangat Baik</td>
                                </tr>
                                <tr style="background-color: #00ca00;">
                                    <td>80 – 99</td>
                                    <td>Baik</td>
                                </tr>
                                <tr style="background-color: #ffe600; color: #333;">
                                    <td>60 – 79</td>
                                    <td>Cukup</td>
                                </tr>
                                <tr style="background-color: #ff9900;">
                                    <td>40 – 59</td>
                                    <td>Pembinaan</td>
                                </tr>
                                <tr style="background-color: #dc3545;">
                                    <td>< 40</td>
                                    <td>Underperformance</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- HISTORY SECTION --}}
                <h4 class="fw-bold text-secondary mb-3">G. HISTORY KINERJA PER BULAN</h4>
                
                <div class="d-flex overflow-auto pb-3" style="gap: 15px;">
                    @foreach(range(1, 12) as $m)
                        @php
                            $hVal = $historyNilai[$m] ?? 0;
                            // Tentukan warna bar kecil
                            if($hVal > 100) $cBar = '#008000';
                            elseif($hVal >= 80) $cBar = '#00ca00';
                            elseif($hVal >= 60) $cBar = '#ffe600';
                            elseif($hVal >= 40) $cBar = '#ff9900';
                            elseif($hVal > 0) $cBar = '#dc3545';
                            else $cBar = '#e9ecef';
                        @endphp
                        <div class="card shadow-sm border text-center" style="min-width: 100px;">
                            <div class="card-body p-2">
                                <div class="fw-bold text-secondary mb-2" style="font-size: 14px;">
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                                </div>
                                <div class="w-100 rounded mb-2" style="height: 6px; background-color: #eee;">
                                    <div class="h-100 rounded" style="width: 100%; background-color: {{ $cBar }};"></div>
                                </div>
                                <div class="fw-bold text-dark">{{ $hVal }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            {{-- Script to update Status Box dynamically based on Total Nilai --}}
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let total = {{ $totalNilaiHasil ?? 0 }};
                    let box = document.getElementById('statusBoxContainer');
                    let quote = document.getElementById('motivasiTextInline');
                    let bar = document.querySelector('.progress-bar');
                    
                    let bg = '#dc3545'; // Default Red
                    let label = 'Underperformance';
                    let text = 'Ayo bangkit! Kamu belum terlambat untuk mengejar.';

                    if(total > 100) {
                        bg = '#008000'; label = 'Sangat Baik';
                        text = 'Luar biasa! Konsistensi kinerjamu sangat menginspirasi!';
                    } else if (total >= 80) {
                        bg = '#00ca00'; label = 'Baik';
                        text = 'Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik.';
                    } else if (total >= 60) {
                        bg = '#ffe600'; label = 'Cukup';
                        text = 'Cukup baik, tapi masih banyak ruang untuk berkembang.';
                    } else if (total >= 40) {
                        bg = '#ff9900'; label = 'Pembinaan';
                        text = 'Jangan menyerah, ini saatnya bangkit!';
                    }

                    if(box) {
                        box.style.backgroundColor = bg;
                        box.innerText = label + ' (' + total + ')';
                        if(total >= 60 && total < 80) box.style.color = '#333'; // Dark text for yellow
                    }
                    if(quote) quote.innerText = text;
                    if(bar) {
                        bar.style.backgroundColor = bg;
                        if(total >= 60 && total < 80) bar.style.color = '#333';
                    }
                });
            </script>
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>


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
// Ambil total nilai dari backend
let totalNilaiHasil = {{ $totalNilaiHasil ?? 0 }};

// Elemen target
const box = document.getElementById("kategoriBox");
const motivasi = document.getElementById("motivasiBox");

// =============================
// SKALA & MOTIVASI
// =============================
const kategori = [
    {
        min: 100, label: "Sangat Baik", bg: "#d1f7d3", border: "#8edb92", color: "#155724",
        motivasi: [ "Luar biasa! Konsistensi kinerjamu sangat menginspirasi!" ]
    },
    {
        min: 80, label: "Baik", bg: "#e9ffd6", border: "#c8eca2", color: "#35630a",
        motivasi: [ "Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik." ]
    },
    {
        min: 60, label: "Cukup", bg: "#fff7d1", border: "#f0dc8a", color: "#8a6d00",
        motivasi: [ "Cukup baik, tapi masih banyak ruang untuk berkembang." ]
    },
    {
        min: 40, label: "Pembinaan", bg: "#ffe4d1", border: "#f3b693", color: "#7a2f00",
        motivasi: [ "Jangan menyerah, ini saatnya bangkit!" ]
    },
    {
        min: 0, label: "Underperformance", bg: "#fcd2d0", border: "#e39a96", color: "#811d1a",
        motivasi: [ "Ayo bangkit! Kamu belum terlambat untuk mengejar." ]
    }
];

if(box && motivasi) {
    let hasil = kategori.find(k => totalNilaiHasil >= k.min) || kategori[kategori.length - 1];

    box.style.background = hasil.bg;
    box.style.borderColor = hasil.border;
    box.style.color = hasil.color;
    box.innerHTML = `${hasil.label} (${totalNilaiHasil})`;

    if (hasil.label === "Pembinaan" || hasil.label === "Underperformance") {
        box.classList.add("pulse");
    }

    motivasi.innerHTML = `
        <p style="padding:12px; border-left:5px solid ${hasil.color}">
            💬 <em>${hasil.motivasi[0]}</em>
        </p>
    `;
}

// === POPUP MOTIVASI LOGIC ===
const motivasiQuotes = [
    "Kerja kerasmu hari ini adalah kesuksesanmu besok!",
    "Tetap fokus, kamu sudah sangat dekat dengan target!",
    "Percaya proses, hasil terbaik sedang menunggumu!",
    "Sedikit lagi! Kamu pasti bisa!",
    "Lakukan yang terbaik, Tuhan yang menyempurnakan!",
    "Jangan menyerah, kegagalan adalah awal dari keberhasilan!",
    "Setiap langkah kecil membawamu lebih dekat ke tujuan.",
    "Jadilah versi terbaik dari dirimu setiap hari.",
    "Tantangan adalah peluang untuk tumbuh.",
    "Sukses tidak datang dari apa yang kamu lakukan sesekali, tapi apa yang kamu lakukan secara konsisten."
];

function tampilMotivasi() {
    // Pilih quote acak
    const quote = motivasiQuotes[Math.floor(Math.random() * motivasiQuotes.length)];
    const motivasiTextElement = document.getElementById('motivasiText');
    
    if(motivasiTextElement) {
        motivasiTextElement.innerText = '"' + quote + '"';
        
        document.getElementById('popupOverlay').style.display = 'block';
        document.getElementById('motivasiPopup').style.display = 'block';
    }
}

function tutupMotivasi() {
    document.getElementById('popupOverlay').style.display = 'none';
    document.getElementById('motivasiPopup').style.display = 'none';
}

// Muncul otomatis setelah 1.5 detik jika halaman baru dimuat
// Bisa tambahkan logic session storage jika ingin muncul sekali per sesi
setTimeout(tampilMotivasi, 1500);

</script>

