@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid pb-5">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-5 mt-4">
        <div>
            <h1 class="h2 mb-1 text-gray-900 font-weight-bold">Performa Dashboard Produksi</h1>
            <p class="text-muted mb-0">Ikhtisar penyelesaian seluruh tugas dan inisiatif.</p>
        </div>
        <div class="d-none d-sm-inline-block">
            <div class="dropdown">
                <button class="btn btn-white shadow-sm border-0 px-4 py-2 rounded-lg font-weight-bold dropdown-toggle" type="button">
                    <i class="fas fa-calendar-alt text-primary me-2"></i> {{ date('F Y') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row g-4">
        <!-- 1. JUMLAH TASK SELESAI -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-done">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-shape bg-soft-success rounded-circle">
                            <i class="fas fa-check-double text-success fa-lg"></i>
                        </div>
                        <div class="percent-badge bg-soft-success text-success small font-weight-bold">
                             DONE
                        </div>
                    </div>
                    <h2 class="h1 font-weight-bold mb-1 text-gray-800">{{ $stats['done'] }}</h2>
                    <p class="text-muted font-weight-bold small mb-0">TASK SELESAI</p>
                </div>
                <div class="stats-progress progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <!-- 2. JUMLAH TASK ON PROSES -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-progress">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-shape bg-soft-warning rounded-circle">
                            <i class="fas fa-spinner text-warning fa-lg fa-spin-slow"></i>
                        </div>
                        <div class="percent-badge bg-soft-warning text-warning small font-weight-bold text-uppercase">
                             ON PROCESS
                        </div>
                    </div>
                    <h2 class="h1 font-weight-bold mb-1 text-gray-800">{{ $stats['progress'] }}</h2>
                    <p class="text-muted font-weight-bold small mb-0">TASK SEDANG BERJALAN</p>
                </div>
                <div class="stats-progress progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <!-- 3. JUMLAH TASK OVERDUE -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-overdue">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-shape bg-soft-danger rounded-circle">
                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                        </div>
                        <div class="percent-badge bg-soft-danger text-danger small font-weight-bold">
                             OVERDUE
                        </div>
                    </div>
                    <h2 class="h1 font-weight-bold mb-1 text-gray-800">{{ $stats['overdue'] }}</h2>
                    <p class="text-muted font-weight-bold small mb-0">TASK TERLAMBAT</p>
                </div>
                <div class="stats-progress progress" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <!-- 4. JUMLAH SELURUH TASK -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-total">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-shape bg-soft-info rounded-circle">
                            <i class="fas fa-layer-group text-info fa-lg"></i>
                        </div>
                        <div class="percent-badge bg-soft-info text-info small font-weight-bold">
                             TOTAL
                        </div>
                    </div>
                    <h2 class="h1 font-weight-bold mb-1 text-gray-800">{{ $stats['total'] }}</h2>
                    <p class="text-muted font-weight-bold small mb-0">DAFTAR SELURUH TASK</p>
                </div>
                <div class="stats-progress progress" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visualization of Progress -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-xl overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <h5 class="m-0 font-weight-bold text-gray-800 d-flex align-items-center">
                        <i class="fas fa-chart-pie text-primary me-2"></i> Tingkat Penyelesaian Keseluruhan
                    </h5>
                </div>
                <div class="card-body px-4 pb-5">
                    @php
                        $percentage = $stats['total'] > 0 ? round(($stats['done'] / $stats['total']) * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-end justify-content-between mb-2">
                        <div>
                            <span class="display-4 font-weight-bold text-gray-900">{{ $percentage }}%</span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small font-weight-bold d-block">PROGRES SELESAI</span>
                            <span class="h6 font-weight-bold">{{ $stats['done'] }} / {{ $stats['total'] }} Task</span>
                        </div>
                    </div>
                    <div class="progress rounded-pill shadow-inner" style="height: 24px;">
                        <div class="progress-bar bg-gradient-success progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: {{ $percentage }}%" 
                             aria-valuenow="{{ $percentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                             {{ $percentage }}%
                        </div>
                    </div>
                    
                    <div class="row mt-5 text-center g-3">
                        <div class="col-md-3">
                            <a href="{{ route('programkerja.index') }}" class="btn btn-primary w-100 py-3 rounded-lg shadow-sm font-weight-bold">
                                <i class="fas fa-tasks me-2"></i> LIHAT PROGRAM KERJA
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('gantt.index') }}" class="btn btn-outline-primary w-100 py-3 rounded-lg shadow-sm font-weight-bold">
                                <i class="fas fa-project-diagram me-2"></i> BUKA GANTT CHART
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Themes */
    body { background-color: #f8fafc; }
    .font-weight-bold { font-weight: 700 !important; }
    .rounded-xl { border-radius: 12px !important; }
    .rounded-lg { border-radius: 10px !important; }
    .shadow-sm { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important; }
    .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.06); }
    
    .stats-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03) !important;
    }
    .stats-card:hover { 
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.125) !important;
    }

    /* Icon Shapes */
    .icon-shape {
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Soft Backgrounds */
    .bg-soft-success { background-color: rgba(46, 204, 113, 0.15); }
    .bg-soft-warning { background-color: rgba(243, 156, 18, 0.15); }
    .bg-soft-danger  { background-color: rgba(231, 76, 60, 0.15); }
    .bg-soft-info    { background-color: rgba(52, 152, 219, 0.15); }

    /* Badge styles */
    .percent-badge {
        padding: 5px 12px;
        border-radius: 50px;
        letter-spacing: 0.5px;
    }

    .bg-gradient-success {
        background: linear-gradient(90deg, #2ecc71 0%, #27ae60 100%);
    }

    .fa-spin-slow {
        animation: fa-spin 3s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(359deg); }
    }
</style>
@endsection
