@extends('layouts.masteradmin')

@section('content')
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Hidden form for creating new data -->
<form id="formTambah" action="{{ route('peserta-smi.store') }}" method="POST">
    @csrf
</form>

<div class="row">
    {{-- Filter Section --}}
    <div class="col-xl-12 mb-3">
        <div class="card shadow-sm border-left-primary">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('peserta-smi.index') }}" class="form-inline">
                    <label class="mr-2 font-weight-bold">Filter SPP:</label>
                    
                    <select name="filter_spp_month" class="form-control form-control-sm mr-2">
                        <option value="">- Pilih Bulan -</option>
                        @php
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach($months as $key => $val)
                            <option value="{{ $key }}" {{ request('filter_spp_month') == $key ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>

                    <select name="filter_spp_status" class="form-control form-control-sm mr-2">
                        <option value="">- Semua Status -</option>
                        <option value="1" {{ request('filter_spp_status') === '1' ? 'selected' : '' }}>Sudah Bayar</option>
                        <option value="0" {{ request('filter_spp_status') === '0' ? 'selected' : '' }}>Belum Bayar</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm mr-2"> <i class="fas fa-filter"></i> Filter</button>
                    
                    @if(request()->has('filter_spp_month'))
                         <a href="{{ route('peserta-smi.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-sync"></i> Reset</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-12 col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Peserta SMI</h6>
                <button class="btn btn-primary btn-sm" onclick="toggleInputRow()">
                    <i class="fas fa-plus"></i> Tambah Peserta
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm" id="dataTable" width="100%" cellspacing="0" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-center" style="width: 3%">No</th>
                                <th rowspan="2" class="align-middle text-center" style="width: 15%">Nama</th>
                                <th rowspan="2" class="align-middle text-center" style="width: 15%">One On One Coaching</th>
                                <th rowspan="2" class="align-middle text-center" style="width: 15%">Tanggal Masuk - Selesai</th>
                                <th rowspan="2" class="align-middle text-center" style="width: 10%">Biaya Pendaftaran</th>
                                <th rowspan="2" class="align-middle text-center" style="width: 10%">CS Closing</th>
                                <th colspan="12" class="text-center">SPP</th>
                                <th rowspan="2" class="align-middle text-center" style="width: 5%">Aksi</th>
                            </tr>
                            <tr>
                                @php
                                    $bulan = [
                                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                                        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                                    ];
                                @endphp
                                @for($i=1; $i<=12; $i++)
                                <th class="text-center bg-light" style="min-width: 40px; width: 40px;">
                                    <a href="{{ route('peserta-smi.index', ['sort_spp' => $i, 'sort_dir' => request('sort_spp') == $i && request('sort_dir') == 'desc' ? 'asc' : 'desc']) }}" class="text-dark text-decoration-none small font-weight-bold">
                                        {{ $bulan[$i] }}
                                        {!! request('sort_spp') == $i ? (request('sort_dir') == 'desc' ? '&#9660;' : '&#9650;') : '' !!}
                                    </a>
                                </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Input Row (Hidden by default) -->
                            <tr id="inputRow" style="display: none; background-color: #e8f0fe;">
                                <td class="text-center align-middle">
                                    <span class="badge badge-info">New</span>
                                </td>
                                <td>
                                    <input form="formTambah" type="text" name="nama" class="form-control form-control-sm" placeholder="Nama Peserta" required>
                                </td>
                                <td>
                                    <input form="formTambah" type="datetime-local" name="one_on_one_coaching" class="form-control form-control-sm">
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input form="formTambah" type="date" name="tanggal_masuk" class="form-control form-control-sm mb-1" placeholder="Masuk">
                                        <input form="formTambah" type="date" name="tanggal_selesai" class="form-control form-control-sm" placeholder="Selesai">
                                    </div>
                                </td>
                                <td>
                                    <input form="formTambah" type="number" name="biaya_pendaftaran" class="form-control form-control-sm" placeholder="Rp...">
                                </td>
                                <td>
                                    <select form="formTambah" name="closing_cs_id" class="form-control form-control-sm">
                                        <option value="">- Pilih CS -</option>
                                        @foreach($listCs as $cs)
                                            <option value="{{ $cs->id }}">{{ $cs->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                @for($i = 1; $i <= 12; $i++)
                                <td class="text-center align-middle p-1">
                                    <input form="formTambah" type="checkbox" name="spp_{{ $i }}" value="1" style="transform: scale(1.5);">
                                </td>
                                @endfor
                                <td class="text-center align-middle">
                                    <button type="submit" form="formTambah" class="btn btn-success btn-sm p-1" title="Simpan">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm p-1" title="Batal" onclick="toggleInputRow()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>

                            @foreach($data as $key => $item)
                            <tr>
                                <form action="{{ route('peserta-smi.update', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td class="text-center align-middle">{{ $key + 1 }}</td>
                                    
                                    {{-- Nama --}}
                                    <td class="p-1 align-middle">
                                        <input type="text" name="nama" class="form-control form-control-sm border-0 bg-transparent" value="{{ $item->nama }}">
                                    </td>
                                    
                                    {{-- Coaching --}}
                                    <td class="p-1 align-middle">
                                        <input type="datetime-local" name="one_on_one_coaching" class="form-control form-control-sm border-0 bg-transparent" value="{{ $item->one_on_one_coaching }}">
                                    </td>
                                    
                                    {{-- Tanggal Masuk & Selesai --}}
                                    <td class="p-1 align-middle">
                                        <input type="date" name="tanggal_masuk" class="form-control form-control-sm border-0 bg-transparent mb-1" value="{{ $item->tanggal_masuk }}" title="Tgl Masuk">
                                        <input type="date" name="tanggal_selesai" class="form-control form-control-sm border-0 bg-transparent" value="{{ $item->tanggal_selesai }}" title="Tgl Selesai">
                                    </td>
                                    
                                    {{-- Biaya --}}
                                    <td class="p-1 align-middle">
                                        <input type="number" name="biaya_pendaftaran" class="form-control form-control-sm border-0 bg-transparent" value="{{ $item->biaya_pendaftaran }}">
                                    </td>
                                    
                                    {{-- CS Closing --}}
                                    <td class="p-1 align-middle">
                                        <select name="closing_cs_id" class="form-control form-control-sm border-0 bg-transparent">
                                            <option value="">- CS -</option>
                                            @foreach($listCs as $cs)
                                                <option value="{{ $cs->id }}" {{ $item->closing_cs_id == $cs->id ? 'selected' : '' }}>{{ $cs->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    
                                    {{-- SPP Checkboxes --}}
                                    @for($i = 1; $i <= 12; $i++)
                                    <td class="text-center align-middle p-0" style="vertical-align: middle;">
                                        <input type="checkbox" name="spp_{{ $i }}" value="1" {{ $item->{"spp_$i"} ? 'checked' : '' }} style="transform: scale(1.2); cursor: pointer;">
                                    </td>
                                    @endfor
                                    
                                    {{-- Action Buttons --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="submit" class="btn btn-primary btn-sm p-1" title="Update Row" style="line-height: 1;">
                                                <i class="fas fa-save fa-xs"></i>
                                            </button>
                                    </form> 
                                            {{-- Delete Form (Separate) --}}
                                            <form action="{{ route('peserta-smi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm p-1" title="Hapus" style="line-height: 1;">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleInputRow() {
        var row = document.getElementById('inputRow');
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }
</script>
<style>
    /* Hilangkan border input saat tidak aktif agar terlihat seperti teks biasa */
    .table input.form-control.bg-transparent:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
</style>
@endsection
