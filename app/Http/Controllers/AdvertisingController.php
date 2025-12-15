<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Data;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdvertisingController extends Controller
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
        $userId = auth()->id();
        $userName = auth()->user()->name;

        // ====================== 🔔 NOTIFIKASI ======================
        $notifikasi = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $notifCount = Notifikasi::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        // ====================== 📊 KPI ADVERTISING ======================
        
        // 1. ROAS (Return on Advertising Spend) - Target 10X
        // Asumsi: ROAS = Total Omset / Biaya Iklan
        // Untuk demo, kita gunakan data dari salesplan
        $totalOmset = SalesPlan::with('data')
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulanNum)
            ->where('status', 'sudah_transfer')
            ->whereHas('data', function($q) {
                $q->where('leads', 'LIKE', '%Iklan%');
            })
            ->sum('nominal');
        
        // Asumsi biaya iklan (bisa diambil dari tabel lain atau setting)
        // Untuk demo, kita set manual atau dari settings
        $biayaIklan = \App\Models\Setting::where('key', 'biaya_iklan')->value('value') ?? 5000000;
        
        $roas = $biayaIklan > 0 ? round($totalOmset / $biayaIklan, 2) : 0;
        $targetRoas = 10; // Target 10X
        $persenRoas = $targetRoas > 0 ? round(($roas / $targetRoas) * 100, 2) : 0;
        $nilaiRoas = min(100, $persenRoas); // Max 100%
        $bobotRoas = 30; // Bobot 30%
        $nilaiAkhirRoas = round(($nilaiRoas / 100) * $bobotRoas, 2);

        // List CS
        $csSMI = ['Latifah', 'Tursia'];
        $csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

        // 2. Jumlah Leads (MBC) - Target 300/bulan
        // Logic: Sumber Leads (kolom 'leads') berisi "Iklan", Created By CS MBC
        $leadsMBC = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->where('leads', 'LIKE', '%Iklan%')
            ->whereIn('created_by', $csMBC)
            ->count();
        
        $targetLeadsMBC = 300;
        $persenLeadsMBC = $targetLeadsMBC > 0 ? round(($leadsMBC / $targetLeadsMBC) * 100, 2) : 0;
        $nilaiLeadsMBC = min(100, $persenLeadsMBC);
        $bobotLeadsMBC = 30; // Bobot 30%
        $nilaiAkhirLeadsMBC = round(($nilaiLeadsMBC / 100) * $bobotLeadsMBC, 2);

        // 3. Jumlah Leads (SMI) - Target 100/bulan
        // Logic: Sumber Leads (kolom 'leads') berisi "Iklan", Created By CS SMI
        $leadsSMI = Data::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->where('leads', 'LIKE', '%Iklan%')
            ->whereIn('created_by', $csSMI)
            ->count();
        
        $targetLeadsSMI = 100;
        $persenLeadsSMI = $targetLeadsSMI > 0 ? round(($leadsSMI / $targetLeadsSMI) * 100, 2) : 0;
        $nilaiLeadsSMI = min(100, $persenLeadsSMI);
        $bobotLeadsSMI = 30; // Bobot 30%
        $nilaiAkhirLeadsSMI = round(($nilaiLeadsSMI / 100) * $bobotLeadsSMI, 2);

        // 4. Penilaian Atasan - Input Oleh Atasan (Manual)
        // Ambil dari tabel penilaian_manuals
        $penilaianAtasan = DB::table('penilaian_manuals')
            ->where('user_id', $userId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulanNum)
            ->value('total_nilai') ?? 0;
        
        $bobotPenilaianAtasan = 10; // Bobot 10%
        $nilaiAkhirPenilaianAtasan = round(($penilaianAtasan / 100) * $bobotPenilaianAtasan, 2);

        // Total Nilai
        $totalNilai = $nilaiAkhirRoas + $nilaiAkhirLeadsMBC + $nilaiAkhirLeadsSMI + $nilaiAkhirPenilaianAtasan;

        // Data untuk tabel KPI
        $kpiData = [
            [
                'no' => 1,
                'aspek' => 'ROAS',
                'indikator' => 'Target 10X',
                'bobot' => $bobotRoas . '%',
                'pencapaian' => $roas . 'X',
                'nilai' => $nilaiAkhirRoas
            ],
            [
                'no' => 2,
                'aspek' => 'Jumlah Leads (MBC)',
                'indikator' => '300/bulan',
                'bobot' => $bobotLeadsMBC . '%',
                'pencapaian' => $leadsMBC,
                'nilai' => $nilaiAkhirLeadsMBC
            ],
            [
                'no' => 3,
                'aspek' => 'Jumlah Leads (SMI)',
                'indikator' => '100/bulan',
                'bobot' => $bobotLeadsSMI . '%',
                'pencapaian' => $leadsSMI,
                'nilai' => $nilaiAkhirLeadsSMI
            ],
            [
                'no' => 4,
                'aspek' => 'Penilaian Atasan',
                'indikator' => 'Input Oleh atasan',
                'bobot' => $bobotPenilaianAtasan . '%',
                'pencapaian' => $penilaianAtasan . '%',
                'nilai' => $nilaiAkhirPenilaianAtasan
            ]
        ];

        // Rumus ROAS untuk ditampilkan
        $rumusRoas = [
            'label' => 'Rumus ROAS',
            'formula' => 'Pendapatan Iklan (CS) / Biaya Iklan (Keuangan)'
        ];

        // ====================== 📋 TABLE DATA PROGRAM KELAS ======================
        // Data hardcoded sesuai request gambar
        // Nantinya bisa dipindahkan ke database jika perlu dinamis
        // ====================== 📋 TABLE DATA PROGRAM KELAS ======================
        // Mengambil data dari tabel Kelas (Jadwal Kelas)
        // Filter kelas yang relevan dengan bulan yang dipilih (berdasarkan Mulai Iklan H-30 s/d Tanggal Acara)
        
        $programData = [];
        
        // Ambil semua kelas (bisa dioptimasi dengan query date range jika data banyak)
        $kelasList = \App\Models\Kelas::all();
        
        $selectedMonthStart = $carbonBulan->copy()->startOfMonth();
        $selectedMonthEnd   = $carbonBulan->copy()->endOfMonth();

        foreach ($kelasList as $kelas) {
            if (!$kelas->tanggal_mulai) continue;
            
            $namaKelas = $kelas->nama_kelas;
            $eventDate = Carbon::parse($kelas->tanggal_mulai);
            // Asumsi Mulai Iklan adalah H-30 dari Tanggal Acara
            $adDate = $eventDate->copy()->subDays(30);
            
            // Logika Filter: Tampilkan jika periode aktivitas (Iklan s/d Acara) beririsan dengan Bulan Terpilih
            // Interval Acara: [AdDate, EventDate]
            // Interval Bulan: [MonthStart, MonthEnd]
            // Overlap jika: (AdDate <= MonthEnd) AND (EventDate >= MonthStart)
            
            if ($adDate->lte($selectedMonthEnd) && $eventDate->gte($selectedMonthStart)) {
                
                // Format tanggal untuk tampilan
                $tglAcaraStr  = $eventDate->translatedFormat('d F Y');
                $mulaiIklanStr = $adDate->translatedFormat('d F Y');
                
                // --- PENGHITUNGAN REALISASI ---
                
                // 1. Realisasi Leads (Khusus Iklan)
                $realisasiLeads = Data::whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanNum)
                    ->where('leads', 'LIKE', '%Iklan%') // Filter Iklan
                    ->whereHas('kelas', function($q) use ($kelas) {
                        $q->where('id', $kelas->id);
                    })
                    ->count();

                // 2. Realisasi Closing (Khusus Iklan)
                $realisasiClosing = SalesPlan::where('status', 'sudah_transfer')
                    ->whereYear('updated_at', $tahun)
                    ->whereMonth('updated_at', $bulanNum)
                    ->whereHas('data', function($q) {
                        $q->where('leads', 'LIKE', '%Iklan%');
                    })
                    ->whereHas('kelas', function($q) use ($kelas) {
                        $q->where('id', $kelas->id);
                    })
                    ->count();

                // 3. Budget & CPL
                // Karena belum ada input budget per kelas di DB, kita gunakan default atau 0
                // Default Target Budget sesuai hardcoded sebelumnya: 4.500.000
                $targetBudget = 4500000;
                $realisasiBudget = 0; // Belum ada input realisasi budget di DB
                
                $realisasiCPL = ($realisasiLeads > 0) ? $realisasiBudget / $realisasiLeads : 0;
                $realisasiCPClosing = ($realisasiClosing > 0) ? $realisasiBudget / $realisasiClosing : 0;

                // Target CP Closing: 
                // Logika lama: Kosong untuk Tik-Tok & Public Speaking. 
                // Disini kita defaultkan 150.000, kecuali nama tertentu jika perlu (optional)
                $targetCPClosing = 150000;
                
                $programData[] = [
                    'nama' => $namaKelas,
                    'tanggal_acara' => $tglAcaraStr,
                    'mulai_iklan' => $mulaiIklanStr,
                    
                    'target_leads' => 300,
                    'realisasi_leads' => $realisasiLeads,
                    
                    'target_closing' => 30,
                    'realisasi_closing' => $realisasiClosing,

                    'target_budget' => $targetBudget,
                    'realisasi_budget' => $realisasiBudget,
                    
                    'target_cpl' => 15000,
                    'realisasi_cpl' => $realisasiCPL,

                    'target_cp_closing' => $targetCPClosing,
                    'realisasi_cp_closing' => $realisasiCPClosing
                ];
            }
        }

        // ====================== 📈 HISTORY NILAI (12 BULAN) ======================
        $historyNilai = [];
        for ($m = 1; $m <= 12; $m++) {
            // 1. ROAS
            $omsetBulan = SalesPlan::with('data')
                ->whereYear('updated_at', $tahun)
                ->whereMonth('updated_at', $m)
                ->where('status', 'sudah_transfer')
                ->whereHas('data', function($q) {
                    $q->where('leads', 'LIKE', '%Iklan%');
                })
                ->sum('nominal');
            
            $roasBulan = $biayaIklan > 0 ? round($omsetBulan / $biayaIklan, 2) : 0;
            $nilaiRoasBulan = min(100, $targetRoas > 0 ? round(($roasBulan / $targetRoas) * 100, 2) : 0);
            $akhirRoasBulan = round(($nilaiRoasBulan / 100) * $bobotRoas, 2);

            // 2. Leads MBC
            $leadsMBCBulan = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $m)
                ->where('leads', 'LIKE', '%Iklan%')
                ->whereIn('created_by', $csMBC)
                ->count();
            $nilaiMBCBulan = min(100, $targetLeadsMBC > 0 ? round(($leadsMBCBulan / $targetLeadsMBC) * 100, 2) : 0);
            $akhirMBCBulan = round(($nilaiMBCBulan / 100) * $bobotLeadsMBC, 2);

            // 3. Leads SMI
            $leadsSMIBulan = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $m)
                ->where('leads', 'LIKE', '%Iklan%')
                ->whereIn('created_by', $csSMI)
                ->count();
            $nilaiSMIBulan = min(100, $targetLeadsSMI > 0 ? round(($leadsSMIBulan / $targetLeadsSMI) * 100, 2) : 0);
            $akhirSMIBulan = round(($nilaiSMIBulan / 100) * $bobotLeadsSMI, 2);

            // 4. Penilaian Manual
            $manualBulan = DB::table('penilaian_manuals')
                ->where('user_id', $userId)
                ->where('tahun', $tahun)
                ->where('bulan', $m)
                ->value('total_nilai') ?? 0;
            $akhirManualBulan = round(($manualBulan / 100) * $bobotPenilaianAtasan, 2);

            // Total
            $historyNilai[$m] = $akhirRoasBulan + $akhirMBCBulan + $akhirSMIBulan + $akhirManualBulan;
        }

        // ====================== RETURN ======================
        return view('advertising', compact(
            'userName',
            'bulan',
            'kpiData',
            'totalNilai',
            'rumusRoas',
            'notifikasi',
            'notifCount',
            // Data detail untuk chart atau info tambahan
            'roas',
            'leadsMBC',
            'leadsSMI',
            'penilaianAtasan',
            'totalOmset',
            'biayaIklan',
            'programData',
            'historyNilai'
        ));
    }
}
