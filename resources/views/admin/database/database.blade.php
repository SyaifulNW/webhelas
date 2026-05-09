@extends('layouts.masteradmin')
@section('content')

    <style>
        /* Contenteditable Placeholder */
        [contenteditable]:empty:before {
            content: attr(data-placeholder);
            color: #aaa;
            font-style: italic;
            pointer-events: none;
            display: block;
        }
    </style>

    <style>
        .table-scroll-container {
            overflow-x: auto !important;
            width: 100%;
            position: relative;
        }

        #myTable thead th {
            position: sticky;
            top: 0px; /* Adjust since the topbar is NOT fixed */
            z-index: 1000;
            background-color: #25799E !important;
            color: white;
            border-bottom: 2px solid #fff !important;
        }

        /* Adjust for sub-header row if any */
        #myTable thead tr:nth-child(2) th {
            top: 65px; /* Fits better with dropdowns in first row */
        }

        #myTable,
        #myTable th,
        #myTable td {
            border: 2px solid #999 !important;
        }

        #myTable thead th {
            border-color: #fff !important;
            border-width: 2px !important;
        }

        .read-more-container {
            position: relative;
            max-height: 4.5em;
            /* Approximately 3 lines */
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            line-height: 1.5;
        }

        .read-more-container.expanded {
            max-height: 2000px;
            /* Large enough */
        }

        .btn-read-more {
            display: block;
            color: #007bff;
            cursor: pointer;
            font-size: 0.75rem;
            margin-top: 4px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-read-more:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .text-wrap-normal {
            white-space: normal !important;
            word-break: break-word;
            min-width: 180px;
            vertical-align: top !important;
        }

        .editable {
            transition: background-color 0.2s ease;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .editable:hover {
            background-color: rgba(0, 123, 255, 0.08);
            outline: 1px dashed #007bff;
        }

        @if(strtolower(auth()->user()->role) === 'marketing')
            #myTable td {
                vertical-align: top;
                padding: 8px 6px;
                font-size: 0.95rem;
            }

            #myTable th {
                vertical-align: middle;
                text-align: center;
                text-transform: uppercase;
                font-size: 0.9rem;
                letter-spacing: 0.5px;
                padding: 10px 5px;
            }

            /* Make container fill more space */
            .container-fluid {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .card {
                width: 100% !important;
            }

        @else #myTable td {
                vertical-align: top;
                padding: 4px 3px;
                font-size: 0.8rem;
            }

            #myTable th {
                vertical-align: middle;
                text-align: center;
                text-transform: uppercase;
                font-size: 0.75rem;
                letter-spacing: 0px;
                padding: 4px 2px;
            }

        @endif

        /* Floating Scrollbar Styles - Match Sales Plan */
        .floating-scroll-bar {
            position: fixed;
            bottom: 0;
            overflow-x: auto;
            z-index: 9999;
            background: #fff;
            border-top: 2px solid #25799E;
            height: 25px;
            display: none;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .floating-scroll-bar::-webkit-scrollbar {
            height: 10px;
        }

        .floating-scroll-bar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .floating-scroll-bar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .floating-scroll-bar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
    <style>
        /* Precision Styling for Compact Columns */
        .col-bat {
            width: 35px !important;
            min-width: 35px !important;
            padding: 4px 0 !important;
            text-align: center !important;
        }

        .col-zoom {
            width: 85px !important;
            min-width: 85px !important;
            padding: 4px 2px !important;
        }

        .col-spin-header {
            width: 105px !important;
            min-width: 105px !important;
        }

        #myTable th.col-bat,
        #myTable td.col-bat {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .col-bat .custom-control.custom-checkbox,
        .col-zoom .custom-control.custom-checkbox {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-left: 0 !important;
            margin: 0 auto;
        }

        .col-bat .custom-control-label::before,
        .col-bat .custom-control-label::after,
        .col-zoom .custom-control-label::before,
        .col-zoom .custom-control-label::after {
            left: 50% !important;
            margin-left: -8px !important;
            /* Half of 1rem checkbox */
            top: 50% !important;
            margin-top: -8px !important;
        }
    </style>
    @php
        $user = auth()->user();
        $userRole = strtolower($user->role);
    @endphp

    @if($userRole !== 'administrator')
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                @if($userRole === 'chapter')
                    Database Calon Peserta M1T Chapter {{ $user->chapter }}
                @else
                    Database Calon Peserta{{ $userRole === 'cs-mbc' ? '' : ' M1T' }}
                @endif
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-light shadow-sm rounded-pill px-4 mb-0" style="border: 1px solid #e3e6f0;">
                    <li class="breadcrumb-item small"><a href="{{ route('home') }}" class="text-secondary text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item active small text-primary fw-bold" aria-current="page">Database Calon Peserta{{ $userRole === 'cs-mbc' ? '' : ' M1T' }}</li>
                </ol>
            </nav>
        </div>
    @endif



    </div>
    </form>

    {{-- ALERT MODE READ ONLY (ADMIN) --}}
    @if(isset($user) && ($readonly ?? false))
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
            <div>
                <strong>Database CS:</strong> <strong>{{ $user->name }} </strong> <br>
                <span class="text-muted small">Email: {{ $user->email }} | Role: {{ ucfirst($user->role) }}</span>
            </div>
            <div>
                <span class="text-white badge bg-primary p-2">Mode Read-Only</span>
            </div>
        </div>

        @if(auth()->user()->name !== 'Agus Setyo')
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-comments me-2"></i> Komentar untuk {{ $user->name }}
                </div>
                <div class="card-body">
                    {{-- Form Kirim Komentar --}}
                    <form id="formKomentar" method="POST" action="{{ route('komentar.store') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <div class="input-group mb-3">
                            <input type="text" name="pesan" class="form-control" placeholder="Tulis komentar untuk CS ini..."
                                required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim
                            </button>
                        </div>
                    </form>
                    @if(session('success'))
                        <script>
                            Swal.fire({
                                title: 'Berhasil!',
                                text: '{{ session('success') }}',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        </script>
                    @endif


                    <button class="btn btn-outline-secondary btn-sm mb-2" data-toggle="modal" data-target="#modalKomentar">
                        <i class="fas fa-history"></i> Lihat Riwayat Komentar
                    </button>

                    <div class="modal fade" id="modalKomentar" tabindex="-1" role="dialog" aria-labelledby="modalKomentarLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title" id="modalKomentarLabel">
                                        <i class="fas fa-comments me-2"></i> Riwayat Komentar
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    @foreach($komentar as $msg)
                                        <div class="alert alert-light border d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $msg->admin->name ?? 'Admin' }}</strong><br>
                                                <span class="text-dark">{{ $msg->pesan }}</span><br>
                                                <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                            </div>
                                            <i class="fas fa-comment-dots text-warning"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif





    <div class="content">
        <div class="card card-info card-outline">
            @php
                use Carbon\Carbon;
                use App\Models\Data;

                // $currentUser used in logic below
                $currentUser = auth()->user();

                // Ensure variables are defined if not passed (fallback for edge cases)
                $now = Carbon::now();
                if (!isset($bulanLabel))
                    $bulanLabel = $now->isoFormat('MMMM YYYY');
                if (!isset($databaseBaru))
                    $databaseBaru = 0;
                if (!isset($totalDatabase))
                    $totalDatabase = 0;
                if (!isset($target))
                    $target = (strtolower(auth()->user()->role) === 'administrator' ? 250 : 50);
                if (!isset($kurang))
                    $kurang = 0;
                if (!isset($data))
                    $data = collect([]);

            @endphp



            <div class="card-header">
                {{-- Stats Cards Section (Moved to Top) --}}
                <style>
                    .stat-card-group {
                        display: flex;
                        gap: 15px;
                        flex-wrap: wrap;
                    }

                    .g-stat-card {
                        display: flex;
                        align-items: center;
                        padding: 10px 15px;
                        border-radius: 12px;
                        color: white;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        transition: all 0.3s ease;
                        min-width: 140px;
                        /* Slightly reduced */
                        position: relative;
                        overflow: hidden;
                        flex: 1;
                        /* Allow growing */
                    }

                    .g-stat-card:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
                    }

                    .g-stat-card::after {
                        content: '';
                        position: absolute;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        left: 0;
                        background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.2), transparent);
                        pointer-events: none;
                    }

                    /* Gradients */
                    .g-sc-cyan {
                        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
                    }

                    .g-sc-blue {
                        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                    }

                    .g-sc-yellow {
                        background: linear-gradient(135deg, #ffca2c 0%, #ffc107 100%);
                        color: #212529;
                    }

                    .g-sc-red {
                        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
                    }

                    .g-sc-content {
                        display: flex;
                        flex-direction: column;
                        z-index: 1;
                    }

                    .g-sc-label {
                        font-size: 0.75rem;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        opacity: 0.9;
                        font-weight: 700;
                        margin-bottom: 2px;
                    }

                    .g-sc-value {
                        font-size: 1.25rem;
                        font-weight: 800;
                        line-height: 1.1;
                    }

                    .g-sc-sub {
                        font-size: 0.65rem;
                        opacity: 0.9;
                        margin-top: 2px;
                    }

                    .g-sc-icon {
                        margin-left: auto;
                        font-size: 1.8rem;
                        opacity: 0.3;
                        z-index: 1;
                        margin-bottom: -5px;
                    }
                </style>


                @if(!in_array($userRole, ['chapter', 'reseller']))
                    <div class="stat-card-group mb-4">
                        <!-- Database Baru -->
                        <div class="g-stat-card g-sc-cyan">
                            <div class="g-sc-content">
                                <span class="g-sc-label">Database Baru</span>
                                <span class="g-sc-value" id="statDatabaseBaru">{{ $databaseBaru }}</span>
                                <span class="g-sc-sub" id="statBulanLabel">{{ $bulanLabel }}</span>
                            </div>
                            <div class="g-sc-icon"><i class="fas fa-database"></i></div>
                        </div>

                        <!-- Total Database -->
                        <div class="g-stat-card g-sc-blue">
                            <div class="g-sc-content">
                                <span class="g-sc-label">Total Database</span>
                                <span class="g-sc-value" id="statTotalDatabase">{{ $totalDatabase }}</span>
                            </div>
                            <div class="g-sc-icon"><i class="fas fa-layer-group"></i></div>
                        </div>

                        <!-- Target -->
                        <div class="g-stat-card g-sc-yellow">
                            <div class="g-sc-content">
                                <span class="g-sc-label">Target Bulanan</span>
                                <span class="g-sc-value">{{ $target }}</span>
                            </div>
                            <div class="g-sc-icon"><i class="fas fa-bullseye"></i></div>
                        </div>

                        <!-- Kurang -->
                        <div class="g-stat-card g-sc-red text-white">
                            <div class="g-sc-content">
                                <span class="g-sc-label text-white">Kurang</span>
                                <span class="g-sc-value" id="statKurang">{{ $kurang }}</span>
                            </div>
                            <div class="g-sc-icon text-white"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                @endif

                {{-- Toolbar Actions Row --}}
                <div class="d-flex justify-content-between align-items-center flex-nowrap gap-2 overflow-x-auto pb-2">
                    <!-- Kiri: Tombol Tambah -->
                    <div class="d-flex align-items-center">
                        @if(!in_array($userRole, ['administrator', 'manager', 'marketing']) && !(auth()->user()->name === 'Linda' && request('view') !== 'me'))
                            @php
                               $slugName = ($userRole === 'chapter' && !empty(auth()->user()->chapter))
                                           ? 'chapter-' . strtolower(str_replace(' ', '-', auth()->user()->chapter)) 
                                           : (!empty(auth()->user()->username) ? auth()->user()->username : strtolower(str_replace(' ', '-', auth()->user()->name)));
                            @endphp
                            <a href="#" class="btn btn-success mr-2" id="btnAddRow" onclick="createNewRow(event)">
                                <i class="fa-solid fa-plus"></i> Tambah
                            </a>
                            @if($userRole !== 'cs-mbc')
                                <a href="{{ route('form.m1t', $slugName) }}" class="btn btn-success mr-2" target="_blank" style="background-color: #20c997; border-color: #20c997;">
                                    <i class="fa-solid fa-link"></i> Tambah Via Link
                                </a>
                            @endif
                        @endif
                        <button type="button" id="btnInteraksi"
                            class="btn btn-primary d-flex align-items-center gap-2 px-3 shadow-sm rounded-pill {{ (request('bulan') && request('tahun')) ? '' : 'd-none' }}"
                            onclick="exportPdfInteraksi()"
                            style="background: linear-gradient(45deg, #1d4ed8, #2563eb); border: none; font-weight: 600;">
                            <i class="fas fa-file-pdf"></i> Follow Up
                        </button>
                        
                        @if(in_array($userRole, ['chapter', 'reseller']))
                            <div class="ml-2 px-2 py-1 bg-light border rounded-pill shadow-sm d-flex align-items-center">
                                <i class="fas fa-database text-info mr-1"></i>
                                <span class="text-xs font-weight-bold text-gray-800">Total Database: <span class="text-primary">{{ number_format($data->count() ?? 0, 0, ',', '.') }}</span></span>
                            </div>
                        @endif
                    </div>

                    <!-- Kanan: Toolbar Filter & Search -->
                    <!-- Kanan: Toolbar Filter & Search -->
                    <div class="d-flex align-items-center justify-content-end gap-2" style="flex: 1;">
                        <style>
                            .modern-filter-container {
                                display: flex;
                                align-items: flex-end;
                                gap: 10px;
                                flex-wrap: nowrap;
                            }

                            .modern-select {
                                border-radius: 50px !important;
                                border: 1px solid #e0e0e0;
                                background-color: #fff;
                                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
                                font-size: 0.75rem;
                                padding: 2px 22px 2px 8px;
                                transition: all 0.2s ease;
                                cursor: pointer;
                                min-height: 30px;
                            }

                            .modern-select:hover {
                                border-color: #b0c4de;
                                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
                                transform: translateY(-1px);
                            }

                            .modern-select:focus {
                                border-color: #86b7fe;
                                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
                                outline: 0;
                            }

                            .modern-search-group {
                                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
                                border-radius: 50px;
                                overflow: hidden;
                                display: flex;
                            }

                            .modern-search-input {
                                border: 1px solid #e0e0e0;
                                border-right: none;
                                padding-left: 20px;
                                font-size: 0.9rem;
                                border-top-left-radius: 50px;
                                border-bottom-left-radius: 50px;
                            }

                            .modern-search-input:focus {
                                box-shadow: none;
                                border-color: #e0e0e0;
                            }

                            .modern-search-btn {
                                border-radius: 0 50px 50px 0 !important;
                                padding-left: 20px;
                                padding-right: 20px;
                                font-weight: 600;
                            }
                        </style>
                        <div class="modern-filter-container">
                            @php
                                use App\Models\User;

                                $user = auth()->user();
                                $csList = collect();

                                // Daftar CS hanya untuk admin/manager
                                // Daftar CS dan Chapter hanya untuk admin/manager
                                if (in_array(strtolower($user->role), ['administrator', 'manager', 'marketing']) || $user->name === 'Agus Setyo' || $user->name === 'Linda') {
                                    $csList = User::where('role', 'cs-mbc')
                                        ->where('is_active', 1)
                                        ->select('id', 'name')
                                        ->orderBy('name')
                                        ->get();
                                        
                                    $chapterList = User::where('role', 'chapter')
                                        ->select('id', 'name', 'chapter')
                                        ->orderBy('name')
                                        ->get();
                                }
                            @endphp

                            @if(!in_array($userRole, ['reseller', 'chapter']))
                                <div class="d-flex flex-column" style="gap: 2px;">
                                    <label class="text-xs fw-bold mb-0 ml-2" style="font-size: 0.65rem; color: #555; text-transform: uppercase;">Pilih Status Ikut Kelas</label>
                                    <select id="filterIkutKelas" class="form-select form-select-sm modern-select"
                                        onchange="toggleDaftarKelas(this.value)">
                                        <option value="">ALL Status</option>
                                        <option value="1" {{ request('ikut_kelas') == '1' ? 'selected' : '' }}>Sudah Pernah Ikut
                                        </option>
                                        <option value="0" {{ request('ikut_kelas') == '0' ? 'selected' : '' }}>Belum Pernah Ikut
                                        </option>
                                    </select>
                                </div>
                            @endif

                            {{-- Filter Daftar Kelas (Internal SalesPlan) --}}
                            <div id="containerDaftarKelas" class="flex-column {{ (request('ikut_kelas') === '1' || request('ikut_kelas') === '0') ? 'd-flex' : 'd-none' }}" style="gap: 2px;">
                                <label class="text-xs fw-bold mb-0 ml-2" style="font-size: 0.65rem; color: #555; text-transform: uppercase;">Pilih Kelas</label>
                                <select id="filterDaftarKelas"
                                    class="form-select form-select-sm modern-select">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ request('daftar_kelas') == $k->id ? 'selected' : '' }}>
                                            {{ str_contains($k->nama_kelas, 'Muslim Indonesia') ? 'M1T' : $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                             {{-- Filter Input Oleh (Chapter) --}}
                            @if((in_array(strtolower($user->role), ['administrator', 'manager', 'marketing']) || $user->name === 'Agus Setyo') && request('view_type') !== 'cs')
                                <div class="d-flex flex-column" style="gap: 2px;">
                                    <label class="text-xs fw-bold mb-0 ml-2" style="font-size: 0.65rem; color: #555; text-transform: uppercase;">Pilih Chapter</label>
                                    <select id="filterChapter" class="form-select form-select-sm modern-select">
                                        <option value="">ALL CHAPTER</option>
                                        @if(isset($chapterList))
                                            @foreach($chapterList as $ch)
                                                <option value="{{ $ch->id }}" {{ request('chapter_id') == $ch->id ? 'selected' : '' }}>
                                                    {{ strtoupper($ch->name) }} {{ $ch->chapter ? '- ' . $ch->chapter : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif

                            {{-- Filter Input Oleh (CS) --}}
                            @if((in_array(strtolower($user->role), ['administrator', 'manager', 'marketing']) || $user->name === 'Agus Setyo') && request('view_type') !== 'chapter')
                                <div class="d-flex flex-column" style="gap: 2px;">
                                    <label class="text-xs fw-bold mb-0 ml-2" style="font-size: 0.65rem; color: #555; text-transform: uppercase;">Pilih Tim CS</label>
                                    <select id="filterCS" class="form-select form-select-sm modern-select">
                                        <option value="">ALL Tim CS</option>
                                        @foreach($csList as $cs)
                                            <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>
                                                {{ $cs->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif



                            {{-- Search Group --}}
                            <div class="modern-search-group">
                                <input type="text" id="tableSearch" class="form-control form-control-sm modern-search-input"
                                    style="width: 120px; height: 30px; font-size: 0.75rem;"
                                    placeholder="Cari..." value="{{ request('search') }}">
                                <button class="btn btn-primary btn-sm modern-search-btn" type="button"
                                    onclick="applyAllDatabaseFilters()" style="height: 30px; font-size: 0.75rem;">
                                    <i class="fas fa-search mr-1"></i> TAMPILKAN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $userRole = strtolower(auth()->user()->role);
                $isAdmin = ($userRole === 'administrator');
            @endphp

            <script>
                function updateFilters(params) {
                    var url = new URL(window.location.href);
                    for (const [key, val] of Object.entries(params)) {
                        if (val || val === '0') {
                            url.searchParams.set(key, val);
                        } else {
                            url.searchParams.delete(key);
                        }

                        if (key === 'ikut_kelas' && val === '') {
                            url.searchParams.delete('daftar_kelas');
                        }
                    }

                    url.searchParams.delete('page');

                    loadDataAjax(url);
                }

                function loadDataAjax(url) {
                    // Show loading state
                    var tableBody = document.getElementById('tableBody');
                    tableBody.style.opacity = '0.4';
                    tableBody.style.pointerEvents = 'none';

                    fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(response => response.json())
                        .then(data => {
                            tableBody.innerHTML = data.html;
                            tableBody.style.opacity = '1';
                            tableBody.style.pointerEvents = 'auto';

                            // Update pagination
                            if (data.pagination !== undefined) {
                                document.getElementById('paginationContainer').innerHTML = data.pagination;
                            }

                            if (data.stats) {
                                if (document.getElementById('statDatabaseBaru')) document.getElementById('statDatabaseBaru').innerText = data.stats.databaseBaru;
                                if (document.getElementById('statTotalDatabase')) document.getElementById('statTotalDatabase').innerText = data.stats.totalDatabase;
                                if (document.getElementById('statKurang')) document.getElementById('statKurang').innerText = data.stats.kurang;
                                if (document.getElementById('statBulanLabel')) document.getElementById('statBulanLabel').innerText = data.stats.bulanLabel;
                            }

                            window.history.pushState({}, '', url.toString());

                            // Scroll to top of table
                            document.getElementById('tableContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
                        })
                        .catch(error => {
                            console.error('Error fetching filtered data:', error);
                            tableBody.style.opacity = '1';
                            tableBody.style.pointerEvents = 'auto';
                        });
                }

                function updateFilter(key, val) {
                    let obj = {};
                    obj[key] = val;
                    updateFilters(obj);
                }

                function applyAllDatabaseFilters() {
                    const getVal = (id) => {
                        const el = document.getElementById(id);
                        return el ? el.value : '';
                    };

                    updateFilters({
                        ikut_kelas: getVal('filterIkutKelas'),
                        daftar_kelas: getVal('filterDaftarKelas'),
                        chapter_id: getVal('filterChapter'),
                        cs_name: getVal('filterCS'),
                        search: getVal('tableSearch'),
                        sumber: getVal('filterSumber'),
                        provinsi: getVal('filterProvinsi'),
                        kota: getVal('filterKota'),
                        potensi: getVal('filterPotensi'),
                        potensi_kelas_id: getVal('filterPotensiKelas')
                    });
                }

                function updatePotensiHeader(val) {
                    let kelasFilter = document.getElementById('filterPotensiKelas');
                    if (val === 'MBC') {
                        if (kelasFilter) kelasFilter.classList.remove('d-none');
                    } else {
                        if (kelasFilter) {
                            kelasFilter.classList.add('d-none');
                            kelasFilter.value = '';
                        }
                    }
                    
                    // If we just want to show the dropdown without immediate filtering, we can stop here.
                    // But usually header filters trigger immediate action.
                    updateFilters({
                        potensi: val,
                        potensi_kelas_id: val === 'MBC' ? (kelasFilter ? kelasFilter.value : '') : ''
                    });
                }

                function toggleDaftarKelas(val) {
                    let container = document.getElementById('containerDaftarKelas');
                    if (!container) return;
                    if (val === '1' || val === '0') {
                        container.classList.remove('d-none');
                        container.classList.add('d-flex');
                    } else {
                        container.classList.add('d-none');
                        container.classList.remove('d-flex');
                    }
                }

                // Intercept pagination link clicks for AJAX navigation
                document.addEventListener('DOMContentLoaded', function () {
                    const searchInput = document.getElementById('tableSearch');
                    if (searchInput) {
                        searchInput.addEventListener('keypress', function (e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                applyAllDatabaseFilters();
                            }
                        });
                    }
                });

                document.addEventListener('click', function (e) {
                    var link = e.target.closest('#paginationContainer a');
                    if (link && link.href) {
                        e.preventDefault();
                        var url = new URL(link.href);
                        loadDataAjax(url);
                    }
                });
            </script>

            <div class="card-body position-relative">
                <div id="tableContainer" class="table-scroll-container" style="overflow-x: auto; width: 100%;">

                    <table id="myTable"
                        class="table table-bordered table-striped {{ strtolower(auth()->user()->role) === 'marketing' ? '' : 'nowrap' }}"
                        style="width: {{ strtolower(auth()->user()->role) === 'marketing' ? '100%' : 'max-content' }};">
                        <thead>
                            <tr>
                                <th rowspan="2">No</th>

                                <th rowspan="2" style="min-width: 150px;">Nama & No.WA</th>
                                <th rowspan="2" style="width: 92.5px;">
                                    Sumber Leads <br>
                                        <select id="filterSumber" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                            <option value="">-- Semua --</option>
                                            <option value="Ads" {{ request('sumber') == 'Ads' ? 'selected' : '' }}>Ads</option>
                                            <option value="Sosmed" {{ request('sumber') == 'Sosmed' ? 'selected' : '' }}>Sosmed</option>
                                            <option value="Zoom" {{ request('sumber') == 'Zoom' ? 'selected' : '' }}>Zoom</option>
                                            <option value="Open House" {{ request('sumber') == 'Open House' ? 'selected' : '' }}>Open House</option>
                                            <option value="Mandiri" {{ request('sumber') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                                            @if(!in_array($userRole, ['reseller', 'agen']))
                                                <option value="Alumni" {{ request('sumber') == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                            @endif
                                        </select>
                                </th>
                                <th rowspan="2" style="width: 140px;">
                                    Prov/Kota <br>
                                    <div class="d-flex flex-column gap-1">
                                        <select id="filterProvinsi" class="form-control form-control-sm mb-1"
                                            style="font-size: 0.7rem; height: auto; padding: 2px;">
                                            <option value="">-- Prov --</option>
                                            @if(isset($provinsiList))
                                                @foreach($provinsiList as $prov)
                                                    <option value="{{ $prov }}" {{ request('provinsi') == $prov ? 'selected' : '' }}>
                                                        {{ $prov }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <select id="filterKota" class="form-control form-control-sm"
                                            style="font-size: 0.7rem; height: auto; padding: 2px;">
                                            <option value="">-- Kota --</option>
                                            @if(isset($kotaList))
                                                @foreach($kotaList as $kota)
                                                    <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>
                                                        {{ $kota }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </th>
                                <th rowspan="2" style="width: 140px;">Nama bisnis</th>
                                <th rowspan="2" style="width: 250px;">{{ in_array($userRole, ['chapter', 'reseller']) ? 'Situasi & Kendala Bisnis' : 'Situasi / Kendala bisnis' }}</th>
                                @if($userRole !== 'cs-mbc')
                                    <th rowspan="2" style="width: 120px;">
                                        Rekap Penilaian
                                        <div class="mt-1">
                                            <select class="form-control form-control-sm" 
                                                onchange="updateFilter('potensi', this.value)"
                                                style="font-size: 0.7rem; padding: 2px 5px; height: auto; border-radius: 5px;">
                                                <option value="">- ALL -</option>
                                                <option value="Cold" {{ request('potensi') == 'Cold' ? 'selected' : '' }}>Cold</option>
                                                <option value="Warm" {{ request('potensi') == 'Warm' ? 'selected' : '' }}>Warm</option>
                                                <option value="Hot" {{ request('potensi') == 'Hot' ? 'selected' : '' }}>Hot</option>
                                            </select>
                                        </div>
                                    </th>
                                @endif

                                <!-- New Columns Grouped under SPIN -->
                                <th colspan="{{ in_array($userRole, ['administrator', 'chapter', 'reseller']) ? '3' : '4' }}"
                                    class="text-center col-spin-header" style="padding: 5px 2px;">
                                    SPIN <br>
                                    <select id="filterSpin" class="form-control form-control-sm mt-1 mx-auto"
                                        onchange="updateFilter('filter_spin', this.value)"
                                        style="font-size: 0.7rem; height: auto; width: 90%;">
                                        <option value="">-- SPIN --</option>
                                        <option value="ALL" {{ request('filter_spin') == 'ALL' ? 'selected' : '' }}>ALL
                                        </option>
                                        <option value="NOT_ALL" {{ request('filter_spin') == 'NOT_ALL' ? 'selected' : '' }}>
                                            NOT ALL</option>
                                    </select>
                                </th>
                                <th rowspan="2" class="text-center col-zoom">
                                    IKUT ZOOM <br>
                                    <select class="form-control form-control-sm mt-1 px-1"
                                        onchange="updateFilter('zoom', this.value)"
                                        style="font-size: 0.7rem; height: auto;">
                                        <option value="">- ALL -</option>
                                        <option value="1" {{ request('zoom') == '1' ? 'selected' : '' }}>Ikut</option>
                                        <option value="0" {{ request('zoom') == '0' ? 'selected' : '' }}>Belum</option>
                                    </select>
                                </th>

                                @if(Auth::user()->role !== 'marketing' && !in_array($userRole, ['reseller', 'chapter']))
                                    <th rowspan="2" style="width: 140px; vertical-align: middle;">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="text-white small fw-bold mb-1" style="font-size: 0.7rem;">POTENSI</div>
                                            <select id="filterPotensi"
                                                class="form-control form-control-sm border-0 bg-white text-dark fw-bold p-0 text-center shadow-none"
                                                style="font-size: 0.7rem; cursor: pointer; height: 22px;"
                                                onchange="updatePotensiHeader(this.value)">
                                                <option value="all">ALL</option>
                                                <option value="MBC" {{ request('potensi') == 'MBC' ? 'selected' : '' }}>MBC
                                                </option>
                                                <option value="SMI" {{ request('potensi') == 'SMI' ? 'selected' : '' }}>M1T
                                                </option>
                                            </select>
                                            
                                            {{-- Filter Dependent MBC --}}
                                            <select id="filterPotensiKelas" 
                                                class="form-control form-control-sm border-0 bg-white text-dark small mt-1 {{ request('potensi') == 'MBC' ? '' : 'd-none' }}"
                                                style="font-size: 0.65rem; height: 20px; padding: 0 2px;"
                                                onchange="updateFilter('potensi_kelas_id', this.value)">
                                                <option value="">- Pilih Kelas -</option>
                                                @foreach($kelas as $k)
                                                    @if(!str_contains($k->nama_kelas, 'Muslim Indonesia'))
                                                        <option value="{{ $k->id }}" {{ request('potensi_kelas_id') == $k->id ? 'selected' : '' }}>
                                                            {{ $k->nama_kelas }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </th>
                                @endif

                                @if(in_array($userRole, ['chapter', 'reseller', 'agen', 'cs-mbc']))
                                    {{-- Status Column (Moved from Prospek as requested) --}}
                                    <th rowspan="2" class="text-center" style="min-width: 150px; background-color: #25799E !important;">
                                        Status
                                        <div class="mt-1">
                                            <select class="form-control form-control-sm" 
                                                style="font-size: 0.7rem; padding: 2px 5px; height: auto; border-radius: 5px;"
                                                onchange="updateFilter('status', this.value)">
                                                <option value="">🔍 Semua</option>
                                                <option value="cold" {{ request('status') == 'cold' ? 'selected' : '' }}>⚪ Cold</option>
                                                <option value="tertarik" {{ request('status') == 'tertarik' ? 'selected' : '' }}>🟡 Tertarik</option>
                                                <option value="sudah_transfer" {{ request('status') == 'sudah_transfer' ? 'selected' : '' }}>🔵 Sudah Transfer</option>
                                                <option value="no" {{ request('status') == 'no' ? 'selected' : '' }}>🔴 No</option>
                                            </select>
                                        </div>
                                    </th>
                                @endif


                                @if(in_array($userRole, ['administrator', 'manager', 'marketing', 'agen']) || auth()->user()->name === 'Agus Setyo')
                                    <th rowspan="2" style="width: 100px;">
                                        <div class="d-flex flex-column">
                                            <a href="javascript:void(0)"
                                                onclick="updateFilters({sort_by: 'created_by', order: '{{ (request('sort_by') == 'created_by' && request('order') == 'asc') ? 'desc' : 'asc' }}'})"
                                                class="text-white text-decoration-none d-flex align-items-center justify-content-between mb-1">
                                                <span>Input Oleh</span>
                                                <span>
                                                    @if(request('sort_by') == 'created_by')
                                                        <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                                    @else
                                                        <i class="fas fa-sort text-white-50"></i>
                                                    @endif
                                                </span>
                                            </a>
                                            <select class="form-control form-control-sm text-dark"
                                                onchange="updateFilter('cs_name', this.value)"
                                                style="min-width: 100px; font-size: 0.75rem;">
                                                <option value="">-- Semua --</option>
                                                @foreach($csList as $cs)
                                                    <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>
                                                        {{ $cs->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </th>

                                @endif

                                @if(!in_array(strtolower(auth()->user()->role), ['marketing', 'administrator']))
                                    <th rowspan="2" style="width: 80px;">Action</th>
                                @endif
                            </tr>
                            <tr>
                                <th class="text-center col-bat">B</th>
                                <th class="text-center col-bat">A</th>
                                <th class="text-center col-bat">T</th>

                                @if(!in_array($userRole, ['administrator', 'chapter', 'reseller']))
                                    <th class="text-center" style="width: 100px;">Tgl Update</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="tableBody">

                            @foreach($data as $item)
                                @include('admin.database.partials.row', ['item' => $item, 'loop' => $loop, 'kelas' => $kelas])
                            @endforeach


                        </tbody>
                    </table>

                    <script>
                        // Horizontal Floating Scroll Logic (Hanging Scroll) - Ported from Sales Plan
                        $(document).ready(function () {
                            function initFloatingScroll() {
                                const tableContainer = document.getElementById('tableContainer');
                                if (!tableContainer) return;

                                // Remove existing if any
                                $('.floating-scroll-bar').remove();

                                // Create scrollbar element
                                const floatingScroll = document.createElement('div');
                                floatingScroll.className = 'floating-scroll-bar';

                                const inner = document.createElement('div');
                                inner.style.height = '1px';
                                floatingScroll.appendChild(inner);
                                document.body.appendChild(floatingScroll);

                                const syncScroll = () => {
                                    if (!tableContainer.isConnected) return;

                                    inner.style.width = tableContainer.scrollWidth + 'px';
                                    const rect = tableContainer.getBoundingClientRect();

                                    // Show if the table's own scrollbar is below the screen and top is visible
                                    const isVisible = rect.top < window.innerHeight && rect.bottom > window.innerHeight;
                                    const hasScroll = tableContainer.scrollWidth > tableContainer.clientWidth;

                                    floatingScroll.style.display = (isVisible && hasScroll) ? 'block' : 'none';

                                    // Sync position and width
                                    floatingScroll.style.left = rect.left + 'px';
                                    floatingScroll.style.width = rect.width + 'px';

                                    // Sync scroll position with debounce
                                    if (Math.abs(floatingScroll.scrollLeft - tableContainer.scrollLeft) > 1) {
                                        floatingScroll.scrollLeft = tableContainer.scrollLeft;
                                    }
                                };

                                let isSyncing = false;
                                floatingScroll.onscroll = () => {
                                    if (isSyncing) return;
                                    isSyncing = true;
                                    tableContainer.scrollLeft = floatingScroll.scrollLeft;
                                    requestAnimationFrame(() => { isSyncing = false; });
                                };

                                tableContainer.onscroll = () => {
                                    if (isSyncing) return;
                                    isSyncing = true;
                                    floatingScroll.scrollLeft = tableContainer.scrollLeft;
                                    requestAnimationFrame(() => { isSyncing = false; });
                                };

                                window.addEventListener('scroll', syncScroll, { passive: true });
                                window.addEventListener('resize', syncScroll, { passive: true });

                                setTimeout(syncScroll, 100);
                            }

                            // Initial Init
                            initFloatingScroll();

                            // Re-init after AJAX
                            $(document).ajaxSuccess(function () {
                                setTimeout(initFloatingScroll, 500);
                            });
                        });
                    </script>

                    <!-- Script FIlter -->
                    <script>
                        $(document).ready(function () {
                            $('#filterLeads, #filterProvinsi, #filterKota, #filterJenisBisnis, #filterInputOleh').on('change', function () {
                                let filters = {
                                    leads: $('#filterLeads').val(),
                                    provinsi: $('#filterProvinsi').val(),
                                    kota: $('#filterKota').val(),
                                    jenisbisnis: $('#filterJenisBisnis').val(),
                                    created_by: $('#filterInputOleh').val(),
                                };

                                $.ajax({
                                    url: "{{ route('admin.database.filter') }}",
                                    type: "GET",
                                    data: filters,
                                    success: function (response) {
                                        $('#tableData').html(response);
                                    },
                                    // error: function() {
                                    //     alert('Gagal memuat data filter');
                                    // }
                                });
                            });
                        });
                    </script>


                    <!-- Script JQuery -->
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        $(document).ready(function () {

                            // Untuk kolom text
                            // Konsolidasi handler untuk kolom text (.editable)
                            $(document).on('focus', '.editable', function () {
                                $(this).addClass('editing');
                            });

                            $(document).on('blur', '.editable', function () {
                                let $this = $(this);
                                let value = $this.text();
                                let field = $this.data('field');
                                let id = $this.closest('tr').data('id');

                                $this.removeClass('editing');

                                $.ajax({
                                    url: "{{ url('admin/database/update-inline') }}",
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        id: id,
                                        field: field,
                                        value: value
                                    },
                                    success: function (res) {
                                        console.log('Updated:', field);
                                        showStatusIcon($this, true);
                                    },
                                    error: function (xhr) {
                                        console.error('Failed to update:', field, xhr);
                                        showStatusIcon($this, false);

                                        let msg = 'Gagal menyimpan perubahan.';
                                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Update Gagal',
                                            text: msg
                                        });
                                    }
                                });
                            });

                            // Konsolidasi handler untuk Potensi Kelas
                            $(document).on('change', '.select-potensi', function () {
                                let $this = $(this);
                                let id = $this.data('id');
                                let kelas_id = $this.val();

                                $.ajax({
                                    url: "{{ url('admin/database/update-potensi') }}/" + id,
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        kelas_id: kelas_id
                                    },
                                    success: function (response) {
                                        console.log('Potensi kelas updated');
                                        showStatusIcon($this, true);
                                    },
                                    error: function () {
                                        console.log('Failed to update potensi kelas');
                                        showStatusIcon($this, false);
                                    }
                                });
                            });

                        });
                    </script>

                    <script>
                        // Delegated event for Potensi MBC/M1T Select
                        $(document).on('change', '.select-potensi-mbc-m1t', function () {
                            let $this = $(this);
                            let id = $this.data('id');
                            let value = $this.val();
                            let $kelasContainer = $this.siblings('.potensi-kelas-container');

                            // Update colors dynamically
                            if (value === 'MBC') {
                                $this.css({ 'background-color': '#8b0000', 'color': '#fff' });
                                $kelasContainer.removeClass('d-none');
                            } else if (value === 'SMI') {
                                $this.css({ 'background-color': '#28a745', 'color': '#fff' });
                                $kelasContainer.addClass('d-none');
                            } else {
                                $this.css({ 'background-color': '#fff', 'color': 'inherit' });
                                $kelasContainer.addClass('d-none');
                            }

                            let updateData = {
                                _token: '{{ csrf_token() }}',
                                id: id,
                                field: 'potensi',
                                value: value
                            };

                            // If SMI, we also want to set the default M1T class ID
                            if (value === 'SMI') {
                                @php
                                    $m1tClass = \App\Models\Kelas::where('nama_kelas', 'like', '%Muslim Indonesia%')->first();
                                    $m1tId = $m1tClass ? $m1tClass->id : null;
                                @endphp
                                @if($m1tId)
                                    updateData.updates = {
                                        potensi: value,
                                        kelas_id: {{ $m1tId }}
                                    };
                                    delete updateData.field;
                                    delete updateData.value;
                                @endif
                            }

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: updateData,
                                success: function (res) {
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Potensi Updated'
                                    });
                                }
                            });
                        });

                        // Delegated event for Secondary Kelas Select
                        $(document).on('change', '.select-potensi-kelas', function () {
                            let $this = $(this);
                            let id = $this.data('id');
                            let value = $this.val();

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: 'kelas_id',
                                    value: value
                                },
                                success: function (res) {
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Kelas Updated'
                                    });
                                }
                            });
                        });

                        // Delegated event for Checkboxes (Spin, Zoom, BANT components)
                        $(document).on('change', '.check-spin, .check-zoom, .check-bant-budget, .check-bant-authority, .check-bant-time', function () {
                            let $this = $(this);
                            let id = $this.data('id');
                            let field = '';

                            if ($this.hasClass('check-spin')) field = 'berhasil_spin';
                            else if ($this.hasClass('check-zoom')) field = 'ikut_zoom';
                            else if ($this.hasClass('check-bant-budget')) field = 'bant_budget';
                            else if ($this.hasClass('check-bant-authority')) field = 'bant_authority';
                            else if ($this.hasClass('check-bant-time')) field = 'bant_time';

                            let value = $this.is(':checked') ? 1 : 0;
                            $this.prop('disabled', true);

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                },
                                complete: function () {
                                    $this.prop('disabled', false);
                                },
                                success: function (res) {
                                    console.log('Updated checkbox:', field);

                                    // Toggle visibility of Potensi & SalesPlan columns based on any SPIN checkbox
                                    let $row = $this.closest('tr');
                                    let isBudget = $row.find('.check-bant-budget').is(':checked');
                                    let isAuthority = $row.find('.check-bant-authority').is(':checked');
                                    let isTime = $row.find('.check-bant-time').is(':checked');

                                    if (isBudget && isAuthority && isTime) {
                                        $row.find('.spin-content').removeClass('d-none');
                                    } else {
                                        $row.find('.spin-content').addClass('d-none');
                                    }

                                    // Show Toast Success
                                    const Toast = Swal.mixin({
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Status Updated'
                                    });
                                },
                                error: function (xhr) {
                                    console.error('Error updating checkbox:', xhr);
                                    let msg = 'Gagal update status.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Update Gagal',
                                        text: msg
                                    });
                                    // Revert checkbox state
                                    $this.prop('checked', !$this.is(':checked'));
                                }
                            });
                        });

                        // Toggle Read More
                        $(document).on('click', '.btn-read-more', function (e) {
                            e.preventDefault();
                            const $this = $(this);
                            const $container = $this.siblings('.read-more-container');

                            if ($container.hasClass('expanded')) {
                                $container.removeClass('expanded');
                                $this.text('Baca Selengkapnya');
                            } else {
                                $container.addClass('expanded');
                                $this.text('Tutup');
                            }
                        });

                        function createNewRow(e) {
                            if (e) e.preventDefault();

                            $.ajax({
                                url: '{{ route("admin.database.createDraft") }}',
                                method: 'POST',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (response) {
                                    if (response.success) {
                                        // Prepend to tbody
                                        $('#myTable tbody').prepend(response.html);

                                        let $newRow = $('#myTable tbody tr:first');

                                        // Populate Provinces for the new row
                                        if (window.populateProvinceRow) {
                                            window.populateProvinceRow($newRow);
                                        }

                                        // Optional: Highlight row or focus name
                                        $newRow.css('background-color', '#d4edda').animate({ backgroundColor: '#fff' }, 2000);
                                    }
                                },
                                error: function (xhr) {
                                    let msg = 'Gagal menambah baris baru.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        msg += '\n' + xhr.responseJSON.message;
                                    }
                                    alert(msg);
                                }
                            });
                        }

                        // Toggle No Potential
                        $(document).on('click', '.btn-no-potensi', function () {
                            let id = $(this).data('id');
                            let $row = $(this).closest('tr');

                            Swal.fire({
                                title: 'Tandai Tidak Potensi?',
                                text: "Data akan ditandai merah dan dipindah ke urutan paling belakang.",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#e74a3b',
                                cancelButtonColor: '#858796',
                                confirmButtonText: 'Ya, Tandai!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: "{{ url('admin/database') }}/" + id + "/toggle-no-potensi",
                                        method: 'POST',
                                        data: { _token: '{{ csrf_token() }}' },
                                        success: function (res) {
                                            if (res.success) {
                                                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                                    // Reload page to apply new sorting
                                                    location.reload();
                                                });
                                            }
                                        },
                                        error: function () {
                                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui status.', 'error');
                                        }
                                    });
                                }
                            });
                        });
                    </script>
                    <style>
                        .editable {
                            cursor: pointer;
                        }

                        .editing {
                            background-color: #fff3cd !important;
                            /* kuning saat edit */
                        }

                        .status-icon {
                            margin-left: 5px;
                            font-size: 14px;
                        }

                        .status-success {
                            color: green;
                        }

                        .status-error {
                            color: red;
                        }
                    </style>

                    <script>
                        $(document).ready(function () {

                            // Handler sudah dikonsolidasi di atas (line 642)

                            // Untuk dropdown Potensi Kelas
                            $('.select-potensi').on('change', function () {
                                let $this = $(this);
                                let id = $this.data('id');
                                let kelas_id = $this.val();
                                let iconSpan = $this.next('.status-icon');

                                $.ajax({
                                    url: `/admin/database/update-potensi/${id}`,
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        kelas_id: kelas_id
                                    },
                                    success: function () {
                                        iconSpan.html('<i class="fa fa-check status-success"></i>');
                                        setTimeout(() => iconSpan.html(''), 2000);
                                    },
                                    error: function () {
                                        iconSpan.html('<i class="fa fa-times status-error"></i>');
                                        setTimeout(() => iconSpan.html(''), 2000);
                                    }
                                });
                            });

                            // Fungsi tampil icon centang atau silang
                            function showStatusIcon($element, success) {
                                let iconHtml = success ?
                                    '<i class="fa fa-check status-success"></i>' :
                                    '<i class="fa fa-times status-error"></i>';

                                let iconSpan = $('<span class="status-icon">' + iconHtml + '</span>');
                                $element.after(iconSpan);

                                setTimeout(() => {
                                    iconSpan.fadeOut(300, function () {
                                        $(this).remove();
                                    });
                                }, 2000);
                            }

                        });
                    </script>



                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="d-flex justify-content-center mt-4">
                    {{ $data->withQueryString()->links('pagination::bootstrap-4') }}
                </div>

            </div>


        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('tableSearch')?.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    updateFilter('search', this.value);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // Global variables to cache default province list
            let cachedProvinces = [];

            // Helper: Populate specific select elements
            function populateProvinceSelect($elements) {
                if (cachedProvinces.length === 0) return;

                $elements.each(function () {
                    let $select = $(this);
                    // check if already populated to avoid potential overwrite issues if logic changes
                    if ($select.children('option').length > 1) return;

                    let currentNama = $select.data('nama');

                    // Keep existing "Pilih" if exists
                    let $default = $select.find('option:first');
                    $select.empty().append($default);

                    cachedProvinces.forEach(function (prov) {
                        let isSelected = (currentNama && currentNama.toUpperCase() === prov.name.toUpperCase()) ? 'selected' : '';
                        $select.append(`<option value="${prov.id}" data-name="${prov.name}" ${isSelected}>${prov.name}</option>`);
                    });
                });
            }

            // 1. Fetch Provinces & Populate
            $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function (provinces) {
                // Sort: Alphabetical
                provinces.sort((a, b) => a.name.localeCompare(b.name));
                cachedProvinces = provinces;

                // Populate existing rows
                populateProvinceSelect($('.select-provinsi'));

                // Also populate Header Filter
                let $filterProv = $('#filterProvinsi');
                cachedProvinces.forEach(function (prov) {
                    // Avoid duplicate append if run multiple times
                    if ($filterProv.find(`option[value="${prov.name}"]`).length === 0) {
                        $filterProv.append(`<option value="${prov.name}" data-id="${prov.id}">${prov.name}</option>`);
                    }
                });
            });

            // Expose populate function purely for local usage pattern if needed, 
            // but better to attach a listener or just call it from createNewRow.

            // We attach it to window so createNewRow can access it if defined outside (though it is defined outside doc.ready)
            window.populateProvinceRow = function ($row) {
                if (cachedProvinces.length > 0) {
                    populateProvinceSelect($row.find('.select-provinsi'));
                } else {
                    // retry if not yet loaded? usually loaded by the time user clicks add
                }
            };

            // 2. Change Province -> Find Cities & Save
            $(document).on('change', '.select-provinsi', function () {
                let $select = $(this);
                let id = $select.data('id');
                let provId = $select.val();
                let provName = $select.find(':selected').data('name');

                let $kotaSelect = $select.closest('tr').find('.select-kota');

                // Save to DB
                if (provId) {
                    $.post('/admin/database/update-location', {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        provinsi_id: provId,
                        provinsi_nama: provName
                    }).done(function () {
                        console.log('Provinsi saved');
                    });

                    // Load Cities
                    loadCities(provId, $kotaSelect);
                } else {
                    $kotaSelect.empty().append('<option value="">-- Pilih Kota --</option>');
                }
            });

            // 3. Change City -> Save
            $(document).on('change', '.select-kota', function () {
                let $select = $(this);
                let id = $select.data('id');
                let kotaId = $select.val();
                let kotaName = $select.find(':selected').data('name');

                if (kotaId) {
                    $.post('/admin/database/update-location', {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        kota_id: kotaId,
                        kota_nama: kotaName
                    }).done(function () {
                        console.log('Kota saved');
                    });
                }
            });

            // 3a. Change Potensi -> Save
            $(document).on('change', '.select-potensi', function () {
                let $select = $(this);
                let id = $select.data('id');
                let val = $select.val();

                // Update Class for Colors
                $select.removeClass('bg-success bg-danger bg-light text-white text-dark text-muted');
                if (val === 'SMI') $select.addClass('bg-success text-dark');
                else if (val === 'MBC') $select.addClass('bg-danger text-white');
                else $select.addClass('bg-light text-muted');

                $.post('/admin/database/update-inline', {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    updates: { potensi: val }
                }).done(function () {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Potensi berhasil diperbarui'
                    });
                });
            });

            // 4. Lazy Load Cities on Click (if not populated)
            $(document).on('click', '.select-kota', function () {
                let $kotaSelect = $(this);
                // Only load if we haven't loaded options yet (length <= 1 means only default option)
                // And ensure we have a province selected
                if ($kotaSelect.children('option').length <= 1) {
                    let $provSelect = $kotaSelect.closest('tr').find('.select-provinsi');
                    let provId = $provSelect.val();

                    if (provId) {
                        loadCities(provId, $kotaSelect);
                    } else {
                        // Try to resolve province ID from its text if user hasn't touched it? 
                        // Difficult because we haven't mapped ID to the initial text unless content matched.
                        if ($provSelect.find('option:selected').val()) {
                            loadCities($provSelect.find('option:selected').val(), $kotaSelect);
                        }
                    }
                }
            });

            function loadCities(provId, $targetSelect) {
                $targetSelect.empty().append('<option value="">Loading...</option>');

                $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`, function (cities) {
                    cities.sort((a, b) => a.name.localeCompare(b.name));

                    $targetSelect.empty().append('<option value="">-- Pilih Kota --</option>');

                    let currentKota = $targetSelect.data('nama');

                    cities.forEach(function (city) {
                        let isSelected = (currentKota && currentKota.toUpperCase() === city.name.toUpperCase()) ? 'selected' : '';
                        $targetSelect.append(`<option value="${city.id}" data-name="${city.name}" ${isSelected}>${city.name}</option>`);
                    });
                });
            }


            // Note: older applyFilters function defined in document.ready above might conflict if not careful.
            // We are overriding or extending functionality. The previous script block used "applyFilters" name. 
            // Since we are inside the same doc.ready (effectively), we should be careful. 
            // To be safe, we'll assume the previous separate scripts might need consolidation, 
            // but typically later script specific listeners will run.
            // We explicitly attach applyTableFilters to the new inputs.
        });
    </script>
    @if(auth()->user()->role === 'administrator')
        <!-- Modal Detail Bisnis & Situasi -->
        <div class="modal fade" id="modalBisnis" tabindex="-1" role="dialog" aria-labelledby="modalBisnisLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title fw-bold" id="modalBisnisLabel"><i class="fas fa-business-time me-2"></i> Bisnis &
                            Situasi: <span class="detailNama"></span></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="bg-light p-3 rounded shadow-sm border">
                            <label class="text-muted text-uppercase fw-bold mb-1 d-block"
                                style="font-size: 0.65rem; letter-spacing: 0.5px;">Nama Bisnis</label>
                            <h6 class="fw-bold mb-1 detailNamaBisnis" style="color: #1e293b;"></h6>
                            <p class="text-info small mb-0 detailJenisBisnis"></p>

                            <hr class="my-3" style="border-top: 1px dashed #cbd5e1;">

                            <label class="text-muted text-uppercase fw-bold mb-1 d-block"
                                style="font-size: 0.65rem; letter-spacing: 0.5px;">Situasi Bisnis</label>
                            <p class="mb-0 text-dark" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.9rem;"
                                id="detailSituasiBisnis"></p>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                        <button type="button" class="btn btn-secondary fw-bold px-4 rounded-pill"
                            data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail Kendala -->
        <div class="modal fade" id="modalKendala" tabindex="-1" role="dialog" aria-labelledby="modalKendalaLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title fw-bold" id="modalKendalaLabel"><i class="fas fa-exclamation-circle me-2"></i>
                            Kendala: <span class="detailNama"></span></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="bg-light p-3 rounded shadow-sm border border-info" style="min-height: 120px;">
                            <p class="mb-0 text-dark" style="white-space: pre-wrap;" id="detailKendalaContent"></p>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                        <button type="button" class="btn btn-secondary fw-bold px-4 rounded-pill"
                            data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).on('click', '.btn-view-bisnis', function () {
                const data = $(this).data();
                $('.detailNama').text(data.nama);
                $('.detailNamaBisnis').text(data.bisnis || '-');
                $('.detailJenisBisnis').text(data.jenis || '-');
                $('#detailSituasiBisnis').text(data.situasi || '-');
                $('#modalBisnis').modal('show');
            });

            $(document).on('click', '.btn-view-kendala', function () {
                const data = $(this).data();
                $('.detailNama').text(data.nama);
                $('#detailKendalaContent').text(data.kendala || '-');
                $('#modalKendala').modal('show');
            });
        </script>
    @endif

    {{-- MODAL MOVE TO SALES PLAN --}}
    <div class="modal fade" id="modalMoveSalesPlan" tabindex="-1" role="dialog" aria-labelledby="modalMoveSalesPlanLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalMoveSalesPlanLabel">@if(in_array($userRole, ['reseller', 'chapter'])) Pindahkan ke Prospek @else Masukkan ke Sales Plan @endif</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formMoveSalesPlan" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <p>Anda akan memindahkan <strong id="modalNamaPeserta"></strong> ke @if(in_array($userRole, ['reseller', 'chapter'])) Prospek @else Sales Plan @endif.
                        @if(!in_array($userRole, ['reseller', 'chapter']))
                            Silakan pilih <span id="textPotensi">Potensi Kelas Pertama</span>:
                        @endif</p>
                        <div class="form-group">
                            <label class="font-weight-bold">Pilih Kelas :</label>
                            <div id="checkbox-kelas-container" class="border rounded p-3 bg-white shadow-sm text-left"
                                style="max-height: 250px; overflow-y: auto; border: 1px solid #e3e6f0 !important;">
                                @foreach($kelas as $k)
                                    @php
                                        $isM1T = str_contains($k->nama_kelas, 'Muslim Indonesia');
                                        $showCheckbox = true;
                                        if(in_array($userRole, ['reseller', 'chapter']) && !$isM1T) {
                                            $showCheckbox = false;
                                        }
                                    @endphp
                                    @if($showCheckbox)
                                        <div class="custom-control custom-checkbox mb-2 ml-1">
                                            <input type="checkbox" name="kelas_id[]" value="{{ $k->id }}"
                                                class="custom-control-input checkbox-kelas-item" id="kelas_{{ $k->id }}">
                                            <label class="custom-control-label fw-bold text-dark" for="kelas_{{ $k->id }}"
                                                style="cursor: pointer; font-size: 0.9rem;">{{ $isM1T ? 'M1T' : $k->nama_kelas }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div id="joinedClassesContainer" class="mt-2">
                            <!-- Badges will be inserted here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">@if(in_array($userRole, ['reseller', 'chapter'])) Pindahkan ke Prospek @else Masukkan ke Salesplan @endif</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.btn-trigger-salesplan', function () {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let kelasId = $(this).data('kelas');
            let isSalesplan = $(this).data('is-salesplan');
            let joinedClasses = $(this).data('joined-classes');

            let url = `/admin/database/${id}/tambah-salesplan`;

            $('#modalMoveSalesPlan').modal('show');
            $('#modalNamaPeserta').text(nama);
            $('#formMoveSalesPlan').attr('action', url);
            $('#formMoveSalesPlan select[name="kelas_id"]').val(kelasId);

            // Update text based on isSalesplan
            if (isSalesplan == '1') {
                $('#textPotensi').text('Potensi Kelas Selanjutnya');
            } else {
                $('#textPotensi').text('Potensi Kelas Pertama');
            }

            // Reset all checkboxes first
            $('.checkbox-kelas-item').prop('checked', false);

            // Check the current class if it exists
            if (kelasId) {
                $(`#kelas_${kelasId}`).prop('checked', true);
            }

            // Display joined classes badges
            let container = $('#joinedClassesContainer');
            container.empty();
            if (joinedClasses) {
                container.append('<label class="d-block mb-1" style="font-size: 0.8rem; font-weight: 600; color: #666;">Sudah Ikut:</label>');
                let classes = joinedClasses.split(',');
                classes.forEach(function (className) {
                    container.append(`<span class="badge bg-success text-white mr-1 mb-1 shadow-sm" style="padding: 5px 8px;">${className}</span>`);
                });
            }
        });


    </script>

    <!-- Modal Riwayat FU -->
    <div class="modal fade" id="modalRiwayat" tabindex="-1" role="dialog" aria-labelledby="modalRiwayatLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalRiwayatLabel">
                        <i class="fas fa-history me-2"></i> Riwayat Follow Up - <span id="namaPeserta"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-2 bg-light text-dark">
                    <input type="hidden" id="riwayat_data_id">
                    <div class="d-flex flex-wrap pb-2" id="fuCardsContainer">
                        @for($i = 1; $i <= 10; $i++)
                            <div class="px-1 mb-3 fu-card-container d-none" id="fu_card_{{ $i }}"
                                style="width: 20%; flex: 0 0 20%;" data-index="{{ $i }}">
                                <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                                    <div class="bg-warning text-dark fw-bold px-2 py-1 d-flex justify-content-between align-items-center"
                                        style="font-size: 0.75rem;">
                                        <span class="text-uppercase" style="font-weight: 900 !important;">Follow Up {{ $i }}</span>
                                        <input type="text" class="fu-at-input border-0 rounded px-1 text-center"
                                            id="fu{{ $i }}_at"
                                            {{ auth()->user()->role === 'administrator' ? 'readonly' : '' }}
                                            style="font-size: 0.6rem; width: 85px; height: 18px; outline: none; background: rgba(255,255,255,0.8);"
                                            placeholder="-">
                                    </div>
                                    <div class="card-body p-2 bg-white">
                                        <div class="row no-gutters text-center mb-2 border rounded bg-light overflow-hidden"
                                            style="margin-left: -2px; margin-right: -2px;">
                                            <div class="col-6 py-1 border-right">
                                                <div class="fw-bold text-uppercase text-dark"
                                                    style="font-size: 0.6rem; letter-spacing: 0.5px; font-weight: 800 !important;">
                                                    WA</div>
                                                <input type="checkbox" id="fu{{ $i }}_wa" class="fu-checkbox"
                                                    {{ auth()->user()->role === 'administrator' ? 'disabled' : '' }}
                                                    style="transform: scale(0.95);">
                                            </div>
                                            <div class="col-6 py-1">
                                                <div class="fw-bold text-uppercase text-dark"
                                                    style="font-size: 0.6rem; letter-spacing: 0.5px; font-weight: 800 !important;">
                                                    TELP</div>
                                                <input type="checkbox" id="fu{{ $i }}_telp" class="fu-checkbox"
                                                    {{ auth()->user()->role === 'administrator' ? 'disabled' : '' }}
                                                    style="transform: scale(0.95);">
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label for="fu{{ $i }}_hasil" class="fw-bold text-dark text-uppercase mb-0"
                                                style="font-size: 0.6rem; display: block; font-weight: 800 !important;">Hasil
                                                FU</label>
                                            <textarea class="form-control form-control-sm border bg-white shadow-sm p-1"
                                                id="fu{{ $i }}_hasil" rows="5"
                                                {{ auth()->user()->role === 'administrator' ? 'readonly' : '' }}
                                                style="font-size: 0.8rem; border-radius: 4px; resize: none; line-height: 1.2; font-weight: 700 !important; border-color: #dee2e6 !important;"
                                                placeholder="Hasil..."></textarea>
                                        </div>
                                        <div class="border-top border-secondary opacity-25 my-2"
                                            style="border-style: dashed !important;"></div>
                                        <div>
                                            <label for="fu{{ $i }}_tindak_lanjut" class="fw-bold text-dark text-uppercase mb-0"
                                                style="font-size: 0.6rem; display: block; font-weight: 800 !important;">Tindak
                                                Lanjut</label>
                                            <textarea class="form-control form-control-sm border bg-white shadow-sm p-1"
                                                id="fu{{ $i }}_tindak_lanjut" rows="4"
                                                {{ auth()->user()->role === 'administrator' ? 'readonly' : '' }}
                                                style="font-size: 0.8rem; border-radius: 4px; resize: none; line-height: 1.2; font-weight: 700 !important; border-color: #dee2e6 !important;"
                                                placeholder="Next..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor

                        @if(auth()->user()->role !== 'administrator')
                            <!-- Tombol Tambah SPIN -->
                            <div class="px-1 mb-3 d-flex align-items-center justify-content-center"
                                style="width: 20%; flex: 0 0 20%;">
                                <button type="button"
                                    class="btn btn-outline-primary border-dashed rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center p-3"
                                    id="btnTambahSpin"
                                    style="height: 100%; border: 2px dashed #4e73df; background: #f8f9fc; transition: all 0.3s; width: 100%; min-height: 200px;">
                                    <i class="fas fa-plus-circle fa-2x mb-2 text-primary"></i>
                                    <span class="fw-bold text-primary" style="font-size: 0.85rem;">Tambah Follow Up</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4 btn-sm" data-dismiss="modal">Tutup</button>
                    @if(auth()->user()->role !== 'administrator')
                        <button type="button" class="btn btn-primary px-4 btn-sm shadow-sm" id="btnSimpanRiwayat">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#modalRiwayat').modal({
                backdrop: 'static',
                keyboard: false,
                show: false
            });
        });

        $(document).on('click', '.btn-riwayat', function () {
            let $btn = $(this);
            let id = $btn.data('id');
            let nama = $btn.data('nama');

            $('#riwayat_data_id').val(id);
            $('#namaPeserta').text(nama);

            // Hide all cards first
            $('.fu-card-container').addClass('d-none');

            // Populate fields and show cards that have data
            let lastVisibleIndex = 1;
            for (let i = 1; i <= 10; i++) {
                let hasil = $btn.attr('data-fu' + i + '-hasil') || $btn.attr('data-fu' + i) || '';
                let tindak = $btn.attr('data-fu' + i + '-tindak-lanjut') || '';
                let wa = $btn.attr('data-fu' + i + '-wa') == 1;
                let telp = $btn.attr('data-fu' + i + '-telp') == 1;
                let dateVal = $btn.attr('data-fu' + i + '-at');

                $('#fu' + i + '_hasil').val(hasil);
                $('#fu' + i + '_tindak_lanjut').val(tindak);
                $('#fu' + i + '_wa').prop('checked', wa);
                $('#fu' + i + '_telp').prop('checked', telp);
                $('#fu' + i + '_at').val(dateVal ? dateVal : '');

                // Show card if it has data or if it's the first card
                if (hasil !== '' || tindak !== '' || wa || telp || i === 1) {
                    $('#fu_card_' + i).removeClass('d-none');
                    lastVisibleIndex = i;
                }
            }

            $('#modalRiwayat').modal('show');
        });

        // Event Tambah SPIN
        $('#btnTambahSpin').on('click', function () {
            // Find the next hidden card
            let $nextCard = $('.fu-card-container.d-none').first();
            if ($nextCard.length) {
                $nextCard.removeClass('d-none');
            } else {
                Swal.fire({
                    icon: 'info',
                    text: 'Maksimal 10 Follow Up tercapai.'
                });
            }
        });

        $('#btnSimpanRiwayat').on('click', function () {
            let id = $('#riwayat_data_id').val();
            let updates = {};
            for (let i = 1; i <= 10; i++) {
                updates['fu' + i + '_hasil'] = $('#fu' + i + '_hasil').val();
                updates['fu' + i + '_tindak_lanjut'] = $('#fu' + i + '_tindak_lanjut').val();
                updates['fu' + i + '_wa'] = $('#fu' + i + '_wa').is(':checked') ? 1 : 0;
                updates['fu' + i + '_telp'] = $('#fu' + i + '_telp').is(':checked') ? 1 : 0;
                updates['fu' + i + '_at'] = $('#fu' + i + '_at').val();
            }

            let $btnSimpan = $(this);
            $btnSimpan.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: '/admin/database/update-inline',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    updates: updates
                },
                success: function (res) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });

                    Toast.fire({
                        icon: 'success',
                        title: 'Interaksi berhasil disimpan'
                    });

                    $('#modalRiwayat').modal('hide');

                    // Update data attributes in the trigger button
                    let $triggerBtn = $(`.btn-riwayat[data-id="${id}"]`);
                    for (let i = 1; i <= 10; i++) {
                        $triggerBtn.attr('data-fu' + i + '-hasil', updates['fu' + i + '_hasil']);
                        $triggerBtn.attr('data-fu' + i + '-tindak-lanjut', updates['fu' + i + '_tindak_lanjut']);
                        $triggerBtn.attr('data-fu' + i + '-wa', updates['fu' + i + '_wa']);
                        $triggerBtn.attr('data-fu' + i + '-telp', updates['fu' + i + '_telp']);

                        // Update the date attribute with the new timestamp from server
                        if (res.timestamps && res.timestamps['fu' + i + '_at']) {
                            $triggerBtn.attr('data-fu' + i + '-at', res.timestamps['fu' + i + '_at']);
                        }
                    }
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan saat menyimpan data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                },
                complete: function () {
                    $btnSimpan.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan');
                }
            });
        });
    </script>
    <!-- Modal Create -->


    <script>
        $('#createForm').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    alert('Berhasil disimpan!');
                    $('#createPesertaModal').modal('hide');
                    location.reload(); // atau refresh tabel data
                },
                error: function (err) {
                    alert('Gagal menyimpan.');
                }
            });
        });
    </script>

    <script>
        function create() {
            $('#createPesertaModal').modal('show');
        }

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            // Add your AJAX call here to save the data
            alert('Data saved successfully!');
            $('#createPesertaModal').modal('hide');
        });
    </script>
    {{--
    <script>
        $(document).ready(function () {
            $('#myTable').DataTable({
                responsive: true,
                autoWidth: false,
            });
        });
    </script> --}}



    <!-- Modal Create -->
    <div class="modal fade" id="createPesertaModal" tabindex="-1" role="dialog" aria-labelledby="createPesertaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPesertaModalLabel">Tambah Peserta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createForm" action="{{ route('admin.database.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">

                        {{-- Nama Peserta --}}
                        <div class="form-group">
                            <label for="nama">Nama Peserta</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>

                        {{-- Potensi Kelas --}}
                        <div class="form-group">
                            <label for="kelas_id">Potensi Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-control" required>
                                <option value="">Pilih Potensi Kelas</option>
                                @forelse($kelas as $item)
                                    <option value="{{ $item->id }}">
                                        {{ str_contains($item->nama_kelas, 'Muslim Indonesia') ? 'M1T' : $item->nama_kelas }}
                                    </option>
                                @empty
                                    <option disabled>Tidak ada kelas tersedia</option>
                                @endforelse
                            </select>
                        </div>

                        {{-- Sumber Leads --}}
                        <div class="form-group">
                            <label for="leads">Sumber Leads</label>
                            <select name="leads" id="leads" class="form-control">
                                <option value="Ads">ADS</option>
                                <option value="Sosmed">Sosial Media</option>
                                <option value="zoom">Zoom Preview</option>
                                <option value="Open House">Open House</option>
                                <option value="Mandiri">Mandiri</option>
                                <option value="Alumni">Alumni</option>
                            </select>
                        </div>

                        {{-- Provinsi --}}
                        <div class="form-group">
                            <label for="provinsi">Provinsi</label>
                            <select id="provinsi" class="form-control" name="provinsi_id" required>
                                <option value="">Pilih Provinsi</option>
                            </select>
                            <input type="hidden" name="provinsi_nama" id="provinsi_nama">
                        </div>

                        {{-- Kota --}}
                        <div class="form-group">
                            <label for="kota">Kota</label>
                            <select id="kota" class="form-control" name="kota_id" required>
                                <option value="">Pilih Kota</option>
                            </select>
                            <input type="hidden" name="kota_nama" id="kota_nama">
                        </div>

                        <script>
                            fetch('/wilayah/provinsi')
                                .then(res => res.json())
                                .then(data => {
                                    data.forEach(prov => {
                                        $('#provinsi').append(`<option value="${prov.id}" data-nama="${prov.name}">${prov.name}</option>`);
                                    });
                                });

                            $('#provinsi').on('change', function () {
                                const id = $(this).val();
                                const nama = $(this).find('option:selected').text();
                                $('#provinsi_nama').val(nama);

                                fetch(`/wilayah/kota/${id}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        $('#kota').html('<option value="">Pilih Kota</option>');
                                        data.forEach(kota => {
                                            $('#kota').append(`<option value="${kota.id}" data-nama="${kota.name}">${kota.name}</option>`);
                                        });
                                    });
                            });

                            $('#kota').on('change', function () {
                                const nama = $(this).find('option:selected').text();
                                $('#kota_nama').val(nama);
                            });
                        </script>

                        {{-- Nama Bisnis --}}
                        <div class="form-group">
                            <label for="nama_bisnis">Nama Bisnis</label>
                            <input type="text" class="form-control" id="nama_bisnis" name="nama_bisnis" required>
                        </div>

                        {{-- Jenis Bisnis --}}
                        <div class="form-group">
                            <label for="jenisbisnis">Jenis Bisnis</label>
                            <select name="jenisbisnis" id="jenisbisnis" class="form-control">
                                <option value="Bisnis Properti">Bisnis Properti</option>
                                <option value="Bisnis Manufaktur">Bisnis Manufaktur</option>
                                <option value="Bisnis F&B (Food & Beverage)">Bisnis F&B (Food & Beverage)</option>
                                <option value="Bisnis Jasa">Bisnis Jasa</option>
                                <option value="Bisnis Digital">Bisnis Digital</option>
                                <option value="Bisnis Online">Bisnis Online</option>
                                <option value="Bisnis Franchise">Bisnis Franchise</option>
                                <option value="Bisnis Edukasi & Pelatihan">Bisnis Edukasi & Pelatihan</option>
                                <option value="Bisnis Kreatif">Bisnis Kreatif</option>
                                <option value="Bisnis Agribisnis">Bisnis Agribisnis</option>
                                <option value="Bisnis Kesehatan & Kecantikan">Bisnis Kesehatan & Kecantikan</option>
                                <option value="Bisnis Keuangan">Bisnis Keuangan</option>
                                <option value="Bisnis Transportasi & Logistik">Bisnis Transportasi & Logistik</option>
                                <option value="Bisnis Pariwisata & Hospitality">Bisnis Pariwisata & Hospitality</option>
                                <option value="Bisnis Sosial (Social Enterprise)">Bisnis Sosial (Social Enterprise)</option>
                            </select>
                        </div>

                        {{-- No WA --}}
                        <div class="form-group">
                            <label for="no_wa">No. WA</label>
                            <input type="text" class="form-control" id="no_wa" name="no_wa" required>
                        </div>

                        {{-- Situasi Bisnis --}}
                        <div class="form-group">
                            <label for="situasi_bisnis">{{ in_array($userRole, ['chapter', 'reseller']) ? 'Situasi & Kendala Bisnis' : 'Situasi Bisnis' }}</label>
                            <textarea class="form-control" id="situasi_bisnis" name="situasi_bisnis" rows="3"></textarea>
                        </div>

                        {{-- Kendala --}}
                        <div class="form-group {{ $userRole === 'chapter' ? 'd-none' : '' }}">
                            <label for="kendala">Kendala</label>
                            <textarea class="form-control" id="kendala" name="kendala" rows="3"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Setting Pembayaran --}}
    <div class="modal fade" id="monthSelectionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-info text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold m-0"><i class="fas fa-wallet me-2"></i> Setting Pembayaran</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-12">
                            <label class="font-weight-bold d-block mb-0 text-muted" style="font-size: 0.7rem;">NAMA PESERTA</label>
                            <span id="modalPlanNameDisplay" class="h6 fw-bold text-dark">-</span>
                            <span id="modalPlanLevelDisplay" class="d-none"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold d-block mb-2 text-uppercase text-secondary" style="font-size: 0.75rem;">Metode Pembayaran</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-info btn-sm active flex-fill">
                                <input type="radio" name="pay_method" id="method-template" value="template" checked> Pembayaran Template
                            </label>
                            <label class="btn btn-outline-info btn-sm flex-fill">
                                <input type="radio" name="pay_method" id="method-custom" value="custom"> Pembayaran Custom
                            </label>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded mb-4" style="border: 1px solid #e0e0e0;">
                        <h6 class="font-weight-bold text-info mb-3" style="font-size: 0.85rem;"><i class="fas fa-money-bill-wave me-1"></i> Detail Pembayaran Pertama</h6>
                        <div class="row">
                            <div class="col-sm-4 mb-2">
                                <label class="font-weight-bold d-block mb-1 text-uppercase text-secondary" style="font-size: 0.7rem;">Pendaftaran</label>
                                <input type="text" id="modalBiayaPendaftaran" class="form-control form-control-sm currency-input" style="border-radius: 8px;" placeholder="0">
                            </div>
                            <div class="col-sm-4 mb-2">
                                <label class="font-weight-bold d-block mb-1 text-uppercase text-secondary" style="font-size: 0.7rem;">Pembayaran SPP</label>
                                <input type="text" id="modalSppPertama" class="form-control form-control-sm currency-input" style="border-radius: 8px;" placeholder="0">
                            </div>
                            <div class="col-sm-4 mb-2">
                                <label class="font-weight-bold d-block mb-1 text-uppercase text-danger" style="font-size: 0.7rem;">Total Saja</label>
                                <input type="text" id="modalSppAwal" class="form-control form-control-sm bg-white fw-bold text-danger" style="border-radius: 8px; border-color: #ffcccc;" readonly>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="modalPlanId">
                    <input type="hidden" id="modalTanggalClosingHidden">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="d-flex align-items-center mb-2" style="gap: 15px;">
                                <label class="font-weight-bold mb-0 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Bulan</label>
                                <div class="custom-control custom-checkbox" id="all-month-wrapper">
                                    <input type="checkbox" class="custom-control-input" id="btn-month-all">
                                    <label class="custom-control-label font-weight-bold text-info" for="btn-month-all" style="font-size: 0.7rem;">ALL</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input text-success" id="cb-pay-full">
                                    <label class="custom-control-label font-weight-bold text-success" for="cb-pay-full" style="font-size: 0.7rem;">Bayar Lunas Langsung</label>
                                </div>
                            </div>
                            <div class="text-muted italic mb-2" style="font-size: 0.65rem;">(Klik ALL jika langsung membayar lunas)</div>
                            <div id="monthChecklist" style="max-height: 250px; overflow-y: auto;">
                                @php
                                    $monthsNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                @endphp
                                @foreach($monthsNames as $index => $month)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input month-cb" id="cb-month-{{ $index + 1 }}" data-value="{{ $index + 1 }}">
                                        <label class="custom-control-label font-weight-normal" for="cb-month-{{ $index + 1 }}">{{ $month }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6 pl-3">
                            <label class="font-weight-bold d-block mb-1 text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Tahun</label>
                            <select id="yearSelect" class="form-control form-control-sm mb-3" style="border-radius: 8px;">
                                <option value="">-- Pilih Tahun --</option>
                                @for($y = 2026; $y <= 2030; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                            
                            <div id="dateSelectionFields" class="mt-3">
                                <div class="mb-3">
                                    <label class="font-weight-bold d-block mb-1 text-uppercase text-info" style="font-size: 0.7rem;">Tanggal Masuk</label>
                                    <input type="date" id="modalTanggalMasuk" class="form-control form-control-sm" style="border-radius: 8px;">
                                </div>
                                
                                <div class="mb-2" id="wrapperTanggalSelesai" style="display: none;">
                                    <label class="font-weight-bold d-block mb-1 text-uppercase text-info" style="font-size: 0.7rem;">Tanggal Selesai</label>
                                    <input type="date" id="modalTanggalSelesai" class="form-control form-control-sm" style="border-radius: 8px;">
                                </div>

                                <div id="customPaymentsContainer" class="d-none mt-3 p-3 bg-white rounded border border-info shadow-sm">
                                    <label class="font-weight-bold d-block mb-3 text-uppercase text-info border-bottom pb-2" style="font-size: 0.75rem;"><i class="fas fa-plus-circle me-1"></i> Pembayaran Selanjutnya</label>
                                    <div id="customPaymentsList"></div>
                                    <button type="button" class="btn btn-sm btn-outline-info w-100 mt-2 font-weight-bold" style="border-radius: 8px; border-style: dashed;" onclick="addCustomPaymentRow()">
                                        <i class="fas fa-plus mr-1"></i> Tambah Pembayaran
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info rounded-pill px-4" onclick="saveSelectedMonths()">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- End Modal Create -->

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>


    <script>
        // Logic filter kelas sudah digabung di applyFilters()
    </script>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- ✅ Tambahkan ini di atas tabel kamu -->


    <script>
        // --- Payment Setting Modal Logic (Copied from SalesPlan) ---
        let tempModalSelections = {};
        const monthsNamesArr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        window.showMonthSelectionModal = function(id, name, currentSelection, tglMasuk, tglSelesai, sppAwal, biayaPendaftaran, pembayaranSpp, totalPembayaran, level, tglClosing) {
            $('#modalPlanId').val(id);
            $('#modalPlanNameDisplay').text(name);
            $('#modalPlanLevelDisplay').text(level || '-');
            $('#modalTanggalClosingHidden').val(tglClosing || '');
            
            // Reset selections
            $('.month-cb').prop('checked', false);
            $('#cb-pay-full').prop('checked', false).trigger('change');
            $('#btn-month-all').prop('checked', false);
            tempModalSelections = {};
            $('#modalTanggalMasuk').val(tglMasuk || tglClosing || '');
            $('#modalTanggalSelesai').val(tglSelesai || '');
            $('#modalBiayaPendaftaran').val(formatCurrencyValue(biayaPendaftaran || ''));
            $('#modalSppPertama').val(formatCurrencyValue(pembayaranSpp || ''));
            $('#modalSppAwal').val(formatCurrencyValue(totalPembayaran || sppAwal || ''));
            
            // Default to Template method
            $('#method-template').prop('checked', true);
            $('input[name="pay_method"]:checked').trigger('change');
            
            let firstYear = '';
            if (currentSelection) {
                if (typeof currentSelection === 'string') {
                    try { currentSelection = JSON.parse(currentSelection); } catch(e) {}
                }
                if (typeof currentSelection === 'object' && !Array.isArray(currentSelection)) {
                    tempModalSelections = currentSelection;
                    let yearsInSelection = Object.keys(tempModalSelections);
                    if (yearsInSelection.length > 0) {
                        firstYear = yearsInSelection[0];
                    }
                }
            }
            
            if (!firstYear) {
                let today = new Date();
                firstYear = today.getFullYear();
                if (firstYear < 2026) firstYear = 2026;
            }

            $('#yearSelect').val(firstYear);
            loadMonthsForYear(firstYear);
            $('#dateSelectionFields').toggle(!!firstYear);
            $('#monthSelectionModal').modal('show');
        };

        function loadMonthsForYear(year) {
            $('.month-cb').prop('checked', false);
            if (year && tempModalSelections[year]) {
                tempModalSelections[year].forEach(m => {
                    $('#cb-month-' + m).prop('checked', true);
                });
            }
        }

        $(document).on('change', '#yearSelect', function() {
            let year = $(this).val();
            loadMonthsForYear(year);
            $('#dateSelectionFields').toggle(!!year);
        });

        $(document).on('change', 'input[name="pay_method"]', function() {
            let method = $(this).val();
            let level = $('#modalPlanLevelDisplay').text().toLowerCase();
            
            if (method === 'template') {
                $('#modalSppPertama').prop('readonly', true).addClass('bg-light');
                $('#modalBiayaPendaftaran').val(formatCurrencyValue('500000'));
                if (level.includes('grow up')) {
                    $('#modalSppPertama').val(formatCurrencyValue('1500000'));
                } else {
                    $('#modalSppPertama').val(formatCurrencyValue('1000000'));
                }
                $('#customPaymentsContainer').addClass('d-none');
            } else {
                $('#modalSppPertama').prop('readonly', false).removeClass('bg-light');
                $('#customPaymentsContainer').removeClass('d-none');
            }
            updateModalTotal();
        });

        $(document).on('change', '#cb-pay-full', function() {
            let isChecked = $(this).is(':checked');
            let level = $('#modalPlanLevelDisplay').text().toLowerCase();

            if (isChecked) {
                $('#monthChecklist').hide();
                $('#all-month-wrapper').hide();
                $('#wrapperTanggalSelesai').show();
                $('#modalSppPertama').val(formatCurrencyValue('15000000'));
                calculateEndDate();
            } else {
                $('#monthChecklist').show();
                $('#all-month-wrapper').show();
                $('#wrapperTanggalSelesai').hide();
                if (level.includes('grow up')) {
                    $('#modalSppPertama').val(formatCurrencyValue('1500000'));
                } else {
                    $('#modalSppPertama').val(formatCurrencyValue('1000000'));
                }
            }
            updateModalTotal();
        });

        function calculateEndDate() {
            let entryVal = $('#modalTanggalMasuk').val();
            if (entryVal && $('#cb-pay-full').is(':checked')) {
                let date = new Date(entryVal);
                date.setMonth(date.getMonth() + 11);
                let y = date.getFullYear();
                let m = String(date.getMonth() + 1).padStart(2, '0');
                let d = String(date.getDate()).padStart(2, '0');
                $('#modalTanggalSelesai').val(`${y}-${m}-${d}`);
            }
        }

        $(document).on('change', '#modalTanggalMasuk', function() {
            calculateEndDate();
        });

        $(document).on('input', '#modalBiayaPendaftaran, #modalSppPertama', function() {
            updateModalTotal();
        });

        function updateModalTotal() {
            let pendaftaran = parseInt($('#modalBiayaPendaftaran').val().replace(/[^0-9]/g, '')) || 0;
            let spp = parseInt($('#modalSppPertama').val().replace(/[^0-9]/g, '')) || 0;
            $('#modalSppAwal').val(formatCurrencyValue(pendaftaran + spp));
        }

        $(document).on('change', '.month-cb', function() {
            let year = $('#yearSelect').val();
            if (!year) {
                Swal.fire('Oops!', 'Pilih tahun terlebih dahulu.', 'warning');
                $(this).prop('checked', false);
                return;
            }
            if (!tempModalSelections[year]) tempModalSelections[year] = [];
            let checkedMonths = [];
            $('.month-cb:checked').each(function() {
                checkedMonths.push(parseInt($(this).data('value')));
            });
            tempModalSelections[year] = checkedMonths.sort((a,b) => a-b);
        });

        $(document).on('click', '#btn-month-all', function() {
            let isChecked = $(this).is(':checked');
            $('.month-cb').prop('checked', isChecked).trigger('change');
        });

        window.saveSelectedMonths = function() {
            let id = $('#modalPlanId').val();
            let tanggalMasuk = $('#modalTanggalMasuk').val();
            let tanggalSelesai = $('#modalTanggalSelesai').val();
            let pendaftaran = $('#modalBiayaPendaftaran').val().replace(/[^0-9]/g, '');
            let sppPertama = $('#modalSppPertama').val().replace(/[^0-9]/g, '');
            let sppAwal = $('#modalSppAwal').val().replace(/[^0-9]/g, '');
            
            let finalSelections = {};
            if ($('#cb-pay-full').is(':checked')) {
                if (!tanggalMasuk) {
                    Swal.fire('Oops!', 'Mohon isi Tanggal Masuk.', 'warning');
                    return;
                }
                let dateMasuk = new Date(tanggalMasuk);
                let startYear = dateMasuk.getFullYear();
                let startMonth = dateMasuk.getMonth() + 1;
                for (let i = 0; i < 12; i++) {
                    let current = new Date(startYear, (startMonth - 1) + i, 1);
                    let curY = current.getFullYear();
                    let curM = current.getMonth() + 1;
                    if (!finalSelections[curY]) finalSelections[curY] = [];
                    finalSelections[curY].push(curM);
                }
            } else {
                for(let y in tempModalSelections) {
                    if (tempModalSelections[y].length > 0) finalSelections[y] = tempModalSelections[y];
                }
            }
            
            let methodSelection = $('input[name="pay_method"]:checked').val();
            let customPayments = [];
            if (methodSelection === 'custom') {
                $('#customPaymentsList .custom-payment-row').each(function() {
                    let date = $(this).find('.cp-date').val();
                    let nominal = $(this).find('.cp-nominal').val().replace(/[^0-9]/g, '');
                    if (date && nominal) customPayments.push({ date: date, nominal: nominal });
                });
            }

            $.ajax({
                url: "{{ route('admin.salesplan.update-selected-months') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    selected_months: JSON.stringify(finalSelections),
                    tanggal_masuk: tanggalMasuk,
                    tanggal_selesai: tanggalSelesai,
                    spp_awal: sppAwal,
                    biaya_pendaftaran: pendaftaran,
                    pembayaran_spp: sppPertama,
                    custom_payments: customPayments
                },
                success: function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil Disimpan', timer: 1500 });
                    $('#monthSelectionModal').modal('hide');
                }
            });
        };

        function formatCurrencyValue(value) {
            if (!value) return '';
            let num = value.toString().replace(/[^0-9]/g, '');
            if (!num) return '';
            return parseInt(num).toLocaleString('id-ID');
        }

        window.addCustomPaymentRow = function() {
            let count = $('#customPaymentsList .custom-payment-row').length + 1;
            let html = `
                <div class="custom-payment-row mb-2 border-bottom pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="font-weight-bold text-secondary" style="font-size: 0.7rem;">Pembayaran ke-${count + 1}</span>
                        <i class="fas fa-times text-danger" onclick="$(this).closest('.custom-payment-row').remove();" style="cursor: pointer;"></i>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-1 pr-1"><input type="date" class="form-control form-control-sm cp-date"></div>
                        <div class="col-sm-6 mb-1 pl-1"><input type="text" class="form-control form-control-sm cp-nominal" oninput="this.value = formatCurrencyValue(this.value)"></div>
                    </div>
                </div>
            `;
            $('#customPaymentsList').append(html);
        };

        function exportPdfInteraksi() {
            let url = '{{ route("admin.database.export-pdf-interaksi") }}';
            let currentParams = new URLSearchParams(window.location.search);

            window.location.href = url + '?' + currentParams.toString();
        }

        // Direct status update function for Chapter/Reseller
        window.updateStatusDirect = function(dataId, status) {
            const $select = $(`.status-select[data-id="${dataId}"]`);
            
            $select.css('opacity', '0.5').prop('disabled', true);
            
            $.ajax({
                url: "{{ route('admin.database.update-status-direct') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    data_id: dataId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Status Updated'
                        });
                        
                        const row = $select.closest('tr');
                        const colors = {
                            'cold': { bg: '#ffffff', text: '#6c757d', border: '#ddd', rowBg: '#ffffff' },
                            'tertarik': { bg: '#fffceb', text: '#856404', border: '#ffeeba', rowBg: '#fffceb' },
                            'sudah_transfer': { bg: '#e7f5ff', text: '#1971c2', border: '#74c0fc', rowBg: '#e7f5ff' },
                            'no': { bg: '#fff5f5', text: '#c92a2a', border: '#ffa8a8', rowBg: '#fff5f5' },
                            'new': { bg: '#ffffff', text: '#495057', border: '#dee2e6', rowBg: '#ffffff' }
                        };
                        
                        const color = colors[status] || colors['new'];
                        
                        // Update select styling
                        $select.css({
                            'background-color': color.bg,
                            'color': color.text,
                            'border-color': color.border,
                            'opacity': '1'
                        }).prop('disabled', false);
                        
                        // Update entire row background
                        row.css('background-color', color.rowBg);

                        // Toggle nominal display visibility
                        if (status === 'sudah_transfer') {
                            row.find('.nominal-display').removeClass('d-none');
                        } else {
                            row.find('.nominal-display').addClass('d-none');
                        }
                        // Trigger Payment Modal if status is Sudah Transfer
                        if (status === 'sudah_transfer') {
                            const nama = $select.attr('data-nama');
                            const level = $select.attr('data-level');
                            const planId = response.plan_id;
                            
                            // Get potensi value from the same row
                            const $row = $select.closest('tr');
                            const potensi = $row.find('.select-potensi-mbc-m1t').val();

                            if (potensi === 'MBC') {
                                // Simplified popup for MBC
                                Swal.fire({
                                    title: 'Setting Pembayaran MBC',
                                    text: 'Masukkan Nominal Pembayaran untuk ' + nama,
                                    input: 'text',
                                    inputPlaceholder: 'Contoh: 1.500.000',
                                    showCancelButton: true,
                                    confirmButtonText: 'Simpan',
                                    cancelButtonText: 'Batal',
                                    customClass: {
                                        confirmButton: 'btn btn-primary rounded-pill px-4',
                                        cancelButton: 'btn btn-secondary rounded-pill px-4'
                                    },
                                    buttonsStyling: false,
                                    didOpen: () => {
                                        const input = Swal.getInput();
                                        $(input).on('input', function() {
                                            let val = this.value.replace(/[^0-9]/g, '');
                                            this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
                                        });
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        let nominal = result.value.replace(/[^0-9]/g, '');
                                        if (!nominal) {
                                            Swal.fire('Error', 'Nominal harus diisi', 'error');
                                            return;
                                        }
                                        
                                        // Save via AJAX
                                        $.ajax({
                                            url: "{{ route('admin.salesplan.update-selected-months') }}",
                                            type: "POST",
                                            data: {
                                                _token: "{{ csrf_token() }}",
                                                id: planId,
                                                spp_awal: nominal,
                                                nominal: nominal, // Also sync to SalesPlan nominal
                                                selected_months: JSON.stringify({}),
                                                tanggal_masuk: new Date().toISOString().split('T')[0]
                                            },
                                            success: function(res) {
                                                Swal.fire({ icon: 'success', title: 'Berhasil Disimpan', timer: 1500 });
                                                // Update UI
                                                $row.find('.nominal-display').text('Rp ' + parseInt(nominal).toLocaleString('id-ID')).removeClass('d-none');
                                            }
                                        });
                                    }
                                });
                            } else {
                                // Detailed modal for M1T (SMI) and others
                                window.showMonthSelectionModal(planId, nama, {}, null, null, null, null, null, null, level, null);
                            }
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Update status failed:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Gagal',
                        text: 'Terjadi kesalahan saat memperbarui status.'
                    });
                    $select.css('opacity', '1').prop('disabled', false);
                }
            });
        };
        
        // --- Reuse Logic ---

        // Reuse Data (Create New SalesPlan Instance)
        $(document).on('click', '.btn-reuse', function() {
            let id = $(this).data('id');
            let $btn = $(this);
            
            Swal.fire({
                title: 'Reuse Data?',
                text: 'Ini akan membuat riwayat penawaran baru (Cold) untuk data ini tanpa menambah jumlah total database.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Segarkan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill px-4',
                    cancelButton: 'btn btn-secondary rounded-pill px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="font-size: 0.7rem;"></i>');
                    
                    $.ajax({
                        url: "{{ route('admin.database.reuse-data') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data Berhasil Disegarkan',
                                    text: 'Status terbaru kini kembali ke COLD.',
                                    timer: 2000
                                }).then(() => {
                                    location.reload(); // Reload to see the new status and updated history
                                });
                            }
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Terjadi kesalahan sistem', 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-retweet" style="color: #fff; font-size: 0.7rem;"></i>');
                        }
                    });
                }
            });
        });
        // Delete Confirmation for Linda & Yasmin (cs-mbc)
        $(document).on('submit', '.delete-form', function(e) {
            @php
                $userName = auth()->user()->name;
                $userRole = strtolower(auth()->user()->role);
                $isTargetUser = (in_array($userName, ['Linda', 'Yasmin']) && $userRole === 'cs-mbc') || $userRole === 'administrator';
            @endphp
            
            @if($isTargetUser)
                e.preventDefault();
                let form = this;
                let nama = $(this).data('nama');
                Swal.fire({
                    title: 'Apakah anda yakin menghapus data dengan nama "' + nama + '",',
                    text: "data yang di hapus tidak dapat di kembalikan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            @endif
        });
    </script>
@endsection