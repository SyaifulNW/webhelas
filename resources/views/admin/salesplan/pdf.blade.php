<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Follow Up & RTL - Salesplan</title>
    <style>
        @page {
            margin: 15mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .title {
            background: linear-gradient(135deg, #1a5276, #2980b9);
            color: #fff;
            display: inline-block;
            padding: 8px 30px;
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 11px;
            margin-top: 6px;
            color: #555;
        }
        .meta-info {
            font-size: 10px;
            color: #777;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #666;
            padding: 4px 3px;
            text-align: center;
            word-wrap: break-word;
            font-size: 8px;
        }
        th {
            background-color: #2c3e50;
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 7px;
        }
        .th-fu {
            background-color: #1a5276;
        }
        .th-sub {
            background-color: #2980b9;
            font-size: 6.5px;
        }
        .text-left {
            text-align: left;
        }
        .fw-bold {
            font-weight: bold;
        }
        .status-sudah_transfer {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-mau_transfer {
            background-color: #d4edda;
            color: #155724;
        }
        .status-tertarik {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cold {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .status-no {
            background-color: #f8d7da;
            color: #721c24;
        }
        .fu-date {
            font-size: 7px;
            color: #000;
            font-weight: bold;
            margin-top: 3px;
            border: 1px solid #333;
            padding: 2px 4px;
            display: inline-block;
            background-color: #f5f5f5;
        }
        .row-even {
            background-color: #f9f9f9;
        }
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Riwayat Follow Up dan Rencana Tindak Lanjut</div>
        <div class="subtitle">Salesplan {{ $type }}</div>
        <div class="subtitle">oleh CS — <strong>{{ $csName }}</strong></div>
        <div class="meta-info">
            Dicetak: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
        </div>
    </div>

    @php
        // Determine max FU level used across all data
        $maxFu = 5;
        foreach ($salesplans as $p) {
            for ($f = 12; $f >= 6; $f--) {
                if (!empty($p->{'fu'.$f.'_hasil'}) || !empty($p->{'fu'.$f.'_tindak_lanjut'})) {
                    $maxFu = max($maxFu, $f);
                    break;
                }
            }
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 20px;">No</th>
                <th rowspan="3" style="width: 70px;">Nama</th>
                <th rowspan="3" style="width: 50px;">Status</th>
                <th rowspan="3" style="width: 55px;">Kebutuhan</th>
                <th colspan="{{ $maxFu * 2 }}" class="th-fu">Follow Up</th>
            </tr>
            <tr>
                @for ($i = 1; $i <= $maxFu; $i++)
                <th colspan="2" class="th-fu">FU {{ $i }}</th>
                @endfor
            </tr>
            <tr>
                @for ($i = 1; $i <= $maxFu; $i++)
                <th class="th-sub">Hasil FU</th>
                <th class="th-sub">RTL</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse ($salesplans as $index => $plan)
                @php
                    $statusLabel = [
                        'sudah_transfer' => 'Sudah Transfer',
                        'mau_transfer' => 'Assesmen',
                        'tertarik' => 'Tertarik',
                        'cold' => 'Cold',
                        'no' => 'No',
                    ];
                @endphp
                <tr class="{{ $index % 2 == 0 ? '' : 'row-even' }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left fw-bold">{{ $plan->nama ?? '-' }}</td>
                    <td>
                        <span class="badge-status status-{{ $plan->status }}">
                            {{ $statusLabel[$plan->status] ?? $plan->status }}
                        </span>
                    </td>
                    <td class="text-left">{{ $plan->kebutuhan ?? '-' }}</td>

                    @for ($i = 1; $i <= $maxFu; $i++)
                        {{-- Hasil FU --}}
                        <td class="text-left">
                            {{ $plan->{'fu'.$i.'_hasil'} ?? '-' }}
                            @if($plan->{'fu'.$i.'_at'})
                                <div class="fu-date">
                                    {{ \Carbon\Carbon::parse($plan->{'fu'.$i.'_at'})->format('d/m/y H:i') }}
                                </div>
                            @endif
                        </td>
                        {{-- RTL --}}
                        <td class="text-left">
                            {{ $plan->{'fu'.$i.'_tindak_lanjut'} ?? '-' }}
                            @if($plan->{'fu'.$i.'_rtl_at'})
                                <div class="fu-date">
                                    {{ \Carbon\Carbon::parse($plan->{'fu'.$i.'_rtl_at'})->format('d/m/y H:i') }}
                                </div>
                            @endif
                        </td>
                    @endfor

                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + ($maxFu * 2) }}">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($salesplans->count() > 0)
    <div class="footer">
        Total Data: {{ $salesplans->count() }} peserta
    </div>
    @endif

    {{-- Tabel Tindak Lanjut (Status Tertarik) --}}
    @php
        $tertarikPlans = $salesplans->where('status', 'tertarik');
    @endphp

    @if($tertarikPlans->count() > 0)
    <div style="margin-top: 40px;">
        <div style="background-color: #f6c23e; color: #fff; padding: 6px 20px; font-weight: bold; border-radius: 4px; display: inline-block; font-size: 10px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
            Daftar Peserta Yang Harus Ditindak Lanjuti (Status: Tertarik)
        </div>
        <table style="table-layout: auto;">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 120px;">Nama Peserta</th>
                    <th style="width: 80px;">Kebutuhan</th>
                    <th>Hasil FU Terakhir</th>
                    <th>Rencana Tindak Lanjut (RTL)</th>
                    <th style="width: 90px;">Jadwal RTL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tertarikPlans as $tIdx => $tPlan)
                    @php
                        // Cari FU terakhir yang ada isinya
                        $lastNum = 0;
                        for($f=12; $f>=1; $f--) {
                            if(!empty($tPlan->{'fu'.$f.'_hasil'}) || !empty($tPlan->{'fu'.$f.'_tindak_lanjut'})) {
                                $lastNum = $f;
                                break;
                            }
                        }
                    @endphp
                    <tr class="{{ $tIdx % 2 == 0 ? '' : 'row-even' }}">
                        <td>{{ $tIdx + 1 }}</td>
                        <td class="text-left fw-bold">{{ $tPlan->nama ?? '-' }}</td>
                        <td class="text-left">{{ $tPlan->kebutuhan ?? '-' }}</td>
                        <td class="text-left">{{ $tPlan->{'fu'.$lastNum.'_hasil'} ?? '-' }}</td>
                        <td class="text-left">{{ $tPlan->{'fu'.$lastNum.'_tindak_lanjut'} ?? '-' }}</td>
                        <td>
                            @if($tPlan->{'fu'.$lastNum.'_rtl_at'})
                                <div class="fw-bold text-primary">
                                    {{ \Carbon\Carbon::parse($tPlan->{'fu'.$lastNum.'_rtl_at'})->format('d/m/y H:i') }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>
