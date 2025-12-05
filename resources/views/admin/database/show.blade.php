@extends('layouts.masteradmin')
@section('content')

<div class="container-fluid px-4">
    <h3 class="mb-4">Detail Database Calon Peserta</h3>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detail Data Calon Peserta: {{ $data->nama }}
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td>{{ $data->id }}</td>
                </tr>
                <tr>
                    <th>Nama</th>
                    <td>{{ $data->nama }}</td>
                </tr>
                <tr>
                    <th>Leads</th>
                    <td>{{ $data->leads }}</td>
                </tr>
                <tr>
                    <th>Kota</th>
                    <td>{{ $data->kota_nama }}</td>
                </tr>
                <tr>
                    <th>Provinsi</th>
                    <td>{{ $data->provinsi_nama }}</td>
                </tr>
                <tr>
                    <th>Nama Bisnis</th>
                    <td>{{ $data->nama_bisnis }}</td>
                </tr>
                <tr>
                    <th>Jenis Bisnis</th>
                    <td>{{ $data->jenisbisnis }}</td>
                </tr>
                <tr>
                    <th>No. Whatsapp</th>
                    <td>{{ $data->no_wa }}</td>
                </tr>
                <tr>
                    <th>Kendala</th>
                    <td>{{ $data->kendala }}</td>
                </tr>
                <tr>
                    <th>Situasi Bisnis</th>
                    <td>{{ $data->situasi_bisnis }}</td>
                </tr>
                <tr>
                    <th>Ikut Kelas</th>
                    <td>{{ $data->ikut_kelas == 1 ? 'Ya' : 'Tidak' }}</td>
                </tr>
                <tr>
                    <th>Kelas</th>
                    <td>
                        @if($data->kelas_id)
                            {{ $data->kelas->nama_kelas }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <!-- Add more fields as necessary -->
            </table>
        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('admin.database.database') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>





@endsection