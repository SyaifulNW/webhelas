<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Data;
use App\Models\Kelas;
use App\Models\KpiSosmed;
use App\Models\Activity;
use App\Models\DailyActiviti;
use App\Models\PenilaianManual;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PenilaianController extends Controller
{
public function index(Request $request)
{
$loggedInUser = auth()->user();

// Logika memilih user lain jika ada input user_id (misal Admin/Manager melihat tim)
if ($request->has('user_id') && ($loggedInUser->role !== 'marketing' || $loggedInUser->name == 'Eko Sulis')) { // Adjust permission logic as needed
$userId = $request->user_id;
$targetUser = User::find($userId);
} else {
$userId = $loggedInUser->id;
$targetUser = $loggedInUser;
}

$namaUserData = $targetUser->name;
$user = $targetUser; // override $user for downstream logic that relies on it being the subject

// ============================
// FILTER BULAN & TAHUN
// ============================
$bulan = $request->bulan ?? date('m');
$tahun = $request->tahun ?? date('Y');
$bulanNum = intval($bulan);

// ============================
// 1. LEADS MBC (45%)
// ============================
// List CS
$csSMI = ['Latifah', 'Tursia'];
$csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

// ============================
// 3. PENILAIAN ATASAN (10%) - Common calculation
// ============================
$manual = \App\Models\PenilaianManual::where('user_id', $userId)
->where('bulan', $bulanNum)
->where('tahun', $tahun)
->first();

$totalSumManual = $manual ? $manual->total_nilai : 0; 

$persenManual = $totalSumManual; // Assumed 0-100

$roas = 0;
$targetRoas = 0;
$persenRoas = 0;
$nilaiRoas = 0;
$nilaiAkhirRoas = 0;
$leadsAds = 0;
$targetLeadsAds = 0;
$persenLeadsAds = 0;
$nilaiLeadsAds = 0;
$leadsFelmi = 0;
$targetLeadsFelmi = 0;
$persenLeadsFelmi = 0;
$nilaiLeadsFelmi = 0;
$leadsNisa = 0;
$targetLeadsNisa = 0;
$persenLeadsNisa = 0;
$nilaiLeadsNisa = 0;
$bobotLeadsMBC = 0;
$bobotLeadsSMI = 0;
$bobotManual = 0;

if ($namaUserData === 'Eko Sulis') {
// --- LOGIK KHUSUS EKO SULIS (ADVERTISING) ---

// 0. ROAS (30%)
$totalOmset = SalesPlan::with('data')
->whereYear('updated_at', $tahun)
->whereMonth('updated_at', $bulanNum)
->where('status', 'sudah_transfer')
->whereHas('data', function($q) {
$q->where('leads', 'LIKE', '%Iklan%');
})
->sum('nominal');

$biayaIklan = \App\Models\Setting::where('key', 'biaya_iklan')->value('value') ?? 5000000;
$roas = $biayaIklan > 0 ? round($totalOmset / $biayaIklan, 2) : 0;
$targetRoas = 10;
$persenRoas = $targetRoas > 0 ? min(($roas / $targetRoas) * 100, 100) : 0;
$nilaiRoas = $persenRoas; // This seems redundant with persenRoas, but keeping for consistency if needed elsewhere
$nilaiAkhirRoas = round(($persenRoas / 100) * 30, 2);

            $felmiUser = User::where('name', 'Felmi')->first();
            $nisaUser = User::where('name', 'Nisa')->first();

            // 1. LEADS ADS (20%)
            $leadsAds = Data::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanNum)
                ->where('leads', 'like', '%Iklan%')
                ->whereIn('created_by', array_merge($csMBC, $csSMI))
                ->count();
            $targetLeadsAds = 400;
            $persenLeadsAds = $targetLeadsAds > 0 ? min(($leadsAds / $targetLeadsAds) * 100, 100) : 0;
            $nilaiLeadsAds = round(($persenLeadsAds / 100) * 20, 2);

            // 2. LEADS EVENT OFFLINE (FELMI) (20%)
            $leadsFelmi = 0;
            if ($felmiUser) {
                $leadsFelmi = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanNum)
                    ->where('created_by', $felmiUser->id)
                    ->count();
            }
            $targetLeadsFelmi = 100;
            $persenLeadsFelmi = $targetLeadsFelmi > 0 ? min(($leadsFelmi / $targetLeadsFelmi) * 100, 100) : 0;
            $nilaiLeadsFelmi = round(($persenLeadsFelmi / 100) * 20, 2);

            // 3. LEADS EVENT ONLINE (NISA) (20%)
            $leadsNisa = 0;
            if ($nisaUser) {
                $leadsNisa = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanNum)
                    ->where('created_by', $nisaUser->id)
                    ->count();
            }
            $targetLeadsNisa = 100;
            $persenLeadsNisa = $targetLeadsNisa > 0 ? min(($leadsNisa / $targetLeadsNisa) * 100, 100) : 0;
            $nilaiLeadsNisa = round(($persenLeadsNisa / 100) * 20, 2);

            // 4. ATASAN (10%)
            $bobotManual = 10;
            $nilaiManualPart = round(($persenManual / 100) * $bobotManual, 2);

            $totalNilai = $nilaiAkhirRoas + $nilaiLeadsAds + $nilaiLeadsFelmi + $nilaiLeadsNisa + $nilaiManualPart;
} else {
// --- DEFAULT MARKETING (45% Leads MBC, 45% Leads SMI, 10% Atasan) ---
// 1. LEADS MBC (45%)
// Logic: Leads from CS MBC with source 'Marketing'
$leadsMBC = Data::whereYear('created_at', $tahun)
->whereMonth('created_at', $bulanNum)
->where('leads', 'like', '%Marketing%')
->whereIn('created_by', $csMBC)
->count();

$targetLeadsMBC = (trim($namaUserData) === 'Nisa') ? 100 : 150;
$persenLeadsMBC = $targetLeadsMBC > 0 ? min(($leadsMBC / $targetLeadsMBC) * 100, 100) : 0;
$bobotLeadsMBC = 45;
$nilaiLeadsMBC = round(($persenLeadsMBC / 100) * $bobotLeadsMBC, 2);


// ============================
// 2. LEADS SMI (45%)
// ============================
// Logic: Leads from CS SMI with source 'Marketing'
$leadsSMI = Data::whereYear('created_at', $tahun)
->whereMonth('created_at', $bulanNum)
->where('leads', 'like', '%Marketing%')
->whereIn('created_by', $csSMI)
->count();

$targetLeadsSMI = 100;
$persenLeadsSMI = $targetLeadsSMI > 0 ? min(($leadsSMI / $targetLeadsSMI) * 100, 100) : 0;
$bobotLeadsSMI = 45;
$nilaiLeadsSMI = round(($persenLeadsSMI / 100) * $bobotLeadsSMI, 2);


// ============================
// 3. PENILAIAN ATASAN (10%)
// ============================
$bobotManual = 10;
$nilaiManualPart = round(($persenManual / 100) * $bobotManual, 2);

    // ============================
    // 4. TOTAL NILAI
    // ============================
    if (trim($namaUserData) === 'Felmi') {
        // --- LOGIK KHUSUS FELMI (EVENT MARKETING) ---
        // 1. Total Leads Baru (40%) - Target 100
        $leadsFelmiCount = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->where('created_by', $userId)
            ->count();
        $persenLeadsFelmi = 100 > 0 ? min(($leadsFelmiCount / 100) * 100, 100) : 0;
        $nilaiLeadsFelmiPart = round(($persenLeadsFelmi / 100) * 40, 2);

        // 2. E-Fest (30%) - Target 50
        $efestCount = \App\Models\MarketingPerformance::where('user_id', $userId)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulanNum)
            ->where(function($q) {
                $q->where('event_name', 'LIKE', '%E-Fest%')
                  ->orWhere('event_name', 'LIKE', '%E- Festival%')
                  ->orWhere('event_name', 'LIKE', '%E-Fest%');
            })
            ->sum('peserta_hadir') ?? 0;
        $persenEfest = 50 > 0 ? min(($efestCount / 50) * 100, 100) : 0;
        $nilaiEfest = round(($persenEfest / 100) * 30, 2);

        // 3. Bisnis Visit (30%) - Target 50
        $visitCount = \App\Models\MarketingPerformance::where('user_id', $userId)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulanNum)
            ->where(function($q) {
                $q->where('event_name', 'LIKE', '%Up%Rev%')
                  ->orWhere('event_name', 'LIKE', '%Bisnis%Visit%');
            })
            ->sum('peserta_hadir') ?? 0;
        $persenVisit = 50 > 0 ? min(($visitCount / 50) * 100, 100) : 0;
        $nilaiVisit = round(($persenVisit / 100) * 30, 2);

        $totalNilai = $nilaiLeadsFelmiPart + $nilaiEfest + $nilaiVisit;
    } else {
        $totalNilai = $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;
    }
}

$dailyTotalKpi = $this->hitungDailyKpi($userId, $bulanNum, $tahun);

// ============================
// DATA KHUSUS FELMI
// ============================
$intakeRecap = [];
$felmiKpi = [];
$totalIntakeScore = 0;
$totalIntakeBobot = 0;
$totalFelmiKpiScore = 0;
$overallPerformanceScore = 0;

if (trim($targetUser->name ?? '') === 'Felmi') {
// 1. INTAKE RECAP
$daysInMonth = Carbon::create($tahun, $bulanNum, 1)->daysInMonth;
$daysInMonth = $daysInMonth ?: 30; // Fallback
$hariKerja = 0;
for ($d = 1; $d <= $daysInMonth; $d++) {
$day = Carbon::create($tahun, $bulanNum, $d);
if ($day->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
}

// Fetch activities for Cats 8, 9, 10
$rawActivities = \App\Models\Activity::whereIn('categories_id', [8, 9, 10])->get();

// If DB activities missing, mock them to ensure table shows up
if ($rawActivities->isEmpty()) {
// Fallback to ensure UI doesn't break
$rawActivities = collect([
(object)['id' => 888, 'categories_id' => 8, 'nama' => 'Daily Intake Activity', 'target_daily' => 1, 'target_bulanan' => 0],
(object)['id' => 999, 'categories_id' => 9, 'nama' => 'Weekly Intake Activity', 'target_daily' => 0, 'target_bulanan' => 4],
(object)['id' => 1010, 'categories_id' => 10, 'nama' => 'Monthly Intake Activity', 'target_daily' => 0, 'target_bulanan' => 1],
]);
}

$groupedActivities = $rawActivities->groupBy('categories_id');

// Hardcoded weights for Felmi Categories
$catWeights = [
8 => 33.33, // DAILY INTAKE
9 => 33.33, // WEEKLY INTAKE
10 => 33.34 // MONTHLY INTAKE
];

// Labels mapping if category name is not ideal
$catNames = [
8 => 'DAILY INTAKE',
9 => 'WEEKLY INTAKE',
10 => 'MONTHLY INTAKE'
];

foreach ([8, 9, 10] as $catId) {
$list = $groupedActivities->get($catId);

if (!$list) continue; // Should not happen with mock, but safety first

$categoryName = $catNames[$catId] ?? ('Kategori ' . $catId);
$activityPercents = [];

foreach ($list as $act) {
$targetDaily = (float) ($act->target_daily ?? 0);

// Target Logic
if ($catId == 9 || str_contains(strtoupper($act->nama), 'WEEKLY')) {
$targetBulanan = 4; 
} elseif ($catId == 10 || str_contains(strtoupper($act->nama), 'MONTHLY')) {
$targetBulanan = 1;
} else {
$targetBulanan = ($targetDaily > 0) ? ($targetDaily * $hariKerja) : (float) ($act->target_bulanan ?? 0);
}

// Realisasi
$totalRealisasi = 0;
if (isset($act->id) && $act->id < 800) { // Only query DB for real IDs
$totalRealisasi = (float) \App\Models\DailyActiviti::where('user_id', $userId)
->where('activity_id', $act->id)
->whereMonth('tanggal', $bulanNum)
->whereYear('tanggal', $tahun)
->sum('realisasi');
}

$percent = 0;
if ($targetBulanan > 0) {
$percent = ($totalRealisasi / $targetBulanan) * 100;
if ($percent > 100) $percent = 100;
}
$activityPercents[] = $percent;
}

$skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
$bobotKategori = $catWeights[$catId] ?? 0;
$nilaiKategori = ($skorKategori / 100) * $bobotKategori;

$intakeRecap[] = [
'nama' => $categoryName,
'target' => '100%',
'bobot' => $bobotKategori,
'persentase' => round($skorKategori, 2),
'nilai' => round($nilaiKategori, 2)
];

$totalIntakeScore += $nilaiKategori;
$totalIntakeBobot += $bobotKategori;
}

// DEBUGGING BLOCK
try {
$debugContent = "Time: " . now() . "\n";
$debugContent .= "User: " . $targetUser->name . "\n";
$debugContent .= "Raw Activities Count: " . $rawActivities->count() . "\n";
$debugContent .= "Grouped Keys: " . json_encode($groupedActivities->keys()) . "\n";
$debugContent .= "Intake Recap Count: " . count($intakeRecap) . "\n";
$debugContent .= "Intake Recap Data: " . print_r($intakeRecap, true) . "\n";

file_put_contents(public_path('debug_felmi_log.txt'), $debugContent);
} catch (\Exception $e) {
// ignore
}

// 2. KPI TABLE FOR FELMI (Event Marketing)
$kpiConfigs = [
['nama' => 'Total Leads Baru/Bulan', 'target' => 100, 'bobot' => 40, 'activity_id' => 33],
['nama' => 'Entrepreneur Forum / E-Fest', 'target' => 50, 'bobot' => 30, 'activity_id' => 49],
['nama' => 'Bisnis Visit / UpRev', 'target' => 50, 'bobot' => 30, 'activity_id' => 50],
];

$felmiKpi = [];
$totalFelmiKpiScore = 0;
foreach ($kpiConfigs as $config) {
$real = 0;
if ($config['nama'] === 'Total Leads Baru/Bulan') {
// Diambil dari jumlah database yang diinput oleh marketing di tabel MarketingParticipant
$real = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)
->whereMonth('created_at', $bulanNum)
->where('created_by', $userId)
->count();
            } elseif (stripos($config['nama'], 'E-Fest') !== false) {
                // Diambil dari data menu dashboard kolom Peserta Hadir (E-Fest)
                $real = \App\Models\MarketingPerformance::where('user_id', $userId)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulanNum)
                    ->where(function($q) {
                        $q->where('event_name', 'LIKE', '%E-Fest%')
                          ->orWhere('event_name', 'LIKE', '%E- Fest%')
                          ->orWhere('event_name', 'LIKE', '%E-Festival%');
                    })
                    ->sum('peserta_hadir') ?? 0;
            } elseif (stripos($config['nama'], 'UpRev') !== false || stripos($config['nama'], 'Visit') !== false) {
                // Diambil dari data menu dashboard kolom Peserta Hadir (Up Rev)
                $real = \App\Models\MarketingPerformance::where('user_id', $userId)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulanNum)
                    ->where(function($q) {
                        $q->where('event_name', 'LIKE', '%Up%Rev%')
                          ->orWhere('event_name', 'LIKE', '%UP REV%');
                    })
                    ->sum('peserta_hadir') ?? 0;
            } else {
                // Default ambil dari DailyActiviti
                $real = \App\Models\DailyActiviti::where('user_id', $userId)
                    ->where('activity_id', $config['activity_id'])
                    ->whereMonth('tanggal', $bulanNum)
                    ->whereYear('tanggal', $tahun)
                    ->sum('realisasi') ?? 0;
            }

$persen = $config['target'] > 0 ? ($real / $config['target']) * 100 : 0;
if ($persen > 100) $persen = 100;

$nilai = ($persen / 100) * $config['bobot'];

$felmiKpi[] = [
'nama' => $config['nama'],
'target' => $config['target'],
'bobot' => $config['bobot'],
'real' => $real,
'nilai' => round($nilai, 2)
];
$totalFelmiKpiScore += $nilai;
}

$overallPerformanceScore = $totalFelmiKpiScore;
}


// ============================
// 7. CHART & HISTORY
// ============================
$labels = [];
$scores = [];
$role = $user->role;

for ($i = 5; $i >= 0; $i--) {
$dt = Carbon::now()->subMonths($i);
$labels[] = $dt->format('M Y');

$scores[] = $this->hitungTotalNilai(
$userId,
$namaUserData,
$dt->month,
$dt->year,
$role
);
}

$historyNilai = array_fill(1, 12, 0);

for ($m = 1; $m <= 12; $m++) {
$historyNilai[$m] = $this->hitungTotalNilai(
$userId,
$namaUserData,
$m,
$tahun,
$role
);
}

// ============================
// 8. KIRIM KE VIEW
// ============================
$daftarCs = User::whereIn('role', ['marketing', 'cs'])->get();

return view('marketing.penilaian.index', compact(
'bulan',
'tahun',
'leadsMBC',
'targetLeadsMBC',
'persenLeadsMBC',
'nilaiLeadsMBC',
'leadsSMI',
'targetLeadsSMI',
'persenLeadsSMI',
'nilaiLeadsSMI',
'totalNilai',
'nilaiManualPart',
'totalSumManual',
'persenManual',
'labels',
'scores',
'historyNilai',
'manual',
'targetUser',
'userId',
        'dailyTotalKpi',
        'roas',
        'targetRoas',
        'persenRoas',
        'nilaiRoas',
        'nilaiAkhirRoas',
        'leadsAds',
        'targetLeadsAds',
        'persenLeadsAds',
        'nilaiLeadsAds',
        'leadsFelmi',
        'targetLeadsFelmi',
        'persenLeadsFelmi',
        'nilaiLeadsFelmi',
        'leadsNisa',
        'targetLeadsNisa',
        'persenLeadsNisa',
        'nilaiLeadsNisa',
        'intakeRecap',
'felmiKpi',
'totalIntakeScore',
'totalIntakeBobot',
'totalFelmiKpiScore',
'overallPerformanceScore',
'daftarCs'
));
}

private function hitungDailyKpi($userId, $bulan, $tahun)
{
$targetUser = User::find($userId);
$targetName = $targetUser ? trim($targetUser->name) : '';

$daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
$hariKerja = 0;
for ($d = 1; $d <= $daysInMonth; $d++) {
$day = Carbon::create($tahun, $bulan, $d);
if ($day->dayOfWeek != Carbon::SUNDAY) {
$hariKerja++;
}
}

// Ambil aktivitas dan hitung KPI
$activityQuery = Activity::with('kategori')->orderBy('categories_id');
if ($targetName === 'Nisa') {
$activityQuery->whereIn('categories_id', [6, 11]);
} else {
$activityQuery->whereIn('categories_id', [1, 2, 3, 4, 5]);
}
$activities = $activityQuery->get()->groupBy('categories_id');

$categoryKpiWeights = [
'Aktivitas Pribadi' => 10,
'Aktivitas Mencari Leads' => 20,
'Aktivitas Memprospek' => 20,
'Aktivitas Closing' => 40,
'Aktivitas Merawat Customer' => 10,
'A. Aktivitas Harian (NON-NEGOTIABLE)' => 60,
'B. Aktivitas Mingguan' => 0,
'C. Aktivitas Bulanan' => 40,
];

$dailyTotalKpi = 0;

foreach ($activities as $kategoriId => $list) {
$categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
$activityPercents = [];

foreach ($list as $act) {
$targetDaily = (float) ($act->target_daily ?? 0);
$targetBulanan = $targetDaily * $hariKerja;

$totalRealisasi = (float) DailyActiviti::where('user_id', $userId)
->where('activity_id', $act->id)
->whereMonth('tanggal', $bulan)
->whereYear('tanggal', $tahun)
->sum('realisasi');

$percent = 0;
if ($targetBulanan > 0) {
$percent = ($totalRealisasi / $targetBulanan) * 100;
if ($percent > 100) $percent = 100;
}
$activityPercents[] = $percent;
}

$skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
$bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
$nilaiKategori = ($skorKategori / 100) * $bobotKategori;

$dailyTotalKpi += $nilaiKategori;
}

return $dailyTotalKpi;
}


// ======================================================
// FUNGSI HITUNG TOTAL NILAI (REUSABLE)
// ======================================================
private function hitungTotalNilai($userId, $namaUserData, $bulan, $tahun, $role)
{
// List CS
$csSMI = ['Latifah', 'Tursia'];
$csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

// Penilaian Atasan
$manual = \App\Models\PenilaianManual::where('user_id', $userId)
->where('bulan', $bulan)
->where('tahun', $tahun)
->first();
$manualVal = $manual ? $manual->total_nilai : 0;

if (trim($namaUserData) === 'Eko Sulis') {
$totalOmset = SalesPlan::with('data')
->whereYear('updated_at', $tahun)
->whereMonth('updated_at', $bulan)
->where('status', 'sudah_transfer')
->whereHas('data', function($q) {
$q->where('leads', 'LIKE', '%Iklan%');
})
->sum('nominal');
$biayaIklan = \App\Models\Setting::where('key', 'biaya_iklan')->value('value') ?? 5000000;
$roas = $biayaIklan > 0 ? round($totalOmset / $biayaIklan, 2) : 0;
$targetRoas = 10;
$persenRoas = $targetRoas > 0 ? min(($roas / $targetRoas) * 100, 100) : 0;
$nilaiAkhirRoas = round(($persenRoas / 100) * 30, 2);

        $felmiUser = User::where('name', 'Felmi')->first();
        $nisaUser = User::where('name', 'Nisa')->first();

        // 1. LEADS ADS (20%)
        $leadsAdsCount = Data::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->where('leads', 'like', '%Iklan%')->whereIn('created_by', array_merge($csMBC, $csSMI))->count();
        $nilaiLeadsAds = round((min(($leadsAdsCount / 400) * 100, 100) / 100) * 20, 2);

        // 2. LEADS FELMI (20%)
        $leadsFelmiCount = 0;
        if ($felmiUser) {
            $leadsFelmiCount = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->where('created_by', $felmiUser->id)->count();
        }
        $nilaiLeadsFelmi = round((min(($leadsFelmiCount / 100) * 100, 100) / 100) * 20, 2);

        // 3. LEADS NISA (20%)
        $leadsNisaCount = 0;
        if ($nisaUser) {
            $leadsNisaCount = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->where('created_by', $nisaUser->id)->count();
        }
        $nilaiLeadsNisa = round((min(($leadsNisaCount / 100) * 100, 100) / 100) * 20, 2);

        return $nilaiAkhirRoas + $nilaiLeadsAds + $nilaiLeadsFelmi + $nilaiLeadsNisa + round(($manualVal / 100) * 10, 2);
}

    // Default
    if (trim($namaUserData) === 'Felmi') {
        // 1. Leads Felmi (40%)
        $leadsFelmiCount = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->where('created_by', $userId)->count();
        $nilaiLeadsFelmi = round((min(($leadsFelmiCount / 100) * 100, 100) / 100) * 40, 2);

        // 2. E-Fest (30%)
        $efestCount = \App\Models\MarketingPerformance::where('user_id', $userId)->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
            ->where(function($q) {
                $q->where('event_name', 'LIKE', '%E-Fest%')
                  ->orWhere('event_name', 'LIKE', '%E- Fest%')
                  ->orWhere('event_name', 'LIKE', '%E-Festival%');
            })
            ->sum('peserta_hadir') ?? 0;
        $nilaiEfest = round((min(($efestCount / 50) * 100, 100) / 100) * 30, 2);

        // 3. Visit (30%)
        $visitCount = \App\Models\MarketingPerformance::where('user_id', $userId)->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
            ->where(function($q) {
                $q->where('event_name', 'LIKE', '%Up%Rev%')
                  ->orWhere('event_name', 'LIKE', '%UP REV%');
            })
            ->sum('peserta_hadir') ?? 0;
        $nilaiVisit = round((min(($visitCount / 50) * 100, 100) / 100) * 30, 2);

        return $nilaiLeadsFelmi + $nilaiEfest + $nilaiVisit;
    }

    $leadsMBC = Data::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->where('leads', 'like', '%Marketing%')->whereIn('created_by', $csMBC)->count();
    $targetMBC = (trim($namaUserData) === 'Nisa') ? 100 : 150;
    $nilaiLeadsMBC = round((min(($leadsMBC / $targetMBC) * 100, 100) / 100) * 45, 2);

    $leadsSMI = Data::whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->where('leads', 'like', '%Marketing%')->whereIn('created_by', $csSMI)->count();
    $nilaiLeadsSMI = round((min(($leadsSMI / 100) * 100, 100) / 100) * 45, 2);

    return $nilaiLeadsMBC + $nilaiLeadsSMI + round(($manualVal / 100) * 10, 2);
}


// ======================================================
// EXPORT PDF
// ======================================================
public function exportPdf(Request $request)
{
if ($request->has('user_id')) {
$user = User::findOrFail($request->user_id);
} else {
$user = auth()->user();
}
$userId = $user->id;
$namaUserData = $user->name;

$bulan = $request->bulan ?? date('m');
$tahun = $request->tahun ?? date('Y');
$bulanNum = intval($bulan);
$role = $user->role;

// Re-calculate or use request data
// List CS
$csSMI = ['Latifah', 'Tursia'];
$csMBC = ['Administrator', 'Linda', 'Yasmin', 'Shafa', 'Arifa', 'Qiyya'];

// Penilaian Atasan
$manual = \App\Models\PenilaianManual::where('user_id', $userId)
->where('bulan', $bulanNum)
->where('tahun', $tahun)
->first();
$manualVal = $manual ? $manual->total_nilai : 0;
$nilaiManualPart = round(($manualVal / 100) * 10, 2);

$extra = [];

// Initialize common leads variables
$leadsMBC = 0;
$targetLeadsMBC = 0;
$nilaiLeadsMBC = 0;
$leadsSMI = 0;
$targetLeadsSMI = 0;
$nilaiLeadsSMI = 0;

// Initialize new variables for Eko Sulis
$leadsAds = 0;
$targetLeadsAds = 0;
$nilaiLeadsAds = 0;
$leadsFelmi = 0;
$targetLeadsFelmi = 0;
$nilaiLeadsFelmi = 0;
$leadsNisa = 0;
$targetLeadsNisa = 0;
$nilaiLeadsNisa = 0;

// Initialize Felmi specific vars
$leadsFelmiCount = 0;
$persenLeadsFelmi = 0;
$nilaiLeadsFelmiPart = 0;
$efestCount = 0;
$persenEfest = 0;
$nilaiEfest = 0;
$visitCount = 0;
$persenVisit = 0;
$nilaiVisit = 0;

$felmiKpi = [];
if (trim($namaUserData) === 'Eko Sulis') {
// ROAS
// ...
$totalOmset = SalesPlan::with('data')->whereYear('updated_at', $tahun)->whereMonth('updated_at', $bulanNum)->where('status', 'sudah_transfer')->whereHas('data', function($q){$q->where('leads', 'LIKE', '%Iklan%');})->sum('nominal');
$biayaIklan = \App\Models\Setting::where('key', 'biaya_iklan')->value('value') ?? 5000000;
$roas = $biayaIklan > 0 ? round($totalOmset / $biayaIklan, 2) : 0;
$targetRoas = 10;
$persenRoas = $targetRoas > 0 ? min(($roas / $targetRoas) * 100, 100) : 0;
$nilaiAkhirRoas = round(($persenRoas / 100) * 30, 2);

        $felmiUser = User::where('name', 'Felmi')->first();
        $nisaUser = User::where('name', 'Nisa')->first();

        // 1. Leads ADS (20%)
        $leadsAds = Data::whereYear('created_at', $tahun)->whereMonth('created_at', $bulanNum)->where('leads', 'like', '%Iklan%')->whereIn('created_by', array_merge($csMBC, $csSMI))->count();
        $targetLeadsAds = 400;
        $nilaiLeadsAds = round((min(($leadsAds / $targetLeadsAds) * 100, 100) / 100) * 20, 2);

        // 2. Leads Felmi (20%)
        $leadsFelmi = 0;
        if ($felmiUser) {
            $leadsFelmi = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)->whereMonth('created_at', $bulanNum)->where('created_by', $felmiUser->id)->count();
        }
        $targetLeadsFelmi = 100;
        $nilaiLeadsFelmi = round((min(($leadsFelmi / $targetLeadsFelmi) * 100, 100) / 100) * 20, 2);

        // 3. Leads Nisa (20%)
        $leadsNisa = 0;
        if ($nisaUser) {
            $leadsNisa = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)->whereMonth('created_at', $bulanNum)->where('created_by', $nisaUser->id)->count();
        }
        $targetLeadsNisa = 100;
        $nilaiLeadsNisa = round((min(($leadsNisa / $targetLeadsNisa) * 100, 100) / 100) * 20, 2);

        $totalNilai = $nilaiAkhirRoas + $nilaiLeadsAds + $nilaiLeadsFelmi + $nilaiLeadsNisa + $nilaiManualPart;

        $extra = [
            'roas' => $roas,
            'targetRoas' => $targetRoas,
            'nilaiAkhirRoas' => $nilaiAkhirRoas,
            'leadsAds' => $leadsAds,
            'targetLeadsAds' => $targetLeadsAds,
            'nilaiLeadsAds' => $nilaiLeadsAds,
            'leadsFelmi' => $leadsFelmi,
            'targetLeadsFelmi' => $targetLeadsFelmi,
            'nilaiLeadsFelmi' => $nilaiLeadsFelmi,
            'leadsNisa' => $leadsNisa,
            'targetLeadsNisa' => $targetLeadsNisa,
            'nilaiLeadsNisa' => $nilaiLeadsNisa,
        ];
} elseif (trim($namaUserData) === 'Felmi') {
// --- KASH KHUSUS FELMI ---
// 1. Intake Recap Score
$daysInMonth = Carbon::create($tahun, $bulanNum, 1)->daysInMonth;
$hariKerja = 0;
for ($d = 1; $d <= $daysInMonth; $d++) {
$day = Carbon::create($tahun, $bulanNum, $d);
if ($day->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
}

$rawActivities = \App\Models\Activity::whereIn('categories_id', [8, 9, 10])->get();
$groupedActivities = $rawActivities->groupBy('categories_id');
$catWeights = [8 => 33.33, 9 => 33.33, 10 => 33.34];
$totalIntakeScore = 0;

foreach ([8, 9, 10] as $catId) {
$list = $groupedActivities->get($catId);
if (!$list) continue;
$activityPercents = [];
foreach ($list as $act) {
if ($catId == 9) $targetBulanan = 4;
elseif ($catId == 10) $targetBulanan = 1;
else $targetBulanan = ($act->target_daily > 0) ? ($act->target_daily * $hariKerja) : (float) ($act->target_bulanan ?? 0);

$realAct = (float) \App\Models\DailyActiviti::where('user_id', $userId)->where('activity_id', $act->id)->whereMonth('tanggal', $bulanNum)->whereYear('tanggal', $tahun)->sum('realisasi');
$percent = $targetBulanan > 0 ? min(100, ($realAct / $targetBulanan) * 100) : 0;
$activityPercents[] = $percent;
}
$skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
$totalIntakeScore += ($skorKategori / 100) * ($catWeights[$catId] ?? 0);
}

// 2. KPI Table
$kpiConfigs = [
['nama' => 'Total Leads Baru/Bulan', 'target' => 100, 'bobot' => 40, 'activity_id' => 33],
['nama' => 'Entrepreneur Forum / E-Fest', 'target' => 50, 'bobot' => 30, 'activity_id' => 49],
['nama' => 'Bisnis Visit / UpRev', 'target' => 50, 'bobot' => 30, 'activity_id' => 50],
];

$totalFelmiKpi = 0;
foreach ($kpiConfigs as $config) {
$realReal = 0;
if ($config['nama'] === 'Total Leads Baru/Bulan') {
$realReal = \App\Models\MarketingParticipant::whereYear('created_at', $tahun)
->whereMonth('created_at', $bulanNum)
->where('created_by', $userId)
->count();
                } elseif (stripos($config['nama'], 'E-Fest') !== false) {
                    $realReal = \App\Models\MarketingPerformance::where('user_id', $userId)
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulanNum)
                        ->where(function($q) {
                            $q->where('event_name', 'LIKE', '%E-Fest%')
                              ->orWhere('event_name', 'LIKE', '%E- Fest%')
                              ->orWhere('event_name', 'LIKE', '%E-Festival%');
                        })
                        ->sum('peserta_hadir') ?? 0;
                } elseif (stripos($config['nama'], 'UpRev') !== false || stripos($config['nama'], 'Visit') !== false) {
                    $realReal = \App\Models\MarketingPerformance::where('user_id', $userId)
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulanNum)
                        ->where(function($q) {
                            $q->where('event_name', 'LIKE', '%Up%Rev%')
                              ->orWhere('event_name', 'LIKE', '%UP REV%');
                        })
                        ->sum('peserta_hadir') ?? 0;
                } else {
                    $realReal = \App\Models\DailyActiviti::where('user_id', $userId)
                        ->where('activity_id', $config['activity_id'])
                        ->whereMonth('tanggal', $bulanNum)
                        ->whereYear('tanggal', $tahun)
                        ->sum('realisasi') ?? 0;
                }

$persen = $config['target'] > 0 ? min(100, ($realReal / $config['target']) * 100) : 0;
$nilai = ($persen / 100) * $config['bobot'];
$felmiKpi[] = [
'nama' => $config['nama'],
'target' => $config['target'],
'bobot' => $config['bobot'],
'real' => $realReal,
'nilai' => round($nilai, 2)
];
$totalFelmiKpi += $nilai;
}

$totalNilai = $totalFelmiKpi;

$extra = [
'felmiKpi' => $felmiKpi
];
} else {
$leadsMBC = Data::whereYear('created_at', $tahun)->whereMonth('created_at', $bulanNum)->where('leads', 'like', '%Marketing%')->whereIn('created_by', $csMBC)->count();
$targetLeadsMBC = (trim($namaUserData) === 'Nisa') ? 100 : 150;
$nilaiLeadsMBC = round((min(($leadsMBC / $targetLeadsMBC) * 100, 100) / 100) * 45, 2);

$leadsSMI = Data::whereYear('created_at', $tahun)->whereMonth('created_at', $bulanNum)->where('leads', 'like', '%Marketing%')->whereIn('created_by', $csSMI)->count();
$targetLeadsSMI = 100;
$nilaiLeadsSMI = round((min(($leadsSMI / $targetLeadsSMI) * 100, 100) / 100) * 45, 2);

$totalNilai = $nilaiLeadsMBC + $nilaiLeadsSMI + $nilaiManualPart;
}

$data = array_merge([
'bulan' => $bulan,
'tahun' => $tahun,
'user' => $user,
'leadsMBC' => $leadsMBC,
'targetLeadsMBC' => $targetLeadsMBC,
'nilaiLeadsMBC' => $nilaiLeadsMBC,
'leadsSMI' => $leadsSMI,
'targetLeadsSMI' => $targetLeadsSMI,
'nilaiLeadsSMI' => $nilaiLeadsSMI,
'manualVal' => $manualVal,
'nilaiManualPart' => $nilaiManualPart,
'totalNilai' => $totalNilai,
'dailyTotalKpi' => $this->hitungDailyKpi($userId, $bulanNum, $tahun)
], $extra);

$pdf = Pdf::loadView('marketing.penilaian.pdf', $data);
$pdf->setOptions([
'isHtml5ParserEnabled' => true,
'isRemoteEnabled' => true,
'defaultFont' => 'DejaVu Sans'
]);
$pdf->setPaper('a4', 'portrait');

return $pdf->download('penilaian_marketing_' . $user->name . '_' . $tahun . '_' . $bulan . '.pdf');
}

public function kpiSosmed(Request $request)
{
$user = auth()->user();
$userId = $user->id;

// Filter
$bulanNum = $request->bulan ?? date('n');
$tahun = $request->tahun ?? date('Y');

// 1. Hitung Hari Kerja (Kecuali Minggu)
$carbon = Carbon::create($tahun, $bulanNum, 1);
$daysInMonth = $carbon->daysInMonth;
$hariKerja = 0;
for ($d = 1; $d <= $daysInMonth; $d++) {
$day = Carbon::create($tahun, $bulanNum, $d);
if ($day->dayOfWeek != Carbon::SUNDAY) {
$hariKerja++;
}
}

// 2. Ambil Aktivitas Marketing
$activities = \App\Models\Activity::where('role', 'marketing')->get();

$activityPercents = [];
foreach ($activities as $act) {
$targetDaily = (float) ($act->target_daily ?? 0);
$targetBulanan = $targetDaily * $hariKerja;

$totalRealisasi = (float) \App\Models\DailyActiviti::where('user_id', $userId)
->where('activity_id', $act->id)
->whereMonth('tanggal', $bulanNum)
->whereYear('tanggal', $tahun)
->sum('realisasi');

if ($targetBulanan > 0) {
$percent = ($totalRealisasi / $targetBulanan) * 100;
$activityPercents[] = min($percent, 100);
}
}

// 3. Skor Rata-rata Disiplin Kerja
$skorDisiplin = count($activityPercents) > 0 ? (array_sum($activityPercents) / count($activityPercents)) : 0;

// 4. Ambil Data KPI Sosmed yang sudah disimpan (jika ada)
$savedKpi = KpiSosmed::where('user_id', $userId)
->where('bulan', $bulanNum)
->where('tahun', $tahun)
->first();

return view('marketing.penilaian.kpi_sosmed', compact('bulanNum', 'tahun', 'skorDisiplin', 'savedKpi'));
}

public function storeKpiSosmed(Request $request)
{
$user = auth()->user();

$kpi = KpiSosmed::updateOrCreate(
[
'user_id' => $user->id,
'bulan'   => (int) $request->bulan,
'tahun'   => (int) $request->tahun,
],
[
'followers_real'      => (float) $request->followers_real,
'followers_skor'      => (float) $request->followers_skor,
'respons_dm_real'     => $request->respons_dm_real,
'respons_dm_skor'     => (float) $request->respons_dm_skor,
'dm_masuk_real'       => (int) $request->dm_masuk_real,
'dm_masuk_skor'       => (float) $request->dm_masuk_skor,
'link_wa_real'        => (int) $request->link_wa_real,
'link_wa_skor'        => (float) $request->link_wa_skor,
'zoom_real'           => (int) $request->zoom_real,
'zoom_skor'           => (float) $request->zoom_skor,
'skor_disiplin'       => (float) $request->skor_disiplin,
'nilai_akhir'         => (float) $request->nilai_akhir,
]
);

return response()->json([
'status' => 'success',
'message' => 'KPI Sosmed berhasil disimpan',
'data' => $kpi
]);
}
}
    