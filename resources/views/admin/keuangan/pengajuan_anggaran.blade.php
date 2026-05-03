@extends('layouts.masteradmin')

@section('content')
    @php
        $isLinda = stripos(Auth::user()->name, 'Linda') !== false;
        $isAdmin = strtolower(Auth::user()->role) === 'administrator';
        // $canManage is for Linda who manages/approves all requests
        $canManage = $isLinda;
        // $isAdminMonitor is for non-Linda administrators who only view
        $isAdminMonitor = $isAdmin && !$isLinda;

        $intervalLabels = [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            '3_monthly' => '3 Bulan',
            '6_monthly' => '6 Bulan',
            'yearly' => 'Tahunan',
        ];
    @endphp

    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Pengajuan Anggaran</h1>
            <div class="d-flex align-items-center">
                <form action="{{ route('admin.keuangan.pengajuan-anggaran') }}" method="GET" class="form-inline mr-3"
                    id="filterForm">
                    @if(request('embed'))
                        <input type="hidden" name="embed" value="{{ request('embed') }}">
                    @endif
                    <select name="month" class="form-control form-control-sm mr-2" style="width: 130px;"
                        onchange="this.form.submit()">
                        <option value="all" {{ $month == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="form-control form-control-sm mr-2" style="width: 120px;"
                        onchange="this.form.submit()">
                        <option value="all" {{ $year == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>

                <a href="{{ route('admin.keuangan.pengajuan-anggaran.export-pdf', ['month' => $month, 'year' => $year]) }}"
                    class="btn btn-danger btn-sm shadow-sm mr-2" target="_blank">
                    <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> Cetak PDF
                </a>

                @if(!$isAdminMonitor)
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnTambahBaris">
                        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Pengajuan Baru
                    </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Pengajuan Anggaran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tablePengajuan" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr class="header-blue">


                                <th width="3%" class="text-center">No</th>
                                <th width="12%" class="text-center align-middle">
                                    <div class="mb-1">{{ $isAdmin ? 'Tanggal' : 'Tanggal Kebutuhan' }}</div>
                                    <select name="sort_date_client" class="form-control form-control-sm m-auto table-sort"
                                        style="width: 100px; font-size: 0.75rem; height: 28px;" data-sort="date"
                                        onchange="applyTableFilters()">
                                        <option value="desc" {{ $sortBy == 'tanggal_pengajuan' && $sortOrder == 'desc' ? 'selected' : '' }}>Terbaru</option>
                                        <option value="asc" {{ $sortBy == 'tanggal_pengajuan' && $sortOrder == 'asc' ? 'selected' : '' }}>Terlama</option>
                                    </select>
                                </th>
                                <th width="15%" class="align-middle">Nama Pengajuan</th>
                                <th width="10%" class="text-center align-middle">
                                    <div class="mb-1">Biaya Pengajuan</div>
                                    <select name="sort_cost_client" class="form-control form-control-sm m-auto table-sort"
                                        style="width: 90px; font-size: 0.75rem; height: 28px;" data-sort="cost"
                                        onchange="applyTableFilters()">
                                        <option value="">Urutan</option>
                                        <option value="asc" {{ $sortBy == 'jumlah_biaya' && $sortOrder == 'asc' ? 'selected' : '' }}>Paling Murah</option>
                                        <option value="desc" {{ $sortBy == 'jumlah_biaya' && $sortOrder == 'desc' ? 'selected' : '' }}>Paling Mahal</option>
                                    </select>
                                </th>
                                @if($canManage || $isAdminMonitor)
                                    <th width="10%" class="text-center align-middle">
                                        <div class="mb-1">Pemohon</div>
                                        <select name="applicant_client" class="form-control form-control-sm m-auto table-filter"
                                            style="width: 90px; font-size: 0.75rem; height: 28px;" data-filter="applicant"
                                            onchange="applyTableFilters()">
                                            <option value="">Semua</option>
                                            @foreach($applicants as $appName)
                                                <option value="{{ strtolower($appName) }}" {{ strtolower($selectedApplicant) == strtolower($appName) ? 'selected' : '' }}>
                                                    {{ $appName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </th>
                                @endif
                                <th width="10%" class="align-middle">Biaya Disetujui</th>
                                <th width="10%" class="align-middle">Biaya Sisa</th>
                                <th width="12%" class="text-center align-middle">
                                    <div class="mb-1">Status</div>
                                    <select name="status_client" class="form-control form-control-sm m-auto table-filter"
                                        style="width: 90px; font-size: 0.75rem; height: 28px;" data-filter="status"
                                        onchange="applyTableFilters()">
                                        <option value="">Semua</option>
                                        <option value="pending" {{ $selectedStatus == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="approved" {{ $selectedStatus == 'approved' ? 'selected' : '' }}>
                                            Disetujui</option>
                                        <option value="rejected" {{ $selectedStatus == 'rejected' ? 'selected' : '' }}>Ditolak
                                        </option>
                                    </select>
                                </th>
                                <th width="10%">Keterangan</th>
                                <th width="10%" class="text-center">Bukti Transfer</th>
                                <th width="3%">Aksi</th>
                            </tr>
                            <tr class="bg-light font-weight-bold summary-row" style="border-bottom: 2px solid #e3e6f0;">
                                <td colspan="3" class="text-right py-3 text-dark text-uppercase"
                                    style="font-size: 0.8rem; letter-spacing: 1px;">Total Keseluruhan</td>
                                <td class="text-right py-3 text-primary" style="font-size: 1rem;">
                                    <span id="headerTotalAwal">Rp {{ number_format($totalAwal, 0, ',', '.') }}</span>
                                </td>
                                @if($canManage || $isAdminMonitor)
                                    <td class="bg-light"></td>
                                @endif
                                <td class="text-right py-3 text-success" style="font-size: 1rem;">
                                    <span id="headerTotalSetuju">Rp {{ number_format($totalSetuju, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-right py-3 text-danger" style="font-size: 1rem;">
                                    <span id="headerTotalSisa">Rp {{ number_format($totalSisa, 0, ',', '.') }}</span>
                                </td>
                                <td colspan="4" class="bg-light"></td>
                            </tr>
                        </thead>
                        <tbody id="requestsBody">
                            @forelse($requests as $i => $req)
                                @php
                                    $isOwner = Auth::id() == $req->user_id;
                                    $isPending = $req->status === 'pending';
                                    $isRejected = $req->status === 'rejected';
                                    // Administrator Monitor cannot edit even if they happen to own it
                                    // Allow owner to edit if pending or rejected
                                    $canEdit = $isOwner && ($isPending || $isRejected) && !$isAdminMonitor;
                                    $rawDate = \Carbon\Carbon::parse($req->tanggal_pengajuan)->format('Y-m-d H:i:s');
                                @endphp
                                <tr id="row-{{ $req->id }}" class="request-row" data-id="{{ $req->id }}"
                                    data-date="{{ $rawDate }}" data-applicant="{{ strtolower($req->diajukan_oleh) }}"
                                    data-cost="{{ $req->jumlah_biaya }}" data-status="{{ $req->status }}">
                                    <td class="text-center align-middle index-column">{{ $requests->firstItem() + $i }}</td>
                                    <td class="text-center align-middle">
                                        @if($canEdit)
                                            <input type="date"
                                                class="form-control form-control-sm border-0 bg-transparent text-center p-0 fw-bold editable-date"
                                                data-id="{{ $req->id }}"
                                                value="{{ \Carbon\Carbon::parse($req->tanggal_pengajuan)->format('Y-m-d') }}"
                                                style="min-width: 120px; font-size: 0.85rem;">
                                        @else
                                            <div class="font-weight-bold">
                                                {{ \Carbon\Carbon::parse($req->tanggal_pengajuan)->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle {{ $canEdit ? 'editable-cell-container' : '' }}">
                                        <div class="d-flex align-items-center">
                                            @if(!$req->overdue_items->isEmpty())
                                                <button class="btn btn-sm btn-link p-0 mr-2 toggle-overdue" data-id="{{ $req->id }}" title="Lihat Tunggakan">
                                                    <i class="fas fa-plus-circle text-warning animate-pulse" style="font-size: 1.1rem;"></i>
                                                </button>
                                            @endif
                                            <div class="font-weight-bold text-dark {{ $canEdit ? 'editable-field' : '' }}"
                                                data-field="nama_pengajuan" contenteditable="{{ $canEdit ? 'true' : 'false' }}">
                                                {{ $req->nama_pengajuan }}
                                            </div>
                                        </div>
                                        <div class="small text-muted font-italic {{ $canEdit ? 'editable-field' : '' }} {{ !$req->keterangan && !$canEdit ? 'd-none' : '' }}"
                                            data-field="keterangan" contenteditable="{{ $canEdit ? 'true' : 'false' }}"
                                            placeholder="Tambah keterangan...">{{ $req->keterangan }}</div>
                                        <div class="mt-2">
                                            @if($canEdit)
                                                <div class="dropdown">
                                                    <button
                                                        class="btn btn-outline-info btn-xs dropdown-toggle shadow-sm w-100 text-left"
                                                        type="button" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false" style="font-size: 0.7rem;">
                                                        <i class="fas fa-redo-alt mr-1"></i>
                                                        {{ $req->is_recurring ? ($intervalLabels[$req->recurring_interval] ?? 'Bulanan') : 'Sekali Saja' }}
                                                    </button>
                                                    <div class="dropdown-menu shadow animated--fade-in" style="font-size: 0.8rem;">
                                                        <a class="dropdown-item recurring-option {{ !$req->is_recurring ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}" data-interval=""
                                                            data-label="Sekali Saja">Sekali Saja</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item recurring-option {{ $req->recurring_interval == 'daily' ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}" data-interval="daily"
                                                            data-label="Harian">Harian</a>
                                                        <a class="dropdown-item recurring-option {{ $req->recurring_interval == 'weekly' ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}"
                                                            data-interval="weekly" data-label="Mingguan">Mingguan</a>
                                                        <a class="dropdown-item recurring-option {{ ($req->recurring_interval == 'monthly' || ($req->is_recurring && !$req->recurring_interval)) ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}"
                                                            data-interval="monthly" data-label="Bulanan">Bulanan</a>
                                                        <a class="dropdown-item recurring-option {{ $req->recurring_interval == '3_monthly' ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}"
                                                            data-interval="3_monthly" data-label="3 Bulan">3 Bulan</a>
                                                        <a class="dropdown-item recurring-option {{ $req->recurring_interval == '6_monthly' ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}"
                                                            data-interval="6_monthly" data-label="6 Bulan">6 Bulan</a>
                                                        <a class="dropdown-item recurring-option {{ $req->recurring_interval == 'yearly' ? 'active' : '' }}"
                                                            href="javascript:void(0)" data-id="{{ $req->id }}"
                                                            data-interval="yearly" data-label="Tahunan">Tahunan</a>

                                                        <div class="p-2 border-top bg-light">
                                                            <label class="small font-weight-bold mb-1 d-block">Tanggal
                                                                Berakhir:</label>
                                                            <input type="date"
                                                                class="form-control form-control-sm recurring-end-date-picker"
                                                                value="{{ $req->recurring_end_date ? $req->recurring_end_date->format('Y-m-d') : '' }}"
                                                                data-id="{{ $req->id }}" style="font-size: 0.75rem;">
                                                        </div>
                                                    </div>
                                                    <input type="hidden" class="recurring-interval-input"
                                                        value="{{ $req->recurring_interval }}" data-id="{{ $req->id }}">
                                                    <input type="hidden" class="is-recurring-input"
                                                        value="{{ $req->is_recurring ? 1 : 0 }}" data-id="{{ $req->id }}">
                                                    <input type="hidden" class="recurring-end-date-input"
                                                        value="{{ $req->recurring_end_date ? $req->recurring_end_date->format('Y-m-d') : '' }}"
                                                        data-id="{{ $req->id }}">
                                                </div>
                                            @elseif($req->is_recurring)
                                                <span class="badge badge-light text-primary border px-2 py-1"
                                                    style="font-size: 0.65rem;">
                                                    <i class="fas fa-redo-alt mr-1"></i>
                                                    Rutin {{ $intervalLabels[$req->recurring_interval] ?? 'Tiap Bulan' }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td
                                        class="text-right align-middle font-weight-bold {{ $canEdit ? 'editable-cell-container' : '' }}">
                                        <div class="d-inline-block">
                                            <div class="{{ $canEdit ? 'editable-field' : '' }}" data-field="jumlah_biaya"
                                                contenteditable="{{ $canEdit ? 'true' : 'false' }}"
                                                data-raw-value="{{ $req->jumlah_biaya }}">
                                                {{ number_format($req->jumlah_biaya, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </td>
                                    @if($canManage || $isAdminMonitor)
                                        <td class="align-middle text-center small font-weight-bold">{{ $req->diajukan_oleh }}</td>
                                    @endif
                                    <td class="align-middle text-right font-weight-bold text-success">
                                        @if($req->status === 'approved')
                                            Rp {{ number_format($req->biaya_disetujui, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-danger">
                                        @if($req->status === 'approved')
                                            Rp {{ number_format($req->jumlah_biaya - $req->biaya_disetujui, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($req->status === 'pending')
                                            @if($canManage)
                                                <div class="d-flex flex-column gap-2 px-2">
                                                    <button type="button" class="btn btn-success btn-sm btn-action mb-1 shadow-sm w-100"
                                                        onclick="openApprovalModal({{ $req->id }}, 'approved', '{{ $req->nama_pengajuan }}', {{ $req->jumlah_biaya }})">
                                                        <i class="fas fa-check mr-1"></i> Setujui
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btn-action shadow-sm w-100"
                                                        onclick="openApprovalModal({{ $req->id }}, 'rejected', '{{ $req->nama_pengajuan }}', {{ $req->jumlah_biaya }})">
                                                        <i class="fas fa-times mr-1"></i> Tolak
                                                    </button>
                                                </div>
                                            @else
                                                <span class="badge badge-warning py-2 px-3 badge-pill shadow-sm">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @endif
                                        @elseif($req->status === 'approved')
                                            <span class="badge badge-primary py-2 px-3 badge-pill shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> Disetujui
                                            </span>
                                        @elseif($req->status === 'rejected')
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge badge-danger py-2 px-3 badge-pill shadow-sm mb-2">
                                                    <i class="fas fa-times-circle mr-1"></i> Ditolak
                                                </span>
                                                @if($isOwner && !$isAdminMonitor)
                                                    <button type="button" class="btn btn-outline-info btn-xs px-2 shadow-sm"
                                                        onclick="resubmitRow({{ $req->id }})">
                                                        <i class="fas fa-redo mr-1"></i> Ajukan Kembali
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($req->catatan_admin)
                                            <div
                                                class="p-2 bg-light rounded small border-left-{{ $req->status === 'approved' ? 'primary' : 'danger' }}">
                                                {{ $req->catatan_admin }}
                                            </div>
                                        @else
                                            <span class="text-muted small font-italic">- Belum ada catatan -</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($req->bukti_transfer)
                                            <div class="position-relative d-inline-block">
                                                <img src="{{ asset($req->bukti_transfer) }}" alt="Bukti Transfer"
                                                    class="img-thumbnail shadow-sm preview-image"
                                                    style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                                    onclick="previewImage('{{ asset($req->bukti_transfer) }}', 'Bukti Transfer - {{ $req->nama_pengajuan }}')"
                                                    title="Klik untuk memperbesar">
                                                <div class="preview-overlay"
                                                    onclick="previewImage('{{ asset($req->bukti_transfer) }}', 'Bukti Transfer - {{ $req->nama_pengajuan }}')">
                                                    <i class="fas fa-search-plus text-white"></i>
                                                </div>
                                            </div>
                                            <div class="mt-1">
                                                @if(!$isAdminMonitor)
                                                    <a href="javascript:void(0)" class="small text-primary font-weight-bold"
                                                        onclick="openUploadModal({{ $req->id }}, '{{ $req->nama_pengajuan }}')">
                                                        <i class="fas fa-sync-alt mr-1"></i>Ganti
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex flex-column align-items-center">
                                                @if(!$isAdminMonitor)
                                                    <button type="button" class="btn btn-outline-primary btn-xs px-2"
                                                        onclick="openUploadModal({{ $req->id }}, '{{ $req->nama_pengajuan }}')">
                                                        <i class="fas fa-upload mr-1"></i> Upload
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if(($isOwner && !$isAdminMonitor) || $canManage)
                                            <button class="btn btn-danger btn-xs btn-delete-row" onclick="deleteRow({{ $req->id }})"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @if(!$req->overdue_items->isEmpty())
                                    @foreach($req->overdue_items as $overdue)
                                        @php
                                            $curMonth = (int)($month ?? date('m'));
                                            $curYear = (int)($year ?? date('Y'));
                                            $overdueDate = \Carbon\Carbon::parse($overdue->tanggal_pengajuan);
                                            $diffInMonths = (($curYear - $overdueDate->year) * 12) + ($curMonth - $overdueDate->month);
                                            $isCritical = $diffInMonths >= 2;
                                            
                                            $badgeStyle = $isCritical 
                                                ? 'color: #fff; background-color: #e74a3b; border: 1px solid #d43f3a;' 
                                                : 'color: #fff; background-color: #fd7e14; border: 1px solid #e67e22;';
                                            $borderStyle = $isCritical 
                                                ? 'border-left: 4px solid #e74a3b;' 
                                                : 'border-left: 4px solid #fd7e14;';
                                            $iconColor = $isCritical ? 'text-danger' : 'text-orange';

                                        @endphp
                                        <tr class="overdue-row overdue-for-{{ $req->id }} bg-light" style="display: none; {{ $borderStyle }}">

                                            <td class="text-center align-middle"><i class="fas fa-level-up-alt fa-rotate-90 {{ $iconColor }}"></i></td>
                                            <td class="text-center align-middle small text-muted font-weight-bold">
                                                {{ \Carbon\Carbon::parse($overdue->tanggal_pengajuan)->format('d/m/Y') }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-secondary small">{{ $overdue->nama_pengajuan }}</div>
                                                <div class="badge {{ $isCritical ? 'badge-danger' : '' }}" style="font-size: 0.6rem; {{ !$isCritical ? 'background-color: #fd7e14; color: white;' : '' }}">
                                                    TUNGGAKAN {{ \Carbon\Carbon::parse($overdue->tanggal_pengajuan)->translatedFormat('F Y') }}
                                                </div>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-secondary" style="font-size: 0.85rem;">
                                                Rp {{ number_format($overdue->jumlah_biaya, 0, ',', '.') }}
                                            </td>
                                            @if($canManage || $isAdminMonitor)
                                                <td class="text-center align-middle small text-muted">{{ $overdue->diajukan_oleh }}</td>
                                            @endif
                                            <td class="text-right align-middle text-muted" style="font-size: 0.85rem;">
                                                Rp {{ number_format($overdue->biaya_disetujui, 0, ',', '.') }}
                                            </td>
                                            <td class="text-right align-middle text-muted" style="font-size: 0.85rem;">
                                                Rp {{ number_format($overdue->jumlah_biaya - $overdue->biaya_disetujui, 0, ',', '.') }}
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge px-2 py-1 shadow-sm" style="font-size: 0.65rem; {{ $badgeStyle }}">
                                                    <i class="fas fa-clock mr-1"></i> Belum Terbayar
                                                </span>
                                            </td>
                                            <td colspan="3" class="align-middle small text-muted font-italic">
                                                {{ $overdue->keterangan ?: '- Belum ada catatan -' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                            @empty
                                <tr id="emptyRow">
                                    <td colspan="{{ ($canManage || $isAdminMonitor) ? 11 : 10 }}" class="text-center py-5">
                                        <div class="p-3">
                                            <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                            <p class="text-muted">Belum ada data pengajuan anggaran.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
                <div class="mt-4 px-2">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Approval/Status -->
    <div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div id="modalHeaderApproval" class="modal-header text-white">
                    <h5 class="modal-title" id="modalTitleApproval">Tindak Lanjut Pengajuan</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="formApproval" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="status" id="inputStatusApproval">
                    <div class="modal-body p-4">
                        <p class="mb-3">Pengajuan: <strong id="namaPengajuanApproval"></strong></p>
                        <p class="mb-3">Biaya Diajukan: <strong id="biayaDiajukanApproval" class="text-primary"></strong>
                        </p>

                        <div id="containerBiayaDisetujui" class="form-group d-none">
                            <label class="font-weight-bold">Biaya Disetujui (Rp)</label>
                            <input type="text" name="biaya_disetujui" id="inputBiayaDisetujui" class="form-control"
                                placeholder="0">
                            <small class="text-muted">Jika dikosongkan, akan disetujui sesuai nominal pengajuan.</small>
                        </div>

                        <div class="form-group">
                            <label id="labelCatatanApproval" class="font-weight-bold">Keterangan / Alasan</label>
                            <textarea name="catatan_admin" id="inputCatatanApproval" class="form-control" rows="3"
                                placeholder="Tambah catatan..."></textarea>
                        </div>

                        <div id="containerBuktiTransfer" class="form-group d-none">
                            <label class="font-weight-bold">Upload Bukti Transfer</label>
                            <div class="custom-file">
                                <input type="file" name="bukti_transfer" class="custom-file-input" id="inputBuktiTransfer"
                                    accept="image/*">
                                <label class="custom-file-label" for="inputBuktiTransfer">Pilih file gambar...</label>
                            </div>
                            <small class="text-muted">Maksimal 2MB (jpeg, png, jpg).</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button class="btn btn-secondary border-0" type="button" data-dismiss="modal">Batal</button>
                        <button class="btn" type="submit" id="btnSubmitApproval">Konfirmasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Upload Bukti Khusus -->
    <div class="modal fade" id="modalUploadBukti" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg text-left">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-upload mr-2"></i>Upload Bukti Transfer</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="formUploadBukti" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="mb-3 text-dark">Mengunggah bukti untuk: <strong id="namaPengajuanUpload"
                                class="text-primary"></strong></p>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Pilih File Bukti (Gambar)</label>
                            <div class="custom-file">
                                <input type="file" name="bukti_transfer" class="custom-file-input" id="inputUploadBukti"
                                    accept="image/*" required>
                                <label class="custom-file-label" for="inputUploadBukti">Pilih file gambar...</label>
                            </div>
                            <div class="mt-2 small text-muted">
                                <i class="fas fa-info-circle mr-1"></i> Format: JPG, PNG, JPEG. Maks: 2MB.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button class="btn btn-secondary btn-sm px-4" type="button" data-dismiss="modal">Batal</button>
                        <button class="btn btn-primary btn-sm px-4 shadow-sm" type="submit">
                            <i class="fas fa-cloud-upload-alt mr-1"></i> Simpan Bukti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview Gambar -->
    <div class="modal fade" id="modalPreviewImage" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg bg-transparent">
                <div class="modal-header border-0 bg-dark text-white rounded-top" style="opacity: 0.9;">
                    <h5 class="modal-title" id="previewTitle">Bukti Transfer</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-0 bg-dark rounded-bottom" style="opacity: 0.9;">
                    <img id="imageFullPreview" src="" class="img-fluid w-100 rounded-bottom" alt="Preview">
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function reorderTable() {
                $('#requestsBody tr.request-row').each(function (index) {
                    $(this).find('.index-column').text(index + 1);
                });
            }

            // Tambah Baris
            $('#btnTambahBaris').click(function () {
                $('#emptyRow').hide();

                const newRow = `
                                                                        <tr id="newRowTemp" class="table-primary border-primary shadow-sm">
                                                                            <td class="text-center align-middle index-column">#</td>
                                                                            <td class="align-middle text-center">
                                                                                <input type="date" id="newTanggal" class="form-control form-control-sm text-center font-weight-bold" value="{{ date('Y-m-d') }}">
                                                                            </td>
                                                                            <td class="align-middle px-3">
                                                                                <div class="dropdown mb-2">
                                                                                    <button class="btn btn-outline-info btn-xs dropdown-toggle shadow-sm w-100" type="button" id="dropdownTemplatesInline" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 0.7rem;">
                                                                                        <i class="fas fa-copy fa-sm mr-1"></i> Pilih Pengajuan Berulang
                                                                                    </button>
                                                                                    <div class="dropdown-menu shadow animated--fade-in" aria-labelledby="dropdownTemplatesInline" style="max-height: 250px; overflow-y: auto;">
                                                                                        <h6 class="dropdown-header">Pengeluaran Rutin:</h6>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Biaya Kuota" style="font-size: 0.8rem;">Biaya Kuota</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Biaya Listrik" style="font-size: 0.8rem;">Biaya Listrik</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Biaya Air" style="font-size: 0.8rem;">Biaya Air</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="BPJS" style="font-size: 0.8rem;">BPJS</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Internet & Wifi" style="font-size: 0.8rem;">Internet & Wifi</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Maintenance Web" style="font-size: 0.8rem;">Maintenance Web</a>
                                                                                        <div class="dropdown-divider"></div>
                                                                                        <h6 class="dropdown-header">Pengeluaran Coach:</h6>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Cicilan mobil Coach" data-cost="5850000" data-note="Maksimal tgl 10" style="font-size: 0.8rem;">Cicilan mobil Coach</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Cicilan mobil teh Lia" data-cost="3000000" data-note="Maksimal tgl 10" style="font-size: 0.8rem;">Cicilan mobil teh Lia</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Uang bulanan Fathin" data-cost="2500000" data-note="Tanggal 1" style="font-size: 0.8rem;">Uang bulanan Fathin</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Gaji ART" data-cost="2500000" data-note="Tanggal 15" style="font-size: 0.8rem;">Gaji ART</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Uang bulanan teh Lia" data-cost="3000000" data-note="Tanggal 10" style="font-size: 0.8rem;">Uang bulanan teh Lia</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Cicilan 2 kartu kredit" data-cost="1392000" data-note="Maksimal tgl 5" style="font-size: 0.8rem;">Cicilan 2 kartu kredit</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Paket paket ustad" data-cost="" data-note="Unlimited" style="font-size: 0.8rem;">Paket paket ustad</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Hutang Tajirw" data-cost="3000000" data-note="Sisa hutang 11.708.000" style="font-size: 0.8rem;">Hutang Tajirw</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Hutang pak Yusron" data-cost="5000000" data-note="Sisa hutang 40 jutaan" style="font-size: 0.8rem;">Hutang pak Yusron</a>
                                                                                        <a class="dropdown-item template-item py-1" href="javascript:void(0)" data-name="Biaya program Dela" data-cost="2500000" data-note="" style="font-size: 0.8rem;">Biaya program Dela</a>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="font-weight-bold text-dark editable-field p-2 bg-white rounded border mb-1" 
                                                                                    id="newNama" contenteditable="true" placeholder="Nama Pengajuan..."></div>
                                                                                <div class="small text-muted font-italic editable-field p-2 bg-white rounded border" 
                                                                                    id="newKeterangan" contenteditable="true" placeholder="Keterangan (Opsional)..."></div>
                                                                            </td>
                                                                            <td class="text-right align-middle font-weight-bold">
                                                                                <div class="editable-field p-2 bg-white rounded border text-center" id="newBiaya" contenteditable="true" placeholder="0"></div>
                                                                            </td>
                                                                            <td colspan="{{ $canManage ? 7 : 6 }}" class="text-center align-middle bg-light">
                                                                                <div class="p-3">
                                                                                    <div class="dropdown mt-2">
                                                                                        <button class="btn btn-outline-info btn-sm dropdown-toggle shadow-sm w-100" type="button" id="btnNewRecurring" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 0.8rem;">
                                                                                            <i class="fas fa-redo-alt mr-1"></i> Pengulangan: Sekali Saja
                                                                                        </button>
                                                                                        <div class="dropdown-menu shadow animated--fade-in" style="font-size: 0.8rem;">
                                                                                            <a class="dropdown-item new-recurring-option active" href="javascript:void(0)" data-interval="" data-label="Sekali Saja">Sekali Saja</a>
                                                                                            <div class="dropdown-divider"></div>
                                                                                            <a class="dropdown-item new-recurring-option" href="javascript:void(0)" data-interval="daily" data-label="Harian">Harian</a>
                                                                                            <a class="dropdown-item new-recurring-option" href="javascript:void(0)" data-interval="weekly" data-label="Mingguan">Mingguan</a>
                                                                                            <a class="dropdown-item new-recurring-option" href="javascript:void(0)" data-interval="monthly" data-label="Bulanan">Bulanan</a>
                                                                                            <a class="dropdown-item new-recurring-option" href="javascript:void(0)" data-interval="3_monthly" data-label="3 Bulan">3 Bulan</a>
                                                                                            <a class="dropdown-item new-recurring-option" href="javascript:void(0)" data-interval="6_monthly" data-label="6 Bulan">6 Bulan</a>
                                                                                            <a class="dropdown-item new-recurring-option" href="javascript:void(0)" data-interval="yearly" data-label="Tahunan">Tahunan</a>

                                                                                            <div class="p-2 border-top bg-light">
                                                                                                <label class="small font-weight-bold mb-1 d-block">Tanggal Berakhir:</label>
                                                                                                <input type="date" id="newRecurringEndDate" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                                                                            </div>
                                                                                        </div>
                                                                                        <input type="hidden" id="newRecurringInterval" value="">
                                                                                        <input type="hidden" id="newIsRecurring" value="0">
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr id="newRowActions" class="table-primary border-primary">
                                                                            <td colspan="{{ $canManage ? 11 : 10 }}" class="text-center py-3 bg-light border-top-0">
                                                                                <div class="d-flex align-items-center justify-content-center">
                                                                                    <button type="button" class="btn btn-primary px-5 shadow-sm mr-3" id="btnSimpanBaris">
                                                                                        <i class="fas fa-save mr-2"></i> Simpan Pengajuan
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-secondary px-4 shadow-sm" id="btnBatalBaris">
                                                                                        <i class="fas fa-times mr-2"></i> Batal
                                                                                </button>
                                                                                                                                           </div>
                                                                            </td>
                                                                        </tr>
                                                                    `;

                if ($('#newRowTemp').length === 0) {
                    $('#requestsBody').prepend(newRow);
                    $('#newNama').focus();
                    reorderTable();
                }
            });

            // Handle template selection
            $(document).on('click', '.template-item', function () {
                const name = $(this).data('name');
                const cost = $(this).data('cost');
                const note = $(this).data('note');

                if ($('#newRowTemp').length === 0) {
                    $('#btnTambahBaris').click();
                }

                $('#newNama').text(name);

                if (cost) {
                    $('#newBiaya').text(formatRupiah(cost));
                } else {
                    $('#newBiaya').text('');
                }

                if (note) {
                    $('#newKeterangan').text(note);
                } else {
                    $('#newKeterangan').text('');
                }

                // Move focus to cost field if empty, otherwise to name
                setTimeout(() => {
                    if (!cost) $('#newBiaya').focus();
                    else $('#newNama').focus();
                }, 100);
            });

            // Simpan Baris Baru
            $(document).on('click', '#btnSimpanBaris', function () {
                const nama = $('#newNama').text().trim();
                const biaya = $('#newBiaya').text().replace(/[^0-9]/g, '');
                const keterangan = $('#newKeterangan').text().trim();
                const tanggal = $('#newTanggal').val();
                const isRecurring = $('#newIsRecurring').val();
                const recurringInterval = $('#newRecurringInterval').val();
                const recurringEndDate = $('#newRecurringEndDate').val();

                if (!nama || !biaya || !tanggal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Nama, Biaya, dan Tanggal wajib diisi!',
                        confirmButtonColor: '#4e73df',
                    });
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: window.location.pathname.split('?')[0],
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        nama_pengajuan: nama,
                        jumlah_biaya: biaya,
                        keterangan: keterangan,
                        tanggal_pengajuan: tanggal,
                        is_recurring: isRecurring,
                        recurring_interval: recurringInterval,
                        recurring_end_date: recurringEndDate
                    },
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i>');
                        let msg = 'Terjadi kesalahan saat menghubungi server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: msg + ' (' + xhr.status + ': ' + xhr.statusText + ').',
                            confirmButtonColor: '#4e73df',
                        });
                    }
                });
            });

            // Batal Baris
            $(document).on('click', '#btnBatalBaris', function () {
                $('#newRowTemp').remove();
                $('#newRowActions').remove();
                if ($('#requestsBody tr.request-row').length === 0) {
                    $('#emptyRow').show();
                }
                reorderTable();
            });

            // Handle contenteditable field focus out
            $(document).on('focusout', '.request-row .editable-field', function () {
                const field = $(this);
                const row = field.closest('tr');
                const id = row.data('id');
                const fieldName = field.data('field');
                let value = field.text().trim();

                if (fieldName === 'jumlah_biaya') {
                    value = value.replace(/[^0-9]/g, '');
                    if (value === "") value = 0;
                    // Reformat visually
                    field.text(formatRupiah(value));
                }

                saveRow(row);
            });

            // Handle date change
            $(document).on('change', '.editable-date', function () {
                saveRow($(this).closest('tr'));
            });

            function saveRow(row) {
                const id = row.data('id');
                const nama = row.find('[data-field="nama_pengajuan"]').text().trim();
                const biaya = row.find('[data-field="jumlah_biaya"]').text().replace(/[^0-9]/g, '');
                const keterangan = row.find('[data-field="keterangan"]').text().trim();
                const tanggal = row.find('.editable-date').val();
                const isRecurring = row.find('.is-recurring-input').val();
                const recurringInterval = row.find('.recurring-interval-input').val();
                const recurringEndDate = row.find('.recurring-end-date-input').val();

                $.ajax({
                    url: window.location.pathname.split('?')[0] + '/' + id + '/update',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        nama_pengajuan: nama,
                        jumlah_biaya: biaya,
                        keterangan: keterangan,
                        tanggal_pengajuan: tanggal,
                        is_recurring: isRecurring,
                        recurring_interval: recurringInterval,
                        recurring_end_date: recurringEndDate
                    },
                    success: function () {
                        row.addClass('table-success-brief');
                        setTimeout(() => row.removeClass('table-success-brief'), 500);
                        // If it was rejected, we might want to refresh the status UI
                        if (row.data('status') === 'rejected') {
                            location.reload();
                        }
                    }
                    ,
                    error: function (xhr) {
                        console.error('Save failed:', xhr.status, xhr.statusText);
                        if (xhr.status !== 200) {
                            row.addClass('table-danger');
                            setTimeout(() => row.removeClass('table-danger'), 1000);
                        }
                    }
                });
            }

            window.resubmitRow = function (id) {
                Swal.fire({
                    title: 'Kirim Kembali?',
                    text: 'Pengajuan ini akan dikirim ulang untuk ditinjau.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a673',
                    cancelButtonColor: '#858796',
                    confirmButtonText: 'Ya, Kirim!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const row = $(`#row-${id}`);
                        const nama = row.find('[data-field="nama_pengajuan"]').text().trim();
                        const biaya = row.find('[data-field="jumlah_biaya"]').text().replace(/[^0-9]/g, '');
                        const keterangan = row.find('[data-field="keterangan"]').text().trim();
                        const tanggal = row.find('.editable-date').length ? row.find('.editable-date').val() : row.data('date');

                        $.ajax({
                            url: window.location.pathname.split('?')[0] + '/' + id + '/update',
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                nama_pengajuan: nama,
                                jumlah_biaya: biaya,
                                keterangan: keterangan,
                                tanggal_pengajuan: tanggal,
                                status: 'pending',
                                is_recurring: row.find('.is-recurring-input').val(),
                                recurring_interval: row.find('.recurring-interval-input').val(),
                                recurring_end_date: row.find('.recurring-end-date-input').val()
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'Pengajuan telah dikirim ulang.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload();
                                    });
                                }
                            },
                            error: function (xhr) {
                                let msg = 'Gagal mengirim ulang';
                                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                                Swal.fire('Error!', msg + ' (' + xhr.status + ': ' + xhr.statusText + ').', 'error');
                            }
                        });
                    }
                });
            }

            // Prevent Enter in single line fields
            $(document).on('keydown', '[data-field="nama_pengajuan"], [data-field="jumlah_biaya"], #newNama, #newBiaya', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).blur();
                }
            });

            reorderTable();

            // Instant Client-side Filtering and Sorting
            window.applyTableFilters = function () {
                const statusFilter = $('select[name="status_client"]').val();
                const applicantFilter = $('select[name="applicant_client"]').val();
                const dateSort = $('select[name="sort_date_client"]').val();
                const costSort = $('select[name="sort_cost_client"]').val();

                const rows = $('#requestsBody tr.request-row').get();

                // 1. Filtering
                rows.forEach(row => {
                    const rowStatus = $(row).data('status');
                    const rowApplicant = $(row).data('applicant');

                    let show = true;
                    if (statusFilter && rowStatus !== statusFilter) show = false;
                    if (applicantFilter && rowApplicant !== applicantFilter) show = false;

                    $(row).toggle(show);
                });

                // 2. Sorting (Cost takes precedence if selected, otherwise Date)
                const visibleRows = rows.filter(row => $(row).is(':visible'));

                visibleRows.sort(function (a, b) {
                    if (costSort) {
                        const costA = parseFloat($(a).data('cost'));
                        const costB = parseFloat($(b).data('cost'));
                        return costSort === 'asc' ? costA - costB : costB - costA;
                    } else {
                        const dateA = new Date($(a).data('date'));
                        const dateB = new Date($(b).data('date'));
                        return dateSort === 'asc' ? dateA - dateB : dateB - dateA;
                    }
                });

                $.each(visibleRows, function (index, row) {
                    $('#requestsBody').append(row);
                });

                reorderTable();
            };

            // Initialize filters on load
            applyTableFilters();

            // Update Sort helper (now legacy but keeping structure if needed)
            window.updateSort = function (select) {
                applyTableFilters();
            };

            // Format Biaya Disetujui as user types
            $(document).on('input', '#inputBiayaDisetujui, #newBiaya, [data-field="jumlah_biaya"]', function () {
                let element = $(this);
                let val = element.is('input') ? element.val() : element.text();

                // Save cursor position if contenteditable
                let selection, range, startOffset;
                if (!element.is('input')) {
                    selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        range = selection.getRangeAt(0);
                        startOffset = range.startOffset;
                    }
                }

                let cleanVal = val.replace(/[^0-9]/g, '');
                if (cleanVal !== "") {
                    let formatted = new Intl.NumberFormat('id-ID').format(cleanVal);

                    if (element.is('input')) {
                        element.val(formatted);
                    } else {
                        element.text(formatted);
                        // Restore cursor position roughly (this is basic, but for numbers it works okay)
                        if (range) {
                            range = document.createRange();
                            let node = element[0].childNodes[0] || element[0];
                            let offset = Math.min(startOffset, node.length || 0);
                            try {
                                range.setStart(node, offset);
                                range.collapse(true);
                                selection.removeAllRanges();
                                selection.addRange(range);
                            } catch (e) { }
                        }
                    }
                } else {
                    if (element.is('input')) element.val('');
                    else element.text('');
                }
            });

            // Clean dots before form submission
            $('#formApproval').on('submit', function () {
                let input = $('#inputBiayaDisetujui');
                let rawVal = input.val().replace(/[^0-9]/g, '');
                input.val(rawVal);
            });

            // Handle custom-file-input label
            $(document).on('change', '.custom-file-input', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Handle Recurring Options (Existing Rows)
            $(document).on('click', '.recurring-option', function () {
                const id = $(this).data('id');
                const interval = $(this).data('interval');
                const label = $(this).data('label');
                const container = $(this).closest('.dropdown');

                container.find('.recurring-interval-input').val(interval);
                container.find('.is-recurring-input').val(interval ? 1 : 0);
                container.find('.dropdown-toggle').html(`<i class="fas fa-redo-alt mr-1"></i> ${label}`);

                container.find('.recurring-option').removeClass('active');
                $(this).addClass('active');

                // Show/hide end date picker area or keep it всегда visible?
                // Usually end date is only relevant if interval is set

                saveRow($(this).closest('tr'));
            });

            // Handle End Date Picker change
            $(document).on('change', '.recurring-end-date-picker', function () {
                const id = $(this).data('id');
                const value = $(this).val();
                const container = $(this).closest('.dropdown');
                container.find('.recurring-end-date-input').val(value);
                saveRow($(this).closest('tr'));
            });

            // Prevent closing dropdown when clicking end date picker
            $(document).on('click', '.recurring-end-date-picker, #newRecurringEndDate', function (e) {
                e.stopPropagation();
            });

            // Handle Recurring Options (New Row)
            $(document).on('click', '.new-recurring-option', function () {
                const interval = $(this).data('interval');
                const label = $(this).data('label');

                $('#newRecurringInterval').val(interval);
                $('#newIsRecurring').val(interval ? 1 : 0);
                $('#btnNewRecurring').html(`<i class="fas fa-redo-alt mr-1"></i> Pengulangan: ${label}`);

                $('.new-recurring-option').removeClass('active');
                $(this).addClass('active');
            });
        });

        window.deleteRow = function (id) {
            Swal.fire({
                title: 'Hapus Pengajuan?',
                text: 'Data ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: window.location.pathname.split('?')[0] + '/' + id + '/delete',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: 'Pengajuan telah berhasil dihapus.',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $(`#row-${id}`).fadeOut(300, function () {
                                    $(this).remove();
                                    $('#requestsBody tr.request-row').each(function (index) {
                                        $(this).find('.index-column').text(index + 1);
                                    });

                                    if ($('#requestsBody tr.request-row').length === 0) {
                                        $('#emptyRow').show();
                                    }
                                });
                            } else {
                                Swal.fire('Gagal!', response.message || 'Terjadi kesalahan saat menghapus.', 'error');
                            }
                        },
                        error: function (xhr) {
                            let msg = 'Gagal menghubungi server';
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            Swal.fire('Error!', msg + ' (' + xhr.status + ': ' + xhr.statusText + ').', 'error');
                        }
                    });
                }
            });
        }

        function openApprovalModal(id, status, name, amount) {
            const modal = $('#modalApproval');
            const form = $('#formApproval');
            const header = $('#modalHeaderApproval');
            const title = $('#modalTitleApproval');
            const btnSubmit = $('#btnSubmitApproval');
            const containerBiaya = $('#containerBiayaDisetujui');

            $('#inputStatusApproval').val(status);
            $('#namaPengajuanApproval').text(name);
            $('#biayaDiajukanApproval').text('Rp ' + new Intl.NumberFormat('id-ID').format(amount));
            $('#inputBiayaDisetujui').val(new Intl.NumberFormat('id-ID').format(amount));
            $('#inputCatatanApproval').val('');

            form.attr('action', window.location.pathname + '/' + id + '/status');

            if (status === 'approved') {
                header.removeClass('bg-danger').addClass('bg-primary');
                title.text('Setujui Pengajuan');
                btnSubmit.removeClass('btn-danger').addClass('btn-primary').text('Setujui Sekarang');
                containerBiaya.removeClass('d-none');
                $('#containerBuktiTransfer').removeClass('d-none');
                $('#labelCatatanApproval').text('Keterangan (Opsional)');
            } else {
                header.removeClass('bg-success').addClass('bg-danger');
                title.text('Tolak Pengajuan');
                btnSubmit.removeClass('btn-success').addClass('btn-danger').text('Konfirmasi Penolakan');
                containerBiaya.addClass('d-none');
                $('#containerBuktiTransfer').addClass('d-none');
                $('#labelCatatanApproval').text('Alasan Penolakan (Wajib)');
                $('#inputCatatanApproval').attr('required', true);
            }

            $('#inputBuktiTransfer').val('');
            $('.custom-file-label').text('Pilih file gambar...');

            modal.modal('show');
        }

        function previewImage(url, title) {
            $('#imageFullPreview').attr('src', url);
            $('#previewTitle').text(title);
            $('#modalPreviewImage').modal('show');
        }

        function openUploadModal(id, name) {
            $('#namaPengajuanUpload').text(name);
            $('#formUploadBukti').attr('action', window.location.pathname + '/' + id + '/upload-bukti');

            $('#inputUploadBukti').val('');
            $('#inputUploadBukti').next('.custom-file-label').removeClass("selected").html('Pilih file gambar...');

            $('#modalUploadBukti').modal('show');
        }
    </script>

    <style>
        /* Premium Look for Editable Cells */
        .editable-cell-container {
            position: relative;
            cursor: pointer;
            transition: background 0.2s;
        }

        .editable-cell-container:hover {
            background-color: #f8f9fc !important;
        }

        /* Thick table borders */
        #tablePengajuan,
        #tablePengajuan th,
        #tablePengajuan td {
            border: 2px solid #b7b9cc !important;
        }

        /* Elegant Blue Header */
        .header-blue th {
            background: linear-gradient(180deg, #4e73df 0%, #2e59d9 100%) !important;
            color: #ffffff !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px 5px !important;
            vertical-align: middle !important;
        }

        /* Styling for filters inside blue header */
        .header-blue .table-sort,
        .header-blue .table-filter {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            border-radius: 4px;
            font-weight: bold;
        }

        .header-blue .table-sort option,
        .header-blue .table-filter option {
            color: #333 !important;
            background-color: #fff !important;
        }

        .header-blue th div {
            color: rgba(255, 255, 255, 0.95) !important;
            margin-bottom: 4px !important;
        }

        .summary-row td {
            background-color: #f8f9fc !important;
            color: #333 !important;
            border-top: 2px solid #d1d3e2 !important;
        }

        /* Standard Table Responsive */
        .table-responsive {
            overflow-x: auto;
        }

        .badge-warning-soft {
            background-color: rgba(246, 194, 62, 0.1);
            border: 1px solid rgba(246, 194, 62, 0.2);
        }

        @keyframes pulse-warning {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .animate-pulse {
            animation: pulse-warning 2s infinite;
        }

        .overdue-row td {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            border-top: 1px dashed #fadbd8 !important;
        }




        .editable-field {
            outline: none;
            padding: 2px 5px;
            border-radius: 4px;
            transition: all 0.2s;
            border: 1px solid transparent;
            min-height: 1em;
        }

        .editable-field:focus {
            background: #fff !important;
            border: 1px solid #4e73df;
            box-shadow: 0 0 0 0.1rem rgba(78, 115, 223, 0.1);
            z-index: 10;
        }

        /* Placeholder for contenteditable */
        [contenteditable=true]:empty:before {
            content: attr(placeholder);
            display: block;
            color: #ccc;
            font-style: italic;
        }

        .table-success-brief {
            background-color: rgba(28, 200, 138, 0.1) !important;
            transition: background-color 0.5s;
        }

        .btn-action {
            font-weight: 600;
            transition: all 0.2s;
            border-radius: 8px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .badge-pill {
            border-radius: 50px;
        }

        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .gap-1 {
            gap: 0.25rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .text-orange {
            color: #fd7e14 !important;
        }


        .border-left-primary {
            border-left: 4px solid #4e73df !important;
            background-color: #f0f7ff !important;
            color: #2e59d9 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .border-left-danger {
            border-left: 4px solid #e74a3b !important;
            background-color: #fff5f5 !important;
            color: #721c24 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .table td {
            vertical-align: middle !important;
        }

        /* Preview Image Styles */
        .preview-image {
            transition: all 0.3s ease;
        }

        .preview-image:hover {
            transform: scale(1.05);
            border-color: #4e73df;
        }

        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
            border-radius: 0.25rem;
        }

        .position-relative:hover .preview-overlay {
            opacity: 1;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Function to ensure overdue rows are always below their parent
            function groupOverdueRows() {
                $('.overdue-row').each(function() {
                    const classList = $(this).attr('class').split(/\s+/);
                    let parentId = null;
                    for (const cls of classList) {
                        if (cls.startsWith('overdue-for-')) {
                            parentId = cls.replace('overdue-for-', '');
                            break;
                        }
                    }
                    
                    if (parentId) {
                        const parentRow = $('#row-' + parentId);
                        if (parentRow.length) {
                            // Move the overdue row to be directly after the parent row
                            parentRow.after($(this));
                        }
                    }
                });
            }

            // Run on load
            groupOverdueRows();

            // Toggle Overdue Rows
            $('.toggle-overdue').on('click', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const targetRows = $(`.overdue-for-${id}`);
                const icon = $(this).find('i');
                
                targetRows.fadeToggle(200);
                
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle text-warning').addClass('fa-minus-circle text-danger');
                } else {
                    icon.removeClass('fa-minus-circle text-danger').addClass('fa-plus-circle text-warning');
                }
            });

            // If any table filtering/sorting happens via JS, we should re-group
            // (Optional: add listeners for table change events if necessary)
        });
    </script>
@endsection

