@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fa-solid fa-chart-pie me-2"></i>
            Manager Performance Dashboard - Start Up Muda Indonesia
        </h4>
        <span class="badge bg-gradient-success fs-6 px-3 py-2 shadow-sm text-white">
            {{ now()->format('F Y') }}
        </span>
    </div>

    {{-- Ringkasan Utama --}}
    <div class="row g-4 mb-5">

        {{-- Total Tim Marketing --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #845EF7, #9775FA); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-users fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Total Tim Marketing</h6>
                    <h2 class="fw-bold mb-0">5</h2>
                </div>
            </div>
        </div>

        {{-- Total Leads Bulanan --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #FF922B, #FFA94D); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-bullseye fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Total Leads Bulan Ini</h6>
                    <h2 class="fw-bold mb-0">126</h2>
                </div>
            </div>
        </div>

        {{-- Program Aktif --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #4DABF7, #228BE6); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-briefcase fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Program Aktif</h6>
                    <h2 class="fw-bold mb-0">8</h2>
                </div>
            </div>
        </div>

        {{-- Program Selesai --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #63E6BE, #38D9A9); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Program Selesai</h6>
                    <h2 class="fw-bold mb-0">12</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Tim --}}
    <div class="row g-4">

        {{-- Top Performer --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 h-100">
                <div class="card-header bg-gradient-primary text-white fw-semibold rounded-top-4">
                    <i class="fa-solid fa-medal me-2"></i>Top Performer Bulan Ini
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-user-tie me-2 text-primary"></i> Andi Setiawan</span>
                            <span class="badge bg-success rounded-pill">42 Leads</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-user-tie me-2 text-primary"></i> Sinta Rahma</span>
                            <span class="badge bg-success rounded-pill">37 Leads</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-user-tie me-2 text-primary"></i> Fajar Prasetyo</span>
                            <span class="badge bg-success rounded-pill">35 Leads</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Progress Program --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 h-100">
                <div class="card-header bg-gradient-info text-white fw-semibold rounded-top-4">
                    <i class="fa-solid fa-chart-line me-2"></i>Progress Program Kerja
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold">Program A</h6>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar bg-success" style="width: 80%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Program B</h6>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar bg-info" style="width: 65%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-semibold">Program C</h6>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar bg-warning" style="width: 50%"></div>
                        </div>
                    </div>
                    <div>
                        <h6 class="fw-semibold">Program D</h6>
                        <div class="progress rounded-pill" style="height: 12px;">
                            <div class="progress-bar bg-danger" style="width: 30%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Style Tambahan --}}
<style>
    .card-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .card-hover:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #51CF66, #37B24D);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #228BE6, #4DABF7);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #15AABF, #22B8CF);
    }
</style>
@endsection
