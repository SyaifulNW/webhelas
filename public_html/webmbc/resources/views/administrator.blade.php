@extends('layouts.masteradmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-4px);
    }

    .table th,
    .table td {
        vertical-align: middle !important;
        text-align: center;
    }

    .fw-bold {
        font-weight: 600;
    }

    .text-dark {
        color: #2c3e50 !important;
    }

    .btn-view {
        background-color: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 6px;
        text-decoration: none;
        transition: 0.3s;
    }

    .btn-view:hover {
        background-color: #218838;
    }

    .dropdown-menu a {
        color: #333;
        font-size: 14px;
    }

    .dropdown-menu a:hover {
        background-color: #f1f1f1;
    }

    .btn-group .dropdown-toggle::after {
        margin-left: 6px;
    }
</style>

<div class="container-fluid px-4">
    <h3 class="mb-4 fw-bold text-dark text-center">
        DASHBOARD ADMINISTRATOR HELAS CORPORATION
    </h3>

    {{-- === Row 1: Statistik Utama === --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4 col-6">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold text-secondary">TOTAL USER</h6>
                    <h4 class="fw-bold text-dark">{{ $totalUser ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-database fa-2x text-success mb-2"></i>
                    <h6 class="fw-bold text-secondary">TOTAL DATABASE PESERTA</h6>
                    <h4 class="fw-bold text-dark">{{ $totalDatabase ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-6">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-school fa-2x text-info mb-2"></i>
                    <h6 class="fw-bold text-secondary">JUMLAH KELAS</h6>
                    <h4 class="fw-bold text-dark">{{ $totalKelas ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>



    {{-- === Info Sistem === --}}
    <div class="card mt-4 border-0">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="fas fa-info-circle me-2"></i> INFORMASI SISTEM
        </div>
        <div class="card-body text-center text-muted">
            <p>Dashboard Administrator menampilkan data real dari database Helas Corporation.</p>
            <p>Terakhir diperbarui: {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>
</div>

{{-- Tambahkan Bootstrap JS (jika belum ada di layout) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
