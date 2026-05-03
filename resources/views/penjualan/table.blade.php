<div class="col-12 col-lg-7">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 hover-lift">
        <div class="card-header bg-gradient bg-primary text-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-bold fs-5">
                    <i class="fa-solid fa-ranking-star me-2"></i> Pencapaian Target Per CS
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Tahun -->
                    <select name="tahun" id="tahun" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold" style="width: 85px; font-size:0.75rem;">
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                            <option value="{{ $y }}" {{ request('tahun', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <!-- Bulan -->
                    <select name="bulan" id="bulan" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold" style="width: 120px; font-size:0.75rem;">
                        <option value="all" {{ request('bulan') == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Kategori Program -->
                    <select name="type" id="type" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold" style="width: 160px; font-size:0.75rem;">
                        <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>Semua Program</option>
                        <option value="mbc"  {{ request('type') == 'mbc'  ? 'selected' : '' }}>MBC</option>
                        <option value="m1t"  {{ request('type') == 'm1t'  ? 'selected' : '' }}>M1T</option>
                    </select>


                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 custom-target-table" style="font-size: 1rem;">
                    <thead class="table-secondary text-dark shadow-sm">
                        <tr>
                            <th class="text-center py-3" style="width: 50px;">No</th>
                            <th class="py-3">Nama CS</th>
                            <th class="text-end py-3 px-3">Pencapaian</th>
                            <th class="text-end py-3 px-3">Target</th>
                            <th class="text-center py-3" style="width: 150px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesData as $index => $sales)
                            @php
                                $isTercapai = $sales['realisasi'] >= 100;
                                $showBreakdown = (request('type', 'all') === 'all') && (!($sales['is_spp_row'] ?? false)) && (($sales['mbc_nominal'] ?? 0) > 0 || ($sales['m1t_nominal'] ?? 0) > 0);
                            @endphp
                             <tr class="target-row transition-all position-relative {{ ($sales['is_spp_row'] ?? false) ? 'bg-light' : '' }}">
                                 <td class="text-center fw-medium text-muted fs-6">{{ $index + 1 }}</td>
                                 <td class="fw-bold">
                                     <div class="d-flex align-items-center">
                                         @if($sales['is_spp_row'] ?? false)
                                             <div class="avatar bg-soft-info text-info fw-bold rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem; background-color: #e0f7fa;">
                                                 <i class="fa-solid fa-receipt"></i>
                                             </div>
                                             <div class="d-flex flex-column">
                                                 <span class="fs-6 fw-bold text-dark">SPP M1T</span>
                                                 <small class="badge badge-light border text-muted" style="font-size: 0.65rem; width: fit-content;">AUTO</small>
                                             </div>
                                         @elseif($sales['is_lainnya_row'] ?? false)
                                             <div class="avatar bg-soft-warning text-warning fw-bold rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem; background-color: #fff8e1;">
                                                 <i class="fa-solid fa-sack-dollar"></i>
                                             </div>
                                             <div class="d-flex flex-column">
                                                 <span class="fs-6 fw-bold text-dark">Pendapatan Lainnya</span>
                                                 <small class="badge badge-light border text-muted" style="font-size: 0.65rem; width: fit-content;">MANUAL</small>
                                             </div>
                                         @else
                                             <div class="avatar bg-soft-primary text-primary fw-bold rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem; background-color: #e6f3ff;">
                                                 {{ substr($sales['nama'], 0, 1) }}
                                             </div>
                                             <span class="text-truncate fs-6 fw-bold" style="max-width: 140px; display: inline-block;">{{ $sales['nama'] }}</span>
                                         @endif
                                     </div>
                                 </td>
                                <td class="text-end px-3 fw-bold text-dark" style="font-size: 1.05rem;">
                                    Rp {{ number_format($sales['total_nominal'], 0, ',', '.') }}
                                    @if($showBreakdown)
                                        <div class="d-flex gap-2 justify-content-end flex-wrap mt-2">
                                            @if(($sales['mbc_nominal'] ?? 0) > 0)
                                                <div class="d-flex flex-column align-items-end">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge px-3 py-2 fw-semibold text-white" style="font-size:0.8rem; background:#4e73df; border-radius:20px; letter-spacing:0.3px;">
                                                            MBC: Rp {{ number_format($sales['mbc_nominal'], 0, ',', '.') }}
                                                        </span>
                                                        <button class="btn btn-link p-0 text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#mbc-detail-{{ $index }}" style="font-size: 1rem; text-decoration: none;">
                                                            <i class="fa-solid fa-circle-plus"></i>
                                                        </button>
                                                    </div>
                                                    <div class="collapse" id="mbc-detail-{{ $index }}">
                                                        <div class="mt-2 text-end bg-white border rounded p-2 shadow-sm" style="min-width: 180px;">
                                                            @foreach($sales['mbc_breakdown'] as $cName => $cNominal)
                                                                <div class="d-flex justify-content-between gap-2 border-bottom py-1" style="font-size: 0.75rem;">
                                                                    <span class="text-muted text-start">{{ $cName }}</span>
                                                                    <span class="fw-bold text-dark">Rp {{ number_format($cNominal, 0, ',', '.') }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if(($sales['m1t_nominal'] ?? 0) > 0)
                                                <span class="badge px-3 py-2 fw-semibold text-white" style="font-size:0.8rem; background:#1cc88a; border-radius:20px; letter-spacing:0.3px;">
                                                    M1T: Rp {{ number_format($sales['m1t_nominal'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                            @if(($sales['spp_nominal'] ?? 0) > 0)
                                                <span class="badge px-3 py-2 fw-semibold text-white" style="font-size:0.8rem; background:#36b9cc; border-radius:20px; letter-spacing:0.3px;">
                                                    SPP: Rp {{ number_format($sales['spp_nominal'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end px-3 text-muted fw-medium" style="font-size: 1rem;">
                                    @if(!($sales['is_spp_row'] ?? false) && !($sales['is_lainnya_row'] ?? false))
                                        Rp {{ number_format($sales['target'], 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center px-2">
                                    @if(!($sales['is_spp_row'] ?? false) && !($sales['is_lainnya_row'] ?? false))
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <span class="badge rounded-pill px-3 py-2 fw-bold mb-1 {{ $isTercapai ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.85rem;">
                                                <i class="fa-solid {{ $isTercapai ? 'fa-check-circle' : 'fa-hourglass-start' }} me-1"></i>
                                                {{ $isTercapai ? 'Tercapai' : 'Belum' }}
                                            </span>
                                            
                                            <div class="w-100 px-2 mt-2">
                                                <div class="progress rounded-pill bg-light" style="height: 7px;">
                                                    <div class="progress-bar rounded-pill {{ $isTercapai ? 'bg-success' : 'bg-warning' }} progress-bar-animated" 
                                                         role="progressbar" 
                                                         style="width: {{ min(100, $sales['realisasi']) }}%;" 
                                                         aria-valuenow="{{ min(100, $sales['realisasi']) }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1 fw-bold" style="font-size:0.85rem;">{{ $sales['realisasi'] }}%</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small italic">{{ ($sales['is_lainnya_row'] ?? false) ? 'Manual/Income' : 'Manual/Monthly' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted fst-italic text-center py-4">
                                    <i class="fa-regular fa-folder-open mb-2 fs-3 text-secondary opacity-50 block"></i><br>
                                    Data pencapaian target belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold border-top-2">
                        @php
                            // Hitung total pencapaian dari semua SalesData
                            $totalPencapaianAll = collect($salesData)->sum('total_nominal');
                            $totalTargetAll = collect($salesData)->sum('target');
                            $totalPersentaseAll = $totalTargetAll > 0 ? min(100, round(($totalPencapaianAll / $totalTargetAll) * 100)) : 0;
                            $isTotalTercapai = $totalPersentaseAll >= 100;
                        @endphp
                        <tr>
                            <td colspan="2" class="text-center py-3 fs-6">TOTAL KESELURUHAN</td>
                            <td class="text-end px-3 py-3 text-dark fs-6">Rp {{ number_format($totalPencapaianAll, 0, ',', '.') }}</td>
                            <td class="text-end px-3 py-3 text-dark fs-6">Rp {{ number_format($totalTargetAll, 0, ',', '.') }}</td>
                            <td class="text-center px-2 py-3">
                                <span class="badge rounded-pill {{ $isTotalTercapai ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 fs-6 shadow-sm">
                                    {{ $totalPersentaseAll }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <!-- SALES PERFORMANCE CHART -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 hover-lift">
        <div class="card-header bg-gradient bg-indigo text-white py-3 border-0" style="background: linear-gradient(135deg, #6610f2 0%, #4e73df 100%) !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-bold fs-5">
                    <i class="fa-solid fa-chart-line me-2"></i> Grafik Penjualan CS Per Bulan
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small">Pilih Tahun:</span>
                    <select name="tahun" id="select_tahun_chart" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold" style="width: 90px; font-size:0.75rem;">
                        @php $currentYear = date('Y'); @endphp

                        @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                            <option value="{{ $y }}" {{ request('tahun', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div style="height: 500px; position: relative;">
                <canvas id="salesCsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof initSalesChart === 'function') {
        initSalesChart(@json($chartDataPerCs));
    } else {
        // Fallback if script not yet loaded
        window.pendingChartData = @json($chartDataPerCs);
    }
</script>


@php
   $targetOmset = [
       1 => 125000000, 2 => 125000000, 3 => 125000000, 4 => 125000000,
       5 => 125000000, 6 => 125000000, 7 => 125000000, 8 => 125000000,
       9 => 125000000, 10 => 125000000, 11 => 125000000, 12 => 125000000
   ];
   
   
   $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
@endphp

<div class="col-12 col-lg-5">
    <!-- TARGET OMSET PANEL -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 hover-lift">
        <div class="card-header bg-gradient bg-primary text-white fw-bold text-center fs-6 py-3 border-0" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;">
            <i class="fa-solid fa-chart-line me-2"></i> Pencapaian Target Omset (Jan - Des)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 custom-target-table" style="font-size: 0.85rem;">
                    <thead class="table-secondary text-dark shadow-sm" style="position: sticky; top: 0; z-index: 5;">
                        <tr>
                            <th class="py-2 px-3">Bulan</th>
                            <th class="text-end py-2 px-3">Target</th>
                            <th class="text-end py-2 px-3">Realisasi</th>
                            <th class="text-center py-2 px-2" style="width: 80px;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthNames as $idx => $mName)
                            @php
                                $mNum = $idx + 1;
                                $t = $targetOmset[$mNum];
                                $r = $monthlyOmset[$mNum] ?? 0;
                                $tercapai = $r >= $t;
                                $persentase = $t > 0 ? min(100, round(($r / $t) * 100)) : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold px-3 py-2 text-muted">{{ $mName }}</td>
                                <td class="text-end px-3 py-2 text-dark fw-medium">Rp {{ number_format($t, 0, ',', '.') }}</td>
                                <td class="text-end px-3 py-2 text-success fw-bold">Rp {{ number_format($r, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-2">
                                    <span class="badge rounded-pill {{ $tercapai ? 'bg-success' : 'bg-warning text-dark' }} w-100 py-1" style="font-size: 0.75rem;">
                                        {{ $persentase }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- PERTUMBUHAN PESERTA M1T (GROWTH) -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 hover-lift">
        <div class="card-header bg-gradient text-white py-3 border-0" style="background: linear-gradient(135deg, #fd7e14 0%, #d63384 100%) !important;">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-2">
                <div class="fw-bold fs-6">
                    <i class="fa-solid fa-arrow-up-right-dots me-2"></i> Pertumbuhan Peserta (M1T)
                </div>
                <div>
                    <select name="m1t_tahun" id="m1t_tahun" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold" style="width: 120px; font-size:0.75rem;" onchange="applyFilters()">
                        <option value="all" {{ request('m1t_tahun') == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear; $y >= 2025; $y--)
                            <option value="{{ $y }}" {{ request('m1t_tahun', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 custom-target-table" style="font-size: 0.85rem;">
                    <thead class="table-info text-dark shadow-sm" style="position: sticky; top: 0; z-index: 5;">
                        <tr class="text-center">
                            <th class="py-2 px-3" style="width: 120px;">Bulan</th>
                            <th class="py-2 px-3">Target Siswa Aktif</th>
                            <th class="py-2 px-3">Realisasi Siswa Aktif</th>
                            <th class="py-2 px-3">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthNames as $idx => $mName)
                            @php
                                $mNum = $idx + 1;
                                $selYear = request('m1t_tahun', date('Y'));
                                $now = \Carbon\Carbon::now();
                                
                                // Jika tahun yang dipilih adalah tahun sekarang, tandai bulan setelah bulan berjalan sebagai 'future'
                                $isFuture = ($selYear != 'all' && $selYear == $now->year && $mNum > $now->month);
                                // Jika tahun yang dipilih lebih besar dari hari ini, semuanya future
                                if ($selYear != 'all' && $selYear > $now->year) $isFuture = true;
                                
                                $totalAktif = $cumulativeSMI[$mNum] ?? 0;
                                $penambahan = $monthlySMI[$mNum] ?? 0;
                            @endphp
                            
                            @php
                                $target = $targetSMI[$mNum] ?? 0;
                                $selisih = $totalAktif - $target;
                            @endphp
                            <tr class="text-center">
                                <td class="fw-bold px-3 py-2 text-muted text-start">{{ $mName }}</td>
                                <td class="px-3 py-2 fw-bold text-primary fs-6">{{ number_format($target, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 fw-bold text-dark fs-6">
                                    {{ $isFuture ? '-' : number_format($totalAktif, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 fw-bold fs-6">
                                    @if($isFuture)
                                        <span class="text-muted">-</span>
                                    @else
                                        @if($selisih > 0)
                                            <span class="text-success">+{{ number_format($selisih, 0, ',', '.') }}</span>
                                        @elseif($selisih < 0)
                                            <span class="text-danger">{{ number_format($selisih, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-success">0</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



    