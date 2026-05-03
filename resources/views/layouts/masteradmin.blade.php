<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('backend/Helas.jpg') }}" type="image/jpeg">

    <title>MBC CS | Dashboard</title>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <!-- Custom fonts -->
    <link href="{{ asset('backend/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom styles -->
    <link href="{{ asset('backend/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- jQuery WAJIB PALING ATAS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Sidebar Desktop */
        .marquee {
            width: 100%;
            overflow: hidden;
            background: linear-gradient(90deg, #1e3a8a, #2563eb);
            color: #fff;
            font-weight: bold;
            padding: 8px 0;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .marquee p {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%;
            animation: marquee 15s linear infinite;
            font-size: 20px;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #2563eb 100%);
            min-height: 100vh;
            width: 16rem !important;
            transition: all 0.3s ease-in-out;

            /* Fixed / Sticky Sidebar Desktop */
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1020;
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .sidebar.toggled {
            width: 6.5rem !important;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
            /* Sembunyikan scrollbar sidebar agar lebih rapi */
        }

        /* Sidebar Mobile */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                /* hidden default */
                width: 220px;
                height: 100vh;
                z-index: 1050;
                transition: all 0.3s ease-in-out;
            }

            .sidebar.active {
                left: 0;
                /* show when active */
            }

            #content-wrapper {
                margin-left: 0 !important;
                padding: 1rem;
            }

            .navbar {
                padding: 0.5rem 1rem;
            }

            .navbar .btn {
                font-size: 1.2rem;
            }
        }

        /* Responsive text & spacing */
        body {
            font-size: 0.95rem;
        }

        @media (max-width: 576px) {
            body {
                font-size: 0.9rem;
            }

            .sidebar-brand img {
                height: 45px;
            }
        }

        /* Topbar Marquee */
        .topbar-marquee-container {
            flex-grow: 1;
            overflow: hidden;
            white-space: nowrap;
            margin: 0 15px;
            display: flex;
            align-items: center;
        }

        .topbar-marquee-text {
            display: inline-block;
            padding-left: 100%;
            /* Animasi Gerak + Animasi Warna RGB */
            animation: topbarMarqueeAnim 30s linear infinite, rgbFlow 3s linear infinite;
            font-size: 1.6rem;
            font-weight: 900;
            letter-spacing: 2px;
            /* RGB Rainbow Gradient */
            background: linear-gradient(90deg, #ff0000, #ff8000, #ffff00, #00ff00, #0080ff, #0000ff, #8000ff, #ff0080, #ff0000);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.4));
        }

        @keyframes rgbFlow {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 100% 50%;
            }
        }

        @keyframes topbarMarqueeAnim {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /* Premium Nav Styles for Produksi */
        .nav-link-premium {
            background: rgba(255, 255, 255, 0.08) !important;
            margin: 5px 12px !important;
            border-radius: 10px !important;
            padding: 12px 15px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: flex !important;
            align-items: center !important;
            border-left: 4px solid transparent !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-link-premium:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            text-decoration: none !important;
        }

        .nav-link-premium i {
            font-size: 1.1rem !important;
            width: 25px;
            text-align: center;
        }

        .nav-link-premium span {
            font-weight: 700 !important;
            letter-spacing: 0.5px;
            font-size: 0.85rem !important;
            text-transform: uppercase;
        }

        .premium-border-warning {
            border-left-color: #ffc107 !important;
        }

        .premium-border-success {
            border-left-color: #2ecc71 !important;
        }

        .premium-border-info {
            border-left-color: #3498db !important;
        }

        .sidebar-premium-heading {
            color: rgba(255, 255, 255, 0.6) !important;
            font-weight: 800 !important;
            font-size: 0.65rem !important;
            letter-spacing: 1.5px !important;
            margin-top: 20px !important;
            margin-bottom: 8px !important;
            padding-left: 20px !important;
            text-transform: uppercase;
        }

        /* 🚀 Sidebar Navigation Box Styles (Kotak-kotak) */
        .sidebar .nav-item {
            margin: 0 10px 6px 10px !important;
            transition: all 0.2s ease-in-out;
        }

        .sidebar .nav-item .nav-link {
            background: rgba(255, 255, 255, 0.07) !important;
            border-radius: 8px !important;
            padding: 12px 15px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center;
            text-align: center;
            min-height: 48px;
        }

        /* 🔄 Dinamis: Teks muncul saat EXPAND, Ikon muncul saat MINIMIZE */
        .sidebar .nav-item .nav-link i,
        .sidebar .nav-item .nav-link .fas,
        .sidebar .nav-item .nav-link .fa-solid {
            display: none !important;
            /* Default: Sembunyikan ikon saat Expand */
            font-size: 1.1rem !important;
            margin: 0 !important;
        }

        .sidebar .nav-item .nav-link span,
        .sidebar .nav-item .nav-link strong {
            display: inline-block !important;
            /* Default: Tampilkan teks saat Expand */
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            width: 100%;
            white-space: normal;
        }

        /* ↔️ Saat Sidebar Diciutkan (Minimized / Toggled) */
        .sidebar.toggled .nav-item {
            margin: 0 5px 6px 5px !important;
        }

        .sidebar.toggled .nav-item .nav-link i,
        .sidebar.toggled .nav-item .nav-link .fas,
        .sidebar.toggled .nav-item .nav-link .fa-solid {
            display: inline-block !important;
            /* Tampilkan ikon saat Minimize */
        }

        .sidebar.toggled .nav-item .nav-link span,
        .sidebar.toggled .nav-item .nav-link strong {
            display: none !important;
            /* Sembunyikan teks saat Minimize */
        }

        .sidebar .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-decoration: none !important;
        }

        /* 🔥 Warna Aktif Rapi */
        .sidebar .nav-item.active .nav-link {
            background: #ffffff !important;
            color: #1a3c7a !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
            border: 1px solid #ffffff !important;
        }

        .sidebar .nav-item.active .nav-link i,
        .sidebar .nav-item.active .nav-link span,
        .sidebar .nav-item.active .nav-link strong {
            color: #1a3c7a !important;
        }

        .sidebar-divider {
            margin: 10px 20px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        /* 🖼️ Logo Adjustment (Maximize/Minimize) */
        .sidebar-brand-icon img {
            transition: all 0.3s ease !important;
        }

        .sidebar.toggled .sidebar-brand-icon img {
            height: 40px !important;
            /* Ukuran lebih kecil saat minimize */
        }
        /* Pulse effect for notification */
        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(231, 74, 59, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0); }
        }
        /* Pulse & Bounce effect for notification */
        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(231, 74, 59, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0); }
        }
        @keyframes notify-bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-4px);}
            60% {transform: translateY(-2px);}
        }
        @keyframes pulse-yellow {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(246, 194, 62, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 12px rgba(246, 194, 62, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(246, 194, 62, 0); }
        }
        .badge-pulse {
            animation: pulse-red 2s infinite, notify-bounce 4s infinite;
            border-radius: 50% !important;
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            font-size: 0.65rem !important;
            font-weight: 800 !important;
            border: 1.5px solid #fff !important;
            background-color: #e74a3b !important;
            color: #fff !important;
            flex-shrink: 0 !important;
            line-height: 1 !important;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("accordionSidebar");
            const toggleBtn = document.getElementById("sidebarToggleTop");

            if (toggleBtn) {
                toggleBtn.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }
        });
    </script>
