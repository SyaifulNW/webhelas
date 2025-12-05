<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Daily Activity & KPI - {{ $bulan }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 8px; 
            margin: 10px;
        }
        h3, h4 { 
            text-align: center; 
            margin: 3px 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
            font-size: 7.5px; 
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
            background: #f2f2f2; 
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
            <td><strong>Nama CS:</strong> {{ $csName }}</td>
            <td style="text-align: right;"><strong>Diunduh pada:</strong> {{ $downloadDate }}</td>
        </tr>
    </table>

    {{-- LOOP PER KATEGORI --}}
    @foreach($categories as $kategori => $aktivitasList)
    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:15%">Aktivitas</th>
                <th style="width:5%">Target/Hari</th>
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
                <td>{{ $act['target_daily'] }}</td>
                <td>{{ $act['target_bulanan'] }}</td>
                <td>{{ $act['bobot'] }}</td>
                <td>{{ $act['real'] }}</td>
                <td>{{ number_format($act['nilai'],2) }}</td>
                @for($d=1; $d<=$jumlahHari; $d++)
                    <td>{{ $act['harian'][$d] ?? '' }}</td>
                @endfor
            </tr>
            @endforeach

            <tr class="total">
                <td colspan="2">TOTAL</td>
                <td>{{ $total[$kategori]['target_daily'] }}</td>
                <td>{{ $total[$kategori]['target_bulanan'] }}</td>
                <td>{{ $total[$kategori]['bobot'] }}</td>
                <td>{{ $total[$kategori]['real'] }}</td>
                <td>{{ number_format($total[$kategori]['nilai'],2) }}</td>
                @for($d=1; $d<=$jumlahHari; $d++)
                    <td>{{ $total[$kategori]['harian'][$d] ?? '' }}</td>
                @endfor
            </tr>
        </tbody>
    </table>
    @endforeach
</body>
</html>
