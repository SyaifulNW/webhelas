@extends('layouts.masteradmin')

@section('content')
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap 5 JS Bundle (termasuk Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid py-4">

    {{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold text-primary mb-0">
        <i class="fa-solid fa-cart-shopping me-2"></i> Dashboard Penjualan
    </h2>

    <form action="{{ route('penjualan.index') }}" method="GET" 
          class="d-flex align-items-center bg-light px-3 py-2 rounded shadow-sm">
        <label for="bulan" class="me-2 fw-semibold text-secondary mb-0">
            <i class="fa-solid fa-calendar-alt me-1 text-primary"></i> Filter Bulan:
        </label>
        &nbsp;
        <select name="bulan" id="bulan" 
                class="form-select form-select-sm border-primary fw-semibold text-primary me-3"
                style="width: 140px;" onchange="this.form.submit()">
            @foreach(range(1, 12) as $m)
                @php
                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                @endphp
                <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                    {{ $monthName }}
                </option>
            @endforeach
        </select>

        <!--<span class="badge bg-success shadow-sm px-3 py-2 text-white">-->
        <!--    <i class="fa-solid fa-clock me-1"></i> -->
        <!--    {{ now()->translatedFormat('F Y') }}-->
        <!--</span>-->
    </form>
</div>



{{-- Row: KPI Utama --}}
<div class="row g-3 mb-4">
    @php
        $metrics = [
            [
                'id' => 'bulanan',
                'title' => 'Total Penjualan Bulanan',
                'value' => $totalBulanan ?? 0,
                'desc' => 'Bulan ini',
                'details' => [
                    ['SMI', $penjualanSMI ?? 0],
                    ['MBC', $penjualanMBC ?? 0],
                    ['Sekolah Kaya', $penjualanSekolah ?? 0],
                ],
            ],
            [
                'id' => 'tahunan',
                'title' => 'Total Penjualan Tahunan',
                'value' => $totalTahunan ?? 0,
                'desc' => '+15% YoY',
                'details' => [
                    ['SMI', $penjualanSMI_Tahun ?? 0],
                    ['MBC', $penjualanMBC_Tahun ?? 0],
                    ['Sekolah Kaya', $penjualanSekolah_Tahun ?? 0],
                ],
            ],
            [
                'id' => 'harian',
                'title' => 'Rata-rata Penjualan / Hari',
                'value' => $rataHarian ?? 0,
                'desc' => 'Bulan ini',
                'details' => [
                    ['SMI', $penjualanSMI_Hari ?? 0],
                    ['MBC', $penjualanMBC_Hari ?? 0],
                    ['Sekolah Kaya', $penjualanSekolah_Hari ?? 0],
                ],
            ],
            [
                'id' => 'pelanggan',
                'title' => 'Total Pelanggan Aktif',
                'value' => $totalPelangganAktif ?? 0,
                'desc' => ($pelangganBaru ?? 0) . ' pelanggan baru',
                'details' => [
                    ['SMI', $pelangganSMI ?? 0],
                    ['MBC', $pelangganMBC ?? 0],
                    ['Sekolah Kaya', $pelangganSekolah ?? 0],
                ],
            ],
        ];
    @endphp

    @foreach ($metrics as $m)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-1">{{ $m['title'] }}</h6>
                    <h4 class="fw-bold text-primary">
                        @if(str_contains($m['title'], 'Pelanggan'))
                            {{ number_format($m['value']) }}
                        @else
                            Rp {{ number_format($m['value'], 0, ',', '.') }}
                        @endif
                    </h4>
                    <small class="{{ str_contains($m['desc'], '+') ? 'text-success' : '' }}">{{ $m['desc'] }}</small>
                    <br>
                    <button class="btn btn-sm btn-outline-primary mt-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modal{{ ucfirst($m['id']) }}">
                        Detail
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Modal Detail per KPI --}}
@foreach ($metrics as $m)
<div class="modal fade" id="modal{{ ucfirst($m['id']) }}" tabindex="-1" aria-labelledby="modal{{ ucfirst($m['id']) }}Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modal{{ ucfirst($m['id']) }}Label">
          Detail {{ $m['title'] }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr>
              <th>Unit Usaha</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($m['details'] as $detail)
                <tr>
                    <td>{{ $detail[0] }}</td>
                    <td>
                        @if(str_contains($m['title'], 'Pelanggan'))
                            {{ number_format($detail[1]) }}
                        @else
                            Rp {{ number_format($detail[1], 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endforeach




    {{-- Row: Target & Grafik --}}
    <div class="row g-3 mb-4">
        
<div class="col-lg-6">
  <div class="card border-0 shadow-xl h-100 performance-card">
    <div class="card-header text-white fw-bold d-flex align-items-center justify-content-between">
      <span><i class="fas fa-chart-line me-2"></i>Target vs Realisasi (Performance Meter)</span>
      <span class="badge bg-light text-primary fs-6 px-3">{{ $persentaseCapaian ?? 0 }}%</span>
    </div>

    <div class="card-body">
      <p class="mb-2 fs-5 fw-semibold text-dark">
        🎯 <span class="text-secondary">Target Bulan Ini:</span> 
        <span class="text-dark fw-bold highlight-text">Rp {{ number_format($targetBulanan ?? 0, 0, ',', '.') }}</span>
      </p>

      <!-- Progress Bar -->
      <div class="progress rounded-pill mb-3 progress-glow" style="height: 32px;">
        <div 
          class="progress-bar progress-animated fw-bold text-center fs-6"
          role="progressbar"
          style="
            width: {{ $persentaseCapaian ?? 0 }}%;
            color: #fff;
            background: 
              @if(($persentaseCapaian ?? 0) < 40) linear-gradient(90deg,#ff4d4f,#ff7875);
              @elseif(($persentaseCapaian ?? 0) < 70) linear-gradient(90deg,#ffc107,#ffdd57);
              @elseif(($persentaseCapaian ?? 0) < 100) linear-gradient(90deg,#007bff,#00bfff);
              @else linear-gradient(90deg,#28a745,#00e676);
              @endif;
            animation: progressAnimation 1.8s ease-out;">
          {{ $persentaseCapaian ?? 0 }}%
        </div>
      </div>

      <p class="fs-5 fw-semibold text-dark">
        💰 <span class="text-secondary">Realisasi:</span> 
        <span class="text-success fw-bold highlight-text">Rp {{ number_format($realisasi ?? 0, 0, ',', '.') }}</span>
      </p>

      <!-- Caption Animatif -->
      <div class="status mt-4 text-center popup-caption">
        @if(($persentaseCapaian ?? 0) < 40)
          <div class="caption text-danger bg-light-danger">
            <i class="fas fa-hourglass-start me-2"></i>
            <small>Ayo semangat! Masih jauh, tapi setiap langkah berarti 💪</small>
          </div>
        @elseif(($persentaseCapaian ?? 0) < 70)
          <div class="caption text-warning bg-light-warning">
            <i class="fas fa-battery-half me-2"></i>
            <small>Kamu sudah di jalur yang benar! Tingkatkan terus ⚡</small>
          </div>
        @elseif(($persentaseCapaian ?? 0) < 100)
          <div class="caption text-primary bg-light-primary">
            <i class="fas fa-bolt me-2"></i>
            <small>Target hampir tercapai 🔥 Teruskan semangatmu!</small>
          </div>
        @else
          <div class="caption text-success bg-light-success">
            <i class="fas fa-trophy me-2"></i>
            <small>Selamat! Target tercapai 🎉 Hebat!</small>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<style>
  .performance-card {
    border-radius: 1.2rem;
    overflow: hidden;
    background-color: #fff;
    transition: all 0.4s ease;
  }

  .performance-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 14px 30px rgba(0, 114, 114, 0.25);
  }

  .performance-card .card-header {
    background: linear-gradient(90deg, #007272, #00bfa5);
    border-bottom: none;
    font-size: 1.1rem;
  }

  /* Highlighted numbers */
  .highlight-text {
    font-size: 1.15rem;
    letter-spacing: 0.5px;
  }

  /* Progress bar with glow */
  .progress {
    background-color: #e9f9f3;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
  }

  .progress-glow .progress-bar {
    box-shadow: 0 0 15px rgba(0,0,0,0.15);
  }

  @keyframes progressAnimation {
    from { width: 0; }
    to { width: var(--progress-width, 100%); }
  }

  /* Caption popup */
  .popup-caption .caption {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 14px;
    font-weight: 600;
    animation: popupFade 0.9s ease-out;
    position: relative;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
  }

  .bg-light-danger { background-color: #ffe6e6; }
  .bg-light-warning { background-color: #fff5e6; }
  .bg-light-primary { background-color: #e6f3ff; }
  .bg-light-success { background-color: #e6fff1; }

  @keyframes popupFade {
    0% { opacity: 0; transform: translateY(20px) scale(0.9); }
    60% { opacity: 1; transform: translateY(-5px) scale(1.05); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
  }

  .popup-caption .caption::before {
    content: "";
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 8px 8px 0 8px;
    border-style: solid;
    border-color: #fff transparent transparent transparent;
  }
</style>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>




<!-- FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>








        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-bold">
                    Grafik Pertumbuhan Penjualan Bulanan
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Row: Kelas & Database --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary fw-bold text-white">Kelas Terlaris & Tidak Laris</div>
                <div class="card-body">
                    <table class="table table-bordered align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kelas</th>
                                <th>Penjualan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelas as $item)
                                <tr>
                                    <td>{{ $item['nama_kelas'] }}</td>
                                    <td>{{ number_format($item['penjualan']) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($item['status'] === 'Laris') bg-success
                                            @elseif($item['status'] === 'Sedang') bg-warning text-dark
                                            @else bg-danger @endif">
                                            {{ $item['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted fst-italic">Belum ada data kelas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white fw-bold">Kontribusi Sumber Database</div>
                <div class="card-body">
                    <canvas id="sourceChart" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Row: Penjualan & Komisi --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary fw-bold text-white">Penjualan Per CS</div>
                <div class="card-body">
                    <table class="table table-bordered text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama CS</th>
                                <th>Penjualan</th>
                                <th>Target</th>
                                <th>Realisasi</th>
                                <th>Conversion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesData as $sales)
                                <tr>
                                    <td>{{ $sales['nama'] }}</td>
                                    <td>{{ $sales['penjualan'] }}</td>
                                    <td>{{ $sales['target'] }}</td>
                                    <td>{{ $sales['realisasi'] }}%</td>
                                    <td>{{ $sales['conversion_rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted fst-italic">Data penjualan belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary fw-bold text-white">Komisi & Bonus</div>
                <div class="card-body">
                    <table class="table table-bordered text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama CS</th>
                                <th>Komisi</th>
                                <th>Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesData as $sales)
                                <tr>
                                    <td>{{ $sales['nama'] }}</td>
                                    <td>Rp {{ number_format($sales['komisi'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($sales['bonus'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted fst-italic">Belum ada data komisi.</td>
                                </tr>
                            @endforelse
                            @if(!empty($salesData))
                            <tr class="table-info fw-bold">
                                <td>Total</td>
                                <td>Rp {{ number_format($totalKomisi ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($totalBonus ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Row: Pelanggan --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pelanggan Baru vs Lama</h6>
                    <canvas id="customerChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Repeat Order Rate</h6>
                    <h3 class="fw-bold text-success mb-0">{{ $repeatOrderRate ?? 0 }}%</h3>
                    <small>Pelanggan melakukan pembelian ulang</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Lifetime Value (LTV)</h6>
                    <h3 class="fw-bold text-primary mb-0">Rp {{ number_format($ltv ?? 0, 0, ',', '.') }}</h3>
                    <small>Nilai rata-rata pelanggan</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifikasi Target --}}
    <div class="alert {{ ($persentaseCapaian ?? 0) < 100 ? 'alert-warning' : 'alert-success' }} mt-4">
        {!! $notifikasi ?? 'Data belum tersedia.' !!}
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const salesCtx = document.getElementById('salesChart');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($penjualanBulanan['labels'] ?? []) !!},
        datasets: [{
            label: 'Penjualan',
            data: {!! json_encode($penjualanBulanan['data'] ?? []) !!},
            borderWidth: 2,
            borderColor: '#007bff',
            fill: true,
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
        }]
    }
});

const sourceCtx = document.getElementById('sourceChart');
new Chart(sourceCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($kontribusiDatabase ?? [])) !!},
        datasets: [{
            data: {!! json_encode(array_values($kontribusiDatabase ?? [])) !!},
            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6610f2']
        }]
    }
});

const customerCtx = document.getElementById('customerChart');
new Chart(customerCtx, {
    type: 'pie',
    data: {
        labels: ['Baru', 'Lama'],
        datasets: [{
            data: [{{ $pelangganBaru ?? 0 }}, {{ $pelangganLama ?? 0 }}],
            backgroundColor: ['#17a2b8', '#6c757d']
        }]
    }
});
</script>
@endsection
