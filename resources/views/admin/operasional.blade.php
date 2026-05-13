@extends('layouts.masteradmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    .op-card {
        border-radius: 15px !important;
        border: none !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
    }

    .op-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }

    .op-card::before {
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

    .op-card:hover::before {
        left: 100%;
    }

    .op-card-title {
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: #ffffff;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    .op-card-text {
        color: rgba(255,255,255,0.9) !important;
        font-size: 0.9rem;
        margin-top: 10px;
    }

    .op-card-icon {
        opacity: 0.4;
        transition: all 0.3s;
        filter: drop-shadow(0 0 5px rgba(255,255,255,0.3));
    }

    .op-card:hover .op-card-icon {
        opacity: 0.8;
        transform: rotate(10deg) scale(1.1);
    }
    
    .stat-card {
        border-radius: 12px;
        border-left: 5px solid;
    }
</style>

<div class="container-fluid px-4 pb-5 pt-4">
    <!-- MAIN MENU CARDS -->
    <h5 class="fw-bold text-dark mb-4 px-2" style="border-left: 5px solid #f6c23e;">
        <i class="fas fa-cogs me-2 text-warning"></i> MANAJEMEN OPERASIONAL
    </h5>
    
    <div class="row g-4">
        <!-- Inventaris Kantor -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card op-card h-100 shadow border-0" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="op-card-title">Inventaris Kantor</div>
                                <div class="op-card-text">Manajemen aset, perbaikan fasilitas, dan stok perlengkapan kantor.</div>
                            </div>
                            <div class="op-card-icon">
                                <i class="fas fa-building fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Kebutuhan Kelas MBC -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card op-card h-100 shadow border-0" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="op-card-title">Kebutuhan Kelas MBC</div>
                                <div class="op-card-text">Persiapan logistik, modul, dan perlengkapan untuk kelas MBC.</div>
                            </div>
                            <div class="op-card-icon">
                                <i class="fas fa-chalkboard-teacher fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Kebutuhan M1T -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="#" class="text-decoration-none h-100 d-block">
                <div class="card op-card h-100 shadow border-0" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="op-card-title">Kebutuhan M1T</div>
                                <div class="op-card-text">Manajemen stok buku, seragam, dan kebutuhan operasional M1T.</div>
                            </div>
                            <div class="op-card-icon">
                                <i class="fas fa-user-graduate fa-3x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
