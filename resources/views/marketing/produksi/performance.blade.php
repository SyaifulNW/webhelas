@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid pb-4">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-3">
        <div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Performa Dashboard Produksi</h1>
            <p class="text-muted small mb-0">Ikhtisar penyelesaian seluruh tugas dan inisiatif.</p>
        </div>
        <div class="d-none d-sm-inline-block">
            <div class="badge bg-white shadow-sm border px-3 py-2 rounded-lg text-dark">
                <i class="fas fa-calendar-alt text-primary me-2"></i> {{ date('F Y') }}
            </div>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row g-3">
        <!-- 1. JUMLAH TASK SELESAI -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-done">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-soft-success rounded-circle">
                            <i class="fas fa-check-double text-success"></i>
                        </div>
                        <div class="text-success small font-weight-bold">DONE</div>
                    </div>
                    <h3 class="font-weight-bold mb-0 text-gray-800">{{ $stats['done'] }}</h3>
                    <p class="text-muted small font-weight-bold mb-0">TASK SELESAI</p>
                </div>
                <div class="bg-success" style="height: 3px; width: 100%"></div>
            </div>
        </div>

        <!-- 2. JUMLAH TASK ON PROSES -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-progress">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-soft-warning rounded-circle">
                            <i class="fas fa-spinner text-warning fa-spin-slow"></i>
                        </div>
                        <div class="text-warning small font-weight-bold">PROCESS</div>
                    </div>
                    <h3 class="font-weight-bold mb-0 text-gray-800">{{ $stats['progress'] }}</h3>
                    <p class="text-muted small font-weight-bold mb-0">ON PROCESS</p>
                </div>
                <div class="bg-warning" style="height: 3px; width: 100%"></div>
            </div>
        </div>

        <!-- 3. JUMLAH TASK OVERDUE -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-overdue">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-soft-danger rounded-circle">
                            <i class="fas fa-clock text-danger"></i>
                        </div>
                        <div class="text-danger small font-weight-bold">OVERDUE</div>
                    </div>
                    <h3 class="font-weight-bold mb-0 text-gray-800">{{ $stats['overdue'] }}</h3>
                    <p class="text-muted small font-weight-bold mb-0">TERLAMBAT</p>
                </div>
                <div class="bg-danger" style="height: 3px; width: 100%"></div>
            </div>
        </div>

        <!-- 4. JUMLAH SELURUH TASK -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden stats-card card-total">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="icon-shape bg-soft-info rounded-circle">
                            <i class="fas fa-layer-group text-info"></i>
                        </div>
                        <div class="text-info small font-weight-bold">TOTAL</div>
                    </div>
                    <h3 class="font-weight-bold mb-0 text-gray-800">{{ $stats['total'] }}</h3>
                    <p class="text-muted small font-weight-bold mb-0">SEMUA TASK</p>
                </div>
                <div class="bg-info" style="height: 3px; width: 100%"></div>
            </div>
        </div>
    </div>

    <!-- Visualization of Progress -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-0">
                    <h6 class="m-0 font-weight-bold text-gray-800">
                        <i class="fas fa-chart-line text-primary me-2"></i> Progres Penyelesaian
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    @php
                        $percentage = $stats['total'] > 0 ? round(($stats['done'] / $stats['total']) * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="h2 font-weight-bold text-gray-900 mb-0">{{ $percentage }}%</span>
                        <div class="text-end text-muted small">
                             {{ $stats['done'] }} dari {{ $stats['total'] }} Task Selesai
                        </div>
                    </div>
                    <div class="progress rounded-pill" style="height: 12px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $percentage }}%" 
                             aria-valuenow="{{ $percentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    
                    <div class="row mt-4 g-2">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('programkerja.index') }}" class="btn btn-primary btn-sm w-100 py-2 rounded font-weight-bold">
                                <i class="fas fa-list me-1"></i> Program Kerja
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('gantt.index') }}" class="btn btn-outline-primary btn-sm w-100 py-2 rounded font-weight-bold">
                                <i class="fas fa-project-diagram me-1"></i> Gantt Chart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .font-weight-bold { font-weight: 700 !important; }
    .rounded-lg { border-radius: 10px !important; }
    
    .stats-card {
        transition: transform 0.2s ease;
    }
    .stats-card:hover { 
        transform: translateY(-4px);
    }

    .icon-shape {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .bg-soft-success { background-color: rgba(46, 204, 113, 0.1); }
    .bg-soft-warning { background-color: rgba(243, 156, 18, 0.1); }
    .bg-soft-danger  { background-color: rgba(231, 76, 60, 0.1); }
    .bg-soft-info    { background-color: rgba(52, 152, 219, 0.1); }

    .fa-spin-slow {
        animation: fa-spin 3s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(359deg); }
    }
</style>
@endsection
