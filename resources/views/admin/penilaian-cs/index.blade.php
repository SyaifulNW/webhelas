@extends('layouts.masteradmin')

@section('content')
<div class="row">

    <!-- Kolom Kiri: Statistik & Input Atasan -->
    <div class="col-lg-6 mb-4">

        <!-- Card Filter -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Filter Karyawan & Periode</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route($routeAction ?? 'admin.penilaian-cs.index') }}">
                    <div class="form-group">
                        <label>Pilih Nama Tim:</label>
                        <select name="user_id" class="form-control">
                            @foreach($daftarCs as $cs)
                                @if($cs->id != 1)
                                <option value="{{ $cs->id }}" {{ $userId == $cs->id ? 'selected' : '' }}>
                                    {{ $cs->name }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @foreach(range(1,12) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="2024" {{ $tahun == '2024' ? 'selected' : '' }}>2024</option>
                                <option value="2025" {{ $tahun == '2025' ? 'selected' : '' }}>2025</option>
                                <option value="2026" {{ $tahun == '2026' ? 'selected' : '' }}>2026</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">
                        <i class="fas fa-search"></i> Tampilkan Data
                    </button>
                </form>
                 <br>
                 <a href="{{ route('admin.activity-cs.viewPdfBulanan', ['cs_id' => $userId, 'bulan' => $tahun . '-' . $bulan]) }}" class="btn btn-danger btn-block" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF Daily Activity
                 </a>
            </div>
        </div>

        <!-- Card Input Penilaian Atasan -->
        <div class="card shadow mb-4 border-left-danger">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Input Penilaian Atasan</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.penilaian-cs.store') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $userId }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kerajinan (0-100)</label>
                        <div class="col-sm-8">
                            <input type="number" name="kerajinan" class="form-control" required min="0" max="100" value="{{ $manual->kerajinan ?? 0 }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Kerjasama (0-100)</label>
                        <div class="col-sm-8">
                            <input type="number" name="kerjasama" class="form-control" required min="0" max="100" value="{{ $manual->kerjasama ?? 0 }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Tanggung Jawab (0-100)</label>
                        <div class="col-sm-8">
                            <input type="number" name="tanggung_jawab" class="form-control" required min="0" max="100" value="{{ $manual->tanggung_jawab ?? 0 }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Inisiatif (0-100)</label>
                        <div class="col-sm-8">
                            <input type="number" name="inisiatif" class="form-control" required min="0" max="100" value="{{ $manual->inisiatif ?? 0 }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Komunikasi (0-100)</label>
                        <div class="col-sm-8">
                            <input type="number" name="komunikasi" class="form-control" required min="0" max="100" value="{{ $manual->komunikasi ?? 0 }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ $manual->catatan ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-block">Simpan Penilaian</button>
                    @if(isset($manual))
                        <div class="mt-2 text-center text-xs text-muted">
                            Terakhir dinilai oleh: User ID {{ $manual->created_by }} pada {{ $manual->updated_at }}
                        </div>
                    @endif
                </form>
            </div>
        </div>

    </div>

    <!-- Kolom Kanan: Statistik System -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Statistik Sistem (Otomatis)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light border-left-info py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Database</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDatabase }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-database fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card bg-light border-left-success py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Closing</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClosing }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                      <h5 class="small font-weight-bold">Total Closing  ({{ $totalClosing }} / 30) <span class="float-right">{{ $closingTarget }}%</span></h5>
                <div class="progress mb-4">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $closingTarget }}%" aria-valuenow="{{ $closingTarget }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <h5 class="small font-weight-bold">Closing Paket 0 <span class="float-right">0%</span></h5>
                <div class="progress mb-4">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <h5 class="small font-weight-bold">Pencapaian Omset <span class="float-right">{{ $nilaiOmset }}%</span></h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $nilaiOmset }}%" aria-valuenow="{{ $nilaiOmset }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="text-right font-weight-bold text-gray-800 mb-4">
                    Omset Bulan Ini: Rp {{ number_format($totalOmset, 0, ',', '.') }}
                </div>
                <div class="text-right font-weight-bold text-gray-800 mb-4">
                    Target Omset: Rp {{ number_format($targetOmset, 0, ',', '.') }}
                </div>
<!-- 
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-primary"></i> Tertarik: {{ $countTertarik }}
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Transfer: {{ $countSudahTransfer }}
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> Cold: {{ $countCold }}
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> No: {{ $countNo }}
                    </span>
                </div> -->

            </div>
        </div>

        <!-- Card Tabel Penilaian Hasil -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success">
                 <h6 class="m-0 font-weight-bold text-white text-center">PENILAIAN HASIL (CS {{ strtoupper($namaUser ?? '') }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="bg-warning text-dark">
                            <tr>
                                <th>No</th>
                                <th>Aspek Kinerja</th>
                                <th>Indikator</th>
                                <th>Bobot</th>
                                <th>Pencapaian</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Penjualan & Omset</td>
                                <td>Target Rp {{ number_format($targetOmset ?? 0,0,',','.') }}/bulan</td>
                                <td>40%</td>
                                <td>Rp {{ number_format($totalOmset ?? 0,0,',','.') }}</td>
                                <td>{{ $scoreOmset ?? 0 }}</td>
                            </tr>
                            <tr>
                                 <td>2</td>
                                 <td>Closing Paket</td>
                                 <td>Target {{ $targetClosingPaket ?? 1 }} closing paket per bulan</td>
                                 <td>20%</td>
                                 <td>{{ $closingPaketCount ?? 0 }} peserta</td>
                                 <td>{{ $scoreClosingPaket ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Database Baru</td>
                                <td>Target {{ $targetDatabase ?? 50 }} database baru</td>
                                <td>20%</td>
                                <td>{{ $totalDatabase ?? 0 }}</td>
                                <td>{{ $scoreDatabase ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Penilaian Atasan</td>
                                <td>Total Skor Kualitatif (Max 500)</td>
                                <td>20%</td>
                                 <td>{{ $manualTotalSum ?? 0 }}</td>
                                 <td>{{ $scoreManual ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Daily Activity
                                </td>
                                <td>Pencapaian KPI Harian</td>
                                <td>(Ref)</td>
                                 <td>{{ number_format($dailyTotalKpi ?? 0, 2) }}%</td>
                                 <td>{{ number_format($dailyTotalKpi ?? 0, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                             <tr style="background-color: #d1f7d6;">
                                 <td colspan="5" class="text-right">TOTAL NILAI</td>
                                 <td>{{ $grandTotal ?? 0 }}</td>
                             </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer Alert -->
        <div class="card shadow mb-4">
             <div class="card-body {{ ($grandTotal ?? 0) < 70 ? 'bg-danger' : 'bg-success' }} text-white text-center">
                <h3 class="font-weight-bold m-0">{{ ($grandTotal ?? 0) < 70 ? 'Underperformance' : 'Good Performance' }} ({{ $grandTotal ?? 0 }})</h3>
            </div>
        </div>

    </div>
</div>
@endsection
