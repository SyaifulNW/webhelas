@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fa-solid fa-users-rectangle me-2"></i>
            Database Marketing ({{ $user->name }})
        </h4>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('marketing-participants.index') }}" method="GET" class="d-flex gap-2">
                <select name="bulan" class="form-select border-1 border-white shadow-sm rounded-pill px-3 bg-gradient-dark-blue text-white fw-bold me-2" style="height: 40px; cursor: pointer;" onchange="this.form.submit()">
                    @foreach($bulanNames as $m => $name)
                        <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }} class="text-dark">{{ $name }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-select border-1 border-white shadow-sm rounded-pill px-3 bg-gradient-dark-blue text-white fw-bold" style="height: 40px; cursor: pointer;" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }} class="text-dark">{{ $y }}</option>
                    @endfor
                </select>
                
                @if(request('marketing_user_id')) <input type="hidden" name="marketing_user_id" value="{{ request('marketing_user_id') }}"> @endif
                @if(request('potensi')) <input type="hidden" name="potensi" value="{{ request('potensi') }}"> @endif
                @if(request('provinsi_id')) <input type="hidden" name="provinsi_id" value="{{ request('provinsi_id') }}"> @endif
                @if(request('kota_id')) <input type="hidden" name="kota_id" value="{{ request('kota_id') }}"> @endif
            </form>
        </div>
    </div>

    {{-- Stats & Filter Section --}}
    <div class="card shadow-sm mb-4 border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="px-4 py-4 bg-light border-bottom">
                <div class="d-flex align-items-stretch flex-wrap gap-4">
                    
                    {{-- Total Counter --}}
                    <div class="bg-dark text-white p-3 rounded-3 shadow-sm d-flex flex-column justify-content-center" style="min-width: 220px; border-left: 5px solid #ffc107;">
                        <span class="text-uppercase fw-bold opacity-75 mb-2" style="font-size: 0.7rem; letter-spacing: 1px;">Status Database</span>
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small opacity-75">Total:</span>
                                <h4 class="mb-0 fw-bold text-warning">{{ $totalMarketingDatabase }}</h4>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small opacity-75">Terkirim:</span>
                                <h6 class="mb-0 fw-bold text-success">{{ $transferredCount }}</h6>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small opacity-75">Belum Kirim:</span>
                                <h6 class="mb-0 fw-bold text-danger">{{ $untransferredCount }}</h6>
                            </div>
                        </div>
                    </div>



                    {{-- Individual CS Badges --}}
                    <div class="flex-grow-1 d-flex flex-column justify-content-between p-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-chart-pie text-primary small"></i>
                                <span class="fw-bold text-muted small text-uppercase" style="letter-spacing: 0.5px;">Distribusi CS ({{ $bulanNames[$currentMonth] }} {{ $currentYear }})</span>
                            </div>

                            @if($isAdministrator || ($isAdvertising ?? false))
                            <form action="{{ route('marketing-participants.index') }}" method="GET" id="filterForm" class="d-flex align-items-center gap-2">
                                @if(request('potensi'))
                                    <input type="hidden" name="potensi" value="{{ request('potensi') }}">
                                @endif
                                <input type="hidden" name="bulan" value="{{ $currentMonth }}">
                                <input type="hidden" name="tahun" value="{{ $currentYear }}">
                                @if(request('provinsi_id')) <input type="hidden" name="provinsi_id" value="{{ request('provinsi_id') }}"> @endif
                                @if(request('kota_id')) <input type="hidden" name="kota_id" value="{{ request('kota_id') }}"> @endif
                                <label class="small fw-bold text-muted text-uppercase mb-0" style="font-size: 0.65rem;">Pilih Tim Marketing:</label>
                                <select name="marketing_user_id" class="form-select border-0 shadow-sm rounded-3 bg-white px-3" style="min-width: 180px; height: 35px; font-size: 0.85rem;" onchange="this.form.submit()">
                                    <option value="all">{{ ($isAdvertising ?? false) ? 'ALL Tim Marketing' : 'Semua Marketing' }}</option>
                                    @foreach($marketingUsers as $mUser)
                                        <option value="{{ $mUser->id }}" {{ request('marketing_user_id') == $mUser->id ? 'selected' : '' }}>
                                            {{ $mUser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($csDistribution as $cs)
                            <div class="bg-white border rounded-2 px-3 py-2 d-flex align-items-center gap-3 shadow-sm" style="min-width: 140px;">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark small text-uppercase" style="font-size: 0.75rem;">{{ $cs->name === 'Putri' ? 'Diah Putri' : $cs->name }}</div>
                                    <div class="text-muted" style="font-size: 0.65rem;"><i class="fa-solid fa-paper-plane me-1"></i>Terkirim</div>
                                </div>
                                <div class="bg-light fw-bold px-2 py-1 rounded border text-primary" style="font-size: 1rem;">
                                    {{ $cs->count }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Manual Add Action (Only for Marketing) --}}
    @if(!$isAdministrator && !($isAdvertising ?? false))
    <div class="mb-4 d-flex justify-content-start">
        <button type="button" id="btnAddRow" class="btn btn-success px-4 rounded-pill shadow-sm d-flex align-items-center gap-2 transition-all" style="height: 45px; background: linear-gradient(135deg, #2ecc71, #27ae60); border: none;">
            <i class="fa-solid fa-plus-circle"></i>
            <span class="fw-bold text-uppercase" style="font-size: 0.8rem;">Tambah data</span>
        </button>
    </div>
    @endif

    {{-- Tabel Database --}}
    <div class="card shadow-lg rounded-4 overflow-hidden mb-5 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center table-hover custom-table" id="participantTable">
                    <thead>
                        <tr class="fw-bold bg-light text-dark text-uppercase small letter-spacing-1">
                            <th>NO</th>
                            <th>NAMA</th>
                            <th>NO WA</th>
                            <th style="width: 160px; vertical-align: middle;">
                                <div class="text-white small fw-bold mb-1">DOMISILI</div>
                                <form action="{{ route('marketing-participants.index') }}" method="GET" class="m-0 d-flex flex-column gap-1">
                                    @if(($isAdministrator || ($isAdvertising ?? false)) && request('marketing_user_id'))
                                        <input type="hidden" name="marketing_user_id" value="{{ request('marketing_user_id') }}">
                                    @endif
                                    <input type="hidden" name="bulan" value="{{ $currentMonth }}">
                                    <input type="hidden" name="tahun" value="{{ $currentYear }}">
                                    @if(request('potensi')) <input type="hidden" name="potensi" value="{{ request('potensi') }}"> @endif
                                    
                                    <select name="provinsi_id" class="form-select form-select-sm border-0 bg-white text-dark fw-bold p-0 text-center shadow-none" style="font-size: 0.7rem; cursor: pointer; height: 22px;" onchange="this.form.submit()">
                                        <option value="">PROVINSI (ALL)</option>
                                        @foreach($filterProvinces as $p)
                                            @php 
                                                $pid = is_array($p) ? $p['id'] : $p->id; 
                                                $pname = is_array($p) ? $p['name'] : $p->name; 
                                            @endphp
                                            <option value="{{ $pid }}" {{ request('provinsi_id') == $pid ? 'selected' : '' }}>{{ strtoupper($pname) }}</option>
                                        @endforeach
                                    </select>
                                    <select name="kota_id" class="form-select form-select-sm border-0 bg-white text-dark fw-bold p-0 text-center shadow-none" style="font-size: 0.7rem; cursor: pointer; height: 22px;" onchange="this.form.submit()">
                                        <option value="">KOTA (ALL)</option>
                                        @foreach($filterCities as $c)
                                            @php 
                                                $cid = is_array($c) ? $c['id'] : $c->id; 
                                                $cname = is_array($c) ? $c['name'] : $c->name; 
                                            @endphp
                                            <option value="{{ $cid }}" {{ request('kota_id') == $cid ? 'selected' : '' }}>{{ strtoupper($cname) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </th>
                            <th>NAMA & JENIS (BISNIS)</th>
                            <th>OMSET</th>
                            <th style="width: 130px; vertical-align: middle;">
                                <form action="{{ route('marketing-participants.index') }}" method="GET" class="m-0">
                                    @if(($isAdministrator || ($isAdvertising ?? false)) && request('marketing_user_id'))
                                        <input type="hidden" name="marketing_user_id" value="{{ request('marketing_user_id') }}">
                                    @endif
                                    <input type="hidden" name="bulan" value="{{ $currentMonth }}">
                                    <input type="hidden" name="tahun" value="{{ $currentYear }}">
                                    @if(request('provinsi_id')) <input type="hidden" name="provinsi_id" value="{{ request('provinsi_id') }}"> @endif
                                    @if(request('kota_id')) <input type="hidden" name="kota_id" value="{{ request('kota_id') }}"> @endif

                                    <select name="potensi" class="form-select form-select-sm border-0 bg-transparent text-white fw-bold p-0 text-center shadow-none" style="font-size: 0.85rem; cursor: pointer; text-transform: uppercase;" onchange="this.form.submit()">
                                        <option value="all" class="text-dark" {{ request('potensi') == 'all' ? 'selected' : '' }}>POTENSI (ALL)</option>
                                        <option value="MBC" class="text-dark" {{ request('potensi') == 'MBC' ? 'selected' : '' }}>POTENSI (MBC)</option>
                                        <option value="SMI" class="text-dark" {{ request('potensi') == 'SMI' ? 'selected' : '' }}>POTENSI (SMI)</option>
                                    </select>
                                </form>
                            </th>
                            @if(!($isAdvertising ?? false))
                            <th style="width: 100px;">ACTION</th>
                            @endif
                            <th style="width: 120px;">CS PENERIMA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $i => $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="bg-light fw-bold text-muted row-number">{{ (method_exists($participants, 'firstItem') ? $participants->firstItem() : 1) + $i }}</td>
                            <td class="text-start px-2">
                                <div class="editable" data-field="nama" contenteditable="{{ ($isAdvertising ?? false) ? 'false' : 'true' }}">{{ $item->nama }}</div>
                            </td>
                            <td class="text-start px-2">
                                <div class="editable" data-field="no_wa" contenteditable="{{ ($isAdvertising ?? false) ? 'false' : 'true' }}">{{ $item->no_wa }}</div>
                            </td>
                            <td class="p-1" style="width: 160px;">
                                <div class="d-flex flex-column gap-1">
                                    <select class="form-select form-select-lg-custom select-provinsi shadow-sm" data-id="{{ $item->id }}" data-nama="{{ $item->provinsi_nama }}" {{ ($isAdvertising ?? false) ? 'disabled' : '' }} style="font-size: 0.75rem !important; height: 28px !important;">
                                        <option value="">{{ $item->provinsi_nama ?: '-- Prov --' }}</option>
                                    </select>
                                    <select class="form-select form-select-lg-custom select-kota shadow-sm" data-id="{{ $item->id }}" data-prov-id="{{ $item->provinsi_id }}" data-nama="{{ $item->kota_nama }}" {{ ($isAdvertising ?? false) ? 'disabled' : '' }} style="font-size: 0.75rem !important; height: 28px !important;">
                                         <option value="">{{ $item->kota_nama ?: '-- Kota --' }}</option>
                                    </select>
                                </div>
                            </td>
                            <td class="p-0">
                                <div class="d-flex flex-column gap-0 p-1">
                                    <div class="editable fw-bold border-bottom pb-1" data-field="nama_bisnis" contenteditable="{{ ($isAdvertising ?? false) ? 'false' : 'true' }}" title="Nama Bisnis">{{ $item->nama_bisnis }}</div>
                                    <div class="editable mt-1" data-field="jenis_bisnis" contenteditable="{{ ($isAdvertising ?? false) ? 'false' : 'true' }}" title="Jenis Bisnis">{{ $item->jenis_bisnis }}</div>
                                </div>
                            </td>
                            <td class="px-2">
                                <select class="form-select text-center fw-bold omset-select shadow-sm" data-field="omset" {{ ($isAdvertising ?? false) ? 'disabled' : '' }} style="font-size: 0.85rem !important; height: 32px !important; border-radius: 10px !important; padding: 0 5px !important;">
                                    <option value="">-- Pilih --</option>
                                    <option value="0 – 30 Juta" {{ $item->omset == '0 – 30 Juta' ? 'selected' : '' }}>0 – 30 Juta</option>
                                    <option value="30 – 50 Juta" {{ $item->omset == '30 – 50 Juta' ? 'selected' : '' }}>30 – 50 Juta</option>
                                    <option value="50 – 100 Juta" {{ $item->omset == '50 – 100 Juta' ? 'selected' : '' }}>50 – 100 Juta</option>
                                    <option value="100 – 250 Juta" {{ $item->omset == '100 – 250 Juta' ? 'selected' : '' }}>100 – 250 Juta</option>
                                    <option value="Di atas 250 Juta" {{ $item->omset == 'Di atas 250 Juta' ? 'selected' : '' }}>Di atas 250 Juta</option>
                                </select>
                            </td>
                            <td class="px-2">
                                <select class="form-select text-center fw-bold potensi-select shadow-sm" data-field="potensi" {{ ($isAdvertising ?? false) ? 'disabled' : '' }}>
                                    <option value="MBC" {{ $item->potensi == 'MBC' ? 'selected' : '' }}>MBC</option>
                                    <option value="SMI" {{ $item->potensi == 'SMI' ? 'selected' : '' }}>SMI</option>
                                </select>
                            </td>
                            @if(!($isAdvertising ?? false))
                            <td class="px-2">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    @if(!$item->is_transferred)
                                        <button type="button" class="btn btn-transfer border-0 p-1" title="Pindahkan ke CS Rotator">
                                            <i class="fa-solid fa-circle-arrow-right"></i>
                                        </button>
                                    @else
                                        <i class="fa-solid fa-check-circle text-success fs-5" title="Telah di CS"></i>
                                    @endif
                                    <button type="button" class="btn btn-delete border-0 p-1" title="Hapus Data">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                            <td class="px-2 fw-bold text-primary text-center">
                                @if($item->assigned_cs)
                                    @php
                                        $csClasses = [
                                            'Linda' => 'cs-badge-linda',
                                            'Yasmin' => 'cs-badge-yasmin',
                                            'Putri' => 'cs-badge-putri',
                                            'Arifa' => 'cs-badge-arifa',
                                            'Puput' => 'cs-badge-puput',
                                        ];
                                        $csClass = $csClasses[$item->assigned_cs] ?? 'bg-soft-blue text-primary border-primary';
                                    @endphp
                                    <span class="badge cs-badge {{ $csClass }} px-2 py-1">
                                        {{ str_ireplace('Putri', 'Diah Putri', $item->assigned_cs) }}
                                    </span>
                                @elseif($item->is_transferred)
                                    <span class="text-muted small">Terkirim</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row">
                            <td colspan="{{ !($isAdvertising ?? false) ? 9 : 8 }}" class="py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p>Belum ada data marketing.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
    
    body { font-family: 'Outfit', sans-serif; background-color: #f0f2f5; color: #1a1a1a; }
    
    /* Balanced Table Styling with High Contrast */
    .custom-table { border-collapse: collapse; width: 100%; border: 1px solid #dee2e6; }
    .custom-table thead th { 
        background-color: #2c3e50 !important; 
        color: #ffffff !important; 
        font-weight: 700; 
        padding: 12px 8px; 
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        text-transform: uppercase;
        vertical-align: middle;
    }
    
    .custom-table tbody td { 
        padding: 8px 6px; 
        font-size: 0.85rem;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        color: #2d3436;
    }

    .row-number { font-size: 0.95rem !important; color: #2c3e50 !important; font-weight: 800; background-color: #f8f9fa !important; }

    /* Compact Editable Areas */
    .editable { 
        outline: none; 
        transition: all 0.2s; 
        cursor: text; 
        min-height: 22px; 
        padding: 3px 5px;
        border-radius: 6px;
        font-weight: 500;
    }
    .editable:hover { background-color: #fffde7 !important; border: 1px dashed #ced4da; }
    .editable:focus { background-color: #ffffff !important; box-shadow: 0 0 0 2px rgba(77, 171, 247, 0.3); border: 1px solid #4DABF7; }
    
    .bg-gradient-dark-blue { 
        background: linear-gradient(135deg, #0984e3, #2c3e50) !important; 
    }
    
    /* Text Specific Sizes - Balanced */
    [data-field="nama"] { font-size: 0.95rem; color: #000; font-weight: 600; }
    [data-field="no_wa"] { font-size: 0.9rem; font-weight: 600; color: #0984e3; }
    [data-field="nama_bisnis"] { font-size: 0.95rem; color: #2d3436; }
    [data-field="jenis_bisnis"] { font-size: 0.85rem; color: #636e72; font-style: italic; }

    /* Compact Form Selects */
    .form-select-lg-custom {
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border: 1px solid #ced4da !important;
        border-radius: 8px !important;
        height: 36px !important;
        padding-left: 10px !important;
        background-color: #fff !important;
    }
    .form-select-lg-custom:focus { border-color: #2c3e50 !important; box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1) !important; }

    .potensi-select { font-size: 0.85rem !important; height: 32px !important; border-radius: 10px !important; padding: 0 5px !important; }
    .potensi-select[value="MBC"], .potensi-select:has(option[value="MBC"]:checked) { background-color: #ff7675 !important; color: #ffffff !important; border: none !important; }
    .potensi-select[value="SMI"], .potensi-select:has(option[value="SMI"]:checked) { background-color: #55efc4 !important; color: #000000 !important; border: none !important; }
    
    /* Compact Action Buttons */
    .btn-move-cs { 
        font-size: 0.75rem !important; 
        padding: 6px 10px !important; 
        font-weight: 700 !important; 
        border-radius: 8px !important;
        border: 1.5px solid #17a2b8 !important;
        background-color: transparent;
        color: #17a2b8;
    }
    .btn-move-cs:hover { background-color: #17a2b8 !important; color: #fff !important; transform: translateY(-1px); }
    
    .btn-delete { font-size: 1rem !important; color: #d63031; transition: all 0.2s; opacity: 0.7; }
    .btn-delete:hover { color: #ff7675; transform: scale(1.1); opacity: 1; }

    .bg-soft-blue { background-color: #e7f5ff !important; }

    /* CS Specific Badges */
    .cs-badge { font-weight: 700 !important; border-width: 1.5px !important; border-style: solid !important; }
    .cs-badge-linda { background-color: #f3f0ff !important; color: #7048e8 !important; border-color: #7048e8 !important; }
    .cs-badge-yasmin { background-color: #e6fcf5 !important; color: #0ca678 !important; border-color: #0ca678 !important; }
    .cs-badge-putri { background-color: #fff4e6 !important; color: #f76707 !important; border-color: #f76707 !important; }
    .cs-badge-arifa { background-color: #fff0f6 !important; color: #d6336c !important; border-color: #d6336c !important; }
    .cs-badge-puput { background-color: #e7f5ff !important; color: #1c7ed6 !important; border-color: #1c7ed6 !important; }
    
    .btn-transfer { font-size: 1.25rem !important; color: #0984e3; transition: all 0.2s; cursor: pointer; }
    .btn-transfer:hover { color: #4dabf7; transform: scale(1.2); }
    .btn-transfer.disabled { color: #adb5bd !important; cursor: not-allowed; transform: none !important; }

    .badge-transferred { font-size: 0.8rem !important; padding: 8px !important; font-weight: 700 !important; border-radius: 8px; }
    
    .transition-all { transition: all 0.2s ease; }
    .transition-all:hover { transform: translateY(-2px); }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAddRow = document.getElementById('btnAddRow');
        const tableBody = document.querySelector('#participantTable tbody');
        const thPotensi = document.querySelector('th:nth-child(6)'); // Header Potensi

        // 1. Logic Sort Potensi (Click Header)
        if (thPotensi) {
            thPotensi.style.cursor = 'pointer';
            thPotensi.innerHTML += ' <i class="fa-solid fa-sort ms-1 opacity-50"></i>';
            let sortOrder = 1; // 1: Asc, -1: Desc

            thPotensi.addEventListener('click', function() {
                const rows = Array.from(tableBody.querySelectorAll('tr:not(.empty-row)'));
                
                rows.sort((a, b) => {
                    const valA = a.querySelector('.potensi-select').value;
                    const valB = b.querySelector('.potensi-select').value;
                    return valA.localeCompare(valB) * sortOrder;
                });

                sortOrder *= -1; // Toggle sort
                
                // Re-append sorted rows
                rows.forEach(row => tableBody.appendChild(row));
                updateRowNumbers();
            });
        }
        
        // Initial binding for existing rows
        document.querySelectorAll('#participantTable tbody tr:not(.empty-row)').forEach(row => {
            bindEvents(row);
            populateProvinces(row);
        });

        if (btnAddRow) {
            btnAddRow.addEventListener('click', function() {
                btnAddRow.disabled = true;
                btnAddRow.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                fetch("{{ route('marketing-participants.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        const emptyRow = tableBody.querySelector('.empty-row');
                        if (emptyRow) emptyRow.remove();

                        const count = tableBody.querySelectorAll('tr').length + 1;
                        const item = res.data;
                        
                        const newRow = document.createElement('tr');
                        newRow.setAttribute('data-id', item.id);
                        newRow.innerHTML = `
                            <td class="bg-light fw-bold text-muted row-number">${count}</td>
                            <td class="text-start px-2">
                                <div class="editable" data-field="nama" contenteditable="true"></div>
                            </td>
                            <td class="text-start px-2">
                                <div class="editable" data-field="no_wa" contenteditable="true"></div>
                            </td>
                            <td class="p-1" style="width: 160px;">
                                <div class="d-flex flex-column gap-1">
                                    <select class="form-select form-select-lg-custom select-provinsi shadow-sm" data-id="${item.id}" style="font-size: 0.75rem !important; height: 28px !important;">
                                        <option value="">-- Prov --</option>
                                    </select>
                                    <select class="form-select form-select-lg-custom select-kota" data-id="${item.id}" style="font-size: 0.75rem !important; height: 28px !important;">
                                         <option value="">-- Kota --</option>
                                    </select>
                                </div>
                            </td>
                            <td class="p-0">
                                <div class="d-flex flex-column gap-0 p-1">
                                    <div class="editable fw-bold border-bottom pb-1" data-field="nama_bisnis" contenteditable="true" title="Nama Bisnis"></div>
                                    <div class="editable mt-1" data-field="jenis_bisnis" contenteditable="true" title="Jenis Bisnis"></div>
                                </div>
                            </td>
                            <td class="px-2">
                                <select class="form-select text-center fw-bold omset-select shadow-sm" data-field="omset" style="font-size: 0.8rem !important; height: 28px !important; border-radius: 10px !important; padding: 0 5px !important;">
                                    <option value="">-- Pilih --</option>
                                    <option value="0 – 30 Juta">0 – 30 Juta</option>
                                    <option value="30 – 50 Juta">30 – 50 Juta</option>
                                    <option value="50 – 100 Juta">50 – 100 Juta</option>
                                    <option value="100 – 250 Juta">100 – 250 Juta</option>
                                    <option value="Di atas 250 Juta">Di atas 250 Juta</option>
                                </select>
                            </td>
                            <td class="px-2">
                                <select class="form-select text-center fw-bold potensi-select shadow-sm" data-field="potensi" style="font-size: 0.8rem !important; height: 28px !important;">
                                    <option value="MBC" selected>MBC</option>
                                    <option value="SMI">SMI</option>
                                </select>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <button type="button" class="btn btn-transfer border-0 p-1" title="Pindahkan ke CS Rotator">
                                        <i class="fa-solid fa-circle-arrow-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-delete border-0 p-1" title="Hapus Data">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-2 fw-bold text-primary text-center">
                                <span class="text-muted small">-</span>
                            </td>
                        `;
                        tableBody.prepend(newRow);
                        updateRowNumbers();
                        bindEvents(newRow);
                        populateProvinces(newRow);
                        
                        const nameCell = newRow.querySelector('[data-field="nama"]');
                        if (nameCell) nameCell.focus();
                    }
                })
                .finally(() => {
                    btnAddRow.disabled = false;
                    btnAddRow.innerHTML = '<i class="fa-solid fa-plus-circle"></i> <span class="fw-bold">TAMBAH BARIS</span>';
                });
            });
        }

        function bindEvents(row) {
            row.querySelectorAll('.editable').forEach(cell => {
                cell.addEventListener('blur', () => updateData(cell));
                cell.addEventListener('keydown', (e) => { if(e.key==='Enter'){ e.preventDefault(); cell.blur(); } });
            });

            const select = row.querySelector('.potensi-select');
            if (select) {
                applySelectColor(select);
                select.addEventListener('change', (e) => {
                    applySelectColor(e.target);
                    updateData(e.target);
                });
            }

            const omsetSelect = row.querySelector('.omset-select');
            if (omsetSelect) {
                omsetSelect.addEventListener('change', (e) => {
                    updateData(e.target);
                });
            }

            const btnDelete = row.querySelector('.btn-delete');
            if (btnDelete) {
                btnDelete.addEventListener('click', function() {
                    const id = row.getAttribute('data-id');
                    
                    // Direct deletion without alert confirmation
                    fetch("{{ url('marketing-participants') }}/" + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            row.remove();
                            updateRowNumbers();
                        } else {
                            throw new Error(data.error || 'Gagal menghapus data.');
                        }
                    })
                    .catch(err => {
                        console.error('Delete Error:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menghapus',
                            text: err.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
                });
            }

            const btnTransfer = row.querySelector('.btn-transfer');
            if (btnTransfer) {
                btnTransfer.addEventListener('click', function() {
                    const id = row.getAttribute('data-id');
                    
                    // Visual feedback: disabling button
                    btnTransfer.classList.add('disabled');
                    btnTransfer.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                    fetch("{{ route('marketing-participants.move-to-cs') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ id })
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.error || data.message || 'Terjadi kesalahan pada server.');
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            // Update CS PENERIMA cell (always the last cell)
                            const csCell = row.cells[row.cells.length - 1];
                            if (csCell) {
                                let csClass = 'bg-soft-blue text-primary border-primary';
                                if (data.assigned_cs === 'Linda') csClass = 'cs-badge-linda';
                                else if (data.assigned_cs === 'Yasmin') csClass = 'cs-badge-yasmin';
                                else if (data.assigned_cs === 'Putri') csClass = 'cs-badge-putri';
                                else if (data.assigned_cs === 'Arifa') csClass = 'cs-badge-arifa';
                                else if (data.assigned_cs === 'Puput') csClass = 'cs-badge-puput';

                                csCell.innerHTML = `
                                    <span class="badge cs-badge ${csClass} px-2 py-1">
                                        ${data.assigned_cs === 'Putri' ? 'Diah Putri' : data.assigned_cs}
                                    </span>
                                `;
                            }

                            // Replace button with success checkmark
                            const parent = btnTransfer.parentElement;
                            btnTransfer.remove();
                            const checkMark = document.createElement('i');
                            checkMark.className = 'fa-solid fa-check-circle text-success fs-5';
                            checkMark.title = 'Telah di CS';
                            parent.prepend(checkMark);

                            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                            Toast.fire({ icon: 'success', title: data.message });
                        } else {
                            throw new Error(data.error || 'Gagal memindahkan data.');
                        }
                    })
                    .catch(err => {
                        btnTransfer.classList.remove('disabled');
                        btnTransfer.innerHTML = '<i class="fa-solid fa-circle-arrow-right"></i>';
                        Swal.fire('Gagal!', err.message, 'error');
                        console.error('Error:', err);
                    });
                });
            }



            // Location Logic
            const provSelect = row.querySelector('.select-provinsi');
            const kotaSelect = row.querySelector('.select-kota');

            if (provSelect && kotaSelect) {
                provSelect.addEventListener('change', function() {
                    const provId = this.value;
                    const provNama = this.options[this.selectedIndex].text;
                    
                    if (!provId) return;

                    // Update Province in DB
                    updateDataMulti(row.getAttribute('data-id'), {
                        provinsi_id: provId,
                        provinsi_nama: provNama,
                        kota_id: null,
                        kota_nama: null
                    });

                    loadCities(provId, kotaSelect);
                });

                kotaSelect.addEventListener('change', function() {
                    const kotaId = this.value;
                    const kotaNama = this.options[this.selectedIndex].text;
                    
                    if (!kotaId) return;

                    // Update City in DB
                    updateDataMulti(row.getAttribute('data-id'), {
                        kota_id: kotaId,
                        kota_nama: kotaNama
                    });
                });
            }
        }

        function populateProvinces(row) {
            const provSelect = row.querySelector('.select-provinsi');
            const currentNama = provSelect.getAttribute('data-nama');

            fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
                .then(response => response.json())
                .then(provinces => {
                    provinces.sort((a, b) => a.name.localeCompare(b.name));
                    provSelect.innerHTML = '<option value="">-- Pilih Prov --</option>';
                    provinces.forEach(p => {
                        const isSelected = (currentNama && currentNama.toUpperCase() === p.name.toUpperCase());
                        const option = document.createElement('option');
                        option.value = p.id;
                        option.text = p.name;
                        if (isSelected) option.selected = true;
                        provSelect.appendChild(option);
                    });

                    // If already has province, load cities
                    if (provSelect.value) {
                        loadCities(provSelect.value, row.querySelector('.select-kota'));
                    }
                });
        }

        function loadCities(provId, targetSelect) {
            const currentNama = targetSelect.getAttribute('data-nama');
            targetSelect.innerHTML = '<option value="">Loading...</option>';

            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`)
                .then(response => response.json())
                .then(cities => {
                    cities.sort((a, b) => a.name.localeCompare(b.name));
                    targetSelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
                    cities.forEach(c => {
                        const isSelected = (currentNama && currentNama.toUpperCase() === c.name.toUpperCase());
                        const option = document.createElement('option');
                        option.value = c.id;
                        option.text = c.name;
                        if (isSelected) option.selected = true;
                        targetSelect.appendChild(option);
                    });
                });
        }

        function applySelectColor(select) {
            if (select.value === 'MBC') {
                select.style.backgroundColor = '#ff7675';
                select.style.color = '#ffffff';
            } else {
                select.style.backgroundColor = '#55efc4';
                select.style.color = '#000000';
            }
        }

        function updateData(element) {
            const row = element.closest('tr');
            const id = row.getAttribute('data-id');
            const field = element.getAttribute('data-field');
            const value = (element.tagName === 'SELECT') ? element.value : element.innerText.trim();

            // Visual feedback: saving
            if (element.tagName !== 'SELECT') {
                element.style.opacity = '0.5';
            }

            fetch("{{ route('marketing-participants.update-inline') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id, field, value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (element.tagName !== 'SELECT') {
                        element.style.opacity = '1';
                        const originalColor = element.style.backgroundColor;
                        element.style.backgroundColor = '#d4edda'; // Success green
                        setTimeout(() => { element.style.backgroundColor = originalColor; }, 500);
                    }
                } else {
                    throw new Error(data.error || 'Gagal menyimpan');
                }
            })
            .catch(err => {
                element.style.opacity = '1';
                element.style.backgroundColor = '#f8d7da'; // Error red
                console.error('Save Error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: err.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        }

        function updateDataMulti(id, updates) {
            fetch("{{ route('marketing-participants.update-inline') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id, updates })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) console.error('Multi Update Error:', data.error);
            })
            .catch(err => console.error('Multi Update Error:', err));
        }

        function updateRowNumbers() {
            document.querySelectorAll('#participantTable tbody tr:not(.empty-row)').forEach((row, index) => {
                row.querySelector('.row-number').innerText = index + 1;
            });
        }
    });
</script>
@endsection
