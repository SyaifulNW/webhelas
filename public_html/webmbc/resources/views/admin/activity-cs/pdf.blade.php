<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Daily Activity {{ $cs->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        h3 { text-align: center; margin-bottom: 5px; }
        h4 { text-align: center; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 0.5px solid #333; padding: 4px; text-align: center; }
        th { background: #e2e8f0; font-weight: bold; }
        td.left { text-align: left; }
    </style>
</head>
<body>
    <h3>Laporan Daily Activity CS</h3>
    <h4>{{ $tanggal }} — {{ $cs->name }}</h4>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Aktivitas</th>
                <th>Kategori</th>
                <th>Target Daily</th>
                <th>Target Bulanan</th>
                <th>Bobot</th>
                <th>Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $i => $act)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td class="left">{{ $act->activity->nama }}</td>
                    <td>{{ $act->activity->kategori->nama ?? '-' }}</td>
                    <td>{{ $act->activity->target_daily }}</td>
                    <td>{{ $act->activity->target_bulanan }}</td>
                    <td>{{ $act->activity->bobot }}</td>
                    <td>{{ $act->realisasi }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
