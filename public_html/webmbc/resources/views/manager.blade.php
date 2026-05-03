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

        {{-- Total Leads --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #FF6B6B, #FF8787); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-bullseye fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Total Leads</h6>
                    <h2 class="fw-bold mb-0">{{ $totalLeads }}</h2>
                </div>
            </div>
        </div>

        {{-- Program Aktif --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #4DABF7, #1C7ED6); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-briefcase fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Program Aktif</h6>
                    <h2 class="fw-bold mb-0">{{ $programAktif }}</h2>
                </div>
            </div>
        </div>

        {{-- Program Selesai --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #63E6BE, #20C997); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Program Selesai</h6>
                    <h2 class="fw-bold mb-0">{{ $programSelesai }}</h2>
                </div>
            </div>
        </div>

        {{-- Closing Bulan Ini --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #845EF7, #9775FA); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-money-bill-trend-up fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Closing Bulan Ini</h6>
                    <h2 class="fw-bold mb-0">{{ $closingBulanIni }}</h2>
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
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.18);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #51CF66, #37B24D);
    }
</style>
@endsection