</head>

<body id="page-top">
    @include('sweetalert::alert')

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @if(!request()->has('embed'))
            <ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">
                <br>
                <!-- Sidebar - Brand -->
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
                    <div class="sidebar-brand-icon"
                        style="background-color: #0000; padding: 8px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        @php
                            $userRole = strtolower(trim(Auth::user()->role));
                            $userName = Auth::user()->name;
                            $nama = $userName ?? '';
                            $namaSMI = ['Latifah', 'Tursia', 'Agus Setyo'];
                            
                            $pendingM1TCount = 0;
                            if ($userRole === 'administrator' || stripos($userName, 'Linda') !== false || stripos($userName, 'Yasmin') !== false) {
                                $pendingM1TCount = \App\Models\PesertaSmi::where(function($q) {
                                    $q->where('approval_status', 'Pending')
                                      ->orWhereNull('approval_status');
                                })
                                ->where(function($q) {
                                    $q->whereHas('closingCs', function($sq) {
                                        $sq->whereIn('role', ['reseller', 'chapter', 'agen']);
                                    })->orWhereHas('createdBy', function($sq) {
                                        $sq->whereIn('role', ['reseller', 'chapter', 'agen']);
                                    })->orWhereHas('salesPlan.createdBy', function($sq) {
                                        $sq->whereIn('role', ['reseller', 'chapter', 'agen']);
                                    });
                                })->count();
                            }
                        @endphp

                        <style>
                            .badge-pending-yellow {
                                background: linear-gradient(135deg, #f6c23e 0%, #f4b619 100%) !important;
                                color: white !important;
                                border: 2px solid #fff !important;
                                box-shadow: 0 0 15px rgba(246, 194, 62, 0.6) !important;
                                font-size: 0.75rem !important;
                                font-weight: 900 !important;
                                width: 22px !important;
                                height: 22px !important;
                                min-width: 22px !important;
                                border-radius: 50% !important;
                                display: inline-flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                animation: pulse-yellow 2s infinite, notify-bounce 3s infinite !important;
                                flex-shrink: 0 !important;
                                line-height: 1 !important;
                                position: relative;
                                top: -1px;
                            }
                        </style>

                        @if(in_array($nama, $namaSMI))

                            {{-- Logo SMI --}}
                            <img src="{{ asset('backend/logosmi1.jpg') }}" alt="SMI Logo"
                                style="height: 70px; width: auto; object-fit: contain; display: block;">
                        @else
                            {{-- Logo MBC --}}
                            <img src="{{ asset('backend/img/MBC.svg') }}" alt="MBC Logo"
                                style="height: 65px; width: auto; object-fit: contain; display: block;">
                        @endif
                    </div>
                </a>

                <!-- Divider -->
                <hr class="sidebar-divider my-0" />

                <!-- Nav Item - Dashboard -->
                {{-- Nav Item - Dashboard --}}
                {{-- Nav Item - Dashboard --}}
                @if(strtolower(Auth::user()->role) === 'administrator')
                    {{-- 1. DASHBOARD ADMIN --}}
                    @if(\App\Models\Menu::isActive('dashboard_admin'))
                        <li class="nav-item {{ request()->routeIs('administrator') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('administrator') }}" title="DASHBOARD ADMIN">
                                <i class="fas fa-fw fa-tachometer-alt"></i>
                                <span><strong>DASHBOARD ADMIN</strong></span>
                            </a>
                        </li>
                    @endif

                    {{-- 2. DATABASE CS --}}
                    @if(\App\Models\Menu::isActive('database_cs'))
                        <li class="nav-item {{ request()->routeIs('admin.database.database') && !request('view') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.database.database') }}" title="DATABASE">
                                <i class="fas fa-fw fa-database"></i>
                                <span><strong>DATABASE</strong></span>
                            </a>
                        </li>
                    @endif

                    {{-- 3. DATA PESERTA MBC --}}
                    @if(\App\Models\Menu::isActive('sales_plan'))
                        <li class="nav-item {{ request('type') == 'mbc' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.salesplan.index', ['type' => 'mbc']) }}" title="DATA PESERTA MBC">
                                <i class="fas fa-fw fa-users"></i>
                                <span><strong>DATA PESERTA MBC</strong></span>
                            </a>
                        </li>
                    @endif

                    {{-- 4. DATA PESERTA M1T --}}
                    <li class="nav-item {{ request()->routeIs('peserta-smi.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('peserta-smi.index') }}" title="Peserta M1T">
                            <i class="fas fa-fw fa-user-graduate"></i>
                            <span style="text-transform: none;"><strong>DATA PESERTA M1T</strong></span>
                            @if($pendingM1TCount > 0)
                                <span class="badge badge-pending-yellow badge-pulse ml-2">{{ $pendingM1TCount }}</span>
                            @endif
                        </a>
                    </li>


                    {{-- 6. PENILAIAN KARYAWAN --}}
                    @if(\App\Models\Menu::isActive('penilaian_karyawan'))
                        <li class="nav-item {{ request()->routeIs('admin.penilaian-cs.index') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.penilaian-cs.index') }}" title="PENILAIAN KARYAWAN">
                                <i class="fas fa-fw fa-star"></i>
                                <span><strong>PENILAIAN KARYAWAN</strong></span>
                            </a>
                        </li>
                    @endif

                    {{-- 7. MONITORING CHAPTER --}}
                    <li class="nav-item {{ (request()->routeIs('gantt.index') && request('view_role') == 'chapter') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('gantt.index', ['view_role' => 'chapter']) }}" title="MONITORING CHAPTER">
                            <i class="fas fa-fw fa-project-diagram"></i>
                            <span><strong>MONITORING CHAPTER</strong></span>
                        </a>
                    </li>

                    {{-- 8. SETTING --}}
                    @if(\App\Models\Menu::isActive('settings') && auth()->user()->name !== 'Yasmin')
                        <li class="nav-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.settings.index') }}" title="SETTING">
                                <i class="fas fa-fw fa-cog"></i>
                                <span><strong>SETTING</strong></span>
                            </a>
                        </li>
                    @endif

                    {{-- Extra Admin Menu --}}
                    @if(\App\Models\Menu::isActive('activity_cs'))
                        <li class="nav-item {{ request()->routeIs('admin.activity-cs.index') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.activity-cs.index') }}" title="ACTIVITY CS">
                                <i class="fas fa-fw fa-list-check"></i>
                                <span><strong>ACTIVITY CS</strong></span>
                            </a>
                        </li>
                    @endif

                @elseif(strtolower(Auth::user()->role) === 'marketing')
                    @if(\App\Models\Menu::isActive('dashboard_marketing'))
                        <li class="nav-item {{ request()->routeIs('marketing') ? 'active' : '' }}">
                            {{-- Dashboard untuk Marketing --}}
                            <a class="nav-link" href="{{ route('marketing') }}" title="DASHBOARD">
                                <i class="fas fa-fw fa-chart-line"></i>
                                <span>DASHBOARD</span>
                            </a>
                        </li>
                    @endif
                @elseif(strtolower(Auth::user()->role) === 'manager')
                    @if(\App\Models\Menu::isActive('dashboard_manager'))
                        <li class="nav-item {{ request()->routeIs('manager') ? 'active' : '' }}">
                            {{-- Dashboard untuk Manager --}}
                            <a class="nav-link" href="#">
                                <i class="fas fa-fw fa-briefcase"></i>
                                <span>DASHBOARD MANAGER</span>
                            </a>
                        </li>
                    @endif
                @elseif(strtolower(Auth::user()->role) === 'hrd')
                    @if(\App\Models\Menu::isActive('dashboard_hr'))
                        <li class="nav-item {{ request()->routeIs('hr') ? 'active' : '' }}">
                            {{-- Dashboard untuk HRD --}}
                            <a class="nav-link" href="{{ route('hr') }}">
                                <i class="fas fa-fw fa-briefcase"></i>
                                <span>DASHBOARD HR</span>
                            </a>
                        </li>
                    @endif
                @elseif(strtolower(trim(Auth::user()->role)) === 'advertising')
                    @if(\App\Models\Menu::isActive('dashboard_advertising'))
                        <li class="nav-item {{ request()->routeIs('advertising') ? 'active' : '' }}">
                            {{-- Dashboard untuk Advertising --}}
                            <a class="nav-link" href="{{ route('advertising') }}">
                                <i class="fas fa-fw fa-bullhorn"></i>
                                <span>DASHBOARD ADVERTISING</span>
                            </a>
                        </li>
                    @endif
                @elseif(strtolower(trim(Auth::user()->role)) !== 'produksi')
                    @if(\App\Models\Menu::isActive('dashboard_general'))
                        <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                            {{-- Dashboard default --}}
                            <a class="nav-link" href="{{ route('home') }}">
                                <i class="fas fa-fw fa-tachometer-alt"></i>
                                <span>DASHBOARD</span>
                            </a>
                        </li>
                    @endif
                @endif


                {{-- Program Kerja & Ganchart untuk Produksi --}}
                @if(strtolower(Auth::user()->role) === 'produksi')
                    <hr class="sidebar-divider d-none d-md-block">

                    <div class="sidebar-premium-heading">
                        <i class="fas fa-layer-group me-1"></i> MANAJEMEN KERJA
                    </div>

                    @if(\App\Models\Menu::isActive('program_kerja'))
                        <li class="nav-item">
                            <a class="nav-link nav-link-premium premium-border-warning" href="{{ route('programkerja.index') }}">
                                <i class="fas fa-tasks text-warning me-2"></i>
                                <span>Program Kerja</span>
                            </a>
                        </li>
                    @endif

                    @if(\App\Models\Menu::isActive('ganchart'))
                        <li class="nav-item">
                            <a class="nav-link nav-link-premium premium-border-warning" href="{{ route('gantt.index') }}">
                                <i class="fas fa-project-diagram text-warning me-2"></i>
                                <span>Ganchart</span>
                            </a>
                        </li>
                    @endif

                    <div class="sidebar-premium-heading">
                        <i class="fas fa-chart-bar me-1"></i> MONITORING & PERFORMA
                    </div>

                    <li class="nav-item {{ request()->routeIs('produksi.performance') ? 'active' : '' }}">
                        <a class="nav-link nav-link-premium premium-border-success" href="{{ route('produksi.performance') }}">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            <span>Performa Dashboard</span>
                        </a>
                    </li>

                    {{--
                    @if(\App\Models\Menu::isActive('content_plan'))
                    <li class="nav-item">
                        <a class="nav-link nav-link-premium premium-border-success"
                            href="{{ route('marketing.penilaian.kpi_sosmed') }}">
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            <span>Content Plan</span>
                        </a>
                    </li>
                    @endif
                    --}}

                    <hr class="sidebar-divider d-none d-md-block">
                @endif

                {{-- Program Kerja & Ganchart untuk Advertising --}}
                @if(in_array(strtolower(Auth::user()->role), ['advertising']))
                    @php
                        $isEkoSulis = (trim(Auth::user()->name) === 'Eko Sulis');
                    @endphp

                    @if(!$isEkoSulis && \App\Models\Menu::isActive('program_kerja'))
                        {{-- Program Kerja --}}
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('programkerja.index') }}">
                                <i class="fas fa-globe me-2"></i>
                                <span>Program Kerja</span>
                            </a>
                        </li>
                    @endif

                    @if(!$isEkoSulis && \App\Models\Menu::isActive('ganchart'))
                        {{-- Ganchart --}}
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('gantt.index') }}">
                                <i class="fas fa-project-diagram me-2"></i>
                                <span>Ganchart</span>
                            </a>
                        </li>
                    @endif

                    {{-- Ganti Program Kerja & Ganchart menjadi Daily Activity khusus Eko Sulis --}}
                    @if($isEkoSulis)
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('admin.ads-activity.index') }}">
                                <i class="fas fa-fw fa-calendar-check me-2"></i>
                                <span>ACTIVITY ADS</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('marketing') }}">
                                <i class="fas fa-fw fa-chart-line me-2"></i>
                                <span>MONITORING TIM MARKETING</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('marketing-participants.index') ? 'active' : '' }}">
                            <a class="nav-link text-white" href="{{ route('marketing-participants.index') }}">
                                <i class="fas fa-fw fa-address-card me-2"></i>
                                <span>MONITORING DATABASE MARKETING</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- Sidebar Marketing --}}
                @auth
                    @if(strtolower(Auth::user()->role) === 'marketing')
                        {{-- <ul class="navbar-nav sidebar sidebar-dark" style="background-color: #0b198f;"> --}}
                            <!-- Removed nested ul that was in original code as it might break layout, kept items inline or check if separate section needed. 
                                                                                                                                                                         Original code started a NEW ul inside the sidebar ul which is invalid HTML structure. 
                                                                                                                                                                         I will flatten this out into the existing list. -->

                            <hr class="sidebar-divider my-0">

                            @if(\App\Models\Menu::isActive('data_lead'))
                                {{-- Data Lead / Prospek --}}
                                <li
                                    class="nav-item {{ (request()->routeIs('admin.database.database') && !request()->has('view')) ? 'active' : '' }}">
                                    <a class="nav-link text-white" href="{{ route('admin.database.database') }}">
                                        <i class="fas fa-table me-2"></i>
                                        <span>Data Lead / Prospek</span>
                                    </a>
                                </li>
                            @endif


                            {{-- Database Marketing (New) --}}
                            @if(in_array(auth()->user()->name, ['Felmi', 'Nisa']) || strtolower(auth()->user()->role) === 'administrator')
                                <li class="nav-item {{ request()->routeIs('marketing-participants.index') ? 'active' : '' }}">
                                    <a class="nav-link text-white" href="{{ route('marketing-participants.index') }}">
                                        <i class="fas fa-users-rectangle me-2"></i>
                                        <span>Database Marketing</span>
                                    </a>
                                </li>
                            @endif


                            {{-- Manajemen Kerja for Felmi & Nisa --}}
                            @if(in_array(auth()->user()->name, ['Felmi', 'Nisa']))
                                <div class="sidebar-premium-heading">
                                    <i class="fas fa-layer-group me-1"></i> MANAJEMEN KERJA
                                </div>
                                <li class="nav-item {{ request()->routeIs('programkerja.index') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-premium premium-border-warning"
                                        href="{{ route('programkerja.index') }}">
                                        <i class="fas fa-tasks text-warning me-2"></i>
                                        <span>Program Kerja</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ request()->routeIs('gantt.index') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-premium premium-border-warning" href="{{ route('gantt.index') }}">
                                        <i class="fas fa-project-diagram text-warning me-2"></i>
                                        <span>Ganchart</span>
                                    </a>
                                </li>
                            @endif




                            {{-- Penilaian Kinerja (Sembunyikan jika Nisa) --}}
                            @if(auth()->user()->name !== 'Nisa')
                                <li class="nav-item {{ request()->routeIs('marketing.penilaian.index') ? 'active' : '' }}">
                                    <a class="nav-link text-white" href="{{ route('marketing.penilaian.index') }}">
                                        <i class="fas fa-fw fa-star me-2"></i>
                                        <span>Penilaian Kinerja (KPI)</span>
                                    </a>
                                </li>
                            @endif

                            {{-- KPI Sosmed (Hanya jika bukan Felmi) --}}
                            @if(auth()->user()->name !== 'Felmi')
                                <li class="nav-item {{ request()->routeIs('marketing.penilaian.kpi_sosmed') ? 'active' : '' }}">
                                    <a class="nav-link text-white" href="{{ route('marketing.penilaian.kpi_sosmed') }}">
                                        <i class="fas fa-fw fa-hashtag me-2"></i>
                                        <span>KPI Sosmed Spesialis</span>
                                    </a>
                                </li>
                            @endif

                            {{-- Daily Activity (Marketing) --}}
                            @if(stripos(auth()->user()->name ?? '', 'Felmi') === false)
                                <li class="nav-item {{ request()->routeIs('admin.dailyactivity.index') ? 'active' : '' }}">
                                    <a class="nav-link text-white" href="{{ route('admin.dailyactivity.index') }}">
                                        <i class="fas fa-fw fa-calendar-check me-2"></i>
                                        <span>Daily Activity</span>
                                    </a>
                                </li>
                            @endif
                    @endif
                @endauth



                    {{-- Sidebar ini hanya tampil jika BUKAN administrator, marketing, manager, hrd, advertising --}}
                    @if(!in_array($userRole, ['administrator', 'marketing', 'manager', 'hrd', 'advertising', 'produksi']))
                                @if(\App\Models\Menu::isActive('data_calon_peserta'))
                                    <li
                                        class="nav-item {{ (request()->routeIs('admin.database.database') && request('view') == 'me') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.database.database', ['view' => 'me']) }}">
                                            <i class="fas fa-fw fa-tachometer-alt"></i>
                                            <span><strong>DATABASE CALON PESERTA{{ $userRole === 'cs-mbc' ? '' : ' M1T' }}</strong></span>
                                        </a>
                                    </li>
                                @endif

                        @if(\App\Models\Menu::isActive('daily_activity') && !in_array($userRole, ['reseller', 'chapter']))

                            <li
                                class="nav-item {{ (request()->routeIs('admin.dailyactivity.index') || request()->routeIs('manager.penilaian-cs.index')) ? 'active' : '' }}">
                                <a class="nav-link"
                                    href="{{ auth()->user()->name === 'Agus Setyo' ? route('manager.penilaian-cs.index') : route('admin.dailyactivity.index') }}">
                                    <i class="fas fa-fw fa-tachometer-alt"></i>
                                    <span>DAILY ACTIVITY</span>
                                </a>
                            </li>
                        @endif

                        {{-- Menu Khusus Chapter & Reseller --}}
                        @if($userRole === 'chapter' || $userRole === 'reseller')

                            <li class="nav-item {{ request()->routeIs('peserta-smi.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('peserta-smi.index') }}" title="Peserta M1T">
                                    <i class="fas fa-fw fa-user-graduate"></i>
                                    <span style="text-transform: none;"><strong>Peserta M1T</strong></span>
                                    @php
                                        // For chapter/reseller, they might also want to see their pending count
                                        // But the user specifically asked for Linda & Admin.
                                        // However, if we show it here, it will be 0 for them unless they are Linda/Admin.
                                    @endphp
                                    @if($pendingM1TCount > 0)
                                        <span class="badge badge-pending-yellow badge-pulse ml-2">{{ $pendingM1TCount }}</span>
                                    @endif
                                </a>
                            </li>

                            @if($userRole === 'chapter')
                                <li class="nav-item {{ request()->routeIs('chapter.reseller.index') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('chapter.reseller.index') }}">
                                        <i class="fas fa-fw fa-users-cog"></i>
                                        <span><strong>MANAJEMEN AGEN</strong></span>
                                    </a>
                                </li>

                                <li class="nav-item {{ request()->routeIs('programkerja.index') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('programkerja.index') }}">
                                        <i class="fas fa-fw fa-tasks"></i>
                                        <span><strong>PROGRAM KERJA</strong></span>
                                    </a>
                                </li>

                                @if($userRole !== 'chapter')
                                    <li class="nav-item {{ request()->routeIs('gantt.index') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('gantt.index') }}">
                                            <i class="fas fa-fw fa-project-diagram"></i>
                                            <span><strong>GANCHART</strong></span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endif

