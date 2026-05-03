@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fa-solid fa-chart-line me-2"></i>
            Marketing Performance Dashboard - Helas Corporation
        </h4>
        <span class="badge bg-gradient-success fs-6 px-3 py-2 shadow-sm text-white">
            {{ now()->format('F Y') }}
        </span>
    </div>

    {{-- Filter & Action Section --}}
    <div class="card shadow-sm mb-4 border-0 rounded-4">
        <div class="card-body p-4 bg-white">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                
                {{-- Left Side: Actions --}}
                <div class="d-flex align-items-center gap-3">
                    @if(!$isAdministrator)
                    <button type="button" id="btnAddRow" class="btn btn-primary px-4 rounded-pill shadow-sm d-flex align-items-center gap-2 transition-all">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span class="fw-bold">TAMBAH BARIS</span>
                    </button>
                    @endif
                    
                    <a href="{{ route('marketing.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'status' => $status, 'marketing_user_id' => $selectedMarketingUserId]) }}" 
                       class="btn btn-danger px-4 rounded-pill shadow-sm d-flex align-items-center gap-2 transition-all">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span class="fw-bold">EXPORT PDF</span>
                    </a>
                </div>

                {{-- Right Side: Filters --}}
                <form action="{{ route('marketing') }}" method="GET" id="filterForm" class="d-flex flex-wrap align-items-end gap-3">
                    
                    @if($isAdministrator)
                    <div class="filter-item">
                        <label class="small fw-bold text-muted mb-2 px-1 text-uppercase letter-spacing-1">Pilih Tim Marketing</label>
                        <select name="marketing_user_id" class="form-select border-0 shadow-sm rounded-3 bg-light px-3" style="min-width: 180px; height: 42px;" onchange="this.form.submit()">
                            @foreach($marketingUsers as $mUser)
                                <option value="{{ $mUser->id }}" {{ $selectedMarketingUserId == $mUser->id ? 'selected' : '' }}>
                                    {{ $mUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="filter-item">
                        <label class="small fw-bold text-muted mb-2 px-1 text-uppercase letter-spacing-1">Bulan</label>
                        <select name="bulan" class="form-select border-0 shadow-sm rounded-3 bg-light px-3" style="min-width: 140px; height: 42px;" onchange="this.form.submit()">
                            <option value="all" {{ $bulan == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-item">
                        <label class="small fw-bold text-muted mb-2 px-1 text-uppercase letter-spacing-1">Tahun</label>
                        <select name="tahun" class="form-select border-0 shadow-sm rounded-3 bg-light px-3" style="min-width: 110px; height: 42px;" onchange="this.form.submit()">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-item">
                        <label class="small fw-bold text-muted mb-2 px-1 text-uppercase letter-spacing-1">Status</label>
                        <select name="status" class="form-select border-0 shadow-sm rounded-3 bg-light px-3" style="min-width: 150px; height: 42px;" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Terlaksana" {{ $status == 'Terlaksana' ? 'selected' : '' }}>Terlaksana</option>
                            <option value="Pending" {{ $status == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Ditunda" {{ $status == 'Ditunda' ? 'selected' : '' }}>Ditunda</option>
                            <option value="Dibatalkan" {{ $status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Tabel Performance --}}
    <div class="card shadow-lg rounded-4 overflow-hidden mb-5 border-0">
        <div class="card-header py-4 text-center" style="background: linear-gradient(135deg, #ffff00, #ffec00); border-bottom: 2px solid #eee;">
            <h5 class="mb-0 fw-bolder text-dark letter-spacing-2 text-uppercase">
                @if(stripos($userName, 'Nisa') !== false)
                    DASHBOARD PERFORMANCE SOSMED SPESIALIS MARKETING ({{ $userName }})
                @elseif(stripos($userName, 'Felmi') !== false)
                    DASHBOARD PERFORMANCE EVENT MARKETING ({{ $userName }})
                @else
                    DASHBOARD PERFORMANCE MARKETING ({{ $userName }})
                @endif
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center table-hover custom-table" id="performanceTable">
                    <thead>
                        <tr class="fw-bold bg-light-blue text-dark text-uppercase small letter-spacing-1">
                            <th style="width: 50px;">NO</th>
                            <th>{{ stripos($userName, 'Nisa') !== false ? 'Event Zoom' : 'Nama Event' }}</th>
                            <th>Tema</th>
                            <th class="sortable" data-column="3" style="cursor: pointer;">
                                Pemateri <i class="fa-solid fa-sort ms-1 opacity-50"></i>
                            </th>
                            <th class="sortable" data-column="4" style="width: 150px; cursor: pointer;">
                                Tanggal <i class="fa-solid fa-sort ms-1 opacity-50"></i>
                            </th>
                            <th>Lokasi</th>
                            @if(stripos($userName, 'Felmi') === false)
                            <th>Jenis Event</th>
                            @endif
                            <th style="width: 100px;">Target Peserta</th>
                            <th style="width: 100px;">Peserta Hadir</th>
                            <th style="width: 100px;">Target Closing</th>
                            <th style="width: 100px;">Real Closing</th>
                            <th style="width: 160px;">Selisih</th>
                            <th class="sortable" data-column="last" style="width: 200px; cursor: pointer;">
                                Aksi <i class="fa-solid fa-sort ms-1 opacity-50"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performances as $i => $perf)
                        <tr data-id="{{ $perf->id }}">
                            <td class="bg-light fw-bold text-muted row-number">{{ $i + 1 }}</td>
                            @if(stripos($userName, 'Felmi') !== false)
                            <td class="p-1">
                                <select class="form-select form-select-sm border-0 fw-bold event-name-select" data-field="event_name" {{ $isAdministrator ? 'disabled' : '' }}>
                                    <option value="E-Fest" {{ $perf->event_name == 'E-Fest' ? 'selected' : '' }}>E-Fest</option>
                                    <option value="Up Rev" {{ $perf->event_name == 'Up Rev' ? 'selected' : '' }}>Up Rev</option>
                                </select>
                            </td>
                            @else
                            <td class="text-start px-3 {{ !$isAdministrator ? 'editable' : '' }}" data-field="event_name" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->event_name }}</td>
                            @endif
                            <td class="text-start px-3 {{ !$isAdministrator ? 'editable' : '' }}" data-field="tema" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->tema }}</td>
                            <td class="text-start px-3 {{ !$isAdministrator ? 'editable' : '' }}" data-field="pemateri" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->pemateri }}</td>
                            <td>
                                <input type="date" class="form-control form-control-sm border-0 bg-transparent text-center date-input font-outfit" 
                                       value="{{ $perf->tanggal ? \Carbon\Carbon::parse($perf->tanggal)->format('Y-m-d') : '' }}"
                                       data-field="tanggal" {{ $isAdministrator ? 'disabled' : '' }}>
                            </td>
                            <td class="{{ !$isAdministrator ? 'editable' : '' }}" data-field="lokasi" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->lokasi }}</td>
                            @if(stripos($userName, 'Felmi') === false)
                            <td class="{{ !$isAdministrator ? 'editable' : '' }}" data-field="jenis_event" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->jenis_event }}</td>
                            @endif
                            <td class="bg-soft-yellow fw-bold">{{ $perf->target_peserta }}</td>
                            <td class="{{ !$isAdministrator ? 'editable' : '' }}" data-field="peserta_hadir" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->peserta_hadir ?: '' }}</td>
                            <td class="bg-soft-yellow fw-bold">{{ $perf->target_closing }}</td>
                            @if(stripos($userName, 'Felmi') !== false)
                                <td class="fw-bold text-primary">{{ $perf->real_closing ?: '0' }}</td>
                            @else
                                <td class="{{ !$isAdministrator ? 'editable' : '' }} fw-bold text-primary" data-field="real_closing" {{ !$isAdministrator ? 'contenteditable=true' : '' }}>{{ $perf->real_closing ?: '' }}</td>
                            @endif
                            <td class="selisih-val fw-bold {{ $perf->selisih < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $perf->selisih ?: '0' }}
                            </td>
                            <td>
                                <div class="action-container">
                                    @php
                                        $statusClass = '';
                                        if($perf->status == 'Terlaksana') $statusClass = 'status-terlaksana';
                                        elseif($perf->status == 'Pending') $statusClass = 'status-pending';
                                        elseif($perf->status == 'Ditunda') $statusClass = 'status-ditunda';
                                        elseif($perf->status == 'Dibatalkan') $statusClass = 'status-dibatalkan';
                                    @endphp
                                    <select class="form-select form-select-sm border-0 text-center fw-bold status-select status-select-precise font-outfit {{ $statusClass }}" 
                                            data-field="status" {{ $isAdministrator ? 'disabled' : '' }}>
                                        <option value="Terlaksana" {{ $perf->status == 'Terlaksana' ? 'selected' : '' }}>Terlaksana</option>
                                        <option value="Pending" {{ $perf->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Ditunda" {{ $perf->status == 'Ditunda' ? 'selected' : '' }}>Ditunda</option>
                                        <option value="Dibatalkan" {{ $perf->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                    
                                    @if(!$isAdministrator)
                                    <form action="{{ route('marketing.delete', $perf->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        <button type="button" class="btn btn-delete-precise btn-delete shadow-sm" title="Hapus Data">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row text-center">
                            <td colspan="{{ stripos($userName, 'Felmi') !== false ? 12 : 13 }}" class="py-5 text-muted">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fa-solid fa-calendar-plus fa-3x mb-3 opacity-25"></i>
                                    <span class="fs-5">Belum ada data performance di periode ini.</span>
                                    @if(!$isAdministrator)
                                    <p class="small">Klik <strong>TAMBAH BARIS</strong> untuk mulai menginput data.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
    
    :root {
        --primary-blue: #4DABF7;
        --soft-blue: #f0f7ff;
        --excel-blue: #cddff3;
        --premium-yellow: #ffff00;
        --dark-text: #2d3436;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background-color: #f0f2f5;
        color: var(--dark-text);
    }

    .letter-spacing-1 { letter-spacing: 1px; }
    .letter-spacing-2 { letter-spacing: 2px; }
    
    .font-outfit { font-family: 'Outfit', sans-serif; }
    
    .bg-light-blue { background-color: var(--excel-blue) !important; }
    .bg-soft-yellow { background-color: #fffde7 !important; }
    
    .transition-all { transition: all 0.2s ease-in-out; }
    .transition-all:hover { transform: translateY(-2px); filter: brightness(1.05); }

    .custom-table { border: 1px solid #e0e0e0; }
    .custom-table thead th {
        background-color: var(--excel-blue);
        border: 1px solid #b8c6d4;
        padding: 12px 8px;
    }

    .editable { outline: none; transition: background 0.2s; cursor: text; }
    .editable:hover { background-color: #fff9c4 !important; }
    .editable:focus { background-color: #fff !important; box-shadow: inset 0 0 0 2px var(--primary-blue); }

    .card { border-radius: 1.2rem !important; }
    
    .form-select, .form-control { border-radius: 8px; }
    .form-select:focus, .form-control:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.1); }

    .shadow-icon { filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }
    
    /* Status Badge Colors */
    .status-select { 
        color: white !important; 
        font-weight: 700 !important;
        border-radius: 20px !important;
        padding: 4px 12px !important;
        cursor: pointer;
        outline: none;
        appearance: none; /* Hide default arrow to style better */
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 10px 10px;
        padding-right: 24px !important;
    }
    .status-select option { color: #333; background: white; }
    
    .status-terlaksana { background-color: #28a745 !important; border: none; }
    .status-pending { background-color: #17a2b8 !important; border: none; }
    .status-ditunda { background-color: #fd7e14 !important; border: none; }
    .status-dibatalkan { background-color: #dc3545 !important; border: none; }

    /* Action Column Refinement */
    .action-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 4px 8px;
    }

    .btn-delete-precise {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #fff1f0;
        border: 1px solid #ffa39e;
        color: #f5222d;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .btn-delete-precise:hover {
        background-color: #f5222d;
        color: #ffffff;
        border-color: #f5222d;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(245, 34, 45, 0.2);
    }

    .status-select-precise {
        min-width: 125px;
        height: 32px;
        font-size: 0.75rem !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
</style>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const btnAddRow = document.getElementById('btnAddRow');
        const tableBody = document.querySelector('#performanceTable tbody');
        const isFelmi = {{ stripos($userName, 'Felmi') !== false ? 'true' : 'false' }};
        const isAdministrator = {{ $isAdministrator ? 'true' : 'false' }};

        if (btnAddRow) {
            btnAddRow.addEventListener('click', function() {
                btnAddRow.disabled = true;
                btnAddRow.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> MEMPROSES...';

                fetch("{{ route('marketing.store') }}", {
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
                        // Remove empty row if exists
                        const emptyRow = tableBody.querySelector('.empty-row');
                        if (emptyRow) emptyRow.remove();

                        const count = tableBody.querySelectorAll('tr').length + 1;
                        const perf = res.data;
                        
                        const newRow = document.createElement('tr');
                        newRow.setAttribute('data-id', perf.id);
                        newRow.innerHTML = `
                            <td class="bg-light fw-bold text-muted row-number">${count}</td>
                            ${isFelmi ? `
                            <td class="p-1">
                                <select class="form-select form-select-sm border-0 fw-bold event-name-select" data-field="event_name">
                                    <option value="E-Fest" ${perf.event_name == 'E-Fest' ? 'selected' : ''}>E-Fest</option>
                                    <option value="Up Rev" ${perf.event_name == 'Up Rev' ? 'selected' : ''}>Up Rev</option>
                                </select>
                            </td>
                            ` : `
                            <td class="text-start px-3 editable" data-field="event_name" contenteditable="true">${perf.event_name}</td>
                            `}
                            <td class="text-start px-3 editable" data-field="tema" contenteditable="true">${perf.tema || ''}</td>
                            <td class="text-start px-3 editable" data-field="pemateri" contenteditable="true">${perf.pemateri || ''}</td>
                            <td>
                                <input type="date" class="form-control form-control-sm border-0 bg-transparent text-center date-input font-outfit" 
                                       value="${res.formatted_date}" data-field="tanggal">
                            </td>
                            <td class="editable" data-field="lokasi" contenteditable="true">${perf.lokasi}</td>
                            ${!isFelmi ? `<td class="editable" data-field="jenis_event" contenteditable="true">${perf.jenis_event}</td>` : ''}
                            <td class="bg-soft-yellow fw-bold">${perf.target_peserta}</td>
                            <td class="editable" data-field="peserta_hadir" contenteditable="true"></td>
                            <td class="bg-soft-yellow fw-bold">${perf.target_closing}</td>
                            <td class="${!isFelmi ? 'editable' : ''} fw-bold text-primary" ${!isFelmi ? 'data-field="real_closing" contenteditable="true"' : ''}>${isFelmi ? '0' : ''}</td>
                            <td class="selisih-val fw-bold text-success">${perf.target_peserta}</td>
                            <td>
                                <div class="action-container">
                                    <select class="form-select form-select-sm border-0 text-center fw-bold status-select status-select-precise font-outfit status-terlaksana" data-field="status">
                                        <option value="Terlaksana" selected>Terlaksana</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Ditunda">Ditunda</option>
                                        <option value="Dibatalkan">Dibatalkan</option>
                                    </select>
                                    <form action="{{ url('marketing/delete') }}/${perf.id}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        <button type="button" class="btn btn-delete-precise btn-delete shadow-sm" title="Hapus Data">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        `;
                        tableBody.appendChild(newRow);
                        
                        // Re-bind listeners for new elements
                        bindEvents(newRow);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Baris baru ditambahkan di paling bawah',
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                })
                .finally(() => {
                    btnAddRow.disabled = false;
                    btnAddRow.innerHTML = '<i class="fa-solid fa-plus-circle"></i> <span class="fw-bold">TAMBAH BARIS</span>';
                });
            });
        }

        // --- DELEGATED EVENTS OR REBIND ---
        function bindEvents(row) {
            if (isAdministrator) return; // Don't bind events if admin

            // Editables
            row.querySelectorAll('.editable').forEach(cell => {
                cell.addEventListener('blur', () => updateData(cell));
                cell.addEventListener('keydown', (e) => { if(e.key==='Enter'){ e.preventDefault(); cell.blur(); } });
            });
            // Date
            const dateInput = row.querySelector('.date-input');
            if (dateInput) dateInput.addEventListener('change', (e) => updateData(e.target));
            // Event Name Select
            const eventNameSelect = row.querySelector('.event-name-select');
            if (eventNameSelect) eventNameSelect.addEventListener('change', (e) => updateData(e.target));
            // Status
            const statusSelect = row.querySelector('.status-select');
            if (statusSelect) {
                statusSelect.addEventListener('change', (e) => {
                    const select = e.target;
                    select.classList.remove('status-terlaksana', 'status-pending', 'status-ditunda', 'status-dibatalkan');
                    if(select.value === 'Terlaksana') select.classList.add('status-terlaksana');
                    else if(select.value === 'Pending') select.classList.add('status-pending');
                    else if(select.value === 'Ditunda') select.classList.add('status-ditunda');
                    else if(select.value === 'Dibatalkan') select.classList.add('status-dibatalkan');
                    updateData(select);
                });
            }
            // Delete
            const btnDelete = row.querySelector('.btn-delete');
            if (btnDelete) {
                btnDelete.addEventListener('click', function() {
                    if(confirm('Hapus baris ini?')) {
                        const form = this.closest('form');
                        form.submit();
                    }
                });
            }
        }

        // Bind initial rows
        document.querySelectorAll('#performanceTable tbody tr').forEach(row => {
            if(!row.classList.contains('empty-row')) bindEvents(row);
        });

        function updateData(element) {
            if (isAdministrator) return;

            const row = element.closest('tr');
            const id = row.getAttribute('data-id');
            const field = element.getAttribute('data-field');
            const value = (element.tagName === 'INPUT' || element.tagName === 'SELECT') ? element.value : element.innerText;

            fetch("{{ route('marketing.update-inline') }}", {
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
                    const selisihCell = row.querySelector('.selisih-val');
                    if (selisihCell) {
                        selisihCell.innerText = data.selisih;
                        selisihCell.className = 'selisih-val fw-bold ' + (data.selisih < 0 ? 'text-danger' : 'text-success');
                    }
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1000 });
                    Toast.fire({ icon: 'success', title: 'Tersimpan' });
                } else if (data.error) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error });
                }
            });
        }

        // --- SORTING LOGIC ---
        let sortOrder = {};
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                let actualIndex = column;
                
                // If column is 'last', find the actual index
                if (column === 'last') {
                    actualIndex = this.parentElement.children.length - 1;
                } else {
                    actualIndex = parseInt(column);
                }

                const rows = Array.from(tableBody.querySelectorAll('tr:not(.empty-row)'));
                
                // Toggle order
                sortOrder[column] = (sortOrder[column] === 'asc') ? 'desc' : 'asc';
                const order = sortOrder[column];

                // Update Icons
                document.querySelectorAll('.sortable i').forEach(icon => {
                    icon.className = 'fa-solid fa-sort ms-1 opacity-50';
                });
                const icon = this.querySelector('i');
                icon.className = `fa-solid fa-sort-${order === 'asc' ? 'up' : 'down'} ms-1`;
                icon.classList.remove('opacity-50');

                rows.sort((a, b) => {
                    let valA, valB;
                    const cellA = a.children[actualIndex];
                    const cellB = b.children[actualIndex];

                    if (actualIndex === 4) { // Tanggal
                        valA = cellA.querySelector('input').value || '';
                        valB = cellB.querySelector('input').value || '';
                    } else if (actualIndex === (a.children.length - 1)) { // Aksi/Status (Last)
                        valA = cellA.querySelector('select').value || '';
                        valB = cellB.querySelector('select').value || '';
                    } else { // Pemateri / Text
                        valA = cellA.innerText.trim().toLowerCase();
                        valB = cellB.innerText.trim().toLowerCase();
                    }

                    if (valA < valB) return order === 'asc' ? -1 : 1;
                    if (valA > valB) return order === 'asc' ? 1 : -1;
                    return 0;
                });

                // Clear and re-append
                rows.forEach((row, index) => {
                    tableBody.appendChild(row);
                    row.querySelector('.row-number').innerText = index + 1;
                });
            });
        });
    });
</script>
@endsection

