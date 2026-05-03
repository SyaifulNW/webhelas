<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Daily Activity & KPI - {{ $bulan }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 8px; 
            margin: 5px;
        }
        h3, h4 { 
            text-align: center; 
            margin: 2px 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 5px 0; 
            font-size: 7.2px; 
            table-layout: fixed;
            page-break-inside: avoid;
        }
        th, td { 
            border: 0.5px solid #333; 
            padding: 1.5px; 
            text-align: center; 
            word-wrap: break-word; 
        }
        th { 
            background: #d9edf7; 
            color: #333333;
            font-weight: bold; 
        }
        td.left { text-align: left; }
        .kategori { 
            background: #d9edf7; 
            font-weight: bold; 
            text-align: left; 
        }
        .total { 
            background: #e6ffe6; 
            font-weight: bold; 
        }
        .info {
            font-size: 9px;
            margin-bottom: 5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table td {
            border: none;
            text-align: left;
            padding: 2px 0;
        }
    </style>
</head>
<body>
    <h3>Laporan Daily Activity</h3>
    <h4>Bulan: {{ $bulan }}</h4>

    {{-- Informasi User & Tanggal Unduhan --}}
    <table class="info-table">
        <tr>
            <td><strong>Nama Karyawan:</strong> {{ $csName }}</td>
            <td style="text-align: right;"><strong>Diunduh pada:</strong> {{ $downloadDate }}</td>
        </tr>
    </table>

    {{-- LOOP PER KATEGORI --}}
    @foreach($categories as $kategori => $aktivitasList)
    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:20%">Aktivitas</th>
                <th style="width:6%">Target/Hari</th>
                <th style="width:6%">Target/Bulan</th>
                <th style="width:5%">Bobot</th>
                <th style="width:6%">Realisasi</th>
                <th style="width:5%">Nilai</th>
                @for($d=1; $d<=$jumlahHari; $d++)
                    <th style="width:1.8%">{{ $d }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="{{ 7 + $jumlahHari }}" class="kategori">{{ $kategori }}</td>
            </tr>

            @foreach($aktivitasList as $i => $act)
            <tr>
                <td>{{ $i+1 }}</td>
                <td class="left">{{ $act['nama'] }}</td>
                @if(strpos($act['nama'], 'Viewer') !== false)
                    <td>100%</td>
                    <td>100%</td>
                @else
                    <td>{{ number_format($act['target_bulanan'] / 25, 0) }}</td>
                    <td>{{ $act['target_bulanan'] }}</td>
                @endif
                <td>{{ $act['bobot'] }}</td>
                <td>{{ $act['real'] }}</td>
                <td>{{ number_format($act['nilai'],2) }}</td>
                @for($d=1; $d<=$jumlahHari; $d++)
                    @php
                        $realDaily = $act['harian'][$d] ?? 0;
                        $targetDaily = ($act['target_daily'] > 0) ? $act['target_daily'] : ($act['target_bulanan'] / 25);
                        
                        $dateObj = \Carbon\Carbon::create($tahun, $bulan_int, $d)->startOfDay();
                        $isSunday = $dateObj->isSunday();
                        $isPast = $dateObj->isPast() && !$dateObj->isToday();

                        $cellStyle = '';
                        if ($realDaily > 0) {
                            if ($realDaily < $targetDaily) {
                                $cellStyle = 'background-color: #ffff99; color: #333333;'; // Kuning
                            } else {
                                $cellStyle = 'background-color: #ffffff; color: #333333;';
                            }
                        } else {
                            // Jangan merahkan untuk kategori yang mengandung "Bulanan" (Monthly)
                            if ($isPast && !$isSunday && strpos(strtolower($kategori), 'bulanan') === false) {
                                $cellStyle = 'background-color: #ff4d4d; color: #ffffff;'; // Merah
                            } else {
                                $cellStyle = 'background-color: #ffffff; color: #333333;';
                            }
                        }
                    @endphp
                    <td style="{{ $cellStyle }} text-align: center;">{{ $realDaily ?: '' }}</td>
                @endfor
            </tr>
            @endforeach

            <tr class="total">
                <td colspan="2">TOTAL</td>
                <td>{{ number_format($total[$kategori]['target_bulanan'] / 25, 0) }}</td>
                <td>{{ $total[$kategori]['target_bulanan'] }}</td>
                <td>{{ $total[$kategori]['bobot'] }}</td>
                <td>{{ $total[$kategori]['real'] }}</td>
                <td>{{ number_format($total[$kategori]['nilai'],2) }}</td>
                @for($d=1; $d<=$jumlahHari; $d++)
                    @php
                        $totalRealDaily = $total[$kategori]['harian'][$d] ?? 0;
                        $totalTargetDaily = $total[$kategori]['target_bulanan'] / 25;
                        
                        $dateObj = \Carbon\Carbon::create($tahun, $bulan_int, $d)->startOfDay();
                        $isSunday = $dateObj->isSunday();
                        $isPast = $dateObj->isPast() && !$dateObj->isToday();

                        $totalCellStyle = '';
                        if ($totalRealDaily > 0) {
                            if ($totalRealDaily < $totalTargetDaily) {
                                $totalCellStyle = 'background-color: #ffff99; color: #333333;';
                            } else {
                                $totalCellStyle = 'background-color: #ffffff; color: #333333;';
                            }
                        } else {
                            // Jangan merahkan untuk kategori yang mengandung "Bulanan"
                            if ($isPast && !$isSunday && strpos(strtolower($kategori), 'bulanan') === false) {
                                $totalCellStyle = 'background-color: #ff4d4d; color: #ffffff;';
                            } else {
                                $totalCellStyle = 'background-color: #ffffff; color: #333333;';
                            }
                        }
                    @endphp
                    <td style="{{ $totalCellStyle }} text-align: center;">{{ $totalRealDaily ?: '' }}</td>
                @endfor
            </tr>
        </tbody>
    </table>
    @endforeach

    {{-- REKAP KPI --}}
    <div style="margin-top: 10px;">
        <h3 style="text-align: left; margin-bottom: 5px; border-bottom: 1px solid #333; padding-bottom: 2px; font-size: 10px;">
            REKAPITULASI INTAKE / KPI
        </h3>
        <table style="font-size: 8px; margin-top: 5px;">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:45%; text-align: left;">Dimensi Aktivitas</th>
                    <th style="width:10%">Target</th>
                    <th style="width:10%">Bobot</th>
                    <th style="width:15%">Pencapaian (%)</th>
                    <th style="width:15%">Skor Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kpiData as $i => $row)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td style="text-align: left;">{{ $row['nama'] }}</td>
                    <td>{{ $row['target'] }}</td>
                    <td>{{ $row['bobot'] }}</td>
                    <td>{{ $row['persentase'] }}%</td>
                    <td style="font-weight: bold;">{{ number_format($row['nilai'], 2) }}</td>
                </tr>
                @endforeach
                <tr style="background: #f2f2f2; font-weight: bold; font-size: 9px;">
                    <td colspan="3" style="text-align: right; padding: 3px;">OVERALL PERFORMANCE SCORE</td>
                    <td>{{ $totalBobot }}</td>
                    <td>—</td>
                    <td style="background: #333; color: #fff; font-size: 10px;">{{ number_format($totalNilai, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- LEGENDA WARNA --}}
    <div style="margin-top: 20px; font-size: 9px; clear: both;">
        <p style="margin-bottom: 5px;"><strong>Keterangan Warna:</strong></p>
        <div style="margin-bottom: 4px;">
            <span style="display: inline-block; width: 15px; height: 10px; background-color: #ff4d4d; border: 0.5px solid #333; vertical-align: middle;"></span>
            <span style="vertical-align: middle; margin-left: 5px;">Merah: Tidak dilakukan (pada hari kerja yang sudah terlewat)</span>
        </div>
        <div style="margin-bottom: 4px;">
            <span style="display: inline-block; width: 15px; height: 10px; background-color: #ffff99; border: 0.5px solid #333; vertical-align: middle;"></span>
            <span style="vertical-align: middle; margin-left: 5px;">Kuning: Dilakukan, namun tidak mencapai target harian</span>
        </div>
        <div style="margin-bottom: 4px;">
            <span style="display: inline-block; width: 15px; height: 10px; background-color: #ffffff; border: 0.5px solid #333; vertical-align: middle;"></span>
            <span style="vertical-align: middle; margin-left: 5px;">Putih: Mencapai target, hari libur (Minggu), atau hari yang belum dijalani</span>
        </div>
    </div>
</body>
</html>
