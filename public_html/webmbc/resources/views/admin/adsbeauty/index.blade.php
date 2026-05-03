@extends('layouts.masteradmin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<!-- 1. Global Functions (Immediate Availability) -->
<script>
    console.log("Ads Performance Script - Global Init Loading...");
    
    function renumberRows() {
        $('#ads-tbody tr').each(function(i) {
            $(this).find('td:first').text(i + 1);
        });
    }
</script>



<div class="container-fluid px-3 my-3">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <!-- <div class="bg-indigo-600 p-3 rounded-circle shadow-indigo me-4 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="fas fa-bullhorn text-white fs-4"></i>
                </div> -->
                <div>
                    {{-- Header removed --}}
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                <a href="{{ route('admin.ads-activity.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'status' => $status]) }}" class="btn btn-danger d-flex align-items-center gap-2 fw-800 shadow-sm hover-elevate" style="font-size: 0.7rem; border-radius: 10px; padding: 10px 18px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; color: white;">
                    <i class="fas fa-file-pdf fs-6"></i> EXPORT PDF
                </a>
                <form action="{{ route('admin.ads-activity.index') }}" method="GET" class="d-flex flex-wrap gap-2" id="filter-form">
                    <div class="custom-select-group d-flex align-items-center px-3 shadow-sm">
                        <span class="label text-uppercase">Bulan</span>
                        <select name="bulan" onchange="document.getElementById('filter-form').submit()">
                            <option value="all" {{ $bulan == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="custom-select-group d-flex align-items-center px-3 shadow-sm">
                        <span class="label text-uppercase">Tahun</span>
                        <select name="tahun" onchange="document.getElementById('filter-form').submit()">
                            @for($y=date('Y')-2; $y<=date('Y')+1; $y++)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="custom-select-group d-flex align-items-center px-3 shadow-sm">
                        <span class="label text-uppercase">Status</span>
                        <select name="status" onchange="document.getElementById('filter-form').submit()" style="min-width: 100px;">
                            <option value="" {{ $status == '' ? 'selected' : '' }}>Semua</option>
                            <option value="running" {{ $status == 'running' ? 'selected' : '' }}>Jalan</option>
                            <option value="not_running" {{ $status == 'not_running' ? 'selected' : '' }}>Tidak Jalan</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm premium-card overflow-hidden">
        <div class="card-header border-0 bg-white px-3 py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <!-- <div class="bg-indigo-50 p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-chart-line text-indigo-600 fs-5"></i>
                    </div> -->
                    <div>
                        <h5 class="mb-0 fw-800 text-dark tracking-tight" style="font-size: 1rem;">TABEL PERFORMA IKLAN (ADS)</h5>
                        <p class="mb-0 text-muted smaller fw-600" style="font-size: 0.75rem;">Pemantauan analisis metrik secara real-time</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary d-flex align-items-center gap-2 fw-800 shadow-sm" id="btn-tambah-iklan" style="font-size: 0.65rem; border-radius: 20px; padding: 6px 15px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
                        <i class="fas fa-plus"></i> TAMBAH IKLAN
                    </button>
                    <span class="badge bg-indigo-50 text-indigo-600 border border-indigo-100 px-3 py-2 rounded-pill fw-700" style="font-size: 0.65rem;">
                        <i class="fas fa-sync-alt fa-spin me-1"></i> AUTO-SYNC ACTIVE
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="ads-performance-table" style="width: 100%; table-layout: fixed; min-width: 1250px;">
                    <thead>
                        <tr class="bg-indigo-950 text-white text-uppercase text-center border-0" style="font-size: 0.6rem; letter-spacing: 0.05em;">
                            <th class="py-2" style="width:40px;">No</th>
                            <th class="py-2 text-start ps-3" style="width: 150px;">DAFTAR ADS</th>
                            <th class="py-2" style="width: 110px;">TANGGAL KELAS / ADS</th>
                            <th class="py-2" style="width: 50px;">LEADS</th>
                            <th class="py-2" style="width: 60px;">CLOSING</th>
                            <th class="py-2" style="width: 110px;">TOTAL OMSET</th>
                            <th class="py-2" style="width: 80px;">CONV (≥ 30%)</th>
                            <th class="py-2" style="width: 80px;">CPA (≤ 10%)</th>
                            <th class="py-2" style="width: 75px;">ROAS (≥ 5X)</th>
                            <th class="py-2" style="width: 85px;">CPL (≤ 30k)</th>
                            <th class="py-2" style="width: 70px;">CTR (≥ 2%)</th>
                            <th class="py-2" style="width: 90px;">BUDGET Terpakai(RP)</th>
                            <th class="py-2" style="width: 105px;">REALISASI (RP)</th>
                            <th class="py-2" style="width: 100px;">PENGAJUAN BUDGET (RP)</th>
                            <th class="py-2 pe-3" style="width: 50px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="ads-tbody" class="ads-tbody" style="font-size: 0.75rem;">
                        @foreach($adsPerformances as $i => $item)
                        <tr class="ads-row transition-all border-bottom" data-id="{{ $item->id }}" data-bulan="{{ $item->bulan }}" style="background: #ffffff;">
                            <td class="text-center text-muted fw-800" style="font-size: 0.7rem;">{{ $i + 1 }}</td>
                            <td class="text-start ps-3">
                                <span class="fw-800 text-indigo-950 d-block text-truncate lh-tight mb-1" style="max-width: 125px; font-size: 0.75rem;" title="{{ $item->manual_name ?: ($item->kelas->nama_kelas ?? 'N/A') }}">
                                    {{ $item->manual_name ?: ($item->kelas->nama_kelas ?? 'N/A') }}
                                </span>
                                <div class="d-flex align-items-center mt-1">
                                    <label class="ads-switch">
                                        <input type="checkbox" name="is_running" class="ads-status-toggle ads-auto-save" data-id="{{ $item->id }}" {{ $item->is_running ? 'checked' : '' }}>
                                        <span class="ads-slider"></span>
                                    </label>
                                    <span class="ms-2 fw-700 status-label {{ $item->is_running ? 'text-success' : 'text-muted' }}" style="font-size: 0.6rem;">
                                        {{ $item->is_running ? 'JALAN' : 'OFF' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-1 text-center">
                                <input type="date" name="tanggal_kelas" class="jakarta-input manual-input ads-auto-save text-center w-100 px-0 mb-1" style="font-size: 0.7rem; height: 24px;" value="{{ $item->tanggal_kelas }}">
                                <input type="date" name="tanggal_set" class="jakarta-input manual-input ads-auto-save text-center w-100 px-0" style="font-size: 0.7rem; height: 24px;" value="{{ $item->tanggal_set }}">
                            </td>
                            <td class="px-1"><input type="number" name="total_leads" class="jakarta-input text-center w-100 {{ $item->kelas_id ? 'bg-light' : 'ads-auto-save' }}" placeholder="0" value="{{ $item->total_leads }}" {{ $item->kelas_id ? 'readonly' : '' }}></td>
                            <td class="px-1"><input type="number" name="jumlah_closing" class="jakarta-input text-center w-100 {{ $item->kelas_id ? 'bg-light' : 'ads-auto-save' }}" placeholder="0" value="{{ $item->jumlah_closing }}" {{ $item->kelas_id ? 'readonly' : '' }}></td>
                            <td class="px-1">
                                <div class="jakarta-group-highlight {{ $item->kelas_id ? 'bg-light' : 'manual-input' }}">
                                    <span class="unit ps-1 text-indigo-700 fw-bold">Rp</span>
                                    <input type="text" name="omset" class="text-center fw-800 text-indigo-800 ads-number-format {{ $item->kelas_id ? '' : 'ads-auto-save' }}" placeholder="0" value="{{ number_format($item->omset, 0, ',', '.') }}" {{ $item->kelas_id ? 'readonly' : '' }} style="border:none; background:transparent; width:100%; outline:none;">
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group">
                                    <input type="text" name="conv_rate" class="ads-input-target bg-light ads-clean-decimal" data-target="30" data-type="min" placeholder="0" value="{{ (float)$item->conv_rate }}" readonly>
                                    <span class="unit">%</span>
                                </div>
                            </td>
                             <td class="px-1">
                                <div class="jakarta-group">
                                    <input type="text" name="cpa" class="ads-input-target bg-light ads-clean-decimal" data-target="10" data-type="max" placeholder="0" value="{{ (float)$item->cpa }}" readonly>
                                    <span class="unit">%</span>
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group">
                                    <input type="text" name="roas" class="ads-input-target bg-light text-center ads-clean-decimal" data-target="5" data-type="min" placeholder="0" value="{{ (float)$item->roas }}" readonly style="border:none; width:100%; font-size:0.75rem; color:#1e1b4b;">
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group">
                                    <span class="unit ps-1">Rp</span>
                                    <input type="text" name="cpl" class="ads-input-target bg-light text-center ads-number-format" data-target="30000" data-type="max" placeholder="0" value="{{ number_format($item->cpl, 0, ',', '.') }}" readonly>
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group manual-input">
                                    <input type="text" name="ctr" class="ads-input-target ads-auto-save ads-clean-decimal" data-target="2" data-type="min" placeholder="0" value="{{ (float)$item->ctr }}">
                                    <span class="unit">%</span>
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group manual-input">
                                    <span class="unit ps-1">Rp</span>
                                    <input type="text" name="budget_iklan" class="ads-auto-save text-center ads-number-format" placeholder="0" value="{{ number_format($item->budget_iklan, 0, ',', '.') }}">
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group bg-light">
                                    <span class="unit ps-1 text-danger fw-bold">Rp</span>
                                    <input type="text" class="text-center fw-800 text-danger ads-realisasi-input" placeholder="0" value="{{ number_format($item->realisasi ?? 0, 0, ',', '.') }}" readonly style="border:none; background:transparent; width:100%; outline:none;">
                                </div>
                            </td>
                            <td class="px-1">
                                <div class="jakarta-group manual-input">
                                    <span class="unit ps-1">Rp</span>
                                    <input type="text" name="pengajuan_budget" class="ads-auto-save text-center ads-number-format" placeholder="0" value="{{ number_format($item->pengajuan_budget, 0, ',', '.') }}">
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-link text-danger p-0 btn-delete-ads" data-id="{{ $item->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-top-2">
                        <tr class="bg-indigo-950 text-white fw-800" style="font-size: 0.75rem;">
                            <td colspan="3" class="py-3 text-center text-uppercase" style="letter-spacing: 1px;">Total Keseluruhan</td>
                            <td class="text-center px-1"><span id="total-leads">{{ number_format($adsPerformances->sum('total_leads'), 0, ',', '.') }}</span></td>
                            <td class="text-center px-1"><span id="total-closing">{{ number_format($adsPerformances->sum('jumlah_closing'), 0, ',', '.') }}</span></td>
                            <td class="text-center px-1">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="me-1">Rp</span>
                                    <span id="total-omset">{{ number_format($adsPerformances->sum('omset'), 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td colspan="5" class="bg-indigo-900 opacity-50"></td>
                            <td class="text-center px-1">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="me-1">Rp</span>
                                    <span id="total-budget">{{ number_format($adsPerformances->sum('budget_iklan'), 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="text-center px-1 text-warning">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="me-1">Rp</span>
                                    <span id="total-realisasi">{{ number_format($adsPerformances->sum('realisasi'), 0, ',', '.') }}</span>
                                </div>
                            </td>
                             <td class="text-center px-1 pe-2">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="me-1">Rp</span>
                                    <span id="total-pengajuan">{{ number_format($adsPerformances->sum('pengajuan_budget'), 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="bg-indigo-950"></td>
                        </tr>
                        <tr class="bg-white text-dark fw-800" style="font-size: 0.75rem;">
                            <td colspan="11" class="py-2 text-end pe-4 text-muted" style="letter-spacing: 0.5px; border-right: 1px solid #e2e8f0;">SISA BUDGET (REALISASI - BUDGET TERPAKAI)</td>
                            <td colspan="2" class="text-center px-1">
                                <div class="d-flex align-items-center justify-content-center py-2" style="border-radius: 8px; background: #f8fafc; border: 1.5px dashed #e2e8f0;">
                                    <span class="me-1 text-primary">Rp</span>
                                    <span id="total-sisa-budget" class="fs-6 text-primary">{{ number_format(($adsPerformances->sum('realisasi') - $adsPerformances->sum('budget_iklan')), 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="bg-light"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light border-0 text-center py-3">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Semua data disimpan secara otomatis pada setiap perubahan.</small>
        </div>
    </div>

    {{-- Removed Template Row --}}
</div>

<script>
    $(document).ready(function() {
        console.log("Ads Performance ready inside content.");
        const bulan = "{{ $bulan }}";
        const tahun = "{{ $tahun }}";
        const csrfToken = "{{ csrf_token() }}";

        // Removed calculateRoas

        function updateColor(input, customVal = null) {
            const $input = $(input);
            let val = customVal !== null ? parseFloat(customVal) : parseFloat($input.val());
            
            const target = parseFloat($input.data('target'));
            const type = $input.data('type');
            if (isNaN(val)) { $input.removeClass('met-target missed-target').closest('.jakarta-group').removeClass('met-target missed-target'); return; }
            
            const isMet = (type === 'min') ? (val >= target) : (val <= target);
            
            if (isMet) { 
                $input.addClass('met-target').removeClass('missed-target'); 
                $input.closest('.jakarta-group').addClass('met-target').removeClass('missed-target');
            } else { 
                $input.addClass('missed-target').removeClass('met-target'); 
                $input.closest('.jakarta-group').addClass('missed-target').removeClass('met-target');
            }
        }

        function formatNumber(n) {
            return new Intl.NumberFormat('id-ID').format(Math.round(n));
        }

        function unformatNumber(s) {
            return s.toString().replace(/\./g, '').replace(/,/g, '.');
        }

        function formatCleanDecimal(n) {
            // Converts to float to drop trailing .00, then formats with ID locale but auto-decimals
            let val = parseFloat(parseFloat(n).toFixed(2));
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val);
        }

        function calculateMetrics($row) {
            const leads = parseFloat($row.find('input[name="total_leads"]').val()) || 0;
            const closing = parseFloat($row.find('input[name="jumlah_closing"]').val()) || 0;
            const budget = parseFloat(unformatNumber($row.find('input[name="budget_iklan"]').val())) || 0;
            const omset = parseFloat(unformatNumber($row.find('input[name="omset"]').val())) || 0;

            // 1. Conversion Rate
            let convRate = 0;
            if (leads > 0) convRate = (closing / leads) * 100;
            const $convInput = $row.find('input[name="conv_rate"]');
            $convInput.val(formatCleanDecimal(convRate));
            updateColor($convInput[0], convRate);

            // 2. CPA
            let cpa = 0;
            if (omset > 0) cpa = (budget / omset) * 100;
            const $cpaInput = $row.find('input[name="cpa"]');
            $cpaInput.val(formatCleanDecimal(cpa));
            updateColor($cpaInput[0], cpa);

            // 3. ROAS
            let roas = 0;
            if (budget > 0) roas = omset / budget;
            const $roasInput = $row.find('input[name="roas"]');
            $roasInput.val(formatCleanDecimal(roas));
            updateColor($roasInput[0], roas);
            
            // 4. CPL
            let cpl = 0;
            if (leads > 0) cpl = budget / leads;
            const $cplInput = $row.find('input[name="cpl"]');
            $cplInput.val(formatNumber(cpl));
            updateColor($cplInput[0], cpl);
            
            // Save calculated values to backend 
            // Metrics are now saved via the main saveAdsRow which is debounced
            debouncedSave($convInput[0]);
            debouncedSave($cpaInput[0]);
            debouncedSave($roasInput[0]);
            debouncedSave($cplInput[0]);

        }

        let saveTimeout = null;
        function debouncedSave(input) {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveAdsRow(input);
            }, 500);
        }

        function updateOverallTotals() {
            let totalLeads = 0;
            let totalClosing = 0;
            let totalOmset = 0;
            let totalBudget = 0;
            let totalRealisasi = 0;
            let totalPengajuan = 0;

            $('#ads-tbody tr').each(function() {
                const $row = $(this);
                totalLeads += parseFloat($row.find('input[name="total_leads"]').val()) || 0;
                totalClosing += parseFloat($row.find('input[name="jumlah_closing"]').val()) || 0;
                totalOmset += parseFloat(unformatNumber($row.find('input[name="omset"]').val())) || 0;
                totalBudget += parseFloat(unformatNumber($row.find('input[name="budget_iklan"]').val())) || 0;
                totalRealisasi += parseFloat(unformatNumber($row.find('.ads-realisasi-input').val())) || 0;
                totalPengajuan += parseFloat(unformatNumber($row.find('input[name="pengajuan_budget"]').val())) || 0;
            });

            $('#total-leads').text(formatNumber(totalLeads));
            $('#total-closing').text(formatNumber(totalClosing));
            $('#total-omset').text(formatNumber(totalOmset));
            $('#total-budget').text(formatNumber(totalBudget));
            $('#total-realisasi').text(formatNumber(totalRealisasi));
            $('#total-pengajuan').text(formatNumber(totalPengajuan));

            // Sisa Budget Calculation
            const sisaBudget = totalRealisasi - totalBudget;
            const $sisaElement = $('#total-sisa-budget');
            $sisaElement.text(formatNumber(sisaBudget));
            
            if (sisaBudget < 0) {
                $sisaElement.addClass('text-danger').removeClass('text-primary');
                $sisaElement.prev().addClass('text-danger').removeClass('text-primary');
            } else {
                $sisaElement.addClass('text-primary').removeClass('text-danger');
                $sisaElement.prev().addClass('text-primary').removeClass('text-danger');
            }
        }

        function saveAdsRow(input) {
            const $input = $(input);
            const $row = $input.closest('tr');
            let id = $row.attr('data-id') || $row.data('id');
            const field = $input.attr('name');
            let value = $input.val();

            if ($input.attr('type') === 'checkbox') {
                value = $input.is(':checked') ? 1 : 0;
            } else if ($input.hasClass('ads-number-format') || $input.hasClass('ads-clean-decimal')) {
                value = unformatNumber(value);
            }

            if (!field) return;

            const rowBulan = $row.attr('data-bulan') || $row.data('bulan') || bulan;

            $.post("{{ route('admin.ads-activity.update-ads') }}", {
                _token: csrfToken, 
                id: id || '', 
                field: field, 
                value: value, 
                bulan: rowBulan, 
                tahun: tahun
            }).done(function(res) {
                if (res.id) { 
                    $row.attr('data-id', res.id);
                    $row.data('id', res.id);
                    $row.find('.btn-delete-ads').attr('data-id', res.id).data('id', res.id);
                    $row.find('.ads-status-toggle').attr('data-id', res.id).data('id', res.id);
                }
            });
        }

        // Delegation
        $(document).on('input', '.ads-number-format', function() {
            let val = unformatNumber($(this).val());
            if (val !== '') {
                $(this).val(formatNumber(val));
            }
        });

        $(document).on('input', '.ads-input-target', function() { 
            let val = $(this).val();
            if ($(this).hasClass('ads-number-format') || $(this).hasClass('ads-clean-decimal')) val = unformatNumber(val);
            updateColor(this, val); 
        });

        $('#btn-tambah-iklan').on('click', function() {
            const tableBody = $('#ads-tbody');
            const rowCount = tableBody.find('tr').length;
            
            const newRow = `
                <tr class="ads-row transition-all border-bottom" data-id="" data-bulan="${bulan}" style="background: #ffffff;">
                    <td class="text-center text-muted fw-800" style="font-size: 0.7rem;">${rowCount + 1}</td>
                    <td class="text-start ps-3">
                        <input type="text" name="manual_name" class="jakarta-input ads-auto-save w-100" placeholder="Ketik Nama Iklan..." style="font-size: 0.75rem;">
                        <div class="d-flex align-items-center mt-1">
                            <label class="ads-switch">
                                <input type="checkbox" name="is_running" class="ads-status-toggle ads-auto-save" data-id="" checked>
                                <span class="ads-slider"></span>
                            </label>
                            <span class="ms-2 fw-700 status-label text-success" style="font-size: 0.6rem;">JALAN</span>
                        </div>
                    </td>
                    <td class="px-1 text-center">
                        <input type="date" name="tanggal_kelas" class="jakarta-input manual-input ads-auto-save text-center w-100 px-0 mb-1" style="font-size: 0.7rem; height: 24px;">
                        <input type="date" name="tanggal_set" class="jakarta-input manual-input ads-auto-save text-center w-100 px-0" style="font-size: 0.7rem; height: 24px;">
                    </td>
                    <td class="px-1"><input type="number" name="total_leads" class="jakarta-input text-center w-100 ads-auto-save" placeholder="0"></td>
                    <td class="px-1"><input type="number" name="jumlah_closing" class="jakarta-input text-center w-100 ads-auto-save" placeholder="0"></td>
                    <td class="px-1">
                        <div class="jakarta-group manual-input">
                            <span class="unit ps-1 text-indigo-700 fw-bold">Rp</span>
                            <input type="text" name="omset" class="text-center fw-800 text-indigo-800 ads-number-format ads-auto-save" placeholder="0" style="border:none; background:transparent; width:100%; outline:none;">
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group bg-light">
                            <input type="text" name="conv_rate" class="ads-input-target ads-clean-decimal" data-target="30" data-type="min" placeholder="0" readonly>
                            <span class="unit">%</span>
                        </div>
                    </td>
                     <td class="px-1">
                        <div class="jakarta-group bg-light">
                            <input type="text" name="cpa" class="ads-input-target ads-clean-decimal" data-target="10" data-type="max" placeholder="0" readonly>
                            <span class="unit">%</span>
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group bg-light">
                            <input type="text" name="roas" class="ads-input-target text-center ads-clean-decimal" data-target="5" data-type="min" placeholder="0" readonly style="border:none; width:100%; font-size:0.75rem; color:#1e1b4b;">
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group bg-light">
                            <span class="unit ps-1">Rp</span>
                            <input type="text" name="cpl" class="ads-input-target text-center ads-number-format" data-target="30000" data-type="max" placeholder="0" readonly>
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group manual-input">
                            <input type="text" name="ctr" class="ads-input-target ads-auto-save ads-clean-decimal" data-target="2" data-type="min" placeholder="0">
                            <span class="unit">%</span>
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group manual-input">
                            <span class="unit ps-1">Rp</span>
                            <input type="text" name="budget_iklan" class="ads-auto-save text-center ads-number-format" placeholder="0">
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group bg-light">
                            <span class="unit ps-1 text-danger fw-bold">Rp</span>
                            <input type="text" class="text-center fw-800 text-danger ads-realisasi-input" placeholder="0" readonly style="border:none; background:transparent; width:100%; outline:none;">
                        </div>
                    </td>
                    <td class="px-1">
                        <div class="jakarta-group manual-input">
                            <span class="unit ps-1">Rp</span>
                            <input type="text" name="pengajuan_budget" class="ads-auto-save text-center ads-number-format" placeholder="0">
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger p-0 btn-delete-ads" data-id="">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            tableBody.append(newRow);
            renumberRows();
        });

        function renumberRows() {
            $('#ads-tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Hapus Iklan (Moved to top of handlers for reliability)
        $(document).on('click', 'button.btn-delete-ads', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const $row = $btn.closest('tr');
            
            // Try to find ID from any possible source
            let id = $btn.attr('data-id') || $btn.data('id') || $row.attr('data-id') || $row.data('id');
            
            console.log("Deleting Record ID:", id);

            if (!id || id === "" || id === "0" || id === "null") {
                // If it's just a local UI row that hasn't saved yet
                $row.fadeOut(200, function() {
                    $(this).remove();
                    renumberRows();
                    updateOverallTotals();
                });
                return;
            }

            // If it's a real record, hide it immediately for responsiveness
            $row.hide(); 

            $.ajax({
                url: `{{ url('admin/ads-activity/ads') }}/${id}`,
                type: 'POST',
                data: { 
                    _token: csrfToken, 
                    _method: 'DELETE' 
                },
                success: function() {
                    // Success! Remove from DOM completely
                    $row.remove();
                    renumberRows();
                    updateOverallTotals();
                },
                error: function(xhr) {
                    // Oops, put it back
                    $row.show();
                    $row.css('opacity', '1');
                    alert("Gagal menghapus data dari server. Silakan refresh halaman.");
                    console.error("Delete Error:", xhr.responseText);
                }
            });
        });

        // Auto Save logic - Separated between rapid input and final change
        $(document).on('input', '.ads-auto-save', function() {
            const $row = $(this).closest('tr');
            const triggerFields = ['budget_iklan', 'pengajuan_budget', 'total_leads', 'jumlah_closing', 'omset'];
            
            if (triggerFields.includes(this.name)) {
                calculateMetrics($row);
                updateOverallTotals();
                debouncedSave(this);
            }
        });

        // Save for text, date, and other fields on CHANGE (blur/finish typing)
        $(document).on('change', '.ads-auto-save', function() {
            const $row = $(this).closest('tr');
            const triggerFields = ['budget_iklan', 'pengajuan_budget', 'total_leads', 'jumlah_closing', 'omset'];
            
            if (this.name === 'is_running') {
                const isChecked = $(this).is(':checked');
                const $label = $(this).closest('.d-flex').find('.status-label');
                if (isChecked) {
                    $label.text('JALAN').addClass('text-success').removeClass('text-muted');
                } else {
                    $label.text('OFF').addClass('text-muted').removeClass('text-success');
                }
            }

            if (!triggerFields.includes(this.name) || this.name === 'is_running') {
                saveAdsRow(this); // Save immediately on change/blur
            }
        });

        // Initial re-run to ensure all colors are correct
        $('.ads-row').each(function() { 
            calculateMetrics($(this));
        });
        updateOverallTotals();
    });
</script>

<style>
    :root {
        --jakarta: 'Plus Jakarta Sans', sans-serif;
        --vibrant-indigo: #6366f1;
        --vibrant-violet: #8b5cf6;
        --indigo-950: #1e1b4b;
        --indigo-900: #312e81;
    }

    body { background-color: #f8fafc; font-family: var(--jakarta); overflow-x: hidden; }
    
    :root {
        --jakarta: 'Plus Jakarta Sans', sans-serif;
        --indigo-950: #0f172a;
        --indigo-900: #1e1b4b;
        --indigo-600: #4f46e5;
    }

    body { background-color: #f8fafc; font-family: var(--jakarta); overflow-x: hidden; }
    
    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .fw-500 { font-weight: 500; }
    .ls-1 { letter-spacing: 1px; }
    .tracking-tight { letter-spacing: -0.025em; }
    
    .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }

    .jakarta-input {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 6px;
        font-family: var(--jakarta);
        font-weight: 800;
        font-size: 0.7rem;
        padding: 4px 6px;
        color: var(--indigo-950);
        transition: all 0.2s;
    }
    .jakarta-input:focus { border-color: var(--indigo-600); outline: none; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

    .jakarta-group {
        display: flex; align-items: center;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .jakarta-group:focus-within { border-color: var(--indigo-600); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .jakarta-group input {
        border: none; background: transparent; width: 100%; text-align: center;
        font-weight: 800; font-size: 0.75rem; padding: 4px 0; color: var(--indigo-950); outline: none;
    }
    .jakarta-group .unit { font-size: 0.6rem; font-weight: 800; color: #94a3b8; padding: 0 4px; }

    .jakarta-group-highlight {
        background: #eff6ff;
        border: 1.5px solid #bfdbfe;
        border-radius: 6px;
        display: flex; align-items: center;
    }
    .jakarta-group-highlight input {
        border: none; background: transparent; width: 100%; text-align: center;
        font-weight: 800; font-size: 0.75rem; padding: 4px 0; color: #1e40af; outline: none;
    }

    /* Target Visuals */
    .ads-input-target.met-target { background: #dcfce7 !important; color: #15803d !important; border-color: #86efac !important; }
    .ads-input-target.missed-target { background: #fee2e2 !important; color: #b91c1c !important; border-color: #fca5a5 !important; }

    .manual-input {
        background-color: #fff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.1);
    }
    .manual-input:focus-within, .jakarta-input.manual-input:focus {
        border-color: #4338ca !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
    }
    
    .bg-indigo-950 { background-color: #0f172a; }
    .bg-indigo-600 { background-color: #4f46e5; }
    .bg-indigo-50 { background-color: #eff6ff; }
    .text-indigo-600 { color: #4f46e5; }
    .border-indigo-100 { border-color: #e0e7ff !important; }

    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: var(--indigo-600) #f1f5f9;
        border-radius: 0 0 12px 12px;
    }
    .table-responsive::-webkit-scrollbar { height: 8px; display: block; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; }
    .table-responsive::-webkit-scrollbar-thumb { background-color: var(--indigo-600); border-radius: 20px; border: 2px solid #f1f5f9; }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 1; }
    
    /* Cleanup Redundancy and Finalize Header Styles */
    .custom-select-group { 
        background-color: white; 
        border-radius: 12px; 
        height: 40px; 
        border: 1px solid #e2e8f0; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        padding: 0 4px;
    }
    .custom-select-group:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .custom-select-group .label { 
        font-size: 0.65rem; 
        font-weight: 800; 
        color: #64748b; 
        padding: 0 12px;
        white-space: nowrap;
        border-right: 1px solid #f1f5f9;
        height: 60%;
        display: flex;
        align-items: center;
        letter-spacing: 0.5px;
    }
    .custom-select-group select { 
        border: none; 
        padding: 0 12px 0 8px;
        font-weight: 800; 
        color: #1e293b; 
        outline: none; 
        background: transparent;
        font-family: var(--jakarta); 
        font-size: 0.75rem; 
        cursor: pointer;
        height: 100%;
    }
    .hover-elevate {
        transition: all 0.2s ease;
    }
    .hover-elevate:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3) !important;
    }

    .btn-delete-ads {
        position: relative;
        z-index: 5;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-delete-ads:hover {
        transform: scale(1.2);
    }

    input::-webkit-outer-spin-button, 
    input::-webkit-inner-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 1; }

    /* Vertical Borders for Table */
    #ads-performance-table {
        border-collapse: collapse;
    }
    #ads-performance-table th, 
    #ads-performance-table td {
        border-right: 1px solid #e2e8f0;
    }
    #ads-performance-table th:last-child, 
    #ads-performance-table td:last-child {
        border-right: none;
    }
    #ads-performance-table thead th {
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }
    #ads-performance-table thead th:last-child {
        border-right: none;
    }
    #ads-performance-table tfoot td {
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }
    #ads-performance-table tfoot td:last-child {
        border-right: none;
    }

    /* Toggle Switch Styles */
    .ads-switch {
        position: relative;
        display: inline-block;
        width: 32px;
        height: 16px;
    }
    .ads-switch input { opacity: 0; width: 0; height: 0; }
    .ads-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e2e8f0;
        transition: .3s;
        border-radius: 18px;
    }
    .ads-slider:before {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }
    input:checked + .ads-slider { background-color: #3b82f6; }
    input:checked + .ads-slider:before { transform: translateX(16px); }
</style>
@endsection
