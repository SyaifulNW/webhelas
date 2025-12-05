@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fa-solid fa-chart-line me-2"></i>
            Marketing Performance Dashboard - Helas Corporation
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
                style="background: linear-gradient(135deg, #FF922B, #FFA94D); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-users fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Total Leads</h6>
                    <h2 class="fw-bold mb-0">18</h2>
                </div>
            </div>
        </div>

        {{-- Jumlah Program Kerja --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #63E6BE, #38D9A9); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-briefcase fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Jumlah Program Kerja</h6>
                    <h2 class="fw-bold mb-0">20</h2>
                </div>
            </div>
        </div>

        {{-- Aktif --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #4DABF7, #228BE6); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-bolt fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Aktif</h6>
                    <h2 class="fw-bold mb-0">10</h2>
                </div>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-lg rounded-4 card-hover"
                style="background: linear-gradient(135deg, #74C0FC, #66D9E8); color:white;">
                <div class="card-body py-4">
                    <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                    <h6 class="fw-semibold text-light mb-1">Selesai</h6>
                    <h2 class="fw-bold mb-0">10</h2>
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
</style>
@endsection
