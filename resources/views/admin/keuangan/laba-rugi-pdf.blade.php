    <!DOCTYPE html>
    <html>
    <head>
        <title>LAPORAN LABA RUGI HELAS CORPORATION {{ $namaBulan }} {{ $tahunDisplay ?? $tahun }}</title>
        <style>
            body {
                font-family: 'Helvetica', sans-serif;
                font-size: 11px;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
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
                font-size: 14px;
                font-weight: bold;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th {
                background-color: #f8f9fc;
                color: #4e73df;
                padding: 8px;
                text-align: left;
                border: 1.5px solid #aaa;
                text-transform: uppercase;
            }
            td {
                padding: 8px;
                border: 1.5px solid #aaa;
                vertical-align: top;
            }
            .category-header {
                background-color: #eaecf4;
                font-weight: bold;
                color: #41434e;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .font-weight-bold {
                font-weight: bold;
            }
            .text-primary {
                color: #4e73df;
            }
            .text-danger {
                color: #e74a3b;
            }
            .bg-light {
                background-color: #f8f9fc;
            }
            .sub-item {
                font-size: 10px;
                color: #5a5c69;
                background-color: #fafafa;
            }
            .sub-item .nominal {
                text-align: left !important;
                padding-left: 20px;
            }
            .total-row {
                background-color: #f8f9fc;
                font-weight: bold;
                font-size: 12px;
            }
            .grand-total {
                background-color: #4e73df;
                color: white;
                font-weight: bold;
                font-size: 14px;
            }
            .footer {
                margin-top: 30px;
                text-align: right;
            }
            .footer p {
                margin: 0;
            }
            .badge {
                display: inline-block;
                padding: 2px 6px;
                font-size: 9px;
                font-weight: bold;
                border-radius: 4px;
                background-color: #f0f3ff;
                color: #4e73df;
                border: 1px solid #d1d3e2;
                margin-left: 5px;
                text-transform: uppercase;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="{{ public_path('backend/helas.png') }}" style="height: 60px; margin-bottom: 10px;">
            <h2>LAPORAN LABA RUGI HELAS CORPORATION</h2>
            <p>Periode: {{ $namaBulan }} {{ $tahunDisplay ?? $tahun }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="40%">Deskripsi Transaksi</th>
                    <th width="30%" class="text-right">Nominal (Rp)</th>
                    <th width="10%" class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $calcTotalPendapatan = ($totalMbc ?? 0) + ($totalSmi ?? 0) + ($totalPrivate ?? 0) + ($pendapatan->where('keterangan', 'Pendapatan Lainnya')->first() ? $pendapatan->where('keterangan', 'Pendapatan Lainnya')->first()->jumlah : 0);
                    $totalSmiPendaftaran = $totalSmiPendaftaran ?? 0;
                    $totalSmiSpp = $totalSmiSpp ?? 0;
                @endphp
                <tr class="category-header">
                    <td colspan="5">PENDAPATAN</td>
                </tr>
                @php $totalPendapatan = 0; @endphp
                
                @php $totalPendapatan += $totalMbc; @endphp
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-center">-</td>
                    <td class="font-weight-bold">Pendapatan MBC (Auto)</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($totalMbc, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($totalMbc / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
                @php $mbcLetters = range('a', 'z'); $mbcIdx = 0; @endphp
                @foreach($mbcBreakdown as $namaKelas => $nominal)
                <tr class="sub-item">
                    <td></td>
                    <td></td>
                    <td style="padding-left: 30px;">{{ $mbcLetters[$mbcIdx++] ?? '-' }}. {{ $namaKelas }}</td>
                    <td class="nominal text-primary">{{ number_format($nominal, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($nominal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
                @endforeach

                @php $totalPendapatan += $totalSmi; @endphp
                <tr>
                    <td class="text-center">2</td>
                    <td class="text-center">-</td>
                    <td class="font-weight-bold">Pendapatan SMI (Auto)</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($totalSmi, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($totalSmi / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
                {{-- Sub-items for SMI: Pendaftaran and SPP --}}
                <tr class="sub-item">
                    <td></td>
                    <td></td>
                    <td style="padding-left: 30px;">a. Pendaftaran SMI</td>
                    <td class="nominal text-primary">{{ number_format($totalSmiPendaftaran, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($totalSmiPendaftaran / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
                <tr class="sub-item">
                    <td></td>
                    <td></td>
                    <td style="padding-left: 30px;">b. SPP SMI</td>
                    <td class="nominal text-primary">{{ number_format($totalSmiSpp, 0, ',', '.') }}</td>
                    <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($totalSmiSpp / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>

                @php $totalPendapatan += $totalPrivate; @endphp
                <tr>
                    <td class="text-center">3</td>
                    <td class="text-center">-</td>
                    <td>Pendapatan Private Coaching (Auto)</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($totalPrivate, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($totalPrivate / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>

                @php 
                    $pendapatanLain = $pendapatan->where('keterangan', 'Pendapatan Lainnya')->first();
                    $nilaiLain = $pendapatanLain ? $pendapatanLain->jumlah : 0;
                    $totalPendapatan += $nilaiLain;
                @endphp
                <tr>
                    <td class="text-center">4</td>
                    <td class="text-center">{{ $pendapatanLain && $pendapatanLain->tanggal ? date('d/m/Y', strtotime($pendapatanLain->tanggal)) : '-' }}</td>
                    <td>Pendapatan Lainnya</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($nilaiLain, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($nilaiLain / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>

                @php 
                    $chapters = \App\Models\User::where('role', 'chapter')->orderBy('name')->get();
                    $chapterMainSum = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === 'Pendapatan Chapter' && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                    $chapterSubSum = $pendapatan->filter(fn($r) => trim($r->parent_keterangan ?? '') === 'Pendapatan Chapter')->sum('jumlah');
                    $chapterTotal = $chapterMainSum + $chapterSubSum;
                    $totalPendapatan += $chapterTotal;
                @endphp
                <tr>
                    <td class="text-center">5</td>
                    <td class="text-center">-</td>
                    <td class="font-weight-bold">Pendapatan Chapter</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($chapterTotal, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($chapterTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
                @php $pChapterLetters = range('a', 'z'); $pChapterLetterIdx = 0; @endphp
                @foreach($chapters as $ch)
                    @php
                        $chKeterangan = $ch->name;
                        $subRow = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === $chKeterangan && trim($r->parent_keterangan ?? '') === 'Pendapatan Chapter')->first();
                        $subJumlah = $subRow ? $subRow->jumlah : 0;
                    @endphp
                    @if($subJumlah > 0)
                        <tr class="sub-item">
                            <td></td>
                            <td class="text-center">{{ $subRow && $subRow->tanggal ? date('d/m/Y', strtotime($subRow->tanggal)) : '-' }}</td>
                            <td style="padding-left: 30px;">{{ $pChapterLetters[$pChapterLetterIdx++] ?? '-' }}. <span class="font-weight-bold" style="color: #333;">{{ $chKeterangan }}</span> <span class="badge">{{ $ch->chapter }}</span></td>
                            <td class="nominal text-primary">{{ number_format($subJumlah, 0, ',', '.') }}</td>
                            <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($subJumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                        </tr>
                    @endif
                @endforeach

                @php 
                    $agens = \App\Models\User::where('role', 'reseller')->orderBy('name')->get();
                    $agenMainSum = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === 'Pendapatan Agen' && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                    $agenSubSum = $pendapatan->filter(fn($r) => trim($r->parent_keterangan ?? '') === 'Pendapatan Agen')->sum('jumlah');
                    $agenTotal = $agenMainSum + $agenSubSum;
                    $totalPendapatan += $agenTotal;
                @endphp
                <tr>
                    <td class="text-center">6</td>
                    <td class="text-center">-</td>
                    <td class="font-weight-bold">Pendapatan Agen</td>
                    <td class="text-right text-primary font-weight-bold">{{ number_format($agenTotal, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($agenTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
                @php $pAgenLetters = range('a', 'z'); $pAgenLetterIdx = 0; @endphp
                @foreach($agens as $ag)
                    @php
                        $agKeterangan = $ag->name;
                        $subRow = $pendapatan->filter(fn($r) => trim($r->keterangan ?? '') === $agKeterangan && trim($r->parent_keterangan ?? '') === 'Pendapatan Agen')->first();
                        $subJumlah = $subRow ? $subRow->jumlah : 0;
                    @endphp
                    @if($subJumlah > 0)
                        <tr class="sub-item">
                            <td></td>
                            <td class="text-center">{{ $subRow && $subRow->tanggal ? date('d/m/Y', strtotime($subRow->tanggal)) : '-' }}</td>
                            <td style="padding-left: 30px;">{{ $pAgenLetters[$pAgenLetterIdx++] ?? '-' }}. <span class="font-weight-bold" style="color: #333;">{{ $agKeterangan }}</span> <span class="badge">{{ $ag->chapter }}</span></td>
                            <td class="nominal text-primary">{{ number_format($subJumlah, 0, ',', '.') }}</td>
                            <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($subJumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                        </tr>
                    @endif
                @endforeach


                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL PENDAPATAN</td>
                    <td class="text-right text-primary">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                    <td class="text-center text-primary">100%</td>
                </tr>

                <tr class="category-header">
                    <td colspan="5">PENGELUARAN</td>
                </tr>
                @php $totalBiaya = 0; @endphp
                @php 
                    $totalBiaya = 0; 
                    $expenseGroups = [
                        ['title' => 'Biaya Event Kelas', 'is_standalone' => true, 'db_key' => 'Biaya Event Kelas'],
                        ['title' => 'Biaya Gaji Karyawan', 'is_standalone' => true, 'db_key' => 'Biaya Gaji Karyawan'],
                        ['title' => 'Biaya Rumah Tangga', 'is_standalone' => false, 'members' => [
                            'Biaya Air' => 'Biaya Air', 'Biaya Listrik' => 'Biaya Listrik', 'Biaya Maintenance Web' => 'Biaya Maintenance Web',
                            'Biaya Kuota' => 'Biaya Kuota/Pulsa', 'Biaya BPJS' => 'Biaya BPJS', 'Biaya Internet & Wifi' => 'Biaya Internet Wifi',
                            'Biaya Kebersihan & Keamanan' => 'Biaya Kebersihan & Keamanan'
                        ]],
                        ['title' => 'Biaya Marketing', 'is_standalone' => false, 'members' => ['Biaya Iklan' => 'Biaya Iklan']],
                        ['title' => 'Biaya ATK', 'is_standalone' => false, 'members' => ['Biaya Cetak/Print' => 'Biaya Cetak/Print', 'Biaya Alat Tulis' => 'Biaya Alat Tulis']],
                        ['title' => 'Biaya Lain-lain', 'is_standalone' => true, 'db_key' => 'Biaya Lain-lain'],
                        ['title' => 'Pengeluaran Coach', 'is_standalone' => true, 'db_key' => 'Pengeluaran Coach'],
                    ];
                    $mainIndex = 1;
                @endphp

                @foreach($expenseGroups as $group)
                    @php
                        $groupTotal = 0;
                        if ($group['is_standalone']) {
                            $itemKey = $group['db_key'];
                            $subItems = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $itemKey);
                            $mainSum = $biaya->filter(fn($r) => trim($r->keterangan ?? '') === $itemKey && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                            $groupTotal = $mainSum + $subItems->sum('jumlah');
                        } else {
                            foreach ($group['members'] as $dbKey => $displayTitle) {
                                $mSub = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $dbKey);
                                $mMainSum = $biaya->filter(fn($r) => trim($r->keterangan ?? '') === $dbKey && empty(trim($r->parent_keterangan ?? '')))->sum('jumlah');
                                $groupTotal += $mMainSum + $mSub->sum('jumlah');
                            }
                        }
                        $totalBiaya += $groupTotal;
                    @endphp
                    <tr class="bg-light">
                        <td class="text-center">{{ $mainIndex++ }}</td>
                        <td class="text-center">-</td>
                        <td class="font-weight-bold">{{ $group['title'] }}</td>
                        <td class="text-right text-danger font-weight-bold">{{ number_format($groupTotal, 0, ',', '.') }}</td>
                        <td class="text-center text-danger font-weight-bold">{{ $calcTotalPendapatan > 0 ? number_format(($groupTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                    </tr>

                    @php $lIdx = 0; $letters = range('a', 'z'); @endphp
                    @if($group['is_standalone'])
                        @php $itemKey = $group['db_key']; $subItems = $biaya->filter(fn($r) => trim($r->parent_keterangan ?? '') === $itemKey); @endphp
                        @foreach($subItems as $sub)
                            @if($sub->jumlah <= 0) @continue @endif
                            <tr class="sub-item">
                                <td></td>
                                <td class="text-center">{{ $sub->tanggal ? date('d/m/Y', strtotime($sub->tanggal)) : '-' }}</td>
                                <td style="padding-left: 20px;">{{ $letters[$lIdx++] ?? '-' }}. {{ $sub->keterangan }}</td>
                                <td class="nominal text-danger">{{ number_format($sub->jumlah, 0, ',', '.') }}</td>
                                <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($sub->jumlah / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                            </tr>
                        @endforeach
                    @else
                        @foreach($group['members'] as $dbKey => $displayTitle)
                            @php
                                $mAll = $biaya->filter(fn($r) => trim($r->keterangan ?? '') === $dbKey || trim($r->parent_keterangan ?? '') === $dbKey);
                                $mTotal = $mAll->sum('jumlah');
                            @endphp
                            @if($mTotal > 0)
                                <tr class="sub-item">
                                    <td></td>
                                    <td class="text-center">
                                        @php $mFirst = $mAll->where('jumlah', '>', 0)->first(); @endphp
                                        {{ $mFirst && $mFirst->tanggal ? date('d/m/Y', strtotime($mFirst->tanggal)) : '-' }}
                                    </td>
                                    <td style="padding-left: 20px;">{{ $letters[$lIdx++] ?? '-' }}. {{ $displayTitle }}</td>
                                    <td class="nominal text-danger">{{ number_format($mTotal, 0, ',', '.') }}</td>
                                    <td class="text-center" style="font-size: 8px;">{{ $calcTotalPendapatan > 0 ? number_format(($mTotal / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL PENGELUARAN</td>
                    <td class="text-right text-danger">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                    <td class="text-center text-danger">{{ $calcTotalPendapatan > 0 ? number_format(($totalBiaya / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>

                @php $labaRugi = $totalPendapatan - $totalBiaya; @endphp
                <tr class="grand-total">
                    <td colspan="3" class="text-right">LABA / RUGI BERSIH</td>
                    <td class="text-right">Rp {{ number_format($labaRugi, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $calcTotalPendapatan > 0 ? number_format(($labaRugi / $calcTotalPendapatan) * 100, 1) . '%' : '0%' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Dicetak pada: {{ date('d F Y H:i:s') }}</p>
            <p>Oleh: {{ Auth::user()->name }}</p>
        </div>
    </body>
    </html>
