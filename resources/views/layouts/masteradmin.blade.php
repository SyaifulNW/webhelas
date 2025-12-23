<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MBC CS | Dashboard</title>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <!-- Custom fonts -->
    <link href="{{ asset('backend/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Custom styles -->
    <link href="{{ asset('backend/css/sb-admin-2.min.css') }}" rel="stylesheet">

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
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #2563eb 100%);
            min-height: 100vh;
            transition: all 0.3s ease-in-out;
        }

        /* Sidebar Mobile */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px; /* hidden default */
                width: 220px;
                height: 100vh;
                z-index: 1050;
                transition: all 0.3s ease-in-out;
            }

            .sidebar.active {
                left: 0; /* show when active */
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
        <ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">
            <br>
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
                <div class="sidebar-brand-icon" style="background-color: #0000; padding: 8px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    @php
                        $nama = Auth::user()->name ?? '';
                        $namaSMI = ['Latifah', 'Tursia', 'Agus Setyo'];
                    @endphp

                    @if(in_array($nama, $namaSMI))
                        {{-- Logo SMI --}}
                        <img src="{{ asset('backend/logosmi1.jpg') }}" alt="SMI Logo" style="height: 70px; width: auto; object-fit: contain; display: block;">
                    @else
                        {{-- Logo MBC --}}
                        <img src="{{ asset('backend/img/MBC.svg') }}" alt="MBC Logo" style="height: 65px; width: auto; object-fit: contain; display: block;">
                    @endif
                </div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0" />

            <!-- Nav Item - Dashboard -->
            {{-- Nav Item - Dashboard --}}
            @if(strtolower(Auth::user()->role) === 'administrator')
                @if(\App\Models\Menu::isActive('dashboard_admin'))
                <li class="nav-item active">
                    {{-- Dashboard untuk Administrator --}}
                    <a class="nav-link" href="{{ route('administrator') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>DASHBOARD ADMIN</span>
                    </a>
                </li>
                @endif
            @elseif(strtolower(Auth::user()->role) === 'marketing')
                @if(\App\Models\Menu::isActive('dashboard_marketing'))
                <li class="nav-item active">
                    {{-- Dashboard untuk Marketing --}}
                    <a class="nav-link" href="{{ route('marketing') }}">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>DASHBOARD</span>
                    </a>
                </li>
                @endif
            @elseif(strtolower(Auth::user()->role) === 'manager')
                @if(\App\Models\Menu::isActive('dashboard_manager'))
                <li class="nav-item active">
                    {{-- Dashboard untuk Manager --}}
                    <a class="nav-link" href="#">
                        <i class="fas fa-fw fa-briefcase"></i>
                        <span>DASHBOARD MANAGER</span>
                    </a>
                </li>
                @endif
            @elseif(strtolower(Auth::user()->role) === 'hrd')
                @if(\App\Models\Menu::isActive('dashboard_hr'))
                <li class="nav-item active">
                    {{-- Dashboard untuk HRD --}}
                    <a class="nav-link" href="{{ route('hr') }}">
                        <i class="fas fa-fw fa-briefcase"></i>
                        <span>DASHBOARD HR</span>
                    </a>
                </li>
                @endif
            @elseif(strtolower(trim(Auth::user()->role)) === 'advertising')
                <li class="nav-item active">
                    {{-- Dashboard untuk Advertising --}}
                    <a class="nav-link" href="{{ route('advertising') }}">
                        <i class="fas fa-fw fa-bullhorn"></i>
                        <span>DASHBOARD ADVERTISING</span>
                    </a>
                </li>
            @else
                @if(\App\Models\Menu::isActive('dashboard_general'))
                <li class="nav-item active">
                    {{-- Dashboard default --}}
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>DASHBOARD</span>
                    </a>
                </li>
                @endif
            @endif

            {{-- Program Kerja & Ganchart untuk Marketing & Manager --}}
            @if(in_array(strtolower(Auth::user()->role), ['advertising']))
                @if(\App\Models\Menu::isActive('program_kerja'))
                {{-- Program Kerja --}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('programkerja.index') }}">
                        <i class="fas fa-globe me-2"></i>
                        <span>Program Kerja</span>
                    </a>
                </li>
                @endif
                @if(\App\Models\Menu::isActive('ganchart'))
                {{-- Ganchart --}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('gantt.index') }}">
                        <i class="fas fa-project-diagram me-2"></i>
                        <span>Ganchart</span>
                    </a>
                </li>
                @endif

                {{-- Penilaian Karyawan --}}
                <!--<li class="nav-item">-->
                <!--    <a class="nav-link text-white" href="{{ route('manager.penilaian-cs.index') }}">-->
                <!--        <i class="fa-solid fa-list-user me-2"></i>-->
                <!--        <span>Penilaian Karyawan</span>-->
                <!--    </a>-->
                <!--</li>-->
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
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('admin.database.database') }}">
                            <i class="fas fa-table me-2"></i>
                            <span>Data Lead / Prospek</span>
                        </a>
                    </li>
                    @endif

                    @if(\App\Models\Menu::isActive('program_kerja'))
                    {{-- Program Kerja --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('programkerja.index') }}">
                            <i class="fas fa-globe me-2"></i>
                            <span>Program Kerja</span>
                        </a>
                    </li>
                    @endif

                    @if(\App\Models\Menu::isActive('ganchart'))
                    {{-- Ganchart --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('gantt.index') }}">
                            <i class="fas fa-project-diagram me-2"></i>
                            <span>Ganchart</span>
                        </a>
                    </li>
                    @endif

                    {{-- Penilaian Kinerja --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('marketing.penilaian.index') }}">
                            <i class="fas fa-fw fa-star me-2"></i>
                            <span>Penilaian Kinerja</span>
                        </a>
                    </li>
                @endif
            @endauth

            {{-- Sidebar ini hanya tampil jika BUKAN administrator, marketing, manager, hrd, advertising --}}
            @if(!in_array(strtolower(trim(Auth::user()->role)), ['administrator', 'marketing', 'manager', 'hrd', 'advertising']))
                @if(\App\Models\Menu::isActive('data_calon_peserta'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.database.database') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span><strong>DATA CALON PESERTA</strong></span>
                    </a>
                </li>
                @endif

                @if(\App\Models\Menu::isActive('daily_activity'))
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('admin.dailyactivity.index') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>DAILY ACTIVITY</span>
                    </a>
                </li>
                @endif

                @if(\App\Models\Menu::isActive('penilaian_kinerja_saya'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.penilaian.index') }}">
                        <i class="fas fa-fw fa-star"></i>
                        <span><strong>PENILAIAN KINERJA SAYA</strong></span>
                    </a>
                </li>
                @endif
            @endif

            @php
                $userName = auth()->user()->name;
            @endphp

            @php
                $userRole = strtolower(trim(Auth::user()->role));
            @endphp
            @if(!in_array($userRole, ['marketing', 'hrd', 'advertising']))
                @if(\App\Models\Menu::isActive('sales_plan'))
                {{-- Jika user adalah Fitra Jaya Saleh atau Agus Setyo --}}
                @if($userName == 'Fitra Jaya Saleh' || $userName == 'Agus Setyo')
                    <li class="nav-item {{ request()->routeIs('admin.salesplan.index') && request('kelas') == null ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.salesplan.index') }}">
                            <i class="fas fa-fw fa-users"></i>
                            <span><strong>SALES PLAN</strong></span>
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                            aria-expanded="true" aria-controls="collapseUtilities">
                            <i class="fas fa-fw fa-users"></i>
                            <span><strong>SALES PLAN</strong></span>
                        </a>
                        <div id="collapseUtilities" class="collapse {{ request()->has('kelas') ? 'show' : '' }}"
                            aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <h6 class="collapse-header">Daftar Kelas MBC:</h6>

                                {{-- Jika Nisa atau Muthia, hanya bisa lihat Sekolah Kaya --}}
                                @if(in_array($userName, ['Muthia']))
                                    <a class="collapse-item {{ request('kelas') == 'Sekolah Kaya' ? 'active' : '' }}"
                                       href="{{ route('admin.salesplan.index', ['kelas' => 'Sekolah Kaya']) }}">
                                       Sekolah Kaya
                                    </a>

                                {{-- Jika Tursia atau Latifah, hanya bisa Start-Up --}}
                        @elseif(auth()->user()->role === 'cs-smi')
    <a class="collapse-item {{ request('kelas') == 'Start-Up Muslim Indonesia' ? 'active' : '' }}"
       href="{{ route('admin.salesplan.index', ['kelas' => 'Start-Up Muslim Indonesia']) }}">
        Start-Up Muda Indonesia
    </a>

                                {{-- Selain itu, tampilkan semua kelas kecuali Sekolah Kaya & Start-Up --}}
                                @else
                                    @foreach ($kelas as $item)
                                        @if($item->nama_kelas != 'Sekolah Kaya' )
                                            <a class="collapse-item {{ request('kelas') == $item->nama_kelas ? 'active' : '' }}"
                                               href="{{ route('admin.salesplan.index', ['kelas' => $item->nama_kelas]) }}">
                                               {{ $item->nama_kelas }}
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </li>
                @endif
                @endif
            @endif

            {{-- Menu Khusus CS MBC & CS SMI --}}
            @if(in_array(strtolower(trim(Auth::user()->role)), ['cs-mbc', 'cs-smi']))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.salesplan.index', ['kelas' => 'Start-Up Muda Indonesia']) }}">
                        <i class="fas fa-fw fa-users"></i>
                        <span><strong>Data Peserta SMI</strong></span>
                    </a>
                </li>
            @endif

            {{-- Dropdown Semua Akun --}}
            @if(strtolower(auth()->user()->role) === 'administrator')
                {{-- Administrator: langsung ke halaman utama Database CS --}}
                @if(\App\Models\Menu::isActive('database_cs'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.database.database') }}">
                        <i class="fas fa-fw fa-users"></i>
                        <span><strong>DATABASE CS</strong></span>
                    </a>
                </li>
                @endif
            @elseif(auth()->user()->name === 'Agus Setyo')
                {{-- Agus Setyo: Hanya bisa lihat Tursia dan Latifah --}}
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseKoordinasi"
                       aria-expanded="false" aria-controls="collapseKoordinasi">
                        <i class="fas fa-fw fa-users"></i>
                        <span><strong>DATABASE CS</strong></span>
                    </a>

                    <div id="collapseKoordinasi" class="collapse" aria-labelledby="headingKoordinasi" data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <h6 class="collapse-header text-uppercase text-secondary">Daftar Pengguna:</h6>
                            @foreach(\App\Models\User::whereIn('name', ['Tursia', 'Latifah'])->orderBy('name')->get() as $user)
                                <a class="collapse-item d-flex align-items-center justify-content-between" href="{{ route('koordinasi.show', $user->id) }}">
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

            @if(strtolower(auth()->user()->role) === 'administrator')
                @if(\App\Models\Menu::isActive('jadwal_kelas'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.kelas.index') }}">
                        <strong><i class="fa-solid fa-chalkboard me-2"></i> JADWAL KELAS</strong> 
                    </a>
                </li>
                @endif

                @if(\App\Models\Menu::isActive('activity_cs'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.activity-cs.index') }}">
                        <strong><i class="fa-solid fa-list-check me-2"></i> ACTIVITY CS</strong> 
                    </a>
                </li>
                @endif
                @if(\App\Models\Menu::isActive('penilaian_karyawan'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.penilaian-cs.index') }}">
                        <strong><i class="fa-solid fa-list-user me-2"></i> PENILAIAN KARYAWAN</strong> 
                    </a>
                </li>
                @endif

                @if(\App\Models\Menu::isActive('penjualan'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('penjualan.index') }}">
                        <strong><i class="fa-solid fa-cart-shopping me-2"></i> PENJUALAN</strong>
                    </a>
                </li>
                @endif

                {{-- NEW SETTING MENU --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.index') }}">
                        <strong><i class="fa-solid fa-cogs me-2"></i> SETTING</strong>
                    </a>
                </li>
            @endif
            
            
   
            

            @if(in_array(Auth::user()->name, ['Linda', 'Yasmin', 'Agus Setyo']))
                @if(\App\Models\Menu::isActive('program_kerja'))
                {{-- Program Kerja --}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('programkerja.index') }}">
                        <i class="fas fa-globe me-2"></i>
                        <span>Program Kerja</span>
                    </a>
                </li>
                @endif
                @if(\App\Models\Menu::isActive('ganchart'))
                {{-- Ganchart --}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('gantt.index') }}">
                        <i class="fas fa-project-diagram me-2"></i>
                        <span>Ganchart</span>
                    </a>
                </li>
                @endif

                @if(\App\Models\Menu::isActive('jadwal_kelas'))
                {{-- Jadwal Kelas --}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('admin.kelas.index') }}">
                        <i class="fa-solid fa-chalkboard me-2"></i>
                        <span>JADWAL KELAS</span>
                    </a>
                </li>
                @endif
                    
                    
            @if(strtolower(auth()->user()->name) === 'Yasmin')
             <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.index') }}">
                        <strong><i class="fa-solid fa-cogs me-2"></i> SETTING</strong>
                    </a>
                </li>
            
            @endif



                {{-- Penilaian Karyawan --}}
                <li class="nav-item">
                    <a class="nav-link text-white" href="{{ route('manager.penilaian-cs.index') }}">
                        <i class="fa-solid fa-list-user me-2"></i>
                        <span>Penilaian Kinerja Tim</span>
                    </a>
                </li>
            @endif

            {{-- MENU HRD --}}
            @if(strtolower(auth()->user()->role) === 'hrd')
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
                        <strong><i class="fa-solid fa-person-walking-arrow-right me-2"></i> Izin / Sakit / Lembur</strong> 
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

            <hr class="sidebar-divider d-none d-md-block" />
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2" />
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- ================== NAVBAR NOTIFIKASI ================== -->
                        @if(auth()->user()->role !== 'administrator')
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
                        @endif

                        <!-- ================== NAVBAR PESAN MASUK (ADMIN) ================== -->
                        @if(auth()->user()->role === 'administrator')
                        <li class="nav-item mx-1">
                            <a class="nav-link position-relative notif-message" href="{{ route('admin.messages.index') }}">
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
                            .notif-bell, .notif-message {
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
                                0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); }
                                70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
                                100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
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
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <strong>{{ strtoupper(Auth::user()->role) }} - {{ Auth::user()->name }}</strong>
                                </span>
                                <img class="img-profile rounded-circle" src="{{ asset('backend/img/undraw_profile.svg') }}" alt="Profile Image" />
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
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
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

    <!-- jQuery WAJIB PALING ATAS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap (harus setelah jQuery) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('backend/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- SB Admin (butuh jQuery) -->
    <script src="{{ asset('backend/js/sb-admin-2.min.js') }}"></script>

    <!-- ChartJS -->
    <script src="{{ asset('backend/vendor/chart.js/Chart.min.js') }}"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/fb703282bd.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js"></script>

    <!-- Demo Charts -->
    <script src="{{ asset('backend/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('backend/js/demo/chart-pie-demo.js') }}"></script>

    <!-- SweetAlert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

    <script>
        $(document).ready(function() {
            $("#close").click(function() {
                $("#exampleModal").modal("hide");
            });
        });
    </script>
</body>

</html>