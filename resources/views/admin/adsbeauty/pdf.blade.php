<!DOCTYPE html>
<html>
<head>
    <title>Activity Advertiser - {{ $monthName }} {{ $tahun }}</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #1e1b4b;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            border-radius: 0 0 10px 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 10px;
            opacity: 0.8;
        }
        .container {
            padding: 0 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
            padding: 8px 4px;
            border: 1px solid #e2e8f0;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #f1f5f9;
            text-align: center;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .text-start {
            text-align: left;
            padding-left: 10px;
        }
        .fw-bold {
            font-weight: bold;
            color: #0f172a;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        
        .met-target { color: #15803d; font-weight: bold; }
        .missed-target { color: #b91c1c; font-weight: bold; }
        
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #94a3b8;
            padding: 10px;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #475569;
            text-transform: uppercase;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP PERFORMA IKLAN (ADS)</h2>
        <p>Periode: <strong>{{ $monthName }} {{ $tahun }}</strong> | Advertiser: <strong>{{ $userName }}</strong></p>
    </div>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th class="text-start">Daftar Ads</th>
                    <th style="width: 45px;">Status</th>
                    <th style="width: 60px;">Tgl Mulai</th>
                    <th style="width: 60px;">Tgl Setting</th>
                    <th style="width: 35px;">Leads</th>
                    <th style="width: 35px;">Clos.</th>
                    <th>Omset</th>
                    <th style="width: 50px;">Conv.</th>
                    <th style="width: 45px;">CPA</th>
                    <th style="width: 40px;">ROAS</th>
                    <th style="width: 55px;">CPL</th>
                    <th style="width: 55px;">Budget Terpakai</th>
                    <th style="width: 60px;">Realisasi</th>
                    <th style="width: 55px;">Pengajuan Budget</th>
                </tr>
            </thead>
            <tbody>
                @foreach($adsPerformances as $i => $item)
                @php
                    $isConvMet = $item->conv_rate >= 30;
                    $isCpaMet = $item->cpa <= 10;
                    $isRoasMet = $item->roas >= 5;
                    $isCplMet = $item->cpl <= 30000;
                @endphp
                <tr>
                    <td style="color: #94a3b8;">{{ $i + 1 }}</td>
                    <td class="text-start fw-bold">{{ $item->kelas->nama_kelas ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $item->is_running ? 'badge-success' : 'badge-danger' }}">
                            {{ $item->is_running ? 'JALAN' : 'OFF' }}
                        </span>
                    </td>
                    <td style="font-size: 8px;">{{ \Carbon\Carbon::parse($item->tanggal_kelas)->format('d/m/Y') }}</td>
                    <td style="font-size: 8px;">{{ $item->tanggal_set ? \Carbon\Carbon::parse($item->tanggal_set)->format('d/m/Y') : '-' }}</td>
                    <td class="fw-bold">{{ number_format($item->total_leads) }}</td>
                    <td class="fw-bold">{{ number_format($item->jumlah_closing) }}</td>
                    <td class="badge-blue fw-bold">Rp {{ number_format($item->omset, 0, ',', '.') }}</td>
                    <td class="{{ $isConvMet ? 'met-target' : 'missed-target' }}">{{ number_format($item->conv_rate, 1) }}%</td>
                    <td class="{{ $isCpaMet ? 'met-target' : 'missed-target' }}">{{ number_format($item->cpa, 1) }}%</td>
                    <td class="{{ $isRoasMet ? 'met-target' : 'missed-target' }}">{{ number_format($item->roas, 1) }}x</td>
                    <td class="{{ $isCplMet ? 'met-target' : 'missed-target' }}">Rp{{ number_format($item->cpl, 0, ',', '.') }}</td>
                    <td class="fw-bold">Rp{{ number_format($item->budget_iklan, 0, ',', '.') }}</td>
                    <td class="fw-bold" style="color: #b91c1c;">Rp{{ number_format($item->realisasi ?? 0, 0, ',', '.') }}</td>
                    <td class="fw-bold">Rp{{ number_format($item->pengajuan_budget ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #1e1b4b; color: white; font-size: 8px;">
                    <td colspan="5" style="padding: 8px; text-align: center; font-weight: bold; text-transform: uppercase;">Total Keseluruhan</td>
                    <td style="font-weight: bold;">{{ number_format($adsPerformances->sum('total_leads')) }}</td>
                    <td style="font-weight: bold;">{{ number_format($adsPerformances->sum('jumlah_closing')) }}</td>
                    <td style="font-weight: bold;">Rp {{ number_format($adsPerformances->sum('omset'), 0, ',', '.') }}</td>
                    <td colspan="4" style="background-color: #312e81; opacity: 0.5;"></td>
                    <td style="font-weight: bold;">Rp {{ number_format($adsPerformances->sum('budget_iklan'), 0, ',', '.') }}</td>
                    <td style="font-weight: bold; color: #fbbf24;">Rp {{ number_format($adsPerformances->sum('realisasi'), 0, ',', '.') }}</td>
                    <td style="font-weight: bold;">Rp {{ number_format($adsPerformances->sum('pengajuan_budget'), 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color: #f8fafc; color: #1e293b; font-size: 8px;">
                    <td colspan="12" style="padding: 6px; text-align: right; font-weight: bold; border-right: 1px solid #e2e8f0;">SISA BUDGET (REALISASI - BUDGET TERPAKAI)</td>
                    <td colspan="2" style="padding: 6px; text-align: center; font-weight: bold; color: #1e40af; background-color: #eff6ff;">
                        Rp {{ number_format(($adsPerformances->sum('realisasi') - $adsPerformances->sum('budget_iklan')), 0, ',', '.') }}
                    </td>
                    <td style="background-color: #f1f5f9;"></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Dicetak secara otomatis pada: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
            <p>&copy; WebHelas Advertiser Tracking Systems</p>
        </div>
    </div>
</body>
</html>
