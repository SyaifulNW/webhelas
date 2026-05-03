<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengajuan Anggaran - {{ $monthName }} {{ $yearDisplay ?? $year }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #4e73df;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #f8f9fc;
            color: #4e73df;
            font-weight: bold;
            text-align: center;
            padding: 10px 5px;
            border: 1.5px solid #aaa;
            text-transform: uppercase;
            font-size: 10px;
        }
        table td {
            padding: 8px 5px;
            border: 1.5px solid #aaa;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            color: white;
            display: inline-block;
            text-transform: uppercase;
            font-weight: bold;
        }
        .status-pending { background-color: #f6c23e; }
        .status-approved { background-color: #1cc88a; }
        .status-rejected { background-color: #e74a3b; }

        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
        }
        .signature-space {
            height: 60px;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fc;
            border-radius: 5px;
            border: 1.5px solid #aaa;
        }
        .summary-item {
            margin-bottom: 5px;
            font-size: 12px;
        }
        .text-success { color: #1cc88a; }
        .text-danger { color: #e74a3b; }
        .text-primary { color: #4e73df; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Pengajuan Anggaran @if(isset($displayName)) ({{ $displayName }}) @endif</h2>
        <p>Periode: {{ $monthName }} {{ $yearDisplay ?? $year }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal Kebutuhan</th>
                <th width="20%">Nama Pengajuan</th>
                <th width="12%">Pemohon</th>
                <th width="12%">Biaya Awl</th>
                <th width="12%">Biaya Disetujui</th>
                <th width="12%">Biaya Sisa</th>
                <th width="10%">Status</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalAwal = 0; 
                $totalSetuju = 0;
                $totalSisa = 0;
            @endphp
            @forelse($requests as $i => $req)
                @php
                    $totalAwal += $req->jumlah_biaya;
                    $totalSetuju += $req->status === 'approved' ? $req->biaya_disetujui : 0;
                    $totalSisa += $req->status === 'approved' ? ($req->jumlah_biaya - $req->biaya_disetujui) : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($req->tanggal_pengajuan)->format('d/m/Y') }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $req->nama_pengajuan }}</div>
                        @if($req->keterangan)
                            <div style="font-size: 9px; color: #666; font-style: italic;">{{ $req->keterangan }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $req->diajukan_oleh }}</td>
                    <td class="text-right">Rp {{ number_format($req->jumlah_biaya, 0, ',', '.') }}</td>
                    <td class="text-right font-weight-bold text-success">
                        @if($req->status === 'approved')
                            Rp {{ number_format($req->biaya_disetujui, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right text-danger">
                        @if($req->status === 'approved')
                            Rp {{ number_format($req->jumlah_biaya - $req->biaya_disetujui, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $req->status }}">
                            {{ $req->status === 'approved' ? 'Disetujui' : ($req->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                        </span>
                    </td>
                    <td>
                        @if($req->catatan_admin)
                            {{ $req->catatan_admin }}
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot style="background-color: #f8f9fc; font-weight: bold;">
            <tr>
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalAwal, 0, ',', '.') }}</td>
                <td class="text-right text-success">Rp {{ number_format($totalSetuju, 0, ',', '.') }}</td>
                <td class="text-right text-danger">Rp {{ number_format($totalSisa, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <div class="summary-item"><strong>Ringkasan Laporan:</strong></div>
        <div class="summary-item">Total Nominal Pengajuan: <span class="text-primary">Rp {{ number_format($totalAwal, 0, ',', '.') }}</span></div>
        <div class="summary-item">Total Dana Disetujui: <span class="text-success">Rp {{ number_format($totalSetuju, 0, ',', '.') }}</span></div>
        <div class="summary-item">Total Efisiensi (Sisa): <span class="text-danger">Rp {{ number_format($totalSisa, 0, ',', '.') }}</span></div>
    </div>

    <div class="footer">
        <div class="signature-box">
            <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
            <div class="signature-space"></div>
            <p>__________________________</p>
            <p><strong>Keuangan Helas Corp</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
