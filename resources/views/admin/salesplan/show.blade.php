@extends('layouts.masteradmin')
@section('content')

<div class="container-fluid px-4">
    <h3 class="mb-4">Detail Sales Plan</h3>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detail Sales Plan: {{ $data->kelas->nama_kelas }} &nbsp; &nbsp; &nbsp;  {{ $data->nama }}
        </div>
        </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td>{{ $salesplan->id }}</td>
                </tr>
                <tr>
                    <th>Nama</th>
                    <td>{{ $salesplan->data->nama }}</td>
                </tr>
                <tr>
                    <th>Leads</th>
                    <td>{{ $salesplan->data->leads }}</td>
                </tr>
                <tr>
                    <th>Situasi</th>
                    <td>{{{$salesplan->data->situasibisnis}}}</td>
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