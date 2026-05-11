{{-- 1. HEADER: Tabs & Filters Aligned in ONE box (Full Width) --}}
<div class="col-12 mb-2">
    <div class="d-flex justify-content-start align-items-center flex-wrap bg-white p-2 rounded-4 shadow-sm border" style="width: fit-content;">
        {{-- Tabs Navigation --}}
        <ul class="nav nav-pills gap-2" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold d-flex align-items-center gap-2" id="pills-pusat-tab" data-bs-toggle="pill" data-bs-target="#pills-pusat" type="button" role="tab" aria-controls="pills-pusat" aria-selected="true">
                    <i class="fa-solid fa-building-user"></i> CS Helas Pusat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold d-flex align-items-center gap-2" id="pills-chapter-tab" data-bs-toggle="pill" data-bs-target="#pills-chapter" type="button" role="tab" aria-controls="pills-chapter" aria-selected="false">
                    <i class="fa-solid fa-map-location-dot"></i> Chapter
                </button>
            </li>
        </ul>

        {{-- Vertical Divider --}}
        <div class="vr mx-3 text-muted opacity-25" style="height: 30px;"></div>

        {{-- Global Filters --}}
        <div class="d-flex align-items-center gap-2 pe-2">
            @include('penjualan.partials.filters')
        </div>
    </div>
</div>

<div class="col-12 col-lg-7">
    <div class="tab-content" id="pills-tabContent">
        {{-- Panel 1: CS Helas Pusat --}}
        <div class="tab-pane fade show active" id="pills-pusat" role="tabpanel" aria-labelledby="pills-pusat-tab">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 hover-lift">
                <div class="card-header bg-gradient bg-primary text-white py-3 border-0">
                    <div class="fw-bold fs-5">
                        <i class="fa-solid fa-ranking-star me-2"></i> Pendapatan CS Helas Pusat
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @include('penjualan.partials.table_body', ['data' => $salesDataPusat])
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel 2: Pendapatan Chapter --}}
        <div class="tab-pane fade" id="pills-chapter" role="tabpanel" aria-labelledby="pills-chapter-tab">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 hover-lift">
                <div class="card-header bg-gradient bg-info text-white py-3 border-0" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;">
                    <div class="fw-bold fs-5">
                        <i class="fa-solid fa-map-location-dot me-2"></i> Pendapatan Chapter
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @include('penjualan.partials.table_body', ['data' => $salesDataChapter, 'isChapter' => true])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SALES PERFORMANCE CHART -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 hover-lift">
        <div class="card-header bg-gradient bg-indigo text-white py-3 border-0" style="background: linear-gradient(135deg, #6610f2 0%, #4e73df 100%) !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-bold fs-5">
                    <i class="fa-solid fa-chart-bar me-2"></i> <span id="chart-title-text">Grafik Penjualan CS Per Bulan</span>
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
    // [USER_REQUEST] Store both datasets for dynamic switching
    window.chartDataPusat = @json($chartDataPusat);
    window.chartDataChapter = @json($chartDataChapter);

    if (typeof initSalesChart === 'function') {
        // Initial chart load based on active tab
        const activeTab = document.querySelector('#pills-tab .nav-link.active');
        const initialData = (activeTab && activeTab.id === 'pills-chapter-tab') ? window.chartDataChapter : window.chartDataPusat;
        initSalesChart(initialData);
    } else {
        window.pendingChartData = document.querySelector('#pills-chapter-tab.active') ? @json($chartDataChapter) : @json($chartDataPusat);
    }

    // [USER_REQUEST] Handle Program Filter visibility & Chart Switching based on Tab
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const pusatTab = document.getElementById('pills-pusat-tab');
        const chapterTab = document.getElementById('pills-chapter-tab');

        function updateFilterAndChart() {
            if (!typeSelect) return;
            const mbcOption = typeSelect.querySelector('option[value="mbc"]');
            const isChapter = chapterTab && chapterTab.classList.contains('active');
            
            // 1. Update Filter Options
            if (isChapter) {
                if (mbcOption) mbcOption.style.display = 'none';
                if (typeSelect.value === 'mbc') {
                    typeSelect.value = 'all';
                    applyFilters();
                }
            } else {
                if (mbcOption) mbcOption.style.display = 'block';
            }

            // 2. Update Chart Data
            if (typeof initSalesChart === 'function') {
                const newData = isChapter ? window.chartDataChapter : window.chartDataPusat;
                initSalesChart(newData);
            }

            // 3. Update Chart Title
            const titleText = document.getElementById('chart-title-text');
            if (titleText) {
                titleText.innerText = isChapter ? 'Grafik Penjualan Chapter Per Bulan' : 'Grafik Penjualan CS Per Bulan';
            }
        }

        // Listen for tab clicks
        if (pusatTab) pusatTab.addEventListener('shown.bs.tab', updateFilterAndChart);
        if (chapterTab) chapterTab.addEventListener('shown.bs.tab', updateFilterAndChart);

        // Initial check
        updateFilterAndChart();
    });
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
                                <td class="fw-bold px-3 py-2 text-muted">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $mName }}</span>
                                        @if($r > 0)
                                            <button class="btn btn-sm btn-link p-0 text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#omset-breakdown-{{ $mNum }}" aria-expanded="false" style="text-decoration: none; font-size: 1rem;">
                                                <i class="fa-solid fa-circle-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end px-3 py-2 text-dark fw-medium">Rp {{ number_format($t, 0, ',', '.') }}</td>
                                <td class="text-end px-3 py-2 text-success fw-bold">Rp {{ number_format($r, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-2">
                                    <span class="badge rounded-pill {{ $tercapai ? 'bg-success' : 'bg-warning text-dark' }} w-100 py-1" style="font-size: 0.75rem;">
                                        {{ $persentase }}%
                                    </span>
                                </td>
                            </tr>
                            @if($r > 0)
                                <tr class="collapse" id="omset-breakdown-{{ $mNum }}">
                                    <td colspan="4" class="p-0 border-0">
                                        <div class="bg-light px-3 py-2 border-start border-primary border-4 rounded-end mx-2 my-1 shadow-sm">
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 0.75rem;">
                                                <span class="text-muted"><i class="fa-solid fa-building-user me-1"></i> CS Helas Pusat:</span>
                                                <span class="fw-bold text-primary">Rp {{ number_format($monthlyOmsetByGroup[$mNum]['pusat'], 0, ',', '.') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between" style="font-size: 0.75rem;">
                                                <span class="text-muted"><i class="fa-solid fa-map-location-dot me-1"></i> Chapter:</span>
                                                <span class="fw-bold text-info">Rp {{ number_format($monthlyOmsetByGroup[$mNum]['chapter'], 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
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