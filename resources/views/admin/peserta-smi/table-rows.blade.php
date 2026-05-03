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
                if ($item->tanggal_masuk) {
                    $startJoin = \Carbon\Carbon::parse($item->tanggal_masuk)->startOfMonth();
                    $countPaid = 0;
                    // Check up to 12 months to see if they reached 6 months total
                    for ($m = 0; $m < 12; $m++) {
                        $checkM = $startJoin->copy()->addMonths($m);
                        $mNum = (int) $checkM->format('n');
                        if (($item->{"spp_$mNum"} ?? 0) >= 1000000) {
                            $countPaid++;
                        }
                    }
                    if ($countPaid >= 6)
                        $isAutoLunas = true;
                }

                $isAllPaid = $isManualLunas || $isAutoLunas;
                $filterYear = request('filter_year', date('Y'));
                
                // [USER_REQUEST] Pre-calculate approval logic for usage throughout the row
                $creatorRole = strtolower($item->closingCs->role ?? $item->createdBy->role ?? $item->salesPlan->createdBy->role ?? '');
                $showApproval = in_array($creatorRole, ['reseller', 'chapter', 'agen']);
                $userRole = strtolower(auth()->user()->role);
                $isAdmin = (in_array($userRole, ['administrator', 'pusat', 'admin']) || auth()->user()->name === 'Linda');
             @endphp
            <div class="d-flex align-items-center">
                <input form="form-update-{{ $item->id }}" type="text" name="nama"
                    class="table-input font-weight-bold text-dark" value="{{ $item->nama }}" placeholder="Nama peserta..."
                    onblur="quickUpdateField(this, {{ $item->id }}, 'nama')">
            </div>
            @if($isAllPaid)
                <div class="px-2 py-0">
                    <span class="badge badge-primary shadow-sm"
                        style="font-size: 0.6rem; letter-spacing: 1px; font-weight: 800; padding: 2px 6px;">LUNAS</span>
                </div>
            @endif
            <div id="wrapper-nama-2-{{ $item->id }}"
                class="{{ $item->nama_2 || $item->nama_asli_2 ? '' : 'participant-2-hidden' }} mt-1 pt-1 border-top">
                <input form="form-update-{{ $item->id }}" type="text" name="nama_2" class="table-input"
                    value="{{ $item->nama_2 }}" placeholder="Nama ke-2..."
                    onblur="quickUpdateField(this, {{ $item->id }}, 'nama_2')">
            </div>
            <div class="d-flex align-items-center mt-2 px-1" style="gap: 4px;">
                @php
                    $statusClass = 'status-aktif';
                    if ($item->status == 'Aktif')
                        $statusClass = 'status-aktif';
                    elseif ($item->status == 'Cuti')
                        $statusClass = 'status-cuti';
                    elseif ($item->status == 'Lulus')
                        $statusClass = 'status-lulus';
                    
                    // Use calculated is_all_paid from controller if available, fallback to manual check
                    $isAllPaid = property_exists($item, 'is_all_paid') ? $item->is_all_paid : $isAllPaid;
                 @endphp
                @php
                    // [USER_REQUEST] Hide "Aktif" badge if it needs approval but is not yet Approved
                    $isStatusVisible = true;
                    if ($showApproval && $item->approval_status !== 'Approved') {
                        $isStatusVisible = false;
                    }
                @endphp
                @if($isStatusVisible)
                <select class="badge-status {{ $statusClass }}" onchange="quickUpdateField(this, {{ $item->id }}, 'status')"
                    style="width: 70px; font-size: 0.65rem; padding: 2px 6px;">
                    <option value="Aktif" {{ $item->status == 'Aktif' ? 'selected' : '' }} class="text-dark bg-white">Aktif
                    </option>
                    <option value="Cuti" {{ $item->status == 'Cuti' ? 'selected' : '' }} class="text-dark bg-white">Cuti
                    </option>
                    <option value="Lulus" {{ $item->status == 'Lulus' ? 'selected' : '' }} class="text-dark bg-white">Lulus
                    </option>
                </select>
                @else
                    <span class="badge badge-warning opacity-50" style="font-size: 0.65rem; padding: 3px 8px;">Menunggu Verifikasi</span>
                @endif
                
                <input form="form-update-{{ $item->id }}" type="date" name="tanggal_masuk" id="tgl_masuk_{{ $item->id }}"
                    class="form-control form-control-sm p-0 text-center" 
                    style="font-size: 0.65rem; height: 18px; width: 75px; border-radius: 4px; border: 1px solid #e3e6f0; color: #4e73df; font-weight: bold;"
                    value="{{ $item->tanggal_masuk }}"
                    title="Ganti Tanggal Masuk"
                    onchange="quickUpdateField(this, {{ $item->id }}, 'tanggal_masuk')">
            </div>
            @if($item->status == 'Cuti')
                <div class="mt-2 d-flex flex-column px-1" style="gap: 4px;">
                    <a href="javascript:void(0)" onclick="focusTanggalSelesai({{ $item->id }})"
                        class="text-secondary font-weight-bold"
                        style="font-size: 0.65rem; border-bottom: 1px dashed #858796; text-decoration: none; width: fit-content;">
                        <i class="fas fa-calendar-check mr-1"></i> Tgl Selesai
                    </a>
                </div>
            @endif
        </td>

        {{-- Nama Peserta Asli --}}
        <td class="p-1 align-middle d-none">
            <input form="form-update-{{ $item->id }}" type="text" name="nama_asli" class="table-input"
                value="{{ $item->nama_asli }}" placeholder="Nama asli..."
                onblur="quickUpdateField(this, {{ $item->id }}, 'nama_asli')">
            <div id="wrapper-nama-asli-2-{{ $item->id }}"
                class="{{ $item->nama_2 || $item->nama_asli_2 ? '' : 'participant-2-hidden' }} mt-1 pt-1 border-top">
                <input form="form-update-{{ $item->id }}" type="text" name="nama_asli_2" class="table-input"
                    value="{{ $item->nama_asli_2 }}" placeholder="Nama asli ke-2..."
                    onblur="quickUpdateField(this, {{ $item->id }}, 'nama_asli_2')">
            </div>
        </td>

        {{-- Status / Level Hidden Columns --}}
        <td class="p-1 align-middle text-center d-none"></td>
        <td class="p-1 align-middle text-center d-none"></td>

        {{-- Biaya Closing Awal --}}
        <td class="p-1 align-middle text-center">
            @php
                $biayaClosing = $item->total_pembayaran ?? $item->spp_awal;
                if (!$biayaClosing && ($item->biaya_pendaftaran || $item->pembayaran_spp)) {
                    $biayaClosing = (float) $item->biaya_pendaftaran + (float) $item->pembayaran_spp;
                }
                if (!$biayaClosing)
                    $biayaClosing = $item->biaya_pendaftaran;
             @endphp
            <div class="text-center h6 font-weight-bold text-dark mb-1">
                {{ number_format((float) $biayaClosing, 0, ',', '.') }}
            </div>
            
            {{-- Approval Status Integrated Here --}}
            @php
                $approvalClass = 'status-pending';
                if ($item->approval_status == 'Approved') $approvalClass = 'status-approved';
                elseif ($item->approval_status == 'Rejected') $approvalClass = 'status-rejected';
                $isChapterOrAgen = in_array($userRole, ['chapter', 'reseller', 'agen']);
            @endphp
            @if($showApproval)
                @if($isAdmin)
                    {{-- Admin: dropdown approve + view bukti --}}
                    <select class="badge-status {{ $approvalClass }}" onchange="quickUpdateField(this, {{ $item->id }}, 'approval_status')"
                        style="width: 85px; font-size: 0.75rem; padding: 2px 6px; height: 26px;">
                        <option value="Pending" {{ ($item->approval_status == 'Pending' || !$item->approval_status) ? 'selected' : '' }} style="background-color: #f6c23e; color: white;">Pending</option>
                        <option value="Approved" {{ $item->approval_status == 'Approved' ? 'selected' : '' }} style="background-color: #1cc88a; color: white;">Approved</option>
                        <option value="Rejected" {{ $item->approval_status == 'Rejected' ? 'selected' : '' }} style="background-color: #e74a3b; color: white;">Rejected</option>
                    </select>
                    @if($item->bukti_transfer)
                        <button type="button" class="btn btn-outline-primary mt-1"
                            style="width: 85px; font-size: 0.75rem; padding: 2px 6px; border-radius: 50px; font-weight: 700; display: block; margin: 2px auto 0;"
                            onclick="viewBuktiTransfer('{{ Storage::url($item->bukti_transfer) }}', '{{ addslashes($item->nama) }}')">
                            <i class="fas fa-image"></i> Bukti
                        </button>
                    @else
                        <span class="text-muted mt-1 d-block" style="font-size: 0.6rem;"><i class="fas fa-exclamation-circle mr-1"></i>Blm ada bukti</span>
                    @endif
                @elseif($isChapterOrAgen)
                    {{-- Chapter/Agen: show status + upload button --}}
                    <span class="badge-status {{ $approvalClass }}" style="width: 85px; font-size: 0.75rem; padding: 2px 6px; cursor: default; display: inline-block;">
                        {{ $item->approval_status ?: 'Pending' }}
                    </span>
                    <button type="button" class="btn btn-xs btn-info mt-1 shadow-sm"
                        style="font-size: 0.6rem; padding: 2px 6px; border-radius: 10px;"
                        onclick="openUploadBukti({{ $item->id }}, '{{ addslashes($item->nama) }}', '{{ $item->bukti_transfer ? Storage::url($item->bukti_transfer) : '' }}')">
                        <i class="fas fa-upload mr-1"></i>
                        {{ $item->bukti_transfer ? 'Ganti Bukti' : 'Upload Bukti' }}
                    </button>
                @else
                    <span class="badge-status {{ $approvalClass }}" style="width: 85px; font-size: 0.75rem; padding: 2px 6px; cursor: default; display: inline-block;">
                        {{ $item->approval_status ?: 'Pending' }}
                    </span>
                @endif
            @endif
        </td>

        {{-- CS yang Closing --}}
        <td class="p-1 align-middle text-center">
            <span class="text-dark font-weight-bold" style="font-size: 0.8rem;">
                {{ $item->cs_name ?: ($item->closingCs->name ?? ($item->salesPlan ? $item->salesPlan->created_by_name : ($item->createdBy->name ?? '-'))) }}
            </span>
        </td>
        @for($i = 1; $i <= 12; $i++)
            <td class="text-center align-middle p-0 spp-col {{ $i > 1 ? 'spp-extra' : '' }}">
                @php
                    $isVisible = true;
                    if ($item->status == 'Cuti' || $item->status == 'Lulus') {
                        $isVisible = false;
                    }
                    $selectedMonths = [];
                    if ($item->salesPlan && is_array($item->salesPlan->selected_months)) {
                        $selectedMonths = $item->salesPlan->selected_months;
                    } else if ($item->salesPlan && is_string($item->salesPlan->selected_months)) {
                        $selectedMonths = json_decode($item->salesPlan->selected_months, true) ?? [];
                    }
                    $isPlanChecked = false;
                    $effectiveDate = null;
                    if ($item->salesPlan) {
                        if ($item->salesPlan->tanggal_closing) {
                            $effectiveDate = \Carbon\Carbon::parse($item->salesPlan->tanggal_closing);
                        } else {
                            $effectiveDate = $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk) : $item->salesPlan->updated_at;
                        }
                    } else {
                        $effectiveDate = $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk) : $item->created_at;
                    }

                    if ($effectiveDate && $effectiveDate->format('Y') == $filterYear && (int)$effectiveDate->format('n') == $i) {
                        $isPlanChecked = true;
                    }

                    // Also check selected_months for other planned installments (Blue)
                    if (isset($selectedMonths[$filterYear]) && in_array((int) $i, $selectedMonths[$filterYear])) {
                        $isPlanChecked = true;
                    }

                    // [USER_REQUEST] Khusus tahun 2026: checklist biru mulai dari April (bulan 4)
                    // Sembunyikan biru untuk bulan Jan/Feb/Mar 2026
                    if ($filterYear == 2026 && $i <= 3) {
                        $isPlanChecked = false;
                    }
                @endphp
                @if($isVisible)
                    @php
                        $customSchedule = $item->spp_custom_schedule ?? [];
                        $plannedNominal = null;
                        $plannedDate = null;
                        foreach ($customSchedule as $sch) {
                            if ($sch['month'] == $i && ($sch['year'] ?? $filterYear) == $filterYear) {
                                $plannedNominal = $sch['nominal'];
                                $plannedDate = $sch['date'];
                                break;
                            }
                        }
                        $isPaid = ($item->{"spp_$i"} > 0);
                        
                        // [USER_REQUEST] Verify if the payment year matches the currently filtered year
                        // If payment was made in a different year, don't show it as 'paid' for this year view.
                        if ($isPaid && $item->{"tanggal_spp_$i"}) {
                            $paymentYear = \Carbon\Carbon::parse($item->{"tanggal_spp_$i"})->format('Y');
                            if ($paymentYear != $filterYear) {
                                $isPaid = false;
                            }
                        }
                    @endphp
                    <div class="spp-wrapper">
                        @php
                            // [USER_REQUEST] Blue for initial plan check, Green for manual monthly check
                            // [USER_REQUEST] All blue if status is "Lunas" (isAllPaid)
                            $accentColor = '#1cc88a'; // Default Green (Manual)
                            if ($isAllPaid || $isPlanChecked || $plannedNominal) {
                                $accentColor = '#4e73df'; // Blue
                            }
                        @endphp
                        <input type="checkbox" class="spp-checkbox" data-id="{{ $item->id }}" data-month="{{ $i }}"
                            data-planned-nominal="{{ $plannedNominal }}" {{ ($isPaid || $isPlanChecked) ? 'checked' : '' }}
                            style="accent-color: {{ $accentColor }};"
                            title="{{ $plannedNominal ? 'Rencana: Rp ' . number_format($plannedNominal, 0, ',', '.') : 'Centang jika Lunas' }}"
                            onclick="toggleSppLunasDirectly(this)">

                        <input form="form-update-{{ $item->id }}" type="text" name="spp_{{ $i }}" id="spp_{{ $i }}_{{ $item->id }}"
                            value="{{ $isPaid ? number_format($item->{"spp_$i"}, 0, ',', '.') : '0' }}"
                            class="table-input input-currency spp-input-small d-none" placeholder="0"
                            oninput="syncSppCheckbox(this, {{ $i }}, {{ $item->id }})" onclick="void(0)"
                            onblur="quickUpdateField(this, {{ $item->id }}, 'spp_{{ $i }}')">

                        @if($isPaid)
                            <div id="date_tanggal_spp_{{ $i }}_{{ $item->id }}" class="text-muted"
                                style="font-size: 0.6rem; color: {{ $accentColor }} !important; font-weight: bold; margin-top: 1px;">
                                <i class="fas fa-{{ ($isAllPaid || $isPlanChecked) ? 'check-double' : 'check-circle' }}"></i>
                                {{ $item->{"tanggal_spp_$i"} ? \Carbon\Carbon::parse($item->{"tanggal_spp_$i"})->format('d/m/y') : '' }}
                            </div>
                        @elseif($plannedNominal)
                            <div class="text-primary font-weight-bold" style="font-size: 0.65rem; margin-top: 1px;">
                                Rp {{ number_format($plannedNominal, 0, ',', '.') }}
                            </div>
                            <div class="text-muted" style="font-size: 0.55rem; opacity: 0.8;">
                                {{ $plannedDate ? \Carbon\Carbon::parse($plannedDate)->format('d/m/y') : '' }}
                            </div>
                        @endif
                    </div>
                @else
                    <span class="text-muted small" style="font-size: 0.7rem;">-</span>
                @endif
            </td>
        @endfor

        {{-- Action Buttons --}}
        <td class="text-center align-middle">
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-danger btn-sm btn-icon-split shadow-sm" title="Hapus"
                    style="padding: 2px 6px;" onclick="deletePeserta({{ $item->id }}, this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@endforeach