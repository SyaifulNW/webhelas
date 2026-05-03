    @extends('layouts.masteradmin')

    @section('content')
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
    <form id="form-update-{{ $item->id }}" action="{{ route('peserta-smi.update', $item->id) }}" method="POST" style="display: none;">
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
        .status-aktif { background-color: #1cc88a !important; }
        .status-cuti { background-color: #e74a3b !important; }
        .status-lulus { background-color: #4e73df !important; }
        
        .badge-status {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 800;
            color: white !important;
            text-align: center;
            border: none;
            cursor: pointer;
            width: 85px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Styling Table */
        .table-hover tbody tr:hover {
            background-color: #f1f7fd; /* Soft blue on hover */
        }
        
        /* Elegant Header */
        thead.thead-dark-blue {
            background: linear-gradient(135deg, #2e59d9, #224abe);
            color: white;
        }
        thead.thead-dark-blue th {
            border-color: #4e73df;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        thead.thead-dark-blue a {
            color: white !important;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        /* Input Styling in Table */
        .table-input {
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 4px;
            transition: all 0.2s;
            background: transparent;
            color: #333;
            width: 100%;
        }
        .table-input:hover {
            background: #f8f9fc;
            border-color: #e3e6f0;
        }
        .table-input:focus {
            background: #fff;
            border-color: #4e73df;
            box-shadow: 0 0 0 0.1rem rgba(78, 115, 223, 0.25);
            outline: none;
        }

        /* Checkbox Styling */
        .custom-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #4e73df; /* Modern browsers support this */
        }

        /* Fixed Layout */
        .table td, .table th {
            vertical-align: middle !important;
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

        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold mr-3"><i class="fas fa-list-alt mr-2"></i>Daftar Peserta SMI</h6>
                        
                        {{-- Summary Badges --}}
                        <div class="d-none d-lg-flex align-items-center ml-3" style="gap: 15px;">
                            <!-- Sudah Bayar -->
                            <div class="d-flex align-items-center px-2 py-1 rounded" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                                <div class="mr-2 text-right">
                                    <div style="font-size: 0.55rem; color: #d1d3e2; font-weight: 700; text-transform: uppercase; line-height: 1;">Sudah</div>
                                    <div style="font-size: 0.75rem; font-weight: 700; color: #fff;">{{ number_format($badgeStats['total_sudah'], 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-success shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem; font-weight: 800;">
                                    {{ $badgeStats['count_lunas'] }}
                                </div>
                            </div>

                            <!-- Belum Bayar -->
                            <div class="d-flex align-items-center px-2 py-1 rounded" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                                <div class="mr-2 text-right">
                                    <div style="font-size: 0.55rem; color: #d1d3e2; font-weight: 700; text-transform: uppercase; line-height: 1;">Belum</div>
                                    <div style="font-size: 0.75rem; font-weight: 700; color: #fff;">{{ number_format($badgeStats['total_belum'], 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-danger shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem; font-weight: 800;">
                                    {{ $badgeStats['count_belum'] }}
                                </div>
                            </div>

                            <!-- Potensi (Target) -->
                            <div class="d-flex align-items-center px-2 py-1 rounded" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                                <div class="mr-2 text-right">
                                    <div style="font-size: 0.55rem; color: #d1d3e2; font-weight: 700; text-transform: uppercase; line-height: 1;">Potensi</div>
                                    <div style="font-size: 0.75rem; font-weight: 700; color: #fff;">{{ number_format($badgeStats['target'] * 1000000, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-primary shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.4);">
                                    {{ $badgeStats['target'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center" style="max-width: 250px;">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-search text-primary"></i></span>
                            </div>
                            <input type="text" id="realtimeSearchSmi" class="form-control border-0 shadow-none" placeholder="Cari nama peserta..." style="height: 30px; font-size: 0.8rem;">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" id="dataTable" width="100%" cellspacing="0" style="font-size: 0.85rem;">
                            <thead class="thead-dark-blue text-white">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 3%">No</th>
                                    <th rowspan="2" class="align-middle text-center border-right" style="min-width: 200px;">Nama Closing</th>
                                    <th rowspan="2" class="align-middle text-center border-right d-none" style="min-width: 200px;">Nama Peserta</th>
                                    
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 10%">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="mb-1">Status</span>
                                            <select form="sppFilterForm" name="filter_status" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="font-size: 0.7rem; height: 22px; padding: 0 5px; width: 85px;" onchange="document.getElementById('sppFilterForm').submit()">
                                                <option value="">ALL</option>
                                                <option value="Aktif" {{ request('filter_status') == 'Aktif' ? 'selected' : '' }}>AKTIF</option>
                                                <option value="Cuti" {{ request('filter_status') == 'Cuti' ? 'selected' : '' }}>CUTI</option>
                                                <option value="Lulus" {{ request('filter_status') == 'Lulus' ? 'selected' : '' }}>LULUS</option>
                                            </select>
                                        </div>
                                    </th>

                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 15%">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="mb-1" style="font-size: 0.8rem;">Tanggal Masuk - Selesai</span>
                                            <div class="d-flex justify-content-center" style="gap: 2px;">
                                                <select form="sppFilterForm" name="filter_entry_month" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="font-size: 0.65rem; height: 22px; padding: 0 2px; width: 62px;" onchange="document.getElementById('sppFilterForm').submit()">
                                                    <option value="">BULAN</option>
                                                    @php
                                                        $mSimple = [1=>'JAN',2=>'FEB',3=>'MAR',4=>'APR',5=>'MEI',6=>'JUN',7=>'JUL',8=>'AGU',9=>'SEP',10=>'OKT',11=>'NOV',12=>'DES'];
                                                    @endphp
                                                    @foreach($mSimple as $k => $v)
                                                        <option value="{{ $k }}" {{ request('filter_entry_month') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                                <select form="sppFilterForm" name="filter_entry_year" class="form-control form-control-sm border-0 bg-light text-primary font-weight-bold" style="font-size: 0.65rem; height: 22px; padding: 0 2px; width: 50px;" onchange="document.getElementById('sppFilterForm').submit()">
                                                    <option value="">THN</option>
                                                    @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                                        <option value="{{ $y }}" {{ (request('filter_entry_year') == $y || (!request('filter_entry_year') && request('filter_year') == $y)) ? 'selected' : '' }}>{{ $y }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </th>
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 10%">Biaya Pendaftaran</th>
                                    <th rowspan="2" class="align-middle text-center border-right" style="width: 10%">CS Closing</th>

                                    <th colspan="12" id="sppMainHeader" class="text-center font-weight-bold text-uppercase border-bottom px-2">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="button" class="btn btn-xs btn-light text-primary mr-2" id="btn-toggle-spp" onclick="toggleSpp()" title="Expand/Collapse SPP">
                                                <i class="fas fa-compress-alt"></i>
                                            </button>
                                            <a href="{{ route('admin.keuangan.laba-rugi', ['bulan' => str_pad(request('filter_spp_month', 'all'), 2, '0', STR_PAD_LEFT), 'tahun' => request('filter_year', date('Y'))]) }}" 
                                            class="text-white text-decoration-none mr-2 font-weight-bold" id="sppHeaderTitle" title="Buka Laporan Laba Rugi SMI">
                                                SPP
                                            </a>
                                            <div id="sppFilterControls" class="d-flex align-items-center">
                                                <select form="sppFilterForm" name="filter_spp_month" class="form-control form-control-sm mr-1 border-0 bg-light" style="width: 100px; font-size: 0.8rem; height: 25px; padding: 0 5px;" onchange="document.getElementById('sppFilterForm').submit()">
                                                    <option value="">- Bulan -</option>
                                                    @php
                                                        $monthsRaw = [
                                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                        ];
                                                    @endphp
                                                    @foreach($monthsRaw as $key => $val)
                                                        <option value="{{ $key }}" {{ request('filter_spp_month') == $key ? 'selected' : '' }}>{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                                
                                                <select form="sppFilterForm" name="filter_spp_status" class="form-control form-control-sm mr-1 border-0 bg-light" style="width: 80px; font-size: 0.8rem; height: 25px; padding: 0 5px;" onchange="document.getElementById('sppFilterForm').submit()">
                                                    <option value="">- Status -</option>
                                                    <option value="1" {{ request('filter_spp_status') === '1' ? 'selected' : '' }}>Lunas</option>
                                                    <option value="0" {{ request('filter_spp_status') === '0' ? 'selected' : '' }}>Belum</option>
                                                </select>

                                                <select form="sppFilterForm" name="filter_year" class="form-control form-control-sm mr-1 border-0 bg-light" style="width: 70px; font-size: 0.8rem; height: 25px; padding: 0 5px;" onchange="document.getElementById('sppFilterForm').submit()">
                                                    <option value="">- Thn -</option>
                                                    @for($y = date('Y'); $y >= 2024; $y--)
                                                        <option value="{{ $y }}" {{ request('filter_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                    @endfor
                                                </select>
                                                
                                                @if(request()->has('filter_spp_month') || request()->has('filter_spp_status') || request()->has('filter_year') || request()->has('filter_status'))
                                                <a href="{{ route('peserta-smi.index') }}" class="text-white ml-2" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                                                @endif
                                            </div>
                                        </div>
                                    </th>
                                    <th rowspan="2" class="align-middle text-center border-left" style="width: 5%">Aksi</th>
                                </tr>
                                <tr>
                                    @php
                                        $bulan = [
                                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                                            7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                                        ];
                                    @endphp
                                    @for($i=1; $i<=12; $i++)
                                    <th class="text-center spp-col {{ $i > 1 ? 'spp-extra' : '' }}" style="min-width: 45px; width: 45px; font-size: 11px;">
                                        <a href="{{ route('admin.keuangan.laba-rugi', ['bulan' => str_pad($i, 2, '0', STR_PAD_LEFT), 'tahun' => request('filter_year', date('Y'))]) }}" 
                                        class="text-white text-decoration-none col-spp-label-{{ $i }}" 
                                        title="Lihat Laba Rugi {{ $bulan[$i] }} {{ request('filter_year', date('Y')) }}">
                                            {{ $bulan[$i] }}
                                        </a>
                                    </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($data as $key => $item)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold text-secondary">
                                        {{ $key + 1 }}
                                    </td>
                                    
                                    {{-- Nama Peserta --}}
                                    <td class="p-1 align-middle">
                                         @php
                                             $isManualLunas = ($item->is_lunas == 1);
                                             $isAutoLunas = false;
                                             
                                             // Automatic check: if at least 6 months are paid (>= 1M each)
                                             if($item->tanggal_masuk) {
                                                 $startJoin = \Carbon\Carbon::parse($item->tanggal_masuk)->startOfMonth();
                                                 $countPaid = 0;
                                                 // Check up to 12 months to see if they reached 6 months total
                                                 for($m=0; $m<12; $m++) {
                                                     $checkM = $startJoin->copy()->addMonths($m);
                                                     $mNum = (int)$checkM->format('n');
                                                     if(($item->{"spp_$mNum"} ?? 0) >= 1000000) {
                                                         $countPaid++;
                                                     }
                                                 }
                                                 if($countPaid >= 6) $isAutoLunas = true;
                                             }
                                             
                                             $isAllPaid = $isManualLunas || $isAutoLunas;
                                         @endphp
                                         <div class="d-flex align-items-center">
                                             <input form="form-update-{{ $item->id }}" type="text" name="nama" class="table-input" value="{{ $item->nama }}" placeholder="Nama peserta..." onblur="quickUpdateField(this, {{ $item->id }}, 'nama')">
                                         </div>
                                         @if($isAllPaid)
                                         <div class="px-2 py-0">
                                            <span class="badge badge-success shadow-sm" style="font-size: 0.6rem; letter-spacing: 1px; font-weight: 800; padding: 2px 6px;">LUNAS</span>
                                         </div>
                                         @endif
                                         <div id="wrapper-nama-2-{{ $item->id }}" class="{{ $item->nama_2 || $item->nama_asli_2 ? '' : 'participant-2-hidden' }} mt-1 pt-1 border-top">
                                             <input form="form-update-{{ $item->id }}" type="text" name="nama_2" class="table-input" value="{{ $item->nama_2 }}" placeholder="Nama ke-2..." onblur="quickUpdateField(this, {{ $item->id }}, 'nama_2')">
                                         </div>
                                     </td>

                                     {{-- Nama Peserta Asli --}}
                                     <td class="p-1 align-middle d-none">
                                         <input form="form-update-{{ $item->id }}" type="text" name="nama_asli" class="table-input" value="{{ $item->nama_asli }}" placeholder="Nama asli..." onblur="quickUpdateField(this, {{ $item->id }}, 'nama_asli')">
                                         <div id="wrapper-nama-asli-2-{{ $item->id }}" class="{{ $item->nama_2 || $item->nama_asli_2 ? '' : 'participant-2-hidden' }} mt-1 pt-1 border-top">
                                             <input form="form-update-{{ $item->id }}" type="text" name="nama_asli_2" class="table-input" value="{{ $item->nama_asli_2 }}" placeholder="Nama asli ke-2..." onblur="quickUpdateField(this, {{ $item->id }}, 'nama_asli_2')">
                                         </div>
                                     </td>

                                     {{-- Status --}}
                                     <td class="p-1 align-middle text-center">
                                         @php
                                             $statusClass = 'status-aktif';
                                             if($item->status == 'Aktif') $statusClass = 'status-aktif';
                                             elseif($item->status == 'Cuti') $statusClass = 'status-cuti';
                                             elseif($item->status == 'Lulus') $statusClass = 'status-lulus';
                                         @endphp
                                         <select class="badge-status {{ $statusClass }}" onchange="quickUpdateField(this, {{ $item->id }}, 'status')">
                                             <option value="Aktif" {{ $item->status == 'Aktif' ? 'selected' : '' }} class="text-dark bg-white">Aktif</option>
                                             <option value="Cuti" {{ $item->status == 'Cuti' ? 'selected' : '' }} class="text-dark bg-white">Cuti</option>
                                             <option value="Lulus" {{ $item->status == 'Lulus' ? 'selected' : '' }} class="text-dark bg-white">Lulus</option>
                                         </select>
                                         @if($item->status == 'Cuti')
                                         <div class="mt-1 d-flex flex-column" style="gap: 4px;">
                                             <a href="javascript:void(0)" onclick="focusTanggalMasuk({{ $item->id }})" class="text-primary font-weight-bold" style="font-size: 0.65rem; border-bottom: 1px dashed #4e73df; text-decoration: none; width: fit-content;">
                                                 <i class="fas fa-calendar-alt"></i> Set Tgl Masuk Kembali
                                             </a>
                                             <a href="javascript:void(0)" onclick="focusTanggalSelesai({{ $item->id }})" class="text-secondary font-weight-bold" style="font-size: 0.65rem; border-bottom: 1px dashed #858796; text-decoration: none; width: fit-content;">
                                                 <i class="fas fa-calendar-check"></i> Set Tgl Selesai Cuti
                                             </a>
                                         </div>
                                         @endif
                                     </td>
                                         
                                     {{-- Tanggal Masuk & Selesai --}}
                                     <td class="p-1 align-middle">
                                         <input form="form-update-{{ $item->id }}" id="tgl_masuk_{{ $item->id }}" type="date" name="tanggal_masuk" class="table-input mb-1" value="{{ $item->tanggal_masuk }}" title="Tgl Masuk" onblur="quickUpdateField(this, {{ $item->id }}, 'tanggal_masuk')">
                                         <input form="form-update-{{ $item->id }}" id="tgl_selesai_{{ $item->id }}" type="date" name="tanggal_selesai" class="table-input" value="{{ $item->tanggal_selesai }}" title="Tgl Selesai" onblur="quickUpdateField(this, {{ $item->id }}, 'tanggal_selesai')">
                                     </td>
                                     
                                     <td class="p-1 align-middle">
                                         <input form="form-update-{{ $item->id }}" type="text" name="biaya_pendaftaran" class="table-input input-currency" value="{{ number_format($item->biaya_pendaftaran, 0, ',', '.') }}" onblur="quickUpdateField(this, {{ $item->id }}, 'biaya_pendaftaran')">
                                     </td>

                                     {{-- CS yang Closing --}}
                                     <td class="p-1 align-middle text-center">
                                         <span class="text-dark font-weight-bold" style="font-size: 0.8rem;">{{ $item->cs_name ?? '-' }}</span>
                                     </td>
                                    

                                        
                                        {{-- SPP Checkboxes --}}
                                        @php
                                            // Get Selected Year for filter
                                            $filterYear = request('filter_year', date('Y'));
                                            
                                            // Calculate active months ONLY for the selected year
                                            $visibleMonths = [];
                                            if($item->tanggal_masuk) {
                                                $start = \Carbon\Carbon::parse($item->tanggal_masuk)->startOfMonth();
                                                // Max 6 months window
                                                $periodEnd = $start->copy()->addMonths(5)->endOfMonth();
                                                
                                                // Check if the filtered year overlaps with this 6-month window
                                                if ($periodEnd->year >= $filterYear && $start->year <= $filterYear) {
                                                    // Determine which months in $filterYear are within [start, periodEnd]
                                                    for($m=1; $m<=12; $m++) {
                                                        $checkDate = \Carbon\Carbon::create($filterYear, $m, 1)->startOfMonth();
                                                        if($checkDate->between($start, $periodEnd)) {
                                                            $visibleMonths[] = $m;
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp

                                    @for($i = 1; $i <= 12; $i++)
                                    <td class="text-center align-middle p-0 spp-col {{ $i > 1 ? 'spp-extra' : '' }}">
                                        @php
                                            $isVisible = in_array($i, $visibleMonths);
                                            // Hide if Cuti or Lulus
                                            if ($item->status == 'Cuti' || $item->status == 'Lulus') {
                                                $isVisible = false;
                                            }
                                        @endphp
                                        @if($isVisible)
                                            <div class="spp-wrapper">
                                                <input type="checkbox" class="spp-checkbox" 
                                                       data-id="{{ $item->id }}" 
                                                       data-month="{{ $i }}" 
                                                       {{ $item->{"spp_$i"} >= 1000000 ? 'checked' : '' }}
                                                       title="Centang jika Lunas (Min. 1.000.000)"
                                                       onclick="openSppModal(this)">
                                                <input form="form-update-{{ $item->id }}" type="text" name="spp_{{ $i }}" 
                                                       id="spp_{{ $i }}_{{ $item->id }}"
                                                       value="{{ number_format($item->{"spp_$i"}, 0, ',', '.') }}" 
                                                       class="table-input input-currency spp-input-small" 
                                                       placeholder="0"
                                                       oninput="syncSppCheckbox(this, {{ $i }}, {{ $item->id }})"
                                                       onclick="openSppModal(this, true)"
                                                       onblur="quickUpdateField(this, {{ $item->id }}, 'spp_{{ $i }}')">
                                            </div>
                                        @else
                                            <span class="text-muted small" style="font-size: 0.7rem;">-</span>
                                        @endif
                                    </td>
                                    @endfor
                                    
                                    {{-- Action Buttons --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center gap-2">
                                            {{-- Manual Lunas Toggle --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-icon-split shadow-sm {{ $item->is_lunas ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                    title="{{ $item->is_lunas ? 'Batalkan Lunas' : 'Tandai Lunas Manual' }}" 
                                                    style="padding: 2px 6px;" 
                                                    onclick="toggleLunasManual({{ $item->id }}, this)">
                                                <i class="fas fa-check-double"></i>
                                            </button>

                                            {{-- Delete Form (Separate) --}}
                                            <button type="button" class="btn btn-danger btn-sm btn-icon-split shadow-sm" title="Hapus" style="padding: 2px 6px;" onclick="deletePeserta({{ $item->id }}, this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                filterControls.classList.remove('spp-hidden');
                btn.innerHTML = '<i class="fas fa-compress-alt"></i>';
                firstLabel.innerHTML = 'Jan'; // Adjust if needed
                localStorage.setItem('spp_collapsed', 'false');
            } else {
                // Collapse
                extras.forEach(el => el.classList.add('spp-hidden'));
                header.setAttribute('colspan', 1);
                filterControls.classList.add('spp-hidden');
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
                if(sink) sink.value = value;
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
                    } catch(e) {
                        throw new Error('Server returned error: ' + response.status);
                    }
                }
                return response.json();
            })
            .then(data => {
                console.log('Success updating ' + fieldName + ':', data);
                // Highlight success briefly
                const originalColor = element.style.borderColor;
                element.style.borderColor = '#1cc88a';
                setTimeout(() => { element.style.borderColor = originalColor; }, 1000);
            })
            .catch(error => {
                console.error('Error:', error);
                element.style.opacity = '1';
                element.style.borderColor = '#e74a3b';
                alert('Gagal simpan otomatis (' + fieldName + '): ' + error.message);
            });
        }

        // Keep quickUpdateStatus legacy call if needed, but point to quickUpdateField
        function quickUpdateStatus(select, id) {
            quickUpdateField(select, id, 'status');
        }

        // POPUP MODAL LOGIC
        let initialSppValues = {}; // Store original values to track changes
        function openSppModal(trigger, isInput = false) {
            let id, month;
            if (isInput) {
                const matches = trigger.id.match(/spp_(\d+)_(\d+)/);
                month = matches[1];
                id = matches[2];
            } else {
                id = trigger.dataset.id;
                month = trigger.dataset.month;
                // Keep checkbox checked as it was toggled by the click
                trigger.checked = !trigger.checked; 
            }

            document.getElementById('modalSppId').value = id;
            document.getElementById('modalSppMonth').value = month;
            
            // Populate checkboxes based on current table state
            const months = [1,2,3,4,5,6,7,8,9,10,11,12];
            initialSppValues = {};
            let totalInitialChecked = 0;
            
            months.forEach(m => {
                const input = document.getElementById(`spp_${m}_${id}`);
                const cb = document.getElementById(`cbMonth${m}`);
                const label = document.querySelector(`label[for="cbMonth${m}"]`);
                const parent = cb.closest('.month-box');
                
                if (input) {
                    const valStr = input.value.replace(/[^0-9]/g, '') || 0;
                    const val = parseInt(valStr);
                    initialSppValues[m] = val;
                    cb.checked = (val >= 1000000);
                    cb.disabled = false;
                    label.classList.remove('text-muted');
                    if(parent) parent.style.background = cb.checked ? '#e3f2fd' : '#f8f9fc';
                    if (cb.checked) totalInitialChecked++;
                } else {
                    initialSppValues[m] = -1; // Not visible
                    cb.checked = false;
                    cb.disabled = true;
                    if(parent) parent.style.background = '#eeeeee';
                    label.classList.add('text-muted');
                }
            });

            // Set nominal to value of targeted month or 1M if checked
            const currentVal = document.getElementById(`spp_${month}_${id}`).value;
            document.getElementById('modalSppNominal').value = currentVal;

            // Reset Lunas Semua checkbox
            document.getElementById('cbLunasSemua').checked = false;

            $('#sppModal').modal('show');
        }

        function toggleCheckAllMonths(checkbox) {
            const allCbs = document.querySelectorAll('.modal-month-cb');
            allCbs.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = checkbox.checked;
                }
            });
            calculateNominalFromCheckboxes();
        }

        function calculateCheckboxesFromNominal() {
            const nominalInput = document.getElementById('modalSppNominal');
            const valNum = parseInt(nominalInput.value.replace(/[^0-9]/g, '') || 0);
            const additionalMonths = Math.floor(valNum / 1000000);
            
            const checkboxes = Array.from(document.querySelectorAll('.modal-month-cb'));
            
            // Reset to initial state (the ones that were already paid in main table)
            checkboxes.forEach(cb => {
                const m = parseInt(cb.dataset.month);
                cb.checked = (initialSppValues[m] >= 1000000);
                const parent = cb.closest('.month-box');
                if(parent) parent.style.background = cb.checked ? '#e3f2fd' : '#f8f9fc';
            });
            
            // Check N additional UNCHECKED and ENABLED months
            let added = 0;
            for (let cb of checkboxes) {
                if (!cb.disabled && !cb.checked && added < additionalMonths) {
                    cb.checked = true;
                    added++;
                    const parent = cb.closest('.month-box');
                    if(parent) parent.style.background = '#e3f2fd';
                }
            }
        }

        function calculateNominalFromCheckboxes() {
            const id = document.getElementById('modalSppId').value;
            const allCbs = Array.from(document.querySelectorAll('.modal-month-cb'));
            const nominalInput = document.getElementById('modalSppNominal');
            
            // Count how many NEWly checked boxes there are relative to initial state?
            // "misal input 3M... dichecklist..."
            // Let's assume the user wants to calculate the INCREMENTAL payment?
            // "input 3M" -> 3 months checked.
            // If they check 4 months, nominal should show 4M.
            
            let checkedAfterCount = 0;
            allCbs.forEach(cb => {
                const parent = cb.closest('.month-box');
                if (cb.checked) {
                    checkedAfterCount++;
                    if(parent) parent.style.background = '#e3f2fd';
                } else {
                    if(parent) parent.style.background = cb.disabled ? '#eee' : '#f8f9fc';
                }
            });
            
            // If user manually checks boxes, maybe we set nominal based on TOTAL checked?
            // But if they clicked Jan (already paid), it shouldn't add to "payment now"?
            // Let's count ONLY months that are checked NOW but were NOT 1M initially.
            let newPaymentCount = 0;
            allCbs.forEach(cb => {
                const m = parseInt(cb.dataset.month);
                if (cb.checked && (initialSppValues[m] < 1000000)) {
                    newPaymentCount++;
                }
            });
            
            nominalInput.value = new Intl.NumberFormat('id-ID').format(newPaymentCount * 1000000);
        }

        function saveSppFromModal() {
            const id = document.getElementById('modalSppId').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');
            formData.append('ajax_field', 'spp_bulk');
            formData.append('is_ajax', '1');

            const checkboxes = document.querySelectorAll('.modal-month-cb');
            checkboxes.forEach(cb => {
                const m = cb.dataset.month;
                if (!cb.disabled) {
                    // Update main table logic: if checked set to 1.000.000 (if it was < 1M)
                    // Wait, what if they UNCHECKED a month?
                    let finalVal = cb.checked ? '1.000.000' : '0';
                    formData.append(`spp_${m}`, finalVal);
                }
            });

            const btn = event.target;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(`{{ url('peserta-smi') }}/${id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update main table
                    checkboxes.forEach(cb => {
                        const m = cb.dataset.month;
                        if (!cb.disabled) {
                            const input = document.getElementById(`spp_${m}_${id}`);
                            if (input) {
                                input.value = cb.checked ? '1.000.000' : '0';
                                syncSppCheckbox(input, m, id);
                            }
                        }
                    });
                    $('#sppModal').modal('hide');
                } else {
                    alert('Gagal simpan: ' + data.message);
                }
            })
            .catch(e => console.error(e))
            .finally(() => {
                btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan';
                btn.disabled = false;
            });
        }

        function toggleSppLunas(checkbox) {
            // Deprecated, now handled by modal
            openSppModal(checkbox);
        }

        function syncSppCheckbox(input, month, id) {
            const val = input.value.replace(/[^0-9]/g, '');
            const checkbox = document.querySelector(`.spp-checkbox[data-id="${id}"][data-month="${month}"]`);
            if (checkbox) {
                checkbox.checked = (parseInt(val || 0) >= 1000000);
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
            
            if (!confirm(`Tanda sebagai ${newVal ? 'LUNAS' : 'Belum Lunas'} manual?`)) return;

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
                    location.reload(); 
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(e => console.error(e))
            .finally(() => btn.disabled = false);
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
            if (!confirm('Apakah Anda yakin ingin menghapus data peserta ini?')) return;

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
                    row.style.transition = 'all 0.5s ease';
                    row.style.transform = 'translateX(20px)';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Update numbers
                        document.querySelectorAll('#dataTable tbody tr').forEach((r, i) => {
                            r.querySelector('td:first-child').innerText = i + 1;
                        });
                    }, 500);
                } else {
                    alert('Gagal menghapus: ' + (data.message || 'Unknown error'));
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'auto';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data.');
                row.style.opacity = '1';
                row.style.pointerEvents = 'auto';
            });
        }

        function updateStatusColor(select, id) {
            // ... kept for compatibility with other fields if needed ...
            select.classList.remove('status-aktif', 'status-cuti', 'status-lulus');
            select.classList.add('status-' + select.value.toLowerCase());
            const sink = document.getElementById('sink-status-' + id);
            if(sink) sink.value = select.value;
        }

        // Auto-collapse on load if preference saved
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('spp_collapsed') === 'true') {
                toggleSpp();
            }

            // Real-time search logic
            const searchInput = document.getElementById('realtimeSearchSmi');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const value = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#dataTable tbody tr');
                    
                    tableRows.forEach(row => {
                        const inputs = row.querySelectorAll('td:nth-child(2) input, td:nth-child(3) input');
                        let nameText = '';
                        inputs.forEach(input => {
                            nameText += input.value + ' ';
                        });
                        
                        if (nameText.toLowerCase().indexOf(value) > -1) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                });
            }

            // Initial currency formatting
            document.querySelectorAll('.input-currency').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/[^0-9]/g, '');
                    if (value) {
                        this.value = new Intl.NumberFormat('id-ID').format(value);
                    } else {
                        this.value = '';
                    }
                });
            });
        });
    </script>

    <!-- SPP NOMINAL MODAL -->
    <div class="modal fade" id="sppModal" tabindex="-1" role="dialog" aria-labelledby="sppModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document" style="max-width: 350px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-primary text-white py-2 px-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h6 class="modal-title" id="sppModalLabel" style="font-size: 0.9rem;"><i class="fas fa-money-bill-wave mr-2"></i>Nominal SPP</h6>
                    <button type="button" class="close text-white py-2" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" id="modalSppId">
                    <input type="hidden" id="modalSppMonth">
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 0.8rem;">Nominal Pembayaran (Otomatis Checklist)</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0" style="font-size: 0.75rem;">Rp</span>
                            </div>
                            <input type="text" id="modalSppNominal" class="form-control input-currency border-left-0 font-weight-bold text-primary" placeholder="0" style="font-size: 0.9rem; height: 35px;" oninput="calculateCheckboxesFromNominal()">
                        </div>
                    </div>

                    <div class="mb-3 px-1">
                        <div class="d-flex align-items-center p-2 border rounded bg-light shadow-sm" style="cursor: pointer;" onclick="const cb = document.getElementById('cbLunasSemua'); cb.checked = !cb.checked; toggleCheckAllMonths(cb);">
                            <input type="checkbox" class="custom-checkbox mr-2" id="cbLunasSemua" onchange="toggleCheckAllMonths(this)" style="width: 18px; height: 18px; cursor: pointer;" onclick="event.stopPropagation()">
                            <label class="font-weight-bold text-success mb-0" for="cbLunasSemua" style="cursor: pointer; font-size: 0.8rem; white-space: nowrap;">
                                <i class="fas fa-check-double mr-1"></i> Tandai Lunas Semua (Ceklis Semua Bulan)
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark mb-2" style="font-size: 0.8rem;">Pilih Bulan Pembayaran</label>
                        <div class="row no-gutters" id="modalMonthCheckboxes">
                            @php
                                $monthsShort = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Ags', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
                            @endphp
                            @foreach($monthsShort as $i => $m)
                            <div class="col-4 p-1">
                                <div class="month-box border rounded p-1 text-center" style="background: #f8f9fc; transition: all 0.2s;">
                                    <div class="custom-control custom-checkbox d-inline-block p-0" style="min-height: auto;">
                                        <input type="checkbox" class="custom-control-input modal-month-cb" id="cbMonth{{ $i }}" data-month="{{ $i }}" onchange="calculateNominalFromCheckboxes()">
                                        <label class="custom-control-label font-weight-bold" for="cbMonth{{ $i }}" style="cursor: pointer; font-size: 0.7rem; color: #5a5c69; padding-left: 20px; line-height: 1.5;">
                                            {{ $m }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal" style="font-size: 0.75rem;">Batal</button>
                    <button type="button" class="btn btn-sm btn-primary px-3 font-weight-bold" onclick="saveSppFromModal()" style="font-size: 0.75rem;">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endsection
