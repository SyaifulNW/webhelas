<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penilaian Marketing</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            color: #1a73e8;
        }

        .info {
            margin-bottom: 20px;
        }

        .info table {
            width: 100%;
            border: none;
        }

        .info td {
            padding: 4px 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .main-table th {
            background-color: #fbbc04;
            color: #000;
        }

        .total-row {
            background-color: #d1f7d6;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
        }

        .footer .signature {
            margin-top: 60px;
            border-top: 1px solid #333;
            display: inline-block;
            width: 200px;
            text-align: center;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
        }

        .bg-success {
            background-color: #28a745;
        }

        .bg-danger {
            background-color: #dc3545;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PENILAIAN KINERJA MARKETING</h2>
        <p>WEB HELAS - MANAJEMEN SISTEM INFORMASI</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="15%">Nama</td>
                <td width="35%">: {{ $user->name }}</td>
                <td width="15%">Bulan</td>
                <td width="35%">: {{ DateTime::createFromFormat('!m', $bulan)->format('F') }}</td>
            </tr>
            <tr>
                <td>Role</td>
                <td>: Marketing</td>
                <td>Tahun</td>
                <td>: {{ $tahun }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Aspek Kinerja</th>
                <th width="30%">Pencapaian / Target</th>
                <th width="15%">Bobot</th>
                <th width="15%">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($felmiKpi) && count($felmiKpi) > 0)
                {{-- KHUSUS FELMI --}}
                @foreach($felmiKpi as $index => $kpi)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $kpi['nama'] }}</td>
                        <td class="center">{{ $kpi['real'] }} / {{ $kpi['target'] }}</td>
                        <td class="center">{{ $kpi['bobot'] }}%</td>
                        <td class="center">{{ number_format($kpi['nilai'], 0) }}</td>
                    </tr>
                @endforeach
            @else
                {{-- DEFAULT / EKO SULIS --}}
                @if($user->name === 'Eko Sulis')
                    {{-- EKO SULIS TABLE --}}
                    <tr>
                        <td class="center">1</td>
                        <td>ROAS (Return on Ad Spend)</td>
                        <td>{{ $roas }}X / {{ $targetRoas }}X</td>
                        <td class="center">30%</td>
                        <td class="center">{{ $nilaiAkhirRoas }}</td>
                    </tr>
                    <tr>
                        <td class="center">2</td>
                        <td>Jumlah Leads ADS</td>
                        <td>{{ $leadsAds }} / {{ $targetLeadsAds }}</td>
                        <td class="center">20%</td>
                        <td class="center">{{ $nilaiLeadsAds }}</td>
                    </tr>
                    <tr>
                        <td class="center">3</td>
                        <td>Jumlah Leads Event Offline (Felmi)</td>
                        <td>{{ $leadsFelmi }} / {{ $targetLeadsFelmi }}</td>
                        <td class="center">20%</td>
                        <td class="center">{{ $nilaiLeadsFelmi }}</td>
                    </tr>
                    <tr>
                        <td class="center">4</td>
                        <td>Jumlah Leads Event Online (Nisa)</td>
                        <td>{{ $leadsNisa }} / {{ $targetLeadsNisa }}</td>
                        <td class="center">20%</td>
                        <td class="center">{{ $nilaiLeadsNisa }}</td>
                    </tr>
                    <tr>
                        <td class="center">5</td>
                        <td>Penilaian Atasan (Sikap & Perilaku)</td>
                        <td>{{ $manualVal }}%</td>
                        <td class="center">10%</td>
                        <td class="center">{{ $nilaiManualPart }}</td>
                    </tr>
                    <tr>
                        <td class="center">6</td>
                        <td>Daily Activity (KPI Harian)</td>
                        <td>{{ number_format($dailyTotalKpi ?? 0, 0) }}%</td>
                        <td class="center">(Ref)</td>
                        <td class="center">{{ number_format($dailyTotalKpi ?? 0, 0) }}</td>
                    </tr>
                @elseif($user->name === 'Felmi')
                    {{-- FELMI TABLE --}}
                    <tr>
                        <td class="center">1</td>
                        <td>Total Leads Baru/Bulan</td>
                        <td>{{ $leadsFelmiCount }} / 100</td>
                        <td class="center">40%</td>
                        <td class="center">{{ $nilaiLeadsFelmiPart }}</td>
                    </tr>
                    <tr>
                        <td class="center">2</td>
                        <td>Entrepreneur Forum / E-Fest</td>
                        <td>{{ $efestCount }} / 50</td>
                        <td class="center">30%</td>
                        <td class="center">{{ $nilaiEfest }}</td>
                    </tr>
                    <tr>
                        <td class="center">3</td>
                        <td>Bisnis Visit / UpRev</td>
                        <td>{{ $visitCount }} / 50</td>
                        <td class="center">30%</td>
                        <td class="center">{{ $nilaiVisit }}</td>
                    </tr>
                    <tr>
                        <td class="center">4</td>
                        <td>Penilaian Atasan (Sikap & Perilaku)</td>
                        <td>{{ $manualVal }}%</td>
                        <td class="center">10%</td>
                        <td class="center">{{ $nilaiManualPart }}</td>
                    </tr>
                    <tr>
                        <td class="center">5</td>
                        <td>Daily Activity (KPI Harian)</td>
                        <td>{{ number_format($dailyTotalKpi ?? 0, 0) }}%</td>
                        <td class="center">(Ref)</td>
                        <td class="center">{{ number_format($dailyTotalKpi ?? 0, 0) }}</td>
                    </tr>
                @else
                    {{-- DEFAULT TABLE --}}
                    <tr>
                        <td class="center">1</td>
                        <td>Leads MBC (Marketing Bisnis Class)</td>
                        <td>{{ $leadsMBC }} / {{ $targetLeadsMBC }}</td>
                        <td class="center">45%</td>
                        <td class="center">{{ $nilaiLeadsMBC }}</td>
                    </tr>
                    <tr>
                        <td class="center">2</td>
                        <td>Leads M1T (Mentoring 1 Tahun)</td>
                        <td>{{ $leadsSMI }} / {{ $targetLeadsSMI }}</td>
                        <td class="center">45%</td>
                        <td class="center">{{ $nilaiLeadsSMI }}</td>
                    </tr>
                    <tr>
                        <td class="center">3</td>
                        <td>Penilaian Atasan (Sikap & Perilaku)</td>
                        <td>{{ $manualVal }}%</td>
                        <td class="center">10%</td>
                        <td class="center">{{ $nilaiManualPart }}</td>
                    </tr>
                    <tr>
                        <td class="center">4</td>
                        <td>Daily Activity (KPI Harian)</td>
                        <td>{{ number_format($dailyTotalKpi ?? 0, 0) }}%</td>
                        <td class="center">(Ref)</td>
                        <td class="center">{{ number_format($dailyTotalKpi ?? 0, 0) }}</td>
                    </tr>
                @endif
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL NILAI AKHIR</td>
                <td class="center">{{ $totalNilai }}</td>
            </tr>
        </tfoot>
    </table>

    <div
        style="margin-top: 20px; text-align: center; padding: 15px; border-radius: 8px; font-weight: bold; color: white; background-color: {{ $totalNilai < 70 ? '#dc3545' : '#28a745' }}">
        STATUS PERFORMA: {{ $totalNilai < 70 ? 'UNDERPERFORMANCE' : 'GOOD PERFORMANCE' }}
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <div style="margin-top: 40px;">
            <p>Atasan Langsung,</p>
            <div class="signature"> ( ................................ ) </div>
        </div>
    </div>
</body>

</html>