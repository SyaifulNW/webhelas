<!DOCTYPE html>
<html>
<head>
    <title>Rekap Interaksi Follow Up</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            background-color: #ffff00;
            border: 2px solid #0000ff;
            display: inline-block;
            padding: 5px 20px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            word-wrap: break-word;
        }
        th {
            background-color: #00ffff;
            text-transform: uppercase;
            font-weight: bold;
        }
        .bg-light {
            background-color: #f2f2f2;
        }
        .text-left {
            text-align: left;
        }
        .fu-cell {
            font-size: 8px;
        }
        .check-icon {
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">RIWAYAT INTERAKSI FOLLOW UP LEADS ({{ $csName }})</div>
        <p>Periode: 
            @if($bulan)
                {{ \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM') }}
            @else
                Semua Bulan
            @endif
            {{ $tahun }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 30px;">NO</th>
                <th rowspan="3" style="width: 70px;">TANGGAL</th>
                <th style="width: 150px;">NAMA</th>
                @for($i=1; $i<=10; $i++)
                <th colspan="2">FU {{ $i }}</th>
                @endfor
            </tr>
            <tr>
                <th>NO WA</th>
                @for($i=1; $i<=10; $i++)
                <th>TELP</th>
                <th>WA</th>
                @endfor
            </tr>
            <tr>
                <th></th>
                @for($i=1; $i<=10; $i++)
                <th>HASIL</th>
                <th>TINDAK LANJUT</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
            @php $isEven = $index % 2 == 0; @endphp
            <tr style="background-color: {{ $isEven ? '#ffffff' : '#f9f9f9' }};">
                <td rowspan="2">{{ $index + 1 }}</td>
                <td rowspan="2">
                    @php
                        $interactionDate = '-';
                        for($i=1; $i<=10; $i++){
                            $fuAt = "fu{$i}_at";
                            if($item->$fuAt && \Carbon\Carbon::parse($item->$fuAt)->month == $bulan && \Carbon\Carbon::parse($item->$fuAt)->year == $tahun){
                                $interactionDate = \Carbon\Carbon::parse($item->$fuAt)->format('d/m/Y');
                                break;
                            }
                        }
                    @endphp
                    {{ $interactionDate }}
                </td>
                <td class="text-left"><strong>{{ $item->nama }}</strong></td>
                @for($i=1; $i<=10; $i++)
                    @php 
                        $telp = "fu{$i}_telp";
                        $wa = "fu{$i}_wa";
                    @endphp
                    <td>{!! $item->$telp ? '<span class="check-icon">✔</span>' : '-' !!}</td>
                    <td>{!! $item->$wa ? '<span class="check-icon">✔</span>' : '-' !!}</td>
                @endfor
            </tr>
            <tr style="background-color: {{ $isEven ? '#ffffff' : '#f9f9f9' }}; border-bottom: 2px solid #000;">
                <td class="text-left">{{ $item->no_wa }}</td>
                @for($i=1; $i<=10; $i++)
                    @php 
                        $hasil = "fu{$i}_hasil";
                        $tl = "fu{$i}_tindak_lanjut";
                        $fuAt = "fu{$i}_at";
                        $formattedDate = $item->$fuAt ? \Carbon\Carbon::parse($item->$fuAt)->format('d/m/Y H:i') : null;
                    @endphp
                    <td class="text-left fu-cell">
                        <div>{{ $item->$hasil ?: '-' }}</div>
                        @if($formattedDate)
                            <div style="font-size: 6px; color: #000; margin-top: 6px; border: 0.5px solid #999; padding: 2px; background-color: #f8f9fa; display: block; text-align: center; border-radius: 2px;">
                                {{ $formattedDate }}
                            </div>x
                        @endif
                    </td>
                    <td class="text-left fu-cell">
                        <div>{{ $item->$tl ?: '-' }}</div>
                        @if($formattedDate)
                            <div style="font-size: 6px; color: #000; margin-top: 6px; border: 0.5px solid #999; padding: 2px; background-color: #f8f9fa; display: block; text-align: center; border-radius: 2px;">
                                {{ $formattedDate }}
                            </div>
                        @endif
                    </td>
                @endfor
            </tr>
            @empty
            <tr>
                <td colspan="23">Data tidak ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $tindakLanjutList = [];
        foreach($items as $item) {
            $latestTl = null;
            $latestDate = null;
            for($i=10; $i>=1; $i--){
                $tl = "fu{$i}_tindak_lanjut";
                $fuAt = "fu{$i}_at";
                if(!empty($item->$tl) && $item->$tl != '-') {
                    // Check if it's within the selected month and year
                    $isMatchPeriode = true;
                    if ($bulan && $tahun) {
                        try {
                            $itemDate = \Carbon\Carbon::parse($item->$fuAt);
                            if ($itemDate->month != $bulan || $itemDate->year != $tahun) {
                                $isMatchPeriode = false;
                            }
                        } catch (\Exception $e) {
                            $isMatchPeriode = false;
                        }
                    }

                    if ($isMatchPeriode) {
                        $latestTl = $item->$tl;
                        $latestDate = $item->$fuAt;
                        break;
                    }
                }
            }
            if($latestTl) {
                $tindakLanjutList[] = [
                    'nama' => $item->nama,
                    'tindak_lanjut' => $latestTl,
                    'no_wa' => $item->no_wa,
                    'tanggal' => $latestDate ? \Carbon\Carbon::parse($latestDate)->format('d/m/Y') : '-'
                ];
            }
        }
    @endphp

    @if(count($tindakLanjutList) > 0)
    <div style="margin-top: 30px; margin-bottom: 10px; text-align: left; font-size: 12px; font-weight: bold; text-transform: uppercase;">
        Berikut yang harus di tindak lanjuti
    </div>
    <table style="margin-top: 5px;">
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th style="width: 80px;">TANGGAL</th>
                <th style="width: 200px;">NAMA</th>
                <th style="width: 150px;">NO WA</th>
                <th>TINDAK LANJUT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tindakLanjutList as $index => $tl)
            @php $isEven = $index % 2 == 0; @endphp
            <tr style="background-color: {{ $isEven ? '#ffffff' : '#f9f9f9' }};">
                <td>{{ $index + 1 }}</td>
                <td>{{ $tl['tanggal'] }}</td>
                <td class="text-left"><strong>{{ $tl['nama'] }}</strong></td>
                <td class="text-left">{{ $tl['no_wa'] }}</td>
                <td class="text-left">{{ $tl['tindak_lanjut'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
