<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\SalesPlan;
use App\Models\Activity;
use App\Models\DailyActiviti;
use App\Models\Data;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // ====================== 📅 FILTER BULAN ======================
        $bulan = $request->input('bulan') ?? Carbon::now()->format('Y-m');
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $tahun = $carbonBulan->year;
        $bulanNum = $carbonBulan->month;

        // ====================== 👤 USER LOGIN ======================
        $csId   = auth()->id();
        $csName = auth()->user()->name;

        // ====================== 🔔 NOTIFIKASI ======================
        $notifikasi = Notifikasi::where('user_id', $csId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $notifCount = Notifikasi::where('user_id', $csId)
            ->where('is_read', false)
            ->count();

        // ====================== 💰 OMSET & KOMISI ======================
        $kelasOmset = Kelas::where(function ($query) use ($tahun, $bulanNum) {
            $query->whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulanNum);
        })
            ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                $query->where('created_by', $csId)
                    ->whereYear('updated_at', $tahun)
                    ->whereMonth('updated_at', $bulanNum);
            }])
            ->get();

        $kelasOmsetFiltered = $kelasOmset->map(function ($kelas) {
            $omset = $kelas->salesplans->sum('nominal');
            $target = 25000000;

            $komisiSementara = $omset * 0.01;
            $komisiTotal = $omset >= $target ? $komisiSementara + 300000 : $komisiSementara;

            return [
                'nama_kelas' => $kelas->nama_kelas,
                'tanggal'    => $kelas->tanggal_mulai,
                'omset'      => $omset,
                'target'     => $target,
                'persen'     => $target > 0 ? round(($omset / $target) * 100, 2) : 0,
                'komisi'     => $komisiTotal,
            ];
        });

        $totalKomisi = $kelasOmsetFiltered->sum('komisi');

        // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
     

        // ====================== 📈 LEADS ======================
        $leads = SalesPlan::select('status', DB::raw('count(*) as total'))
            ->where('created_by', $csId)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->groupBy('status')
            ->pluck('total', 'status');

        $cold           = $leads['cold'] ?? 0;
        $tertarik       = $leads['tertarik'] ?? 0;
        $mau_transfer   = $leads['mau_transfer'] ?? 0;
        $sudah_transfer = $leads['sudah_transfer'] ?? 0;
        $no             = $leads['no'] ?? 0;

        $totalLeadAktif = $cold + $tertarik + $mau_transfer + $sudah_transfer + $no;

        // ====================== ⚙️ KPI ======================
        $hariKerja = 0;
        for ($d = 1; $d <= $carbonBulan->daysInMonth; $d++) {
            $day = Carbon::create($tahun, $bulanNum, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
        }

        $activities = Activity::with('kategori')
            ->orderBy('categories_id')
            ->get()
            ->groupBy('categories_id');

        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
        ];

        $kpiData = [];
        $totalKpi = 0;
        $totalBobot = 0;

        foreach ($activities as $kategoriId => $list) {

            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
            $activityPercents = [];

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulanan = $targetDaily * $hariKerja;

                $totalRealisasi = (float) DailyActiviti::where('user_id', $csId)
                    ->where('activity_id', $act->id)
                    ->whereMonth('tanggal', $bulanNum)
                    ->whereYear('tanggal', $tahun)
                    ->sum('realisasi');

                $percent = 0;
                if ($targetBulanan > 0) {
                    $percent = ($totalRealisasi / $targetBulanan) * 100;
                    if ($percent > 100) $percent = 100;
                }

                $activityPercents[] = $percent;
            }

            $skorKategori = count($activityPercents)
                ? (array_sum($activityPercents) / count($activityPercents))
                : 0;

            $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

            $kpiData[] = [
                'categories_id' => $kategoriId,
                'nama'        => $categoryName,
                'target'      => '100%',
                'bobot'       => $bobotKategori,
                'persentase'  => round($skorKategori, 2),
                'nilai'       => round($nilaiKategori, 2),
            ];

            $totalKpi += $nilaiKategori;
            $totalBobot += $bobotKategori;
        }

        $totalNilai = round($totalKpi, 2);

        // ====================== DATABASE PERSEN ======================
        $databaseTotal = Data::where('created_by', $csId)->count();
        $persentaseDatabaseBaru = $databaseTotal > 0 ? round(($databaseBaru / $databaseTotal) * 100, 2) : 0;
        $persentaseDatabaseLama = 100 - $persentaseDatabaseBaru;


            // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
    
    // OMSET
    $totalOmset = $kelasOmsetFiltered->sum('omset'); 
    $targetBulananOmset = 50000000;
    
    // 🔥 Pencapaian Omset untuk ditampilkan di tabel
    $pencapaianOmset = $totalOmset;
    
    // Nilai Omset (0-100)
    $nilaiOmset = $targetBulananOmset > 0
        ? min(100, round(($totalOmset / $targetBulananOmset) * 100))
        : 0;
    
    // Bobot 40%
    $nilaiOmset = round(($nilaiOmset / 100) * 40, 2);
    
    
    // ============ Closing Paket ============
    $closingPaket = SalesPlan::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulanNum)
        ->where('status', 'sudah_transfer')
        ->count();
    
    // 🔥 Pencapaian Closing Paket untuk tabel
    $pencapaianClosingPaket = $closingPaket;
    
    $nilaiClosingPaket = $closingPaket >= 1 ? 100 : 0;
    $nilaiClosingPaket = round(($nilaiClosingPaket / 100) * 30, 2);
    
    
    // ============ Database Baru ============
    $databaseBaru = Data::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulanNum)
        ->count();
    
    // 🔥 Pencapaian Database Baru untuk tabel
    $pencapaianDatabaseBaru = $databaseBaru;
    
    $nilaiDatabaseBaru = $databaseBaru >= 50 ? 100 : ($databaseBaru * 2);
    if ($nilaiDatabaseBaru > 100) $nilaiDatabaseBaru = 100;
    
    $nilaiDatabaseBaru = round(($nilaiDatabaseBaru / 100) * 30, 2);



        // ====================== RETURN ======================
        return view('home', compact(
            'kelasOmsetFiltered',
            'totalKomisi',

            // Nilai hasil CS
            'nilaiOmset',
            'nilaiClosingPaket',
            'nilaiDatabaseBaru',

            'cold',
            'tertarik',
            'mau_transfer',
            'sudah_transfer',
            'no',
            'totalLeadAktif',

            'csName',
            'bulan',

            'kpiData',
            'totalBobot',
            'totalNilai',

            'databaseBaru',
            'databaseTotal',
            'persentaseDatabaseBaru',
            'persentaseDatabaseLama',

            'pencapaianOmset',
            'pencapaianClosingPaket',
            'pencapaianDatabaseBaru',
            
            // Closing Paket
            'closingPaket',  

        
            'notifikasi',
            'notifCount'
        ));
    }
}
