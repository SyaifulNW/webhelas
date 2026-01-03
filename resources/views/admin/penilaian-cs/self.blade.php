@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">

    {{-- JUDUL --}}
    <div class="text-center mb-3">
        <h3 class="fw-bold" style="color: #5a5c69;">Key Performance Index</h3>
    </div>

    {{-- FILTER BULAN & TAHUN --}}
    <form method="GET" action="{{ route($routeAction ?? 'manager.penilaian-cs.index') }}" class="d-flex justify-content-center align-items-center mb-4" style="gap: 10px;">
        @if(isset($userId))
        <input type="hidden" name="user_id" value="{{ $userId }}">
        @endif
        
        <select name="bulan" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
            @foreach(range(1, 12) as $m)
                <option value="{{ sprintf('%02d', $m) }}" {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>

        <select name="tahun" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
            @foreach(range(date('Y'), 2023) as $y)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </form>

    {{-- PROGRESS BAR TOTAL PENCAPAIAN --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-secondary mb-2">Total Pencapaian: {{ $grandTotal ?? 0 }}/100</h5>
            <div class="progress" style="height: 25px; background-color: #e9ecef; border-radius: 5px;">
                <div class="progress-bar fw-bold" role="progressbar" 
                    style="width: {{ $grandTotal ?? 0 }}%; background-color: #dc3545; font-size: 14px;" 
                    aria-valuenow="{{ $grandTotal ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $grandTotal ?? 0 }}%
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL PENILAIAN UTAMA --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-header text-white text-center fw-bold" style="background-color: #20c997;">
            KEY PERFORMANCE INDEX (CS {{ strtoupper($namaUser ?? '') }})
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 text-center align-middle">
                    <thead style="background-color: #ffed8b;">
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
                        {{-- 1. Penjualan & Omset --}}
                        <tr>
                            <td>1</td>
                            <td class="text-left">Penjualan & Omset</td>
                            <td class="text-left">Target Rp {{ number_format($targetOmset ?? 50000000, 0, ',', '.') }}/bulan</td>
                            <td>40%</td>
                            <td>Rp {{ number_format($totalOmset ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $scoreOmset ?? 0 }}</td>
                        </tr>
                        {{-- 2. Closing Paket --}}
                        <tr>
                            <td>2</td>
                            <td class="text-left">Closing Paket</td>
                            <td class="text-left">Target {{ $targetClosingPaket ?? 1 }} closing paket per bulan</td>
                            <td>20%</td>
                            <td>{{ $closingPaketCount ?? 0 }} peserta</td>
                            <td>{{ $scoreClosingPaket ?? 0 }}</td>
                        </tr>
                        {{-- 3. Database Baru --}}
                        <tr>
                            <td>3</td>
                            <td class="text-left">Database Baru</td>
                            <td class="text-left">Target {{ $targetDatabase ?? 50 }} database baru</td>
                            <td>20%</td>
                            <td>{{ $totalDatabase ?? 0 }}</td>
                            <td>{{ $scoreDatabase ?? 0 }}</td>
                        </tr>
                        {{-- 4. Penilaian Atasan --}}
                        <tr>
                            <td>4</td>
                            <td class="text-left">Penilaian Atasan</td>
                            <td class="text-left">Total Skor Kualitatif (Max 500)</td>
                            <td>20%</td>
                            <td>{{ $manualTotalSum ?? 0 }}</td>
                            <td>{{ $scoreManual ?? 0 }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #dff0d8;">
                            <td colspan="5" class="text-right fw-bold pr-4">TOTAL NILAI</td>
                            <td class="fw-bold">{{ $grandTotal ?? 0 }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- TABEL PENILAIAN KUALITATIF (ATASAN) --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-header text-white text-center fw-bold" style="background-color: #4e73df;">
            PENILAIAN KUALITATIF (OLEH ATASAN)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-left">Aspek</th>
                            <th>Nilai (0-100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left">Kerajinan</td>
                            <td>{{ $manual->kerajinan ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">Kerjasama</td>
                            <td>{{ $manual->kerjasama ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">Tanggung Jawab</td>
                            <td>{{ $manual->tanggung_jawab ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">Inisiatif</td>
                            <td>{{ $manual->inisiatif ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-left">Komunikasi</td>
                            <td>{{ $manual->komunikasi ?? 0 }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #e2e6ea;">
                            <td class="text-left fw-bold">TOTAL SKOR</td>
                            <td class="fw-bold">{{ $manualTotalSum ?? 0 }}</td>
                        </tr>
                        <tr style="background-color: #fff3cd;">
                            <td class="text-left fw-bold">RATA-RATA NILAI</td>
                            <td class="fw-bold">{{ isset($manual) ? round($manual->total_nilai, 1) : 0 }}</td>
                        </tr>
                        @if(isset($manual) && $manual->catatan)
                        <tr>
                            <td colspan="2" class="text-left p-3">
                                <strong>Catatan Atasan:</strong><br>
                                <em class="text-muted">{{ $manual->catatan }}</em>
                            </td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    {{-- STATUS BOX & LEGEND --}}
    <div class="card shadow border-0 p-4 mb-4">
        
        {{-- Dinamic Status Box --}}
        <div id="statusBoxContainer" class="p-3 text-center text-white fw-bold fs-4 mb-3" 
             style="border-radius: 5px; background-color: #dc3545;">
             Underperformance ({{ $grandTotal ?? 0 }})
        </div>

        {{-- Motivasi Text --}}
        <div class="d-flex align-items-start mb-4">
            <i class="fas fa-comment-dots fa-lg me-2 mt-1" style="color: #aaa;"></i>
            <em id="motivasiTextInline" style="color: #555;">
                Ayo bangkit! Kamu belum terlambat untuk mengejar.
            </em>
        </div>

        <h5 class="fw-bold mb-3">Keterangan Skala Nilai</h5>
        <div class="table-responsive">
            <table class="table text-center text-white fw-bold mb-0">
                <thead style="background-color: #2c3e50;">
                    <tr>
                        <th>Rentang Nilai</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: #008000;">
                        <td>> 100</td>
                        <td>Sangat Baik</td>
                    </tr>
                    <tr style="background-color: #00ca00;">
                        <td>80 – 99</td>
                        <td>Baik</td>
                    </tr>
                    <tr style="background-color: #ffe600; color: #333;">
                        <td>60 – 79</td>
                        <td>Cukup</td>
                    </tr>
                    <tr style="background-color: #ff9900;">
                        <td>40 – 59</td>
                        <td>Pembinaan</td>
                    </tr>
                    <tr style="background-color: #dc3545;">
                        <td>< 40</td>
                        <td>Underperformance</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- HISTORY SECTION --}}
    <h4 class="fw-bold text-secondary mb-3">G. HISTORY KINERJA PER BULAN</h4>
    
    <div class="d-flex overflow-auto pb-3" style="gap: 15px;">
        @foreach(range(1, 12) as $m)
            @php
                $hVal = $historyNilai[$m] ?? 0;
                // Tentukan warna bar kecil
                if($hVal > 100) $cBar = '#008000';
                elseif($hVal >= 80) $cBar = '#00ca00';
                elseif($hVal >= 60) $cBar = '#ffe600';
                elseif($hVal >= 40) $cBar = '#ff9900';
                elseif($hVal > 0) $cBar = '#dc3545';
                else $cBar = '#e9ecef';
            @endphp
            <div class="card shadow-sm border text-center" style="min-width: 100px;">
                <div class="card-body p-2">
                    <div class="fw-bold text-secondary mb-2" style="font-size: 14px;">
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                    </div>
                    <div class="w-100 rounded mb-2" style="height: 6px; background-color: #eee;">
                        <div class="h-100 rounded" style="width: 100%; background-color: {{ $cBar }};"></div>
                    </div>
                    <div class="fw-bold text-dark">{{ $hVal }}</div>
                </div>
            </div>
        @endforeach
    </div>

</div>

{{-- Script to update Status Box dynamically based on Total Nilai --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let total = {{ $grandTotal ?? 0 }};
        let box = document.getElementById('statusBoxContainer');
        let quote = document.getElementById('motivasiTextInline');
        let bar = document.querySelector('.progress-bar');
        
        let bg = '#dc3545'; // Default Red
        let label = 'Underperformance';
        let text = 'Ayo bangkit! Kamu belum terlambat untuk mengejar.';

        if(total > 100) {
            bg = '#008000'; label = 'Sangat Baik';
            text = 'Luar biasa! Konsistensi kinerjamu sangat menginspirasi!';
        } else if (total >= 80) {
            bg = '#00ca00'; label = 'Baik';
            text = 'Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik.';
        } else if (total >= 60) {
            bg = '#ffe600'; label = 'Cukup';
            text = 'Cukup baik, tapi masih banyak ruang untuk berkembang.';
        } else if (total >= 40) {
            bg = '#ff9900'; label = 'Pembinaan';
            text = 'Jangan menyerah, ini saatnya bangkit!';
        }

        if(box) {
            box.style.backgroundColor = bg;
            box.innerText = label + ' (' + total + ')';
            if(total >= 60 && total < 80) box.style.color = '#333'; 
        }
        if(quote) quote.innerText = text;
        if(bar) {
            bar.style.backgroundColor = bg;
            if(total >= 60 && total < 80) bar.style.color = '#333';
        }
    });
</script>
@endsection