{{-- Dompet menu removed because integrated to dashboard --}}

                    @endif


                    @if(!in_array($userRole, ['marketing', 'hrd', 'advertising', 'produksi']))
                        @if(\App\Models\Menu::isActive('sales_plan'))

                            @if($userRole == 'administrator')
                                {{-- Administrator handled above --}}
                            @else

                                {{-- SALES PLAN MBC (LAINNYA) --}}
                                @if(!in_array($userRole, ['cs-smi', 'reseller', 'chapter']) && !in_array($userName, ['Latifah', 'Tursia', 'Agus Setyo']))
                                    <li class="nav-item">
                                        <a class="nav-link {{ (request('type') == 'mbc' || (request()->has('kelas') && request('kelas') != 'Start-Up Muslim Indonesia')) ? '' : 'collapsed' }}"
                                            href="#" data-toggle="collapse" data-target="#collapseMBC"
                                            aria-expanded="{{ (request('type') == 'mbc' || (request()->has('kelas') && request('kelas') != 'Start-Up Muslim Indonesia')) ? 'true' : 'false' }}"
                                            aria-controls="collapseMBC">
                                            <i class="fas fa-fw fa-users"></i>
                                            <span><strong>DATA PESERTA MBC</strong></span>
                                        </a>
                                        <div id="collapseMBC"
                                            class="collapse {{ (request('type') == 'mbc' || (request()->has('kelas') && request('kelas') != 'Start-Up Muslim Indonesia')) ? 'show' : '' }}"
                                            aria-labelledby="headingMBC" data-parent="#accordionSidebar">
                                            <div class="bg-white py-2 collapse-inner rounded">
                                                @if(stripos($userName, 'Linda') !== false)
                                                    <a class="collapse-item {{ request('type') == 'mbc' && !request('kelas') ? 'active' : '' }}"
                                                    href="{{ route('admin.salesplan.index', ['type' => 'mbc']) }}">DATA PESERTA ALL</a>
                                                @endif

                                                <h6 class="collapse-header">Daftar Kelas MBC:</h6>

                                                @if(in_array($userName, ['Muthia']))
                                                    <a class="collapse-item {{ request('kelas') == 'Sekolah Kaya' ? 'active' : '' }}"
                                                        href="{{ route('admin.salesplan.index', ['kelas' => 'Sekolah Kaya', 'type' => 'mbc']) }}">
                                                        Sekolah Kaya
                                                    </a>
                                                @else
                                                    @foreach ($kelas as $item)
                                                        @if($item->nama_kelas != 'Sekolah Kaya' && $item->nama_kelas != 'Start-Up Muslim Indonesia')
                                                            <a class="collapse-item {{ request('kelas') == $item->nama_kelas ? 'active' : '' }}"
                                                                href="{{ route('admin.salesplan.index', ['kelas' => $item->nama_kelas, 'type' => 'mbc']) }}">
                                                                {{ $item->nama_kelas }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                {{-- SALES PLAN SMI / M1T (LAINNYA) --}}
                                @if(in_array($userRole, ['cs-smi', 'cs-mbc']) || in_array($userName, ['Latifah', 'Tursia', 'Agus Setyo', 'Linda', 'Fitra Jaya Saleh']))
                                    @if(in_array($userName, ['Yasmin', 'Linda']))
                                        <li class="nav-item {{ request('type') == 'smi' ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.salesplan.index', ['type' => 'smi', 'kelas' => 'Start-Up Muslim Indonesia']) }}" title="DATA PESERTA M1T">
                                                <i class="fas fa-fw fa-user-graduate"></i>
                                                <span style="text-transform: none;"><strong>DATA PESERTA M1T</strong></span>
                                            </a>
                                        </li>
                                    @else
                                        <li class="nav-item {{ request()->routeIs('peserta-smi.index') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('peserta-smi.index') }}" title="Peserta M1T">
                                                <i class="fas fa-fw fa-user-graduate"></i>
                                                <span style="text-transform: none;"><strong>Peserta M1T</strong></span>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            @endif

                            {{-- Setting for Reseller --}}
                            @if($userRole === 'reseller')
                                <li class="nav-item {{ request()->routeIs('reseller.setting.index') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('reseller.setting.index') }}" title="MANAJEMEN AGEN">
                                        <i class="fas fa-fw fa-users-cog"></i>
                                        <span><strong>MANAJEMEN AGEN</strong></span>
                                    </a>
                                </li>
                            @endif

                            {{-- Menu Keuangan Linda --}}
                            @if(stripos($userName, 'Linda') !== false)
                                <li
                                    class="nav-item {{ request()->routeIs(['admin.keuangan.*', 'admin.keuangan.pengajuan-anggaran']) ? 'active' : '' }}">
                                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKeuangan"
                                        aria-expanded="{{ request()->routeIs(['admin.keuangan.*', 'admin.keuangan.pengajuan-anggaran']) ? 'true' : 'false' }}"
                                        aria-controls="collapseKeuangan">
                                        <i class="fas fa-fw fa-wallet"></i>
                                        <span><strong>KEUANGAN</strong></span>
                                    </a>
                                    <div id="collapseKeuangan"
                                        class="collapse {{ request()->routeIs(['admin.keuangan.*', 'admin.keuangan.pengajuan-anggaran']) ? 'show' : '' }}"
                                        aria-labelledby="headingKeuangan" data-parent="#accordionSidebar">
                                        <div class="bg-white py-2 collapse-inner rounded">
                                            <a class="collapse-item {{ request()->routeIs('admin.keuangan.laba-rugi') ? 'active' : '' }}"
                                                href="{{ route('admin.keuangan.laba-rugi') }}">Laporan Laba Rugi</a>
                                            <a class="collapse-item {{ request()->routeIs('admin.keuangan.kas') ? 'active' : '' }}"
                                                href="{{ route('admin.keuangan.kas') }}">Kas</a>
                                            <a class="collapse-item {{ request()->routeIs('admin.keuangan.pengajuan-anggaran') ? 'active' : '' }}"
                                                href="{{ route('admin.keuangan.pengajuan-anggaran') }}">Pengajuan Anggaran</a>
                                            <a class="collapse-item {{ request()->routeIs('admin.keuangan.zakat') ? 'active' : '' }}"
                                                href="{{ route('admin.keuangan.zakat') }}">Zakat</a>
                                        </div>
                                    </div>
                                </li>
                            @endif

                        @endif
                    @endif



                    {{-- Database SMI --}}
                    @if(in_array($userRole, ['administrator']) || in_array($userName, ['Linda', 'Puput', 'Diah Putri']))
                        @if(strtolower(auth()->user()->role) !== 'administrator')
                            <li class="nav-item {{ request()->routeIs('peserta-smi.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('peserta-smi.index') }}" title="Peserta M1T">
                                    <i class="fas fa-fw fa-user-graduate"></i>
                                    <span style="text-transform: none;"><strong>Peserta M1T</strong></span>
                                    @if($pendingM1TCount > 0)
                                        <span class="badge badge-pending-yellow badge-pulse ml-2">{{ $pendingM1TCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endif


                    {{-- Database CS --}}
                    @if(strtolower(auth()->user()->role) === 'administrator')
                        @if(\App\Models\Menu::isActive('database_cs'))
                            @if(strtolower(auth()->user()->role) !== 'administrator')
                                <li
                                    class="nav-item {{ request()->routeIs('admin.database.database') && !request('view') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.database.database') }}" title="DATABASE">
                                        <i class="fas fa-fw fa-database"></i>
                                        <span><strong>DATABASE</strong></span>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endif

                    @if(auth()->user()->name === 'Agus Setyo')
                        {{-- Agus Setyo: Hanya bisa lihat Tursia dan Latifah --}}
                        <li class="nav-item {{ request()->routeIs('pembelajaran.index') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('pembelajaran.index') }}">
                                <i class="fas fa-fw fa-book"></i>
                                <span><strong>PEMBELAJARAN SISWA</strong></span>
                            </a>
                        </li>



                        @if(\App\Models\Menu::isActive('database_cs'))
                            <li class="nav-item">
                                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKoordinasi"
                                    aria-expanded="false" aria-controls="collapseKoordinasi">
                                    <i class="fas fa-fw fa-users"></i>
                                    <span><strong>DATABASE</strong></span>
                                </a>

                                <div id="collapseKoordinasi" class="collapse" aria-labelledby="headingKoordinasi"
                                    data-parent="#accordionSidebar">
                                    <div class="bg-white py-2 collapse-inner rounded">
                                        <h6 class="collapse-header text-uppercase text-secondary">Daftar Pengguna:</h6>
                                        @foreach(\App\Models\User::whereIn('name', ['Tursia', 'Latifah', 'Gunawan', 'Puput'])->orderBy('name')->get() as $user)
                                            <a class="collapse-item d-flex align-items-center justify-content-between"
                                                href="{{ route('koordinasi.show', $user->id) }}">
                                                <span>
                                                    <i class="fas fa-user-circle mr-2 text-primary"></i>
                                                    {{ $user->name }}
                                                </span>
                                                <small class="text-muted">({{ ucfirst($user->role) }})</small>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                        @endif
                    @endif

                    {{-- Administrator Handle Ends --}}






                    @if ($userRole === 'administrator' || in_array($userName, ['Linda', 'Yasmin', 'Agus Setyo']))
                        @if(\App\Models\Menu::isActive('program_kerja') && $userRole !== 'administrator')
                            {{-- Program Kerja --}}
                            <li class="nav-item {{ request()->routeIs('programkerja.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('programkerja.index') }}" title="Program Kerja">
                                    <i class="fas fa-fw fa-tasks"></i>
                                    <span>Program Kerja</span>
                                </a>
                            </li>
                        @endif
                        @if(\App\Models\Menu::isActive('ganchart') && $userRole !== 'administrator')
                            {{-- Ganchart --}}
                            <li class="nav-item {{ request()->routeIs('gantt.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('gantt.index') }}" title="Ganchart">
                                    <i class="fas fa-fw fa-project-diagram"></i>
                                    <span>Ganchart</span>
                                </a>
                            </li>
                        @endif

                        @if(\App\Models\Menu::isActive('jadwal_kelas') && auth()->user()->name !== 'Agus Setyo' && $userRole !== 'administrator')
                            {{-- Jadwal Kelas --}}
                            <li class="nav-item {{ request()->routeIs('admin.kelas.index') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.kelas.index') }}" title="JADWAL KELAS">
                                    <i class="fas fa-fw fa-calendar-alt"></i>
                                    <span>JADWAL KELAS</span>
                                </a>
                            </li>
                        @endif


                        @if(auth()->user()->name === 'Yasmin')
                            {{-- Menu Keuangan khusus Yasmin --}}
                            @if(\App\Models\Menu::isActive('keuangan'))
                                <li class="nav-item {{ request()->routeIs(['admin.keuangan.*']) ? 'active' : '' }}">
                                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKeuanganYasmin"
                                        aria-expanded="{{ request()->routeIs(['admin.keuangan.*']) ? 'true' : 'false' }}"
                                        aria-controls="collapseKeuanganYasmin" title="KEUANGAN">
                                        <i class="fas fa-fw fa-wallet"></i>
                                        <span><strong>KEUANGAN</strong></span>
                                    </a>
                                    <div id="collapseKeuanganYasmin"
                                        class="collapse {{ request()->routeIs(['admin.keuangan.*']) ? 'show' : '' }}"
                                        aria-labelledby="headingKeuangan" data-parent="#accordionSidebar">
                                        <div class="bg-white py-2 collapse-inner rounded">
                                            <a class="collapse-item {{ request()->routeIs('admin.keuangan.kas-kecil.index') ? 'active' : '' }}"
                                                href="{{ route('admin.keuangan.kas-kecil.index') }}">Kas Kecil</a>
                                            <a class="collapse-item {{ request()->routeIs('admin.keuangan.zakat') ? 'active' : '' }}"
                                                href="{{ route('admin.keuangan.zakat') }}">Zakat</a>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @endif



                        {{-- 8. DOMPET (MANAJEMEN) --}}
                        @if(($userRole === 'administrator' && false) || $userName === 'Linda') {{-- Hidden for Admin, keeping for Linda --}}
                        @php
                            $pendingWalletWD = \App\Models\WalletTransaction::where('type', 'withdrawal')->where('status', 'pending')->count();
                        @endphp
                        <li class="nav-item {{ request()->routeIs('admin.wallet.*') ? 'active' : '' }}">
                            <a class="nav-link d-flex align-items-center" href="{{ route('admin.wallet.index') }}" title="PENARIKAN DOMPET">
                                <i class="fas fa-fw fa-wallet mr-2"></i>
                                <div class="d-flex align-items-center">
                                    <span><strong>PENARIKAN DOMPET</strong></span>
                                    @if($pendingWalletWD > 0)
                                        <span class="badge badge-danger badge-pulse ml-2">{{ $pendingWalletWD }}</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                        @endif

                        {{-- Penilaian Karyawan --}}
                        @if(\App\Models\Menu::isActive('penilaian_karyawan') && $userRole !== 'administrator')
                            @if(auth()->user()->name !== 'Agus Setyo')
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="{{ route('manager.penilaian-cs.index') }}">
                                        <i class="fa-solid fa-list-user me-2"></i>
                                        <span>Penilaian Kinerja Tim</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endif

                    {{-- MENU HRD --}}
                    @if(strtolower(auth()->user()->role) === 'hrd')
                        @if(\App\Models\Menu::isActive('menu_hrd'))
                            <li class="nav-item mt-3">
                                <span class="nav-link text-uppercase fw-bold fs-5" style="color: #a8c6ff;">
                                    MENU HRD
                                </span>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-users me-2"></i> Data Karyawan</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-sitemap me-2"></i> Jabatan & Divisi</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-calendar-check me-2"></i> Absensi</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-person-walking-arrow-right me-2"></i> Izin / Sakit /
                                        Lembur</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-star-half-stroke me-2"></i> Penilaian Kinerja</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-chart-line me-2"></i> KPI</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-money-bill-wave me-2"></i> Payroll / Slip Gaji</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-umbrella-beach me-2"></i> Cuti</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-user-plus me-2"></i> Rekrutmen</strong>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <strong><i class="fa-solid fa-file-lines me-2"></i> Laporan HRD</strong>
                                </a>
                            </li>
                        @endif
                    @endif

                    @if(strtolower(auth()->user()->role) !== 'administrator' && strtolower(auth()->user()->role) !== 'produksi' && !in_array($userRole, ['reseller', 'chapter']) && stripos(auth()->user()->name, 'Linda') === false)

                        <li class="nav-item {{ request()->routeIs('admin.keuangan.pengajuan-anggaran') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.keuangan.pengajuan-anggaran') }}">
                                <i class="fas fa-fw fa-file-invoice-dollar"></i>
                                <span><strong>PENGAJUAN ANGGARAN</strong></span>
                            </a>
                        </li>
                    @endif

                    <hr class="sidebar-divider d-none d-md-block" />

                    <!-- Sidebar Toggler (Sidebar) -->
                    <div class="text-center d-none d-md-inline">
                        <button class="rounded-circle border-0" id="sidebarToggle"
                            style="background: rgba(255,255,255,0.2); color: #fff;"></button>
                    </div>
                </ul>
        @endif
            <!-- End of Sidebar -->

            <!-- Content Wrapper -->
            <div id="content-wrapper" class="d-flex flex-column">
                <!-- Main Content -->
                <div id="content">
                    @if(!request()->has('embed'))
                        <!-- Topbar -->
                        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"
                            style="position: sticky; top: 0; z-index: 1100;">
                            <!-- Sidebar Toggle (Topbar) -->
                            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                                <i class="fa fa-bars"></i>
                            </button>

                            @if(!in_array($userRole, ['chapter', 'reseller']))
                                <!-- Topbar Running Text -->
                                <div class="topbar-marquee-container d-none d-md-flex">
                                    <div class="topbar-marquee-text">
                                        @if(request()->routeIs('admin.dailyactivity.*') || request()->is('admin/dailyactivity*'))
                                            📝 JANGAN LUPA MENGISI DAILY ACTIVITY SETIAP JAM 15.00 , SEMANGAT... 💪
                                        @else
                                            ✨ SELAMAT DATANG DI HELAS CORP. SELAMAT BEKERJA, ✨
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Topbar Navbar -->
                            <ul class="navbar-nav ml-auto">
                                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                                <li class="nav-item dropdown no-arrow d-sm-none">
                                    <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-search fa-fw"></i>
                                    </a>
                                    <!-- Dropdown - Messages -->
                                    <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                        aria-labelledby="searchDropdown">
                                        <form class="form-inline mr-auto w-100 navbar-search">
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-light border-0 small"
                                                    placeholder="Search for..." aria-label="Search"
                                                    aria-describedby="basic-addon2" />
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="button">
                                                        <i class="fas fa-search fa-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </li>

                                <!-- ================== NAVBAR NOTIFIKASI (DIHAPUS SESUAI REQUEST) ================== -->
                                {{-- @if(auth()->user()->role !== 'administrator')
                                <li class="nav-item mx-1">
                                    <a class="nav-link position-relative notif-bell" href="{{ route('notifikasi.index') }}">
                                        <i class="fas fa-bell fa-lg text-primary"></i>
                                        @if(isset($notifCount) && $notifCount > 0)
                                        <span class="badge badge-pill badge-danger badge-counter pulse-badge">
                                            {{ $notifCount }}
                                        </span>
                                        @endif
                                    </a>
                                </li>
                                @endif --}}

                                <!-- ================== NAVBAR PESAN MASUK (ADMIN) ================== -->
                                @if(auth()->user()->role === 'administrator')
                                    <li class="nav-item mx-1">
                                        <a class="nav-link position-relative notif-message"
                                            href="{{ route('admin.messages.index') }}">
                                            <i class="fas fa-envelope fa-lg text-primary"></i>
                                            @if(isset($messageCount) && $messageCount > 0)
                                                <span class="badge badge-pill badge-danger badge-counter pulse-badge">
                                                    {{ $messageCount }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endif

                                <!-- ================== STYLE BADGE ================== -->
                                <style>
                                    /* Lonceng & Pesan */
                                    .notif-bell,
                                    .notif-message {
                                        display: flex;
                                        align-items: center;
                                    }

                                    .badge-counter {
                                        font-size: 0.65rem;
                                        padding: 3px 6px;
                                    }

                                    .pulse-badge {
                                        position: absolute;
                                        top: 9px;
                                        right: 6px;
                                        min-width: 18px;
                                        height: 18px;
                                        font-size: 0.7rem;
                                        padding: 0;
                                        border-radius: 50%;
                                        display: flex;
                                        justify-content: center;
                                        align-items: center;
                                        animation: pulse 1.5s infinite;
                                    }

                                    @keyframes pulse {
                                        0% {
                                            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
                                        }

                                        70% {
                                            box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
                                        }

                                        100% {
                                            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
                                        }
                                    }

                                    .notif-bell:hover i {
                                        color: #f59e0b;
                                        transform: scale(1.1);
                                        transition: 0.3s;
                                    }

                                    .notif-message:hover i {
                                        color: #2563eb;
                                        transform: scale(1.1);
                                        transition: 0.3s;
                                    }
                                </style>

                                <div class="topbar-divider d-none d-sm-block"></div>

                                <!-- Nav Item - User Information -->
                                <li class="nav-item dropdown no-arrow">
                                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="mr-2 d-none d-lg-inline d-flex align-items-center">
                                            <span class="badge badge-info px-2 py-1 shadow-sm mr-2"
                                                style="font-size: 10px; font-weight: 800;">{{ Auth::user()->role === 'reseller' ? 'AGEN' : strtoupper(Auth::user()->role) }}</span>
                                            @if(Auth::user()->chapter)
                                                <span class="badge badge-light border shadow-sm px-2 py-1 mr-2"
                                                    style="font-size: 10px; color: #5a5c69; font-weight: 700;">
                                                    CHAPTER {{ strtoupper(Auth::user()->chapter) }}
                                                </span>
                                            @endif
                                            <span
                                                class="text-gray-700 font-weight-bold small">{{ Auth::user()->name }}</span>
                                        </span>
                                        @if(Auth::user()->photo)
                                            <img class="img-profile rounded-circle" src="{{ asset(Auth::user()->photo) }}"
                                                style="object-fit: cover; width:35px; height:35px;">
                                        @else
                                            <img class="img-profile rounded-circle"
                                                src="{{ asset('backend/img/undraw_profile.svg') }}"
                                                style="width:35px; height:35px;">
                                        @endif
                                    </a>
                                    <!-- Dropdown - User Information -->
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                        aria-labelledby="userDropdown" style="z-index: 9999 !important; min-width: 160px;">
                                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                            Profile
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                            Logout
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </nav>
                    @endif
                    <!-- End of Topbar -->

                    <!-- Begin Page Content -->
                    <div class="container-fluid" @if(request()->has('embed'))
                    style="padding: 0; margin: 0; width: 100%; max-width: 100%;" @endif>
                        <!-- Isi Konten -->
                        @yield('content')
                    </div>
                    <!-- /.container-fluid -->
                </div>
                <!-- End of Main Content -->

                <!-- Footer -->
                <!-- 
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Muslim Bisnis Coaching - 2025 </span>
                    </div>
                </div>
            </footer> 
            -->
                <!-- End of Footer -->
            </div>
            <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <!--
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    -->

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Apakah anda yakin ingi Keluar ?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">X</span>
                    </button>
                </div>
                <div class="modal-body">
                    Pilih "Logout" Jika anda ingin keluar dari sistem.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">
                        Cancel
                    </button>
                    <!-- Logout Redirect Login -->
                    <a class="btn btn-primary" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery WAJIB PALING ATAS (MOVED TO HEAD) -->

    <!-- Bootstrap (harus setelah jQuery, include Popper.js) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('backend/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- SB Admin (butuh jQuery) -->
    <script src="{{ asset('backend/js/sb-admin-2.min.js') }}"></script>

    <!-- ChartJS -->
    <script src="{{ asset('backend/vendor/chart.js/Chart.min.js') }}"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/fb703282bd.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

    <script>
        $(document).ready(function () {
            $("#close").click(function () {
                $("#exampleModal").modal("hide");
            });
        });
    </script>
</body>

</html>