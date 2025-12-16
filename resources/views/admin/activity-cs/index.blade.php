@extends('layouts.masteradmin')

@section('content')
<div class="container my-4">
    <h4 class="mb-3 text-center text-primary">📅 MONITORING DAILY ACTIVITY CS</h4>

    <form id="filter-form" action="{{ route('admin.activity-cs.index') }}" method="GET" class="mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                         <label class="form-label fw-bold">Pilih CS:</label>
                         <select name="cs_id" class="form-control" onchange="this.form.submit()">
                            @foreach($csList as $cs)
                                <option value="{{ $cs->id }}" {{ $csId == $cs->id ? 'selected' : '' }}>
                                    {{ $cs->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Tanggal:</label>
                        <input type="date" name="tanggal" class="form-control" 
                               value="{{ $tanggal }}"
                               onchange="this.form.submit()">
                    </div>
                    <div class="col-md-5 mb-3 text-md-right">
                         <!-- Export PDF -->
                        <a href="{{ route('admin.activity-cs.viewPdfBulanan', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m'), 'cs_id' => $csId]) }}" 
                           class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export Laporan Bulanan (PDF)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($csId)
        @foreach($activities as $kategoriId => $list)
            <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span>{{ $list->first()->kategori->nama ?? 'Tanpa Kategori' }}</span>

                @if(($list->first()->kategori->nama ?? '') === 'Aktivitas Merawat Customer')
                <small class="fst-italic">
                    🌟 Aktivitas ini fleksibel
                </small>
                @endif
            </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">No</th>
                                <th style="width:35%">Aktivitas</th>
                                <th style="width:10%" class="text-center">Target Daily</th>
                                <th style="width:10%" class="text-center">Target Bulan</th>
                                <th style="width:10%" class="text-center">Bobot</th>
                                <th style="width:15%" class="text-center">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list as $i => $act)
                                <tr>
                                    <td class="text-center">{{ $i+1 }}</td>
                                    <td>{{ $act->nama }}</td>
                                    <td class="text-center">{{ number_format($act->target_daily, 0) }}</td>
                                    <td class="text-center">{{ number_format($act->target_bulanan, 0) }}</td>
                                    <td class="text-center">{{ $act->bobot }}</td>
                                    <td class="text-center">
                                        {{-- Tampilkan Realisasi (Read-Only) --}}
                                        <input type="number" 
                                               class="form-control form-control-sm text-center"
                                               value="{{ $daily[$act->id] ?? 0 }}"
                                               disabled
                                               style="background-color: #f8f9fa; font-weight: bold; color: #333;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        
        {{-- ================= KPI BULANAN ================= --}}
        <div class="card shadow-lg border-0 mt-5">
            <div class="card-header bg-gradient bg-success text-white text-center fw-bold fs-5">
                📊 PREDIKSI KPI BULANAN ({{ \Carbon\Carbon::parse($tanggal)->format('F Y') }})
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:5%">No</th>
                            <th style="width:30%">Aktivitas</th>
                            <th style="width:15%">⚖️ Bobot</th>
                            <th style="width:15%">📈 Presentase</th>
                            <th style="width:20%">⭐ Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpiData as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td class="text-left fw-semibold" style="text-align: left;">{{ $row['nama'] }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        {{ $row['bobot'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark px-3 py-2">
                                        {{ $row['persentase'] }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary px-3 py-2">
                                        {{ number_format($row['nilai'],2) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="table-success fw-bold">
                            <td colspan="2" class="text-right" style="text-align: right;">TOTAL</td>
                            <td>
                                <span class="badge bg-dark px-3 py-2">{{ $totalBobot }}</span>
                            </td>
                            <td>—</td>
                            <td>
                                <span class="badge bg-danger px-3 py-2">{{ number_format($totalNilai,2) }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    @else
        <div class="alert alert-warning text-center">
            Belum ada CS yang dipilih atau Anda tidak memiliki akses ke data CS.
        </div>
    @endif

</div>
@endsection
