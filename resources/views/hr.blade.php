@extends('layouts.masteradmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


<style>
    .hr-card {
        border-radius: 15px !important;
        border: none !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
    }

    .hr-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }

    .hr-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            120deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: all 0.6s;
    }

    .hr-card:hover::before {
        left: 100%;
    }

    .hr-card-title {
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: #ffffff;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    .hr-card-text {
        color: rgba(255,255,255,0.9) !important;
        font-size: 0.9rem;
        margin-top: 10px;
    }

    .hr-card-icon {
        opacity: 0.4;
        transition: all 0.3s;
        filter: drop-shadow(0 0 5px rgba(255,255,255,0.3));
    }

    .hr-card:hover .hr-card-icon {
        opacity: 0.8;
        transform: rotate(10deg) scale(1.1);
    }
</style>

<div class="container-fluid px-4 pb-5">

    <!-- HEADER -->
    <h3 class="mb-4 py-3 fw-bold text-dark text-center" style="letter-spacing: 1px; border-bottom: 2px dashed #ddd;">
        <i class="fas fa-users-cog me-2 text-success"></i> PERSONALIA (HRD) HELAS
    </h3>

    <div class="row g-4 mt-2">
        <!-- DATA KARYAWAN -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card hr-card h-100 shadow border-0" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="hr-card-title">Data Karyawan</div>
                                <div class="hr-card-text">Kelola profil, jabatan, divisi, kontrak, dan status karyawan.</div>
                            </div>
                            <div class="hr-card-icon">
                                <i class="fas fa-users fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- ABSENSI -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card hr-card h-100 shadow border-0" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="hr-card-title">Absensi & Izin</div>
                                <div class="hr-card-text">Pantau absensi harian, izin, sakit, cuti dan laporan bulanan.</div>
                            </div>
                            <div class="hr-card-icon">
                                <i class="fas fa-calendar-check fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- REKAP LEMBUR -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card hr-card h-100 shadow border-0" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="hr-card-title">Rekap Lembur</div>
                                <div class="hr-card-text">Kelola jam kerja tambahan dan rekapitulasi lembur akurat.</div>
                            </div>
                            <div class="hr-card-icon">
                                <i class="fas fa-clock fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- KPI / PENILAIAN -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card hr-card h-100 shadow border-0" style="background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="hr-card-title">Penilaian KPI</div>
                                <div class="hr-card-text">Penilaian kinerja staf, CS, dan evaluasi capaian bulanan.</div>
                            </div>
                            <div class="hr-card-icon">
                                <i class="fas fa-star fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- PAYROLL -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card hr-card h-100 shadow border-0" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="hr-card-title">Payroll / Gaji</div>
                                <div class="hr-card-text">Proses penggajian, insentif, THR, dan laporan keuangan SDM.</div>
                            </div>
                            <div class="hr-card-icon">
                                <i class="fas fa-money-check-alt fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
