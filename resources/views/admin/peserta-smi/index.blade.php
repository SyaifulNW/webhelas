@extends('layouts.masteradmin')

@section('content')
    @php
        $monthsRaw = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
    @endphp
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="sppFilterForm" method="GET" action="{{ route('peserta-smi.index') }}">
        <!-- Hidden Form for Filters -->
    </form>

    {{-- Hidden Forms for Update --}}
    @foreach($data as $item)
        <form id="form-update-{{ $item->id }}" action="{{ route('peserta-smi.update', $item->id) }}" method="POST"
            style="display: none;">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" id="sink-status-{{ $item->id }}" value="{{ $item->status }}">
        </form>
    @endforeach

    <div class="row">
        {{-- Filter Section --}}
        {{-- Filter Section Moved to Table Header --}}

        <style>
            /* Status Badges */
            .badge-status {
                padding: 5px 10px;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 700;
                color: white !important;
                display: inline-block;
                width: 80px;
                text-align: center;
                border: none;
                cursor: pointer;
            }

            .status-aktif {
                background-color: #1cc88a !important;
            }

            .status-cuti {
                background-color: #e74a3b !important;
            }

            .status-lulus {
                background-color: #4e73df !important;
            }

            .status-pending {
                background-color: #f6c23e !important;
            }

            .status-approved {
                background-color: #1cc88a !important;
            }

            .status-rejected {
                background-color: #e74a3b !important;
            }

            .badge-status {
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                padding: 4px 12px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 800;
                color: white !important;
                text-align: center;
                border: none;
                cursor: pointer;
                width: 90px;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .badge-status option {
                color: white !important;
                font-weight: bold;
            }

            .badge-status option[value="Pending"] { background-color: #f6c23e !important; }
            .badge-status option[value="Approved"] { background-color: #1cc88a !important; }
            .badge-status option[value="Rejected"] { background-color: #e74a3b !important; }

            .badge-status option:checked {
                background-color: inherit !important;
                color: white !important;
            }

            /* Styling Table */
            .table-hover tbody tr:hover {
                background-color: #f1f7fd;
                /* Soft blue on hover */
            }

            /* 📌 Sticky Header & Single Scroll Logic */
            .sticky-header-top {
                position: sticky !important;
                top: 85px !important; /* Offset for SB-Admin-2 topbar + marquee height */
                z-index: 1040 !important;
                background-color: #4e73df !important;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                border-bottom: 2px solid #2e59d9 !important;
            }

            /* Sticky table header styles (Freeze Pane) */
            #dataTable thead th {
                position: sticky !important;
                background-color: #2e59d9 !important; /* Navy Blue */
                z-index: 1035 !important;
                border: 1px solid rgba(255,255,255,0.1) !important;
                box-shadow: 0 2px 2px rgba(0,0,0,0.1);
            }

            /* No and Name main row */
            #dataTable thead tr:nth-child(1) th {
                top: 0px !important; 
            }

            /* Secondary Month row sticks below No/Name row */
            #dataTable thead tr:nth-child(2) th {
                top: 47px !important; /* Height of the first row header */
                z-index: 1034 !important;
            }

            /* Prevent parent containers from hiding the sticky elements */
            .card, .col-xl-12, .row {
                overflow: visible !important;
            }

            #content-wrapper, #wrapper {
                overflow: visible !important;
            }

            /* Ensure table container allows horizontal scroll AND vertical freeze pane */
            .table-responsive {
                overflow: auto !important;
                max-height: 65vh; /* Constraints height so vertical scroll happens INSIDE the table */
                border-bottom: 1px solid #e3e6f0;
            }

            .dummy-scroll-content {
                height: 1px;
            }

            /* SPP Expand/Collapse */
            .spp-col {
                transition: all 0.3s ease;
            }

            .spp-hidden {
                display: none !important;
            }

            #btn-toggle-spp {
                padding: 0.1rem 0.4rem;
                font-size: 0.7rem;
            }

            /* SPP Checkbox and Input Combo */
            .spp-wrapper {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2px;
                padding: 4px 2px;
            }

            .spp-checkbox {
                width: 16px;
                height: 16px;
                cursor: pointer;
                margin: 0;
                accent-color: #1cc88a;
            }

            .spp-input-small {
                font-size: 0.7rem !important;
                padding: 2px !important;
                min-width: 55px !important;
                text-align: center;
                height: 22px;
            }

            .btn-add-participant {
                font-size: 0.65rem;
                padding: 0px 4px;
                margin-left: 2px;
                opacity: 0.6;
                transition: opacity 0.2s;
            }

            .btn-add-participant:hover {
                opacity: 1;
            }

            .participant-2-hidden {
                display: none;
            }
        </style>

        <style>
            .stat-card-premium {
                border: none;
                border-radius: 12px;
                transition: all 0.3s ease;
                overflow: hidden;
            }
            .stat-card-premium:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            }
            .stat-icon-bg {
                position: absolute;
                right: -10px;
                bottom: -10px;
                font-size: 4rem;
                opacity: 0.1;
                transform: rotate(-15deg);
            }
            .stat-label-mini {
                font-size: 0.65rem;
                letter-spacing: 0.5px;
                text-transform: uppercase;
                font-weight: 800;
                opacity: 0.8;
            }
            .stat-value-main {
                font-size: 1.4rem;
                line-height: 1;
            }
            .stat-value-sub {
                font-size: 0.85rem;
                font-weight: 700;
            }
            .stat-card-premium .card-body {
                padding: 0.75rem !important;
            }
            .bg-gradient-blue { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
            .bg-gradient-green { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
            .bg-gradient-red { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); }
            .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); }
            .bg-gradient-yellow { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
            .col-stat-7 {
                flex: 0 0 14.28%;
                max-width: 14.28%;
                padding-left: 6px;
                padding-right: 6px;
            }
            .stat-label-mini {
                font-size: 0.6rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .stat-value-main {
                font-size: 1.25rem;
            }
            .stat-value-sub {
                font-size: 0.8rem;
            }
            .stat-card-premium .card-body {
                padding: 0.6rem 0.75rem !important;
            }
        </style>

        <!-- Card Stats Section -->
        <div class="col-12 mb-3">
            <div class="row flex-nowrap" style="margin-left: -6px; margin-right: -6px;">
                <div class="col-stat-7 mb-2">
                    <div class="card stat-card-premium bg-gradient-blue text-white shadow-sm h-100">
                        <div class="card-body position-relative">
                            <div class="stat-label-mini mb-1">Total Peserta</div>
                            <div class="stat-value-main font-weight-bold" id="stat-total">{{ number_format($stats['total']) }}</div>
                            <i class="fas fa-users stat-icon-bg"></i>
                        </div>
                    </div>
                </div>

                <div class="col-stat-7 mb-2">
                    <div class="card stat-card-premium bg-gradient-green text-white shadow-sm h-100">
                        <div class="card-body position-relative">
                            <div class="stat-label-mini mb-1">Peserta Aktif</div>
                            <div class="stat-value-main font-weight-bold" id="stat-aktif">{{ number_format($stats['aktif']) }}</div>
                            <i class="fas fa-user-check stat-icon-bg"></i>
                        </div>
                    </div>
                </div>

                <div class="col-stat-7 mb-2">
                    <div class="card stat-card-premium bg-gradient-red text-white shadow-sm h-100">
                        <div class="card-body position-relative">
                            <div class="stat-label-mini mb-1">Peserta Cuti</div>
                            <div class="stat-value-main font-weight-bold" id="stat-cuti">{{ number_format($stats['cuti']) }}</div>
                            <i class="fas fa-user-slash stat-icon-bg"></i>
                        </div>
                    </div>
                </div>

        @php
            $isMonthFilter = ($stats['is_month_filter'] ?? false);
            $monthName = $stats['filter_month_name'] ?? '';
        @endphp

                <!-- MONTHLY CARDS -->
                <div class="col-stat-7 mb-2 stat-month-only {{ $isMonthFilter ? '' : 'd-none' }}">
                    <div class="card stat-card-premium shadow-sm h-100 border-bottom-primary" style="background: #fff; border-left: 4px solid #4e73df;">
                        <div class="card-body">
                            <div class="stat-label-mini text-primary mb-1">Closing (Blue) <span class="stat-month-label">{{ $monthName }}</span></div>
                            <div class="d-flex align-items-baseline">
                                <div class="stat-value-main font-weight-bold text-gray-800 mr-2" id="stat-count-closing">{{ number_format($stats['count_closing'] ?? 0) }}</div>
                                <div class="stat-value-sub text-primary" id="stat-nom-closing">Rp {{ number_format($stats['nominal_closing'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-stat-7 mb-2 stat-month-only {{ $isMonthFilter ? '' : 'd-none' }}">
                    <div class="card stat-card-premium shadow-sm h-100 border-bottom-success" style="background: #fff; border-left: 4px solid #1cc88a;">
                        <div class="card-body">
                            <div class="stat-label-mini text-success mb-1">Sudah Bayar <span class="stat-month-label">{{ $monthName }}</span></div>
                            <div class="d-flex align-items-baseline">
                                <div class="stat-value-main font-weight-bold text-gray-800 mr-2" id="stat-count-spp">{{ number_format($stats['count_spp'] ?? 0) }}</div>
                                <div class="stat-value-sub text-success" id="stat-nom-spp">Rp {{ number_format($stats['nominal_spp'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-stat-7 mb-2 stat-month-only {{ $isMonthFilter ? '' : 'd-none' }}">
                    <div class="card stat-card-premium shadow-sm h-100 border-bottom-warning" style="background: #fff; border-left: 4px solid #f6c23e;">
                        <div class="card-body">
                            <div class="stat-label-mini text-warning mb-1">Blm Bayar (Potensi) <span class="stat-month-label">{{ $monthName }}</span></div>
                            <div class="d-flex align-items-baseline">
                                <div class="stat-value-main font-weight-bold text-gray-800 mr-2" id="stat-count-belum">{{ number_format($stats['count_belum'] ?? 0) }}</div>
                                <div class="stat-value-sub text-warning" id="stat-nom-belum">Rp {{ number_format($stats['nominal_belum'] ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-stat-7 mb-2 stat-month-only {{ $isMonthFilter ? '' : 'd-none' }}">
                    <div class="card stat-card-premium shadow-sm h-100 border-bottom-info" style="background: #fff; border-left: 4px solid #36b9cc;">
                        <div class="card-body">
                            <div class="stat-label-mini text-info mb-1">Total Keseluruhan <span class="stat-month-label">{{ $monthName }}</span></div>
                            <div class="d-flex align-items-baseline">
                                <div class="stat-value-main font-weight-bold text-gray-800 mr-2" id="stat-count-total-month">{{ number_format(($stats['count_spp'] ?? 0) + ($stats['count_belum'] ?? 0)) }}</div>
                                <div class="stat-value-sub text-info" id="stat-nom-total-month">Rp {{ number_format(($stats['nominal_spp'] ?? 0) + ($stats['nominal_belum'] ?? 0), 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend Section -->
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0 bg-white p-2">
                <div class="d-flex align-items-center flex-wrap" style="gap: 25px; font-size: 0.82rem; padding: 0 10px;">
                    <div class="d-flex align-items-center">
                        <div
                            style="width: 20px; height: 20px; background-color: #4e73df; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(78,115,223,0.2);">
                            <i class="fas fa-check-double text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <span class="text-dark"><b style="color: #4e73df;">Checklist Biru:</b> Pembayaran Awal (Closing) / Status LUNAS</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div
                            style="width: 20px; height: 20px; background-color: #1cc88a; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(28,200,138,0.2);">
                            <i class="fas fa-check-circle text-white" style="font-size: 0.7rem;"></i>
                        </div>
                        <span class="text-dark"><b style="color: #1cc88a;">Checklist Hijau:</b> Pembayaran Bulanan
                            (Manual)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 col-md-12 mb-3">
            <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-alt mr-2"></i>Daftar Peserta M1T</h5>
        </div>

        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card shadow mb-4">
                <div
                    class="card-header py-2 d-flex flex-row align-items-center justify-content-start bg-primary text-white sticky-header-top" style="gap: 20px;">


                    <div class="d-flex align-items-end flex-wrap p-2 rounded w-100" style="gap: 10px; background: rgba(255,255,255,0.07);">
                        {{-- Chapter Filter --}}
                        @if(strtolower(auth()->user()->role) === 'administrator' || in_array(auth()->user()->name, ['Linda', 'Yasmin']))
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">CHAPTER</label>
                            <select form="sppFilterForm" name="filter_chapter" id="smi_filter_chapter" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="font-size: 0.75rem; height: 30px; width: 110px;">
                                <option value="all" {{ request('filter_chapter', 'all') == 'all' ? 'selected' : '' }}>ALL</option>
                                <option value="Cirebon" {{ request('filter_chapter') == 'Cirebon' ? 'selected' : '' }}>CIREBON </option>
                                <option value="Kaltim" {{ request('filter_chapter') == 'Kaltim' ? 'selected' : '' }}>KALTIM</option>
                                <option value="Depok" {{ request('filter_chapter') == 'Depok' ? 'selected' : '' }}>DEPOK</option>
                                <option value="Jakarta" {{ request('filter_chapter') == 'Jakarta' ? 'selected' : '' }}>JAKARTA</option>
                                <option value="Tangerang" {{ request('filter_chapter') == 'Tangerang' ? 'selected' : '' }}>TANGERANG</option>
                                <option value="Makassar" {{ request('filter_chapter') == 'Makassar' ? 'selected' : '' }}>MAKASSAR</option>
                                <option value="Lampung" {{ request('filter_chapter') == 'Lampung' ? 'selected' : '' }}>LAMPUNG</option>
                            </select>
                        </div>
                        @endif

                        {{-- Status Filter --}}
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">STATUS PESERTA</label>
                            <select form="sppFilterForm" name="filter_status" id="smi_filter_status" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="font-size: 0.75rem; height: 30px; width: 90px;">
                                <option value="all" {{ request('filter_status', 'all') == 'all' ? 'selected' : '' }}>ALL</option>
                                <option value="Aktif" {{ request('filter_status') == 'Aktif' ? 'selected' : '' }}>AKTIF</option>
                                <option value="Cuti" {{ request('filter_status') == 'Cuti' ? 'selected' : '' }}>CUTI</option>
                                <option value="Lulus" {{ request('filter_status') == 'Lulus' ? 'selected' : '' }}>LULUS</option>
                            </select>
                        </div>

                        {{-- Approval Filter --}}
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">APPROVE STATUS</label>
                            <select form="sppFilterForm" name="filter_approval" id="smi_filter_approval" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="font-size: 0.75rem; height: 30px; width: 110px;">
                                <option value="all" {{ request('filter_approval', 'all') == 'all' ? 'selected' : '' }}>ALL</option>
                                <option value="Pending" {{ request('filter_approval') == 'Pending' ? 'selected' : '' }}>PENDING</option>
                                <option value="Approved" {{ request('filter_approval') == 'Approved' ? 'selected' : '' }}>APPROVED</option>
                                <option value="Rejected" {{ request('filter_approval') == 'Rejected' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </div>

                        {{-- SPP Bulan --}}
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">PEMBAYARAN SPP</label>
                            <select form="sppFilterForm" name="filter_spp_month" id="smi_filter_spp_month" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light" style="width: 110px; font-size: 0.75rem; height: 30px;">
                                <option value="all" {{ request('filter_spp_month') == 'all' ? 'selected' : '' }}>ALL</option>
                                @foreach($monthsRaw as $key => $val)
                                    <option value="{{ $key }}" {{ request('filter_spp_month', date('n')) == $key ? 'selected' : '' }}>{{ $val }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SPP Status --}}
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">STATUS SPP</label>
                            <select form="sppFilterForm" name="filter_spp_status" id="smi_filter_spp_status" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light" style="width: 100px; font-size: 0.75rem; height: 30px;">
                                <option value="all" {{ request('filter_spp_status', 'all') == 'all' ? 'selected' : '' }}>ALL</option>
                                <option value="1" {{ request('filter_spp_status') === '1' ? 'selected' : '' }}>Lunas</option>
                                <option value="0" {{ request('filter_spp_status') === '0' ? 'selected' : '' }}>Belum</option>
                            </select>
                        </div>

                        {{-- Tahun --}}
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">TAHUN</label>
                            <select form="sppFilterForm" name="filter_year" id="smi_filter_year" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light" style="width: 80px; font-size: 0.75rem; height: 30px;">
                                <option value="all" {{ request('filter_year', 'all') == 'all' ? 'selected' : '' }}>ALL</option>
                                @for($y = date('Y') + 1; $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ request('filter_year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Level Filter (Linda Only) --}}
                        @if(auth()->user()->name === 'Linda')
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">LEVEL</label>
                            <select form="sppFilterForm" name="filter_level" id="smi_filter_level" onchange="updateSmiFilters()" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="width: 110px; font-size: 0.75rem; height: 30px;">
                                <option value="all" {{ request('filter_level', 'all') == 'all' ? 'selected' : '' }}>ALL</option>
                                <option value="Grow Up" {{ request('filter_level') == 'Grow Up' ? 'selected' : '' }}>GROW UP</option>
                                <option value="Start Up" {{ request('filter_level') == 'Start Up' ? 'selected' : '' }}>START UP</option>
                            </select>
                        </div>
                        @endif


                        <a href="javascript:void(0)" onclick="resetSmiFilters()" class="text-white ml-1 mb-1" title="Reset Filter">
                            <i class="fas fa-sync-alt"></i>
                        </a>

                        {{-- Vertical Divider --}}
                        <div class="mx-1" style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                        {{-- Search Block --}}
                        <div class="d-flex flex-column" style="gap: 2px;">
                            <label class="mb-0 text-white font-weight-bold" style="font-size: 0.65rem; margin-left: 2px; letter-spacing: 0.5px;">CARI NAMA</label>
                            <input type="text" name="search" id="smi_search" class="form-control form-control-sm border-0 bg-light" style="width: 150px; height: 30px; border-radius: 5px; font-size: 0.75rem;" placeholder="Cari nama..." value="{{ request('search') }}">
                        </div>
                        <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm px-3" onclick="updateSmiFilters()" style="height: 30px; border-radius: 5px; font-size: 0.7rem; color: #2e59d9;">
                            <i class="fas fa-search mr-1"></i> TAMPILKAN DATA
                        </button>
                    </div>
                </div>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" id="dataTable" width="100%"
                            cellspacing="0" style="font-size: 0.85rem;">
                            <thead class="thead-dark-blue text-white">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 3%">No</th>
                                    <th rowspan="2" class="align-middle text-center border-right" style="min-width: 200px;">
                                        Nama Closing
                                    </th>
                                    <th rowspan="2" class="align-middle text-center border-right d-none"
                                        style="width: 8%">
                                        Status</th>
                                    <th rowspan="2" class="align-middle text-center border-right d-none"
                                        style="width: 10%">
                                        Level</th>
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 10%;">
                                        Biaya Closing Awal
                                    </th>
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 10%">
                                        @if(in_array(strtolower(auth()->user()->role), ['reseller', 'chapter']))
                                            Agen Closing
                                        @else
                                            CS Closing
                                        @endif
                                    </th>

                                    <th colspan="12" id="sppMainHeader"
                                        class="text-center font-weight-bold text-uppercase border-bottom px-2">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="button" class="btn btn-xs btn-light text-primary mr-2"
                                                id="btn-toggle-spp" onclick="toggleSpp()" title="Expand/Collapse SPP">
                                                <i class="fas fa-compress-alt"></i>
                                            </button>
                                            <a href="{{ route('admin.keuangan.laba-rugi', ['bulan' => str_pad(request('filter_spp_month', 'all'), 2, '0', STR_PAD_LEFT), 'tahun' => request('filter_year', 'all')]) }}"
                                                class="text-white text-decoration-none mr-2 font-weight-bold"
                                                id="sppHeaderTitle" title="Buka Laporan Laba Rugi M1T">
                                                Monitoring SPP
                                                @if(request('filter_spp_month') == 'all' || request('filter_year') == 'all')
                                                    Seluruh Periode
                                                @else
                                                    Bulan {{ $monthsRaw[request('filter_spp_month', (int) date('m'))] ?? '' }}
                                                    {{ request('filter_year', date('Y')) }}
                                                @endif
                                            </a>
                                        </div>
                                    </th>
                                    <th rowspan="2" class="align-middle text-center border-left" style="width: 5%">Aksi</th>
                                </tr>
                                <tr>
                                    @php
                                        $bulan = [
                                            1 => 'Jan',
                                            2 => 'Feb',
                                            3 => 'Mar',
                                            4 => 'Apr',
                                            5 => 'Mei',
                                            6 => 'Jun',
                                            7 => 'Jul',
                                            8 => 'Ags',
                                            9 => 'Sep',
                                            10 => 'Okt',
                                            11 => 'Nov',
                                            12 => 'Des'
                                        ];
                                    @endphp
                                    @for($i = 1; $i <= 12; $i++)
                                        <th class="text-center spp-col {{ $i > 1 ? 'spp-extra' : '' }}"
                                            style="min-width: 45px; width: 45px; font-size: 11px;">
                                            <a href="{{ route('admin.keuangan.laba-rugi', ['bulan' => str_pad($i, 2, '0', STR_PAD_LEFT), 'tahun' => request('filter_year', date('Y'))]) }}"
                                                class="text-white text-decoration-none col-spp-label-{{ $i }}"
                                                title="Lihat Laba Rugi {{ $bulan[$i] }} {{ request('filter_year', date('Y')) }}">
                                                {{ $bulan[$i] }}
                                            </a>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody id="smiTableBody">
                                @include('admin.peserta-smi.table-rows')
                            </tbody>
                        </table>
                    </div>

                    <!-- Dummy Horizontal Scrollbar yang akan mengambang di bawah -->
                    <div id="floating-scrollbar" class="floating-scrollbar-wrapper">
                        <div id="floating-scrollbar-content" class="dummy-scroll-content"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tableContainer = document.querySelector('.table-responsive');
            const table = tableContainer.querySelector('table');
            const floatScroll = document.getElementById('floating-scrollbar');
            const floatContent = document.getElementById('floating-scrollbar-content');

            function syncFloatingScrollbar() {
                const rect = tableContainer.getBoundingClientRect();
                const windowHeight = window.innerHeight;

                // Hitung apakah tabel lebih lebar dari layar (butuh horizontal scroll)
                const needsHorizontalScroll = table.offsetWidth > tableContainer.clientWidth;

                // Tampilkan floating scrollbar JIKA:
                // 1. Bagian atas tabel sudah terlihat (top < windowHeight)
                // 2. Bagian bawah tabel masih belum terlihat / tertutup layar (bottom > windowHeight)
                // 3. Memang butuh scroll (needsHorizontalScroll)
                if (needsHorizontalScroll && rect.top < windowHeight && rect.bottom > windowHeight) {
                    floatScroll.style.display = 'block';
                    floatScroll.style.left = rect.left + 'px';
                    floatScroll.style.width = rect.width + 'px';
                    floatContent.style.width = table.offsetWidth + 'px';

                    // Supaya sinkronnya update saat muncul
                    floatScroll.scrollLeft = tableContainer.scrollLeft;
                } else {
                    floatScroll.style.display = 'none';
                }
            }

            // Sync scrolling
            floatScroll.addEventListener('scroll', function () {
                tableContainer.scrollLeft = floatScroll.scrollLeft;
            });

            tableContainer.addEventListener('scroll', function () {
                floatScroll.scrollLeft = tableContainer.scrollLeft;
            });

            // Sync on window events
            window.addEventListener('scroll', syncFloatingScrollbar);
            window.addEventListener('resize', syncFloatingScrollbar);

            // Inisiasi awal
            setTimeout(syncFloatingScrollbar, 500);
        });

        function toggleParticipant2(id) {
            const wrap1 = document.getElementById('wrapper-nama-2-' + id);
            const wrap2 = document.getElementById('wrapper-nama-asli-2-' + id);
            if (wrap1.classList.contains('participant-2-hidden')) {
                wrap1.classList.remove('participant-2-hidden');
                wrap2.classList.remove('participant-2-hidden');
            } else {
                wrap1.classList.add('participant-2-hidden');
                wrap2.classList.add('participant-2-hidden');
            }
        }

        function toggleInputRow() {
            var row = document.getElementById('inputRow');
            if (row.style.display === 'none') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }

        function toggleSpp() {
            const header = document.getElementById('sppMainHeader');
            const extras = document.querySelectorAll('.spp-extra');
            const filterControls = document.getElementById('sppFilterControls');
            const btn = document.getElementById('btn-toggle-spp');
            const firstLabel = document.querySelector('.col-spp-label-1');

            const isCollapsed = header.getAttribute('colspan') == 1;

            if (isCollapsed) {
                // Expand
                extras.forEach(el => el.classList.remove('spp-hidden'));
                header.setAttribute('colspan', 12);
                if(filterControls) filterControls.classList.remove('spp-hidden');
                btn.innerHTML = '<i class="fas fa-compress-alt"></i>';
                firstLabel.innerHTML = 'Jan'; // Adjust if needed
                localStorage.setItem('spp_collapsed', 'false');
            } else {
                // Collapse
                extras.forEach(el => el.classList.add('spp-hidden'));
                header.setAttribute('colspan', 1);
                if(filterControls) filterControls.classList.add('spp-hidden');
                btn.innerHTML = '<i class="fas fa-expand-alt"></i>';
                firstLabel.innerHTML = 'SPP';
                localStorage.setItem('spp_collapsed', 'true');
            }
        }
        function quickUpdateField(element, id, fieldName) {
            const value = element.value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Visual feedback
            element.style.opacity = '0.5';

            // For status dropdown, update class
            if (fieldName === 'status') {
                element.classList.remove('status-aktif', 'status-cuti', 'status-lulus');
                element.classList.add('status-' + value.toLowerCase());
                const sink = document.getElementById('sink-status-' + id);
                if (sink) sink.value = value;
            }

            if (fieldName === 'approval_status') {
                element.classList.remove('status-pending', 'status-approved', 'status-rejected');
                element.classList.add('status-' + value.toLowerCase());
            }

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');
            formData.append('ajax_field', fieldName);
            formData.append(fieldName, value);
            formData.append('is_ajax', '1');

            fetch(`{{ url('peserta-smi') }}/${id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(async response => {
                    element.style.opacity = '1';
                    if (!response.ok) {
                        const text = await response.text();
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || 'Server error');
                        } catch (e) {
                            throw new Error('Server returned error: ' + response.status);
                        }
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success updating ' + fieldName + ':', data);

                    // Update Date Display Real-time
                    if (data.date_field) {
                        const dateEl = document.getElementById('date_' + data.date_field + '_' + id);
                        if (dateEl) {
                            dateEl.innerText = data.date_value || '';
                        }
                    }

                    // Highlight success briefly
                    const originalColor = element.style.borderColor;
                    element.style.borderColor = '#1cc88a';
                    setTimeout(() => { element.style.borderColor = originalColor; }, 1000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    element.style.opacity = '1';
                    element.style.borderColor = '#e74a3b';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Simpan',
                        text: 'Terjadi kesalahan: ' + error.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
        }

        // Keep quickUpdateStatus legacy call if needed, but point to quickUpdateField
        function quickUpdateStatus(select, id) {
            quickUpdateField(select, id, 'status');
        }

        // DIRECT TOGGLE FOR SPP (Replaces Modal)
        function toggleSppLunasDirectly(checkbox) {
            const id = checkbox.dataset.id;
            const month = checkbox.dataset.month;
            const plannedNominal = checkbox.dataset.plannedNominal;
            // [USER_REQUEST] Use level-based nominal: Grow = 1.500.000, Start = 1.000.000
            const levelNominal = checkbox.dataset.levelNominal || '1000000';
            const input = document.getElementById(`spp_${month}_${id}`);

            if (input) {
                let newVal = '0';
                if (checkbox.checked) {
                    if (plannedNominal) {
                        newVal = plannedNominal;
                        if (newVal.indexOf('.') === -1) {
                            newVal = new Intl.NumberFormat('id-ID').format(newVal);
                        }
                    } else {
                        // Use level-based nominal
                        newVal = new Intl.NumberFormat('id-ID').format(parseInt(levelNominal));
                    }
                }

                input.value = newVal;

                if (checkbox.checked) {
                    checkbox.style.accentColor = '#1cc88a'; // Green for manual
                }

                quickUpdateField(input, id, `spp_${month}`);
                setTimeout(() => updateSmiFilters(), 500);
            }
        }

        function syncSppCheckbox(input, month, id) {
            const val = input.value.replace(/[^0-9]/g, '');
            const checkbox = document.querySelector(`.spp-checkbox[data-id="${id}"][data-month="${month}"]`);
            if (checkbox) {
                const levelNominal = parseInt(checkbox.dataset.levelNominal || '1000000');
                checkbox.checked = (parseInt(val || 0) >= levelNominal);
            }
        }

        function quickUpdateSpp(input, id, month) {
            quickUpdateField(input, id, `spp_${month}`);
        }

        function focusTanggalMasuk(id) {
            const el = document.getElementById('tgl_masuk_' + id);
            if (el) {
                el.style.backgroundColor = '#fff3cd';
                el.focus();
                // Reset color after focus
                setTimeout(() => { el.style.backgroundColor = ''; }, 2000);
            }
        }

        function toggleLunasManual(id, btn) {
            const isLunas = btn.classList.contains('btn-success');
            const newVal = isLunas ? 0 : 1;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            Swal.fire({
                title: 'Konfirmasi Lunas',
                text: `Tanda sebagai ${newVal ? 'LUNAS' : 'Belum Lunas'} manual?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('_method', 'PUT');
                    formData.append('ajax_field', 'is_lunas');
                    formData.append('is_lunas', newVal);
                    formData.append('is_ajax', '1');

                    fetch(`{{ url('admin/peserta-smi') }}/${id}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Status lunas berhasil diperbarui',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => updateSmiFilters());
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        })
                        .catch(e => {
                            console.error(e);
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        })
                        .finally(() => btn.disabled = false);
                }
            });
        }

        function focusTanggalSelesai(id) {
            const el = document.getElementById('tgl_selesai_' + id);
            if (el) {
                el.style.backgroundColor = '#d1e7ff';
                el.focus();
                // Reset color after focus
                setTimeout(() => { el.style.backgroundColor = ''; }, 2000);
            }
        }


        function deletePeserta(id, btn) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah Anda yakin ingin menghapus data peserta ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const row = btn.closest('tr');

                    row.style.opacity = '0.5';
                    row.style.pointerEvents = 'none';

                    fetch(`{{ url('peserta-smi') }}/${id}`, {
                        method: 'POST',
                        body: new URLSearchParams({
                            '_token': csrfToken,
                            '_method': 'DELETE'
                        }),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: 'Data peserta berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                row.style.transition = 'all 0.5s ease';
                                row.style.transform = 'translateX(20px)';
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    document.querySelectorAll('#dataTable tbody tr').forEach((r, i) => {
                                        r.querySelector('td:first-child').innerText = i + 1;
                                    });
                                }, 500);
                            } else {
                                Swal.fire('Gagal', (data.message || 'Unknown error'), 'error');
                                row.style.opacity = '1';
                                row.style.pointerEvents = 'auto';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Terjadi kesalahan saat menghapus data.', 'error');
                            row.style.opacity = '1';
                            row.style.pointerEvents = 'auto';
                        });
                }
            });
        }

        function updateStatusColor(select, id) {
            // ... kept for compatibility with other fields if needed ...
            select.classList.remove('status-aktif', 'status-cuti', 'status-lulus');
            select.classList.add('status-' + select.value.toLowerCase());
            const sink = document.getElementById('sink-status-' + id);
            if (sink) sink.value = select.value;
        }

        // Auto-collapse on load if preference saved
        document.addEventListener('DOMContentLoaded', function () {
            if (localStorage.getItem('spp_collapsed') === 'true') {
                toggleSpp();
            }



            // Initial currency formatting
            initCurrencyInputs();

            // Enter key trigger for search
            const searchInput = document.getElementById('smi_search');
            if (searchInput) {
                searchInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        updateSmiFilters();
                    }
                });
            }
        });

        function updateSmiFilters() {
            const container = document.getElementById('smiTableBody');
            const statTotal = document.getElementById('stat-total');
            const statAktif = document.getElementById('stat-aktif');
            const statCuti = document.getElementById('stat-cuti');

            if (!container) return;

            // Collect all filter values with safe checks
            const getVal = (id) => {
                const el = document.getElementById(id);
                return el ? el.value : 'all';
            };

            const params = new URLSearchParams({
                ajax: 1,
                filter_chapter: getVal('smi_filter_chapter'),
                filter_status: getVal('smi_filter_status'),
                filter_approval: getVal('smi_filter_approval'),
                filter_spp_month: getVal('smi_filter_spp_month'),
                filter_spp_status: getVal('smi_filter_spp_status'),
                filter_year: getVal('smi_filter_year'),
                filter_sort: getVal('smi_filter_sort'),
                filter_level: getVal('smi_filter_level'),
                search: getVal('smi_search')
            });

            // Visual feedback
            container.style.opacity = '0.5';

            const baseUrl = `{{ route('peserta-smi.index') }}`;
            const finalUrl = baseUrl.includes('?') ? `${baseUrl}&${params.toString()}` : `${baseUrl}?${params.toString()}`;

            fetch(finalUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    if (!res.ok) throw new Error('Data refresh failed');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        container.innerHTML = data.html;
                        if (statTotal) statTotal.innerText = new Intl.NumberFormat('id-ID').format(data.stats.total);
                        if (statAktif) statAktif.innerText = new Intl.NumberFormat('id-ID').format(data.stats.aktif);
                        if (statCuti) statCuti.innerText = new Intl.NumberFormat('id-ID').format(data.stats.cuti);

                        // Update New Monthly Stats
                        const statCountClosing = document.getElementById('stat-count-closing');
                        const statNomClosing = document.getElementById('stat-nom-closing');
                        const statCountSpp = document.getElementById('stat-count-spp');
                        const statNomSpp = document.getElementById('stat-nom-spp');
                        const statCountBelum = document.getElementById('stat-count-belum');
                        const statNomBelum = document.getElementById('stat-nom-belum');
                        
                        const monthLabelEls = document.querySelectorAll('.stat-month-label');
                        const monthCardEls = document.querySelectorAll('.stat-month-only');

                        if (data.stats.is_month_filter) {
                            const fmt = (val) => "Rp " + new Intl.NumberFormat('id-ID').format(val);
                            const num = (val) => new Intl.NumberFormat('id-ID').format(val);

                            if (statCountClosing) statCountClosing.innerText = num(data.stats.count_closing);
                            if (statNomClosing) statNomClosing.innerText = fmt(data.stats.nominal_closing);
                            if (statCountSpp) statCountSpp.innerText = num(data.stats.count_spp);
                            if (statNomSpp) statNomSpp.innerText = fmt(data.stats.nominal_spp);
                            if (statCountBelum) statCountBelum.innerText = num(data.stats.count_belum);
                            if (statNomBelum) statNomBelum.innerText = fmt(data.stats.nominal_belum);

                            const statCountTotalMonth = document.getElementById('stat-count-total-month');
                            const statNomTotalMonth = document.getElementById('stat-nom-total-month');
                            if (statCountTotalMonth) statCountTotalMonth.innerText = num(data.stats.count_spp + data.stats.count_belum);
                            if (statNomTotalMonth) statNomTotalMonth.innerText = fmt(data.stats.nominal_spp + data.stats.nominal_belum);

                            monthLabelEls.forEach(el => el.innerText = data.stats.filter_month_name);
                            monthCardEls.forEach(el => el.classList.remove('d-none'));
                        } else {
                            monthLabelEls.forEach(el => el.innerText = "");
                            monthCardEls.forEach(el => el.classList.add('d-none'));
                        }

                        // Update Monitoring Header
                        const sppHeaderTitle = document.getElementById('sppHeaderTitle');
                        if (sppHeaderTitle && data.spp_header) {
                            sppHeaderTitle.innerText = data.spp_header;
                        }
                    } else {
                        console.error('Server returned success:false', data);
                    }
                })
                .catch(err => {
                    console.error('Filter error:', err);
                })
                .finally(() => {
                    container.style.opacity = '1';
                    if (typeof initCurrencyInputs === 'function') initCurrencyInputs();
                });
        }



        function initCurrencyInputs() {
            document.querySelectorAll('.input-currency').forEach(input => {
                input.removeEventListener('input', currencyInputHandler);
                input.addEventListener('input', currencyInputHandler);
            });
        }

        function currencyInputHandler(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = new Intl.NumberFormat('id-ID').format(value);
            } else {
                this.value = '';
            }
        }

        function resetSmiFilters() {
            const ids = [
                'smi_filter_chapter',
                'smi_filter_status',
                'smi_filter_spp_month',
                'smi_filter_spp_status',
                'smi_filter_year',
                'smi_filter_sort',
                'smi_filter_approval',
                'smi_filter_level',
                'smi_search'
            ];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    if (el.tagName === 'SELECT') el.value = 'all';
                    else el.value = '';
                }
            });
            updateSmiFilters();
        }

    </script>

    {{-- SPP NOMINAL MODAL REMOVED AS REQUESTED --}}

    {{-- ====== MODAL UPLOAD BUKTI TRANSFER ====== --}}
    <div class="modal fade" id="modalUploadBukti" tabindex="-1" role="dialog" aria-labelledby="modalUploadBuktiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #36b9cc, #258391); border: none;">
                    <h5 class="modal-title text-white font-weight-bold" id="modalUploadBuktiLabel">
                        <i class="fas fa-upload mr-2"></i>Upload Bukti Transfer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">Peserta: <strong id="uploadBuktiNama"></strong></p>
                    <p class="text-muted mb-3" style="font-size: 0.8rem;">Upload foto bukti transfer untuk diverifikasi oleh admin pusat.</p>

                    {{-- Preview existing bukti --}}
                    <div id="buktiPreviewWrapper" class="mb-3 text-center d-none">
                        <p class="text-muted" style="font-size: 0.78rem; margin-bottom: 4px;">Bukti saat ini:</p>
                        <img id="buktiPreviewImg" src="" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 160px; border: 2px solid #e3e6f0;">
                    </div>

                    <form id="formUploadBukti" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark" style="font-size: 0.85rem;">Pilih Foto Bukti Transfer <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="inputBuktiTransfer" name="bukti_transfer"
                                    accept="image/jpeg,image/png,image/jpg,image/gif" required onchange="previewSelectedFile(this)">
                                <label class="custom-file-label" for="inputBuktiTransfer">Pilih foto...</label>
                            </div>
                            <small class="text-muted">Format: JPG, PNG, GIF. Maks. 2MB.</small>
                        </div>
                        {{-- New file preview --}}
                        <div id="newFilePreview" class="text-center mb-2 d-none">
                            <img id="newFilePreviewImg" src="" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 150px; border: 2px dashed #f6c23e;">
                            <p class="text-muted mt-1" style="font-size: 0.75rem;">Preview foto baru</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-info btn-sm font-weight-bold shadow-sm" id="btnSubmitUploadBukti" onclick="submitUploadBukti()">
                        <i class="fas fa-cloud-upload-alt mr-1"></i>Upload Bukti
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== MODAL VIEW BUKTI TRANSFER (Admin) ====== --}}
    <div class="modal fade" id="modalViewBukti" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #4e73df, #224abe); border: none;">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="fas fa-image mr-2"></i>Bukti Transfer — <span id="viewBuktiNama"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="viewBuktiImg" src="" alt="Bukti Transfer" class="img-fluid rounded shadow" style="max-height: 500px; border: 2px solid #e3e6f0;">
                    <div class="mt-3">
                        <a id="viewBuktiDownloadLink" href="" target="_blank" download class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download mr-1"></i>Unduh Gambar
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let _currentUploadId = null;

        function openUploadBukti(id, nama, currentBuktiUrl) {
            _currentUploadId = id;
            document.getElementById('uploadBuktiNama').textContent = nama;
            document.getElementById('inputBuktiTransfer').value = '';
            document.querySelector('#formUploadBukti .custom-file-label').textContent = 'Pilih foto...';
            document.getElementById('newFilePreview').classList.add('d-none');

            const previewWrapper = document.getElementById('buktiPreviewWrapper');
            const previewImg = document.getElementById('buktiPreviewImg');
            if (currentBuktiUrl) {
                previewImg.src = currentBuktiUrl;
                previewWrapper.classList.remove('d-none');
            } else {
                previewWrapper.classList.add('d-none');
            }

            $('#modalUploadBukti').modal('show');
        }

        function previewSelectedFile(input) {
            const label = input.nextElementSibling;
            if (input.files && input.files[0]) {
                label.textContent = input.files[0].name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('newFilePreviewImg').src = e.target.result;
                    document.getElementById('newFilePreview').classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function submitUploadBukti() {
            if (!_currentUploadId) return;

            const fileInput = document.getElementById('inputBuktiTransfer');
            if (!fileInput.files || !fileInput.files[0]) {
                Swal.fire({ icon: 'warning', title: 'Pilih Foto', text: 'Silakan pilih foto bukti transfer terlebih dahulu.', timer: 2000, showConfirmButton: false });
                return;
            }

            const btn = document.getElementById('btnSubmitUploadBukti');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengunggah...';

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('bukti_transfer', fileInput.files[0]);

            fetch(`{{ url('peserta-smi') }}/${_currentUploadId}/upload-bukti`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text().then(text => ({ ok: res.ok, text })))
            .then(({ ok, text }) => {
                $('#modalUploadBukti').modal('hide');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-1"></i>Upload Bukti';
                if (ok) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Bukti transfer berhasil diunggah. Menunggu persetujuan admin.', timer: 2500, showConfirmButton: false })
                        .then(() => updateSmiFilters());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal Upload', text: 'Terjadi kesalahan. Pastikan ukuran file tidak lebih dari 2MB.', timer: 3000, showConfirmButton: false });
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-1"></i>Upload Bukti';
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan.', timer: 2000, showConfirmButton: false });
            });
        }

        function viewBuktiTransfer(url, nama) {
            document.getElementById('viewBuktiNama').textContent = nama;
            document.getElementById('viewBuktiImg').src = url;
            document.getElementById('viewBuktiDownloadLink').href = url;
            $('#modalViewBukti').modal('show');
        }
    </script>
@endsection