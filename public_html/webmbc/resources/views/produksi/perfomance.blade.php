@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Performance Dashboard</h1>
            <p class="text-muted small mb-0">Statistik kinerja pribadi dan tim (Mentee)</p>
        </div>
        <div class="d-none d-sm-inline-block">
            <span class="badge badge-primary px-3 py-2 shadow-sm">
                <i class="fas fa-calendar-alt me-1"></i> {{ date('F Y') }}
            </span>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row">
        <!-- Dashboard Pribadi -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card shadow h-100 border-left-primary overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-tie me-2"></i>Dashboard Pribadi
                    </h6>
                    <span class="small text-muted">Inisiatif Saya</span>
                </div>
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Aktif</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $stats['pribadi']['aktif'] }}</div>
                                </div>
                                <div class="col-4 border-left border-right">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Selesai</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $stats['pribadi']['selesai'] }}</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $stats['pribadi']['overdue'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        @php
                            $totalPribadi = $stats['pribadi']['aktif'] + $stats['pribadi']['selesai'] + $stats['pribadi']['overdue'];
                            $percentPribadi = $totalPribadi > 0 ? round(($stats['pribadi']['selesai'] / $totalPribadi) * 100) : 0;
                        @endphp
                        <h4 class="small font-weight-bold">Penyelesaian <span class="float-right">{{ $percentPribadi }}%</span></h4>
                        <div class="progress progress-sm mb-0">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentPribadi }}%" aria-valuenow="{{ $percentPribadi }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Mentee -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card shadow h-100 border-left-info overflow-hidden">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-users me-2"></i>Grup & Mentee
                    </h6>
                    <span class="small text-muted">Monitoring Tim</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border-left border-info shadow-sm h-100">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Mentee</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['mentee']['total_orang'] }} Orang</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded border-left border-secondary shadow-sm h-100">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Grup</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $stats['total_grup'] }} Grup</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row text-center no-gutters">
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Mentee Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['mentee']['aktif'] }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Mentee Selesai</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['mentee']['selesai'] }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Mentee Overdue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['mentee']['overdue'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links / Recent Activities Placeholder -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body border-left-warning d-flex align-items-center justify-content-between py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning-light p-3 mr-3">
                            <i class="fas fa-tasks text-warning"></i>
                        </div>
                        <div>
                            <h6 class="m-0 font-weight-bold text-gray-800">Manajemen Program Kerja</h6>
                            <p class="text-muted small mb-0">Klik untuk melihat detail pekerjaan dan update inisiatif.</p>
                        </div>
                    </div>
                    <a href="{{ route('programkerja.index') }}" class="btn btn-warning btn-sm shadow-sm px-4 rounded-pill font-weight-bold">
                        Buka Program Kerja <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .font-weight-bold { font-weight: bold !important; }
    .bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .progress-sm { height: 8px; border-radius: 4px; }
    .card { transition: transform .2s; }
    .card:hover { transform: translateY(-3px); }
</style>
@endsection
