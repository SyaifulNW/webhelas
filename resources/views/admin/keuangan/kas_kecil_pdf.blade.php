<!DOCTYPE html>
<html>

<head>
    <title>Laporan Kas Kecil</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .summary {
            margin-top: 20px;
        }

        .font-weight-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        $totalMasuk = $kas->sum('masuk');
        $totalKeluar = $kas->sum('keluar');
        $saldoAwal = $saldoAwal ?? 0;
        $saldoAkhir = $saldoAwal + $totalMasuk - $totalKeluar;
    @endphp

    <div class="header">
        <h2>LAPORAN KAS KECIL</h2>
        <p>Bulan: {{ $months[$bulan] }} {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">TANGGAL</th>
                <th>KETERANGAN</th>
                <th width="15%">MASUK</th>
                <th width="15%">KELUAR</th>
                <th width="15%">SISA</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = $saldoAwal; @endphp
            @if($saldoAwal != 0)
            <tr style="background-color: #f9f9f9;">
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td style="font-style: italic; color: #666;">Saldo Bulan Sebelumnya</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-weight-bold">{{ number_format($saldoAwal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @foreach($kas as $index => $item)
                @php $balance += ($item->masuk - $item->keluar); @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-right">{{ $item->masuk > 0 ? number_format($item->masuk, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $item->keluar > 0 ? number_format($item->keluar, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($balance, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="3" class="text-center">TOTAL</td>
                <td class="text-right">{{ number_format($totalMasuk, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalKeluar, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($saldoAkhir, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <p>Saldo Bulan Sebelumnya: Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
        <p>Total Pemasukan: Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
        <p>Total Pengeluaran: Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
        <p class="font-weight-bold" style="border-top: 1px solid #000; padding-top: 5px; width: 300px;">Saldo Akhir: Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
    </div>
</body>

</html>