@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Performa Reseller: {{ $reseller->name }}</h1>
        <a href="{{ route('chapter.reseller.index') }}" class="btn btn-secondary btn-sm shadow-sm px-3">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Header Performa Reseller -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0" style="border-left: 4px solid #4e73df !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jumlah Closing
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ ($students ?? collect())->where('status', 'sudah_transfer')->count() }} Closing</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ ($students ?? collect())->count() }} Leads</div>
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
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 bg-white d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-network-wired mr-2"></i> Struktur Downline Sederhana (Daftar Peserta)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light text-dark small text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Peserta (Downline)</th>
                                    <th>Program / Kelas</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Nominal Pembayaran</th>
                                    <th>Tanggal Closing</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $student->nama }}</div>
                                        <small class="text-muted">No. WA: {{ $student->no_wa ?? '-' }}</small>
                                    </td>
                                    <td>
                                        {{ $student->kelas->nama_kelas ?? 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        @if($student->status == 'sudah_transfer')
                                            <span class="badge bg-success text-white px-3 py-2 rounded-pill">Closing (Aktif)</span>
                                        @elseif($student->status == 'mau_transfer')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Hot Prospect</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-2 rounded-pill">{{ ucfirst($student->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        @if($student->status == 'sudah_transfer')
                                            Rp {{ number_format($student->nominal, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ $student->tanggal_closing ? date('d M Y', strtotime($student->tanggal_closing)) : ($student->status == 'sudah_transfer' ? date('d M Y', strtotime($student->updated_at)) : '-') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">Reseller ini belum membawa leads atau closing baru.</div>
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
