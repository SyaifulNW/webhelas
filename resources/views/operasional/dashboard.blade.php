@extends('layouts.masteradmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --premium-blue: #4e73df;
        --premium-success: #1cc88a;
        --premium-warning: #f6c23e;
        --premium-dark: #2c3e50;
        --glass-bg: rgba(255, 255, 255, 0.9);
    }

    .dashboard-container {
        padding: 2rem;
        background: #f8f9fc;
        min-height: 100vh;
    }

    .welcome-card {
        background: linear-gradient(135deg, var(--premium-blue) 0%, #224abe 100%);
        color: white;
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 30px rgba(78, 115, 223, 0.2);
        position: relative;
        overflow: hidden;
    }

    .welcome-card::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .stat-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .nav-pills-premium {
        background: #fff;
        padding: 0.5rem;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        display: inline-flex;
        margin-bottom: 2rem;
    }

    .nav-pills-premium .nav-link {
        border-radius: 50px;
        padding: 0.8rem 2rem;
        color: #5a5c69;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .nav-pills-premium .nav-link.active {
        background: var(--premium-blue);
        color: white;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
    }

    .table-card {
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .table-premium thead {
        background: #f8f9fc;
        color: #4e73df;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }

    .table-premium th {
        padding: 1.25rem !important;
        border: none !important;
    }

    .table-premium td {
        padding: 1.25rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f3f9 !important;
    }

    .badge-premium {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .performance-circle {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 10px solid #f8f9fc;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .performance-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--premium-blue);
    }

    .filter-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .bg-soft-success {
        background-color: rgba(28, 200, 138, 0.1);
    }
</style>

<div class="dashboard-container">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1 class="h3 text-gray-800 font-weight-bold">Dashboard Operasional</h1>
            <p class="text-muted">Selamat datang kembali, {{ $csName }}!</p>
        </div>
        <div class="col-md-6 text-right">
            <div class="d-inline-flex align-items-center filter-card mb-0">
                <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center">
                    <label class="mr-3 mb-0 font-weight-bold text-gray-700">Periode:</label>
                    <input type="month" name="bulan" class="form-control form-control-sm mr-2" value="{{ $bulan }}" onchange="this.form.submit()" style="border-radius: 8px; border: 1.5px solid #e3e6f0;">
                </form>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="welcome-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="font-weight-bold mb-3">Hello, {{ $csName }}! 🚀</h2>
                <p class="lead mb-0" style="opacity: 0.9;">Mari kita capai target operasional terbaik di bulan {{ $namaBulan }} ini. Tetap semangat dan fokus!</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="performance-circle">
                    <span class="text-muted small font-weight-bold">Skor KPI</span>
                    <span class="performance-value">{{ round($totalNilaiHasil, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-pills-premium mb-4" id="opTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="performance-tab" data-toggle="pill" href="#performance" role="tab">
                <i class="fas fa-chart-bar mr-2"></i> Performa Kelas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="kpi-tab" data-toggle="pill" href="#kpi" role="tab">
                <i class="fas fa-star mr-2"></i> Penilaian Kinerja
            </a>
        </li>
    </ul>

    <div class="tab-content" id="opTabsContent">
        <!-- Tab 1: Performa Kelas -->
        <div class="tab-pane fade show active" id="performance" role="tabpanel">
            <div class="card table-card">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="m-0 font-weight-bold text-primary">Omset Kelas ({{ $namaBulan }} {{ $tahun }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-premium mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Kelas</th>
                                    <th>Tanggal</th>
                                    <th class="text-right">Omset</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-center">Capaian</th>
                                    <th class="text-right">Insentif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelasOmsetFiltered as $k)
                                    <tr>
                                        <td class="font-weight-bold text-dark">{{ $k['nama_kelas'] }}</td>
                                        <td class="text-muted">{{ $k['tanggal'] ? \Carbon\Carbon::parse($k['tanggal'])->format('d M Y') : '-' }}</td>
                                        <td class="text-right font-weight-bold text-success">Rp {{ number_format($k['omset'], 0, ',', '.') }}</td>
                                        <td class="text-right text-muted">Rp {{ number_format($k['target'], 0, ',', '.') }}</td>
                                        <td class="text-center" style="width: 200px;">
                                            <div class="progress mb-1" style="height: 8px; border-radius: 10px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $k['persen']) }}%" aria-valuenow="{{ $k['persen'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="small font-weight-bold text-primary">{{ $k['persen'] }}%</span>
                                        </td>
                                        <td class="text-right">
                                            @if($k['insentif'] > 0)
                                                <span class="badge badge-premium bg-soft-success text-success">
                                                    +Rp {{ number_format($k['insentif'], 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Belum ada data untuk periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Penilaian Kinerja -->
        <div class="tab-pane fade" id="kpi" role="tabpanel">
            <div class="row">
                <div class="col-xl-8">
                    <div class="card table-card">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="m-0 font-weight-bold text-primary">Riwayat Performa ({{ $tahun }})</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card table-card h-100">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="m-0 font-weight-bold text-primary">Status Bulan Ini</h5>
                        </div>
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="mb-4">
                                <h6 class="text-muted font-weight-bold mb-3">Target Pencapaian</h6>
                                <div class="h1 font-weight-bold text-primary mb-0">{{ round($totalNilaiHasil, 1) }}%</div>
                            </div>
                            <div class="alert {{ $totalNilaiHasil >= 80 ? 'alert-success' : ($totalNilaiHasil >= 60 ? 'alert-warning' : 'alert-danger') }} border-0 shadow-sm" style="border-radius: 12px;">
                                <i class="fas {{ $totalNilaiHasil >= 80 ? 'fa-check-circle' : 'fa-info-circle' }} mr-2"></i>
                                <strong>{{ $totalNilaiHasil >= 80 ? 'Performa Sangat Baik!' : ($totalNilaiHasil >= 60 ? 'Performa Cukup' : 'Butuh Peningkatan') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const historyData = @json(array_values($historyNilai));
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Skor Performa (%)',
                    data: historyData,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderWidth: 3,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            drawBorder: false,
                            color: '#f1f3f9'
                        },
                        ticks: {
                            stepSize: 20
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endsection
