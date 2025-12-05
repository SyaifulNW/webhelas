@extends('layouts.masteradmin')

@section('content')
<div class="container">

    <!-- HEADER -->
    <h2 class="fw-bold mb-4 text-primary">
        <i class="fa-solid fa-user-gear me-2"></i> Dashboard HR
    </h2>

    <div class="row g-4">

        <!-- DATA KARYAWAN -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary">
                        <i class="fa-solid fa-users me-2"></i> Data Karyawan
                    </h5>
                    <p class="text-muted mb-3">Kelola data karyawan: identitas, jabatan, divisi, status kerja.</p>
                    <a href="#" class="btn btn-primary w-100">
                        Lihat Data
                    </a>
                </div>
            </div>
        </div>

        <!-- ABSENSI -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-success">
                        <i class="fa-solid fa-calendar-check me-2"></i> Absensi
                    </h5>
                    <p class="text-muted mb-3">Pantau absensi harian, izin, sakit, dan laporan bulanan.</p>
                    <a href="#" class="btn btn-success w-100">
                        Masuk
                    </a>
                </div>
            </div>
        </div>

        <!-- REKAP LEMBUR -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-warning">
                        <i class="fa-solid fa-clock me-2"></i> Rekap Lembur
                    </h5>
                    <p class="text-muted mb-3">Cek rekap lembur karyawan dengan cepat dan akurat.</p>
                    <a href="#" class="btn btn-warning w-100 text-dark">
                        Buka
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
