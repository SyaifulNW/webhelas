@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-3">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Detail Performa Agen: {{ $reseller->name }}</h1>
        <a href="{{ route('reseller.setting.index') }}" class="btn btn-secondary btn-sm shadow-sm px-3 fw-bold">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Header Performa Agen -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0" style="border-left: 4px solid #4e73df !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jumlah Closing
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $students->where('status', 'sudah_transfer')->count() }} Closing</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-handshake fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0" style="border-left: 4px solid #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Omset Kontribusi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($students->where('status', 'sudah_transfer')->sum('nominal'), 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 border-0" style="border-left: 4px solid #36b9cc !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Leads yang Didapat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $students->count() }} Leads</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Struktur Downline Sederhana (List Peserta) -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header py-3 bg-white d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-network-wired mr-2"></i> Daftar Peserta (Downline dari Agen ini)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-dark small text-uppercase fw-bold">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nama Peserta</th>
                                    <th>Program / Kelas</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Nominal Pembayaran</th>
                                    <th>Tanggal Closing</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td class="text-muted font-weight-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $student->nama }}</div>
                                        <small class="text-muted"><i class="fab fa-whatsapp text-success mr-1"></i> {{ $student->no_wa ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border pr-2 pl-2">
                                            {{ $student->kelas->nama_kelas ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($student->status == 'sudah_transfer')
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill">Closing (Lunas)</span>
                                        @elseif($student->status == 'mau_transfer')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Hot Prospect</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-2 rounded-pill">{{ ucfirst(str_replace('_', ' ', $student->status)) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        @if($student->status == 'sudah_transfer')
                                            Rp {{ number_format($student->nominal, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-muted">
                                        {{ $student->tanggal_closing ? date('d M Y', strtotime($student->tanggal_closing)) : ($student->status == 'sudah_transfer' ? date('d M Y', strtotime($student->updated_at)) : '-') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                                            <p>Agen ini belum membawa leads atau closing baru.</p>
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
    </div>
</div>
@endsection
