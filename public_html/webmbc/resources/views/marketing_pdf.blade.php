<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Performance Marketing - {{ $userName }}</title>
    <style>
        @page {
            margin: 0.8cm;
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 8.5px; 
            color: #333;
            line-height: 1.4;
        }
        .header-section {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
        }
        .header-section h2 { 
            margin: 0; 
            font-size: 16px; 
            color: #1a2a3a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-section p { 
            margin: 3px 0 0; 
            font-size: 11px; 
            color: #555;
            font-weight: bold;
        }
        .printed-date {
            text-align: right;
            font-size: 8px;
            color: #777;
            margin-bottom: 5px;
            font-style: italic;
        }
        
        .dashboard-banner { 
            background-color: #ffd700; 
            color: #000;
            padding: 8px; 
            text-align: center; 
            font-weight: bold; 
            font-size: 11px;
            border: 1px solid #c0c0c0;
            margin-bottom: 15px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background-color: #fff;
            table-layout: fixed;
        }
        th { 
            background-color: #e2e8f0; 
            color: #1a2a3a; 
            font-weight: bold; 
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 8px 3px;
            border: 1px solid #a0aec0;
        }
        td { 
            border: 1px solid #cbd5e0; 
            padding: 6px 3px; 
            text-align: center; 
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .left { 
            text-align: left; 
            padding-left: 6px;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-primary { color: #3182ce; }
        .text-success { color: #38a169; }
        .text-danger { color: #e53e3e; }
        .bg-totals { background-color: #edf2f7; font-weight: bold; }
    </style>
</head>
<body>
    <div class="printed-date">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>

    <div class="header-section">
        <h2>Laporan Performance Marketing</h2>
        <p>{{ $userName }} — {{ $monthName }} {{ $tahun }}</p>
    </div>

    <div class="dashboard-banner">
        @if(stripos($userName, 'Nisa') !== false)
            DASHBOARD PERFORMANCE SOSMED SPESIALIS MARKETING
        @elseif(stripos($userName, 'Felmi') !== false)
            DASHBOARD PERFORMANCE EVENT MARKETING
        @else
            DASHBOARD PERFORMANCE MARKETING
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 80px;">{{ stripos($userName, 'Nisa') !== false ? 'Event Zoom' : 'Nama Event' }}</th>
                <th style="width: 80px;">Tema</th>
                <th style="width: 80px;">Pemateri</th>
                <th style="width: 55px;">Tanggal</th>
                <th style="width: 75px;">Lokasi</th>
                @if(stripos($userName, 'Felmi') === false)
                <th style="width: 65px;">Jenis Event</th>
                @endif
                <th style="width: 55px;">Target Peserta</th>
                <th style="width: 55px;">Peserta Hadir</th>
                <th style="width: 55px;">Target Closing</th>
                <th style="width: 55px;">Real Closing</th>
                <th style="width: 45px;">Selisih</th>
                <th style="width: 70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performances as $i => $perf)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="left font-bold">{{ $perf->event_name }}</td>
                    <td class="left">{{ $perf->tema }}</td>
                    <td class="left">{{ $perf->pemateri }}</td>
                    <td>{{ $perf->tanggal ? \Carbon\Carbon::parse($perf->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $perf->lokasi }}</td>
                    @if(stripos($userName, 'Felmi') === false)
                    <td>{{ $perf->jenis_event }}</td>
                    @endif
                    <td class="bg-totals">{{ $perf->target_peserta }}</td>
                    <td>{{ $perf->peserta_hadir ?: '0' }}</td>
                    <td class="bg-totals text-primary">{{ $perf->target_closing }}</td>
                    <td class="font-bold text-success">{{ $perf->real_closing ?: '0' }}</td>
                    <td class="font-bold {{ $perf->selisih < 0 ? 'text-danger' : '' }}">{{ $perf->selisih ?: '0' }}</td>
                    <td class="font-bold">{{ $perf->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ stripos($userName, 'Felmi') !== false ? 12 : 13 }}" style="padding: 30px; color: #718096; font-style: italic; font-size: 11px;">
                        Tidak ada data yang tersedia untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>



