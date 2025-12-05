@extends('layouts.masteradmin')
@section('content')
<div class="container-fluid px-4">
    <h1 class="h3 mb-4 text-gray-800">Detail Alumni</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detail Data Alumni: {{ $alumni->nama }}
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td>{{ $alumni->id }}</td>
                </tr>
                <tr>
                    <th>Nama</th>
                    <td>{{ $alumni->nama }}</td>
                </tr>
                <tr>
                    <th>Sumber Leads</th>
                    <td>{{ $alumni->leads }}</td>
                </tr>
                <tr>
                    <th>Provinsi</th>
                    <td>{{ $alumni->provinsi_nama }}</td>
                </tr>
                <tr>
                    <th>Kota</th>
                    <td>{{ $alumni->kota_nama }}</td>
                </tr>
                <tr>
                    <th>Nama Bisnis</th>
                    <td>{{ $alumni->nama_bisnis }}</td>
                </tr>
                <tr>
                    <th>Jenis Bisnis</th>
                    <td>{{ $alumni->jenisbisnis }}</td>
                </tr>
                <tr>
                    <th>No. Whatsapp</th>
                    <td>{{ $alumni->no_wa }}</td>
                </tr>
                <tr>
                    <th>Kendala</th>
                    <td>{{ $alumni->kendala }}</td>
                </tr>
                <tr>
                    <th>Sudah Pernah Ikut Kelas</th>
                    <td>{{ $alumni->sudah_pernah_ikut_kelas_apa_saja }}</td>
                </tr>
                <tr>
                    <th>Kelas Yang Belum Diikuti</th>
                    <td>{{ $alumni->kelas_yang_belum_diikuti_apa_saja }}</td>
                </tr>
            </table>

        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('admin.alumni.alumni') }}" class="btn btn-secondary">Kembali ke Daftar Alumni</a>
    </div>
</div>
@endsection

