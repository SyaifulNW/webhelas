@php
    $latestSp = $item->salesplan->first();
    $statusKey = $latestSp ? strtolower($latestSp->status) : 'cold';
    
    $statusConfig = [
        'cold' => ['label' => 'Cold', 'bg' => '#ffffff', 'text' => '#6c757d', 'border' => '#ddd', 'rowBg' => '#ffffff'],
        'tertarik' => ['label' => 'Tertarik', 'bg' => '#fffceb', 'text' => '#856404', 'border' => '#ffeeba', 'rowBg' => '#fffceb'],
        'sudah_transfer' => ['label' => 'Sudah Transfer', 'bg' => '#e7f5ff', 'text' => '#1971c2', 'border' => '#74c0fc', 'rowBg' => '#e7f5ff'],
        'no' => ['label' => 'No', 'bg' => '#fff5f5', 'text' => '#c92a2a', 'border' => '#ffa8a8', 'rowBg' => '#fff5f5']
    ];
    
    $currentConfig = $statusConfig[$statusKey] ?? $statusConfig['cold'];
@endphp
<tr data-created-by="{{ strtolower($item->created_by) }}"
    data-bulan="{{ \Carbon\Carbon::parse($item->created_at)->month }}"
    data-year="{{ \Carbon\Carbon::parse($item->created_at)->year }}" data-kota="{{ $item->kota_nama }}"
    data-id="{{ $item->id }}"
    style="background-color: {{ $currentConfig['rowBg'] }}; transition: background-color 0.3s ease;">

    <td class="text-center" style="vertical-align: middle;">
        {{ isset($data) && method_exists($data, 'firstItem') ? $data->firstItem() + $loop->index : $loop->iteration }}
    </td>

    @php
        $userRole = strtolower(auth()->user()->role);
        $isCs = in_array($userRole, ['cs', 'cs-mbc', 'cs-smi', 'customer_service']);
        $canEdit = !in_array($userRole, ['marketing', 'administrator']);
    @endphp

    {{-- Nama + WA + CTA (Merged) --}}
    <td class="px-2 py-2">
        <div class="d-flex flex-column gap-2" style="min-width: 160px;">
            <div contenteditable="{{ $canEdit ? 'true' : 'false' }}"
                class="{{ $canEdit ? 'editable' : '' }} fw-bold {{ $item->is_no_potensi ? 'text-white' : 'text-dark' }} text-nowrap p-1 px-2 shadow-sm"
                data-field="nama"
                style="font-size: 0.95rem; border: 1px solid #dee2e6; border-radius: 10px; background: {{ $item->is_no_potensi ? '#e74a3b' : '#fff' }}; transition: all 0.3s; min-height: 32px; display: flex; align-items: center;"
                onfocus="this.style.borderColor='#4e73df'; this.style.boxShadow='0 0 0 0.15rem rgba(78, 115, 223, 0.25)'"
                onblur="this.style.borderColor='#dee2e6'; this.style.boxShadow='none'">
                {{ $item->nama }}
            </div>

            <!-- No WA -->
            <div class="d-flex align-items-center p-1 px-2 shadow-sm"
                style="border: 1px solid #dee2e6; border-radius: 10px; background: #fff; min-height: 32px;">
                <i class="bi bi-phone me-1 text-primary" style="font-size: 0.8rem;"></i>
                <span contenteditable="{{ $canEdit ? 'true' : 'false' }}"
                    class="{{ $canEdit ? 'editable' : '' }} small text-secondary flex-grow-1" data-field="no_wa"
                    style="font-size: 0.85rem; letter-spacing: 0.3px; outline: none; border: none;">
                    {{ $item->no_wa }}
                </span>
            </div>

            <!-- Buttons -->
            <div class="d-flex align-items-center gap-1 mt-1">
                @if($item->no_wa)
                    @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank"
                        class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 shadow-sm"
                        style="width: 28px; height: 28px; transition: transform 0.2s;"
                        onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
                        title="Chat WhatsApp">
                        <i class="bi bi-whatsapp" style="font-size: 0.9rem;"></i>
                    </a>
                @endif

                <button type="button" class="btn btn-primary btn-sm btn-riwayat rounded-pill shadow-sm px-3 border-0"
                    style="height: 28px; line-height: 1; font-size: 0.75rem; font-weight: 700; background: linear-gradient(45deg, #4e73df, #224abe);"
                    data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" data-fu1="{{ $item->fu1 }}"
                    data-fu1-wa="{{ $item->fu1_wa }}" data-fu1-telp="{{ $item->fu1_telp }}"
                    data-fu1-at="{{ $item->fu1_at ? $item->fu1_at->format('d/m/Y H:i') : '' }}"
                    data-fu1-hasil="{{ $item->fu1_hasil }}" data-fu1-tindak-lanjut="{{ $item->fu1_tindak_lanjut }}"
                    data-fu2="{{ $item->fu2 }}" data-fu2-wa="{{ $item->fu2_wa }}" data-fu2-telp="{{ $item->fu2_telp }}"
                    data-fu2-at="{{ $item->fu2_at ? $item->fu2_at->format('d/m/Y H:i') : '' }}"
                    data-fu2-hasil="{{ $item->fu2_hasil }}" data-fu2-tindak-lanjut="{{ $item->fu2_tindak_lanjut }}"
                    data-fu3="{{ $item->fu3 }}" data-fu3-wa="{{ $item->fu3_wa }}" data-fu3-telp="{{ $item->fu3_telp }}"
                    data-fu3-at="{{ $item->fu3_at ? $item->fu3_at->format('d/m/Y H:i') : '' }}"
                    data-fu3-hasil="{{ $item->fu3_hasil }}" data-fu3-tindak-lanjut="{{ $item->fu3_tindak_lanjut }}"
                    data-fu4="{{ $item->fu4 }}" data-fu4-wa="{{ $item->fu4_wa }}" data-fu4-telp="{{ $item->fu4_telp }}"
                    data-fu4-at="{{ $item->fu4_at ? $item->fu4_at->format('d/m/Y H:i') : '' }}"
                    data-fu4-hasil="{{ $item->fu4_hasil }}" data-fu4-tindak-lanjut="{{ $item->fu4_tindak_lanjut }}"
                    data-fu5="{{ $item->fu5 }}" data-fu5-wa="{{ $item->fu5_wa }}" data-fu5-telp="{{ $item->fu5_telp }}"
                    data-fu5-at="{{ $item->fu5_at ? $item->fu5_at->format('d/m/Y H:i') : '' }}"
                    data-fu5-hasil="{{ $item->fu5_hasil }}" data-fu5-tindak-lanjut="{{ $item->fu5_tindak_lanjut }}"
                    data-fu6="{{ $item->fu6 }}" data-fu6-wa="{{ $item->fu6_wa }}" data-fu6-telp="{{ $item->fu6_telp }}"
                    data-fu6-at="{{ $item->fu6_at ? $item->fu6_at->format('d/m/Y H:i') : '' }}"
                    data-fu6-hasil="{{ $item->fu6_hasil }}" data-fu6-tindak-lanjut="{{ $item->fu6_tindak_lanjut }}"
                    data-fu7="{{ $item->fu7 }}" data-fu7-wa="{{ $item->fu7_wa }}" data-fu7-telp="{{ $item->fu7_telp }}"
                    data-fu7-at="{{ $item->fu7_at ? $item->fu7_at->format('d/m/Y H:i') : '' }}"
                    data-fu7-hasil="{{ $item->fu7_hasil }}" data-fu7-tindak-lanjut="{{ $item->fu7_tindak_lanjut }}"
                    data-fu8="{{ $item->fu8 }}" data-fu8-wa="{{ $item->fu8_wa }}" data-fu8-telp="{{ $item->fu8_telp }}"
                    data-fu8-at="{{ $item->fu8_at ? $item->fu8_at->format('d/m/Y H:i') : '' }}"
                    data-fu8-hasil="{{ $item->fu8_hasil }}" data-fu8-tindak-lanjut="{{ $item->fu8_tindak_lanjut }}"
                    data-fu9="{{ $item->fu9 }}" data-fu9-wa="{{ $item->fu9_wa }}" data-fu9-telp="{{ $item->fu9_telp }}"
                    data-fu9-at="{{ $item->fu9_at ? $item->fu9_at->format('d/m/Y H:i') : '' }}"
                    data-fu9-hasil="{{ $item->fu9_hasil }}" data-fu9-tindak-lanjut="{{ $item->fu9_tindak_lanjut }}"
                    data-fu10="{{ $item->fu10 }}" data-fu10-wa="{{ $item->fu10_wa }}"
                    data-fu10-telp="{{ $item->fu10_telp }}"
                    data-fu10-at="{{ $item->fu10_at ? $item->fu10_at->format('d/m/Y H:i') : '' }}"
                    data-fu10-hasil="{{ $item->fu10_hasil }}" data-fu10-tindak-lanjut="{{ $item->fu10_tindak_lanjut }}">
                    Follow Up
                </button>
            </div>
        </div>
    </td>

    {{-- Sumber Leads --}}
    <td>
        <select class="form-control form-control-sm select-sumber" data-id="{{ $item->id }}"
            style="font-size: 0.85rem; padding: 2px 5px;" {{ $canEdit ? '' : 'disabled' }}>
            <option value="">- Pilih -</option>
            <option value="Ads" {{ $item->leads == 'Ads' ? 'selected' : '' }}>Ads</option>
            <option value="Sosmed" {{ $item->leads == 'Sosmed' ? 'selected' : '' }}>Sosmed</option>
            <option value="Zoom" {{ $item->leads == 'Zoom' ? 'selected' : '' }}>Zoom</option>
            <option value="Open House" {{ $item->leads == 'Open House' ? 'selected' : '' }}>Open House</option>
            <option value="Mandiri" {{ $item->leads == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
            @if(!in_array($userRole, ['reseller', 'agen']))
                <option value="Alumni" {{ $item->leads == 'Alumni' ? 'selected' : '' }}>Alumni</option>
            @endif
        </select>
    </td>

    {{-- Provinsi & Kota (Merged) --}}
    <td>
        <div class="d-flex flex-column gap-1">
            <select class="form-control form-control-sm select-provinsi mb-1" data-id="{{ $item->id }}"
                data-nama="{{ $item->provinsi_nama }}" style="font-size: 0.8rem; padding: 2px 5px; height: auto;" {{ $canEdit ? '' : 'disabled' }}>
                <option value="">{{ $item->provinsi_nama ?: '-- Prov --' }}</option>
            </select>
            <select class="form-control form-control-sm select-kota" data-id="{{ $item->id }}"
                data-prov-id="{{ $item->provinsi_id }}" data-nama="{{ $item->kota_nama }}"
                style="font-size: 0.8rem; padding: 2px 5px; height: auto;" {{ $canEdit ? '' : 'disabled' }}>
                <option value="">{{ $item->kota_nama ?: '-- Kota --' }}</option>
            </select>
        </div>
    </td>

    <td>
        <div contenteditable="{{ $canEdit ? 'true' : 'false' }}"
            class="{{ $canEdit ? 'editable' : '' }} fw-bold text-dark p-1 px-2 shadow-sm" data-field="nama_bisnis"
            style="font-size: 0.9rem; border: 1px solid #dee2e6; border-radius: 8px; background: #fff; min-height: 28px; min-width: 140px;">
            {{ $item->nama_bisnis }}
        </div>
    </td>

    {{-- Common Columns --}}

    {{-- Situasi / Kendala (Merged) --}}
    <td class="text-wrap-normal">
        <div class="d-flex flex-column gap-2" style="min-width: 200px;">
            <div class="situasi-block">
                <div class="read-more-container p-2 shadow-sm"
                    style="border: 1px solid #dee2e6; border-radius: 8px; background: #fff; min-height: 40px;">
                    <div contenteditable="{{ $canEdit ? 'true' : 'false' }}" class="{{ $canEdit ? 'editable' : '' }}"
                        data-field="situasi_bisnis" style="outline: none; white-space: pre-wrap; font-size: 0.75rem; line-height: 1.4; color: #444;">{{ $item->situasi_bisnis }}</div>
                </div>
                @if(strlen($item->situasi_bisnis ?? '') > 100)
                    <a href="javascript:void(0)" class="btn-read-more small mt-1 d-inline-block">Baca Situasi</a>
                @endif
            </div>
            
            @if($userRole !== 'chapter')
            <div class="kendala-block" style="border-top: 1px dashed #ddd; padding-top: 5px;">
                <div class="read-more-container p-1 px-2 shadow-sm"
                    style="border: 1px solid #dee2e6; border-radius: 8px; background: #f9f9f9; min-height: 40px;">
                    <div contenteditable="{{ $canEdit ? 'true' : 'false' }}"
                        class="{{ $canEdit ? 'editable' : '' }} italic text-muted" data-field="kendala"
                        style="outline: none;">{{ $item->kendala }}</div>
                </div>
                @if(strlen($item->kendala ?? '') > 100)
                    <a href="javascript:void(0)" class="btn-read-more small mt-1 d-inline-block">Baca Kendala</a>
                @endif
            </div>
            @endif
        </div>
    </td>



    {{-- Rekap Penilaian (Parsed from situasi_bisnis) --}}
    @php
        $skorText = '-';
        $rawSkor = null;
        if(preg_match('/Total Skor(?: Form)?:? ?(\d+(?: \/ \d+)?)/i', $item->situasi_bisnis ?? '', $matches)) {
            $skorText = $matches[1];
            $parts = explode('/', str_replace(' ', '', $skorText));
            $rawSkor = isset($parts[0]) ? (int) $parts[0] : null;
        }

        $badgeClass = 'bg-secondary text-white';
        $textClass = 'text-secondary';
        $badgeText = '';
        $subText = '';

        if($rawSkor !== null) {
            if($rawSkor >= 41) {
                // HOT (Hijau)
                $badgeClass = 'bg-success text-white';
                $textClass = 'text-success';
                $badgeText = 'HOT';
                $subText = 'Siap closing, prioritas utama';
            } elseif($rawSkor >= 25) {
                // WARM (Kuning)
                $badgeClass = 'bg-warning text-dark';
                $textClass = 'text-warning';
                $badgeText = 'WARM';
                $subText = 'Potensial, perlu pendekatan';
            } else {
                // COLD (Putih)
                $badgeClass = 'bg-white text-dark border border-secondary';
                $textClass = 'text-secondary';
                $badgeText = 'COLD';
                $subText = 'Tidak prioritas';
            }
        }
    @endphp
    @if($userRole !== 'cs-mbc')
    <td class="text-center" style="vertical-align: middle;">
        @if($skorText !== '-')
            <div class="d-flex flex-column align-items-center justify-content-center">
                <span class="font-weight-bold {{ $textClass }} mb-1" style="font-size: 0.85rem; letter-spacing: 0.05em;">{{ $badgeText }}</span>
                <span class="badge {{ $badgeClass }} p-2 shadow-sm mb-1" style="font-size: 0.85rem; border-radius: 6px; width: 40px;">{{ $rawSkor }}</span>
                <small class="text-muted d-block mt-1" style="font-size: 0.65rem; line-height: 1.1; max-width: 130px; white-space: normal; text-align: center;">{{ $subText }}</small>
            </div>
        @else
            <span class="text-muted small">-</span>
        @endif
    </td>
    @endif

    {{-- SPIN: Budget, Authority, Time --}}
    <td class="col-bat">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input check-bant-budget" id="budget{{ $item->id }}"
                data-id="{{ $item->id }}" {{ $item->bant_budget ? 'checked' : '' }} {{ $canEdit ? '' : 'disabled' }}>
            <label class="custom-control-label" for="budget{{ $item->id }}"></label>
        </div>
    </td>
    <td class="col-bat">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input check-bant-authority" id="authority{{ $item->id }}"
                data-id="{{ $item->id }}" {{ $item->bant_authority ? 'checked' : '' }} {{ $canEdit ? '' : 'disabled' }}>
            <label class="custom-control-label" for="authority{{ $item->id }}"></label>
        </div>
    </td>
    <td class="col-bat">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input check-bant-time" id="time{{ $item->id }}"
                data-id="{{ $item->id }}" {{ $item->bant_time ? 'checked' : '' }} {{ $canEdit ? '' : 'disabled' }}>
            <label class="custom-control-label" for="time{{ $item->id }}"></label>
        </div>
    </td>

    @if(!in_array($userRole, ['administrator', 'chapter', 'reseller']))
        <td class="text-center small text-muted px-1" style="font-size: 0.75rem; width: 100px;">
            {{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-' }}
        </td>
    @endif

    {{-- Ikut Zoom (All Roles) --}}
    <td class="text-center p-1 col-zoom">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input check-zoom" id="zoom{{ $item->id }}"
                data-id="{{ $item->id }}" {{ $item->ikut_zoom ? 'checked' : '' }} {{ $canEdit ? '' : 'disabled' }}>
            <label class="custom-control-label" for="zoom{{ $item->id }}"></label>
        </div>
    </td>
    @php
        $hasSpin = $item->bant_budget && $item->bant_authority && $item->bant_time;
    @endphp

    @php
        $bgPotensi = '';
        $textPotensi = 'inherit';
        $curPotensi = strtoupper($item->potensi ?? '');
        if ($curPotensi == 'MBC') {
            $bgPotensi = '#8b0000'; // Maroon
            $textPotensi = '#fff';
        } elseif ($curPotensi == 'SMI') {
            $bgPotensi = '#28a745'; // Green
            $textPotensi = '#fff';
        }
    @endphp

    @if(Auth::user()->role !== 'marketing' && !in_array($userRole, ['reseller', 'chapter']))
        <td class="text-center">
            <div class="d-flex flex-column gap-2 align-items-center">
                <select class="form-control form-control-sm select-potensi-mbc-m1t" 
                    data-id="{{ $item->id }}"
                    {{ $canEdit ? '' : 'disabled' }}
                    style="font-size: 0.7rem; height: auto; padding: 2px 4px; border-radius: 6px; border: 1px solid #ddd; cursor: pointer; width: 80px; margin: 0 auto; background-color: {{ $bgPotensi ?: '#fff' }} !important; color: {{ $textPotensi }} !important; font-weight: bold;">
                    <option value="" style="background-color: #fff !important; color: #000 !important;">- Pilih -</option>
                    <option value="MBC" {{ $curPotensi == 'MBC' ? 'selected' : '' }} style="background-color: #8b0000 !important; color: #fff !important;">MBC</option>
                    <option value="SMI" {{ $curPotensi == 'SMI' ? 'selected' : '' }} style="background-color: #28a745 !important; color: #fff !important;">M1T</option>
                </select>

                {{-- Secondary Dropdown for Kelas (Visible only if MBC) --}}
                <div class="potensi-kelas-container {{ $curPotensi == 'MBC' ? '' : 'd-none' }}" style="width: 100px; margin-top: 8px;">
                    <select class="form-control form-control-sm select-potensi-kelas" 
                        data-id="{{ $item->id }}"
                        {{ $canEdit ? '' : 'disabled' }}
                        style="font-size: 0.65rem; height: auto; padding: 2px; border-radius: 4px; border: 1px solid #ced4da; cursor: pointer; width: 100%; color: #000 !important; font-weight: 600;">
                        <option value="" style="color: #000;">- Pilih Kelas -</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }} style="color: #000;">
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </td>
    @endif

    @if(in_array($userRole, ['chapter', 'reseller', 'agen', 'cs-mbc']))
        {{-- Status (Eager Loaded from SalesPlan) --}}
        <td class="text-center" style="background-color: inherit;">
            <select class="form-control form-control-sm status-select" 
                data-id="{{ $item->id }}"
                data-nama="{{ $item->nama }}"
                data-level="{{ $latestSp ? $latestSp->level : 'Grow Up' }}"
                onchange="updateStatusDirect({{ $item->id }}, this.value)"
                {{ $canEdit ? '' : 'disabled' }}
                style="background-color: {{ $currentConfig['bg'] }}; color: {{ $currentConfig['text'] }}; border: 1px solid {{ $currentConfig['border'] }}; padding: 2px 4px; font-size: 0.7rem; border-radius: 6px; font-weight: 700; width: 110px; height: auto; cursor: pointer;">
                @foreach($statusConfig as $key => $s)
                    <option value="{{ $key }}" {{ $statusKey == $key ? 'selected' : '' }}>{{ strtoupper($s['label']) }}</option>
                @endforeach
            </select>

            {{-- Nominal Display (Visible if sudah_transfer) --}}
            @php
                $nominalVal = $latestSp ? ($latestSp->nominal ?: ($latestSp->pesertaSmi->total_pembayaran ?? 0)) : 0;
            @endphp
            <div class="nominal-display mt-1 {{ $statusKey == 'sudah_transfer' ? '' : 'd-none' }}" 
                 style="font-size: 0.75rem; font-weight: 800; color: #1971c2; background: rgba(25, 113, 194, 0.05); padding: 2px 4px; border-radius: 4px; border: 1px solid rgba(25, 113, 194, 0.2);">
                 Rp {{ number_format($nominalVal, 0, ',', '.') }}
            </div>
        </td>
    @endif



    @if(in_array($userRole, ['administrator', 'manager', 'marketing', 'agen']) || auth()->user()->name === 'Agus Setyo')
        <td>{{ $item->createdBy->name ?? $item->created_by }}</td>


    @endif


    @if(!in_array(strtolower(auth()->user()->role), ['marketing', 'administrator']))
        <td class="text-center">
            <div class="d-flex flex-row gap-2 justify-content-center align-items-center">
                <button type="button" class="btn btn-info btn-sm btn-reuse" data-id="{{ $item->id }}"
                    title="Reuse Data (Segarkan)" style="padding: 0.2rem 0.4rem; background-color: #17a2b8; border-color: #17a2b8;">
                    <i class="fas fa-retweet" style="color: #fff; font-size: 0.7rem;"></i>
                </button>
                <button type="button" class="btn btn-warning btn-sm btn-no-potensi" data-id="{{ $item->id }}"
                    title="Tanda Tidak Potensi (X)" style="padding: 0.2rem 0.4rem; margin-left: 2px;">
                    <i class="fa fa-times" style="color: #fff; font-size: 0.7rem;"></i>
                </button>
                <form action="{{ route('delete-database', $item->id) }}" method="POST" style="display:inline; margin-left: 2px;"
                    class="delete-form" data-nama="{{ $item->nama }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-delete" style="padding: 0.2rem 0.4rem;">
                        <i class="fa-solid fa-trash" style="color:#fff; font-size: 0.7rem;"></i>
                    </button>
                </form>
            </div>
        </td>
    @endif

</tr>