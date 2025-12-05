@extends('layouts.masteradmin')
@section('content')
<div class="container-fluid px-4">
    <h3 class="mb-4">Dashboard CS MBC</h3>
    <div class="row">
        {{-- Kolom Kiri (8 dari 12 kolom) --}}
        <div class="col-md-8">
            <div class="row">
                {{-- Omset --}}
                <div class="col-md-12 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h5 class="text-success">OMSET PER KELAS (BULAN INI)</h5>
                            <p>Kelas 1: Rp 26.000.000<br>Target: Rp 25.000.000</p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" style="width: 104%">104%</div>
                            </div>
                            <small class="text-success">✅ Bonus Rp300.000</small>
                            <hr>
                            <p>Kelas 2: Rp 19.000.000<br>Target: Rp 25.000.000</p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" style="width: 76%">76%</div>
                            </div>
                            <small class="text-danger">❌ Belum Capai Bonus</small>
                            <hr>
                            <strong>Total Omset: Rp 45.000.000</strong><br>
                            <span class="badge bg-secondary mt-2">Belum capai total target gabungan</span>
                        </div>
                    </div>
                </div>

                {{-- Total Closing --}}
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-success">
                        <div class="card-body">
                            <h5 class="text-success">TOTAL CLOSING (TAHUN INI)</h5>
                            <h4>Rp. 25.000.000</h4>
                            <p>Target: Rp. 60.000.000</p>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: 41%">41,6% tercapai</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Komisi --}}
                <div class="col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <h5 class="text-success">KOMISI SEMENTARA</h5>
                            <h4>Rp 425.000</h4>
                            <p>Komisi Dasar: Rp 125.000<br>Bonus Target: Rp 300.000</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (4 dari 12 kolom) --}}
        <div class="col-md-4">
         <div class="card shadow-sm border-primary">
    <div class="card-body">
        <h5 class="text-primary mb-3">📊 JUMLAH LEAD AKTIF</h5>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Cold</span>
            <span class="badge bg-white  rounded-pill px-3 py-2">0</span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Warm</span>
            <span class="badge bg-warning text-white rounded-pill px-3 py-2">4</span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Hot</span>
            <span class="badge bg-success text-white rounded-pill px-3 py-2">7</span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Tidak Bisa Ikut (No)</span>
            <span class="badge bg-danger text-white rounded-pill px-3 py-2">2</span>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center">
            <strong>Total Lead</strong>
            <span class="badge bg-info text-white rounded-pill px-3 py-2"> 13</span>
        </div>
    </div>
</div>
        <br>


            <div class="mb-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="text-success">JUMLAH PESERTA AKTIF</h5>
                        <h4>7 Peserta</h4>
                    </div>
                </div>
            </div>

            <div>
                <div class="card border-info">
                    <div class="card-body">
                        <h5 class="text-info">JUMLAH REPEAT ORDER</h5>
                        <h4>3 Peserta</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection



