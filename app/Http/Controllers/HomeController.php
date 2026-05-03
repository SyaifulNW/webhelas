<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\SalesPlan;
use App\Models\Activity;
use App\Models\DailyActiviti;
use App\Models\Data;
use App\Models\Notifikasi;
use App\Models\LabaRugi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function storePendapatanLainnya(Request $request)
    {
        $request->validate([
            'omset' => 'required|numeric|min:0',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $now = now();
        
        LabaRugi::updateOrCreate(
            [
                'bulan' => str_pad($request->bulan, 2, '0', STR_PAD_LEFT),
                'tahun' => $request->tahun,
                'keterangan' => 'Pendapatan Lainnya',
                'type' => 'pendapatan',
                'created_by' => auth()->id(),
            ],
            [
                'tanggal' => $now->format('Y-m-d'),
                'jumlah' => $request->omset,
            ]
        );

        return back()->with('success', 'Pendapatan Lainnya berhasil ditambahkan.');
    }

    public function index(Request $request)
    {
        $role = auth()->check() ? strtolower(auth()->user()->role) : 'guest';
        if (auth()->check()) {
            if ($role === 'produksi') {
                return redirect()->route('produksi.performance');
            }
            if ($role === 'administrator') {
                return redirect()->route('administrator');
            }
            if ($role === 'chapter' || $role === 'reseller') {
                // Pre-calculate filter values
                $bulanStr = $request->input('bulan') ?? \Carbon\Carbon::now()->format('Y-m');
                try {
                    $carbonBulan = \Carbon\Carbon::createFromFormat('Y-m', $bulanStr);
                } catch (\Exception $e) {
                    $carbonBulan = \Carbon\Carbon::now();
                }
                $tahun = $carbonBulan->year;
                $bulanNum = $carbonBulan->month;
                $bulanLabel = $carbonBulan->isoFormat('MMMM YYYY');
                
                $user = auth()->user();
                $chapterName = $user->chapter;
                $userId = $user->id;
                $isChapter = ($role === 'chapter');

                // 0. Clean Chapter Name and Identify Team Members (Regional for Chapter)
                $cleanChapterName = trim(str_ireplace('CHAPTER', '', $chapterName));
                
                // 0. Identify Direct Team Members (1 level below only)
                $resellerMembersIds = \App\Models\User::where('role', 'reseller')
                    ->where('created_by', $userId)
                    ->pluck('id');
                
                $allTeamIds = $resellerMembersIds->merge([$userId])->unique();

                // 0b. Regional IDs for Chapter stats (Leads, Participants, Direct Fee)
                $regionalTeamIds = $allTeamIds;
                if ($isChapter) {
                    $regionalMemberIds = \App\Models\User::where('role', 'reseller')
                        ->where('chapter', 'LIKE', '%' . $cleanChapterName . '%')
                        ->pluck('id');
                    $regionalTeamIds = $regionalMemberIds->merge([$userId])->unique();
                }

                // 1. Total Peserta Aktif (Bulan Ini)
                $pesertaAktifQuery = \App\Models\SalesPlan::join('peserta_smis', 'salesplans.id', '=', 'peserta_smis.sales_plan_id')
                    ->where('salesplans.status', 'sudah_transfer')
                    ->where('peserta_smis.approval_status', 'Approved')
                    ->whereYear('salesplans.updated_at', $tahun)
                    ->whereMonth('salesplans.updated_at', $bulanNum);
                
                if ($isChapter) {
                    $pesertaAktifQuery->whereIn('salesplans.created_by', $regionalTeamIds);
                } else {
                    $pesertaAktifQuery->whereIn('salesplans.created_by', $allTeamIds);
                }
                $totalPesertaAktif = $pesertaAktifQuery->count();

                // 1b. Total Peserta Aktif (Keseluruhan)
                $pesertaAktifAllTimeQuery = \App\Models\SalesPlan::join('peserta_smis', 'salesplans.id', '=', 'peserta_smis.sales_plan_id')
                    ->where('salesplans.status', 'sudah_transfer')
                    ->where('peserta_smis.approval_status', 'Approved');
                
                if ($isChapter) {
                    $pesertaAktifAllTimeQuery->whereIn('salesplans.created_by', $regionalTeamIds);
                } else {
                    $pesertaAktifAllTimeQuery->whereIn('salesplans.created_by', $allTeamIds);
                }
                $totalPesertaAktifAllTime = $pesertaAktifAllTimeQuery->count();

                // 2. Total prospek (leads) from Data (Menu Database)
                // [USER_REQUEST] Total Leads adalah total database masuk, di menu database.
                $allTeamNames = \App\Models\User::whereIn('id', $allTeamIds)->pluck('name')->toArray();
                $leadsBaseQuery = \App\Models\Data::whereYear('created_at', $tahun)
                    ->whereMonth('created_at', $bulanNum)
                    ->whereIn('status_peserta', ['peserta_baru', 'pindah_salesplan']);

                // Calculate Personal Leads (Strictly by ID or Name)
                $leadsPribadi = (clone $leadsBaseQuery)
                    ->where(function($q) use ($userId, $user) {
                        $q->where('created_by', $userId)
                          ->orWhere('created_by', $user->name);
                    })
                    ->count();

                if ($isChapter) {
                    // CHAPTER: Total includes regional visibility (City name) + Team
                    $totalLeads = (clone $leadsBaseQuery)
                        ->where(function ($q) use ($chapterName, $cleanChapterName, $allTeamIds, $allTeamNames) {
                            if ($chapterName) {
                                $q->where('kota_nama', 'LIKE', '%' . $chapterName . '%')
                                  ->orWhere('kota_nama', 'LIKE', '%' . $cleanChapterName . '%');
                            }
                            $q->orWhereIn('created_by', $allTeamIds)
                              ->orWhereIn('created_by', $allTeamNames);
                        })
                        ->count();
                    $leadsTeam = max(0, $totalLeads - $leadsPribadi);
                } else {
                    // RESELLER: Total is Personal + Direct Team
                    $resellerTeamNames = \App\Models\User::whereIn('id', $resellerMembersIds)->pluck('name')->toArray();
                    $leadsTeam = (clone $leadsBaseQuery)
                        ->where(function($q) use ($resellerMembersIds, $resellerTeamNames) {
                            $q->whereIn('created_by', $resellerMembersIds)
                              ->orWhereIn('created_by', $resellerTeamNames);
                        })
                        ->count();
                    $totalLeads = $leadsPribadi + $leadsTeam;
                }

                // 3. Omset (Revenue) - Sum of SalesPlan nominal OR PesertaSmi spp_awal
                // [USER_REQUEST] Omset should only count Approved participants
                $baseOmsetQuery = \App\Models\SalesPlan::join('peserta_smis', 'salesplans.id', '=', 'peserta_smis.sales_plan_id')
                    ->where('salesplans.status', 'sudah_transfer')
                    ->where('peserta_smis.approval_status', 'Approved')
                    ->whereYear('salesplans.updated_at', $tahun)
                    ->whereMonth('salesplans.updated_at', $bulanNum);
                
                $omsetPribadi = (clone $baseOmsetQuery)
                    ->where('salesplans.created_by', $userId)
                    ->sum(\DB::raw('CAST(COALESCE(peserta_smis.pembayaran_spp, salesplans.nominal, 0) AS DECIMAL(15,2))'));
                
                $omsetReseller = 0;
                if ($resellerMembersIds->isNotEmpty()) {
                    $omsetReseller = (clone $baseOmsetQuery)
                        ->whereIn('salesplans.created_by', $resellerMembersIds)
                        ->sum(\DB::raw('CAST(COALESCE(peserta_smis.pembayaran_spp, salesplans.nominal, 0) AS DECIMAL(15,2))'));
                }
                
                $omsetBulanIni = $omsetPribadi + $omsetReseller;

                $monthEarnings = \App\Services\EarningsService::calculateTotalEarnings($userId, $tahun, $bulanNum);
                
                // Detailed breakdown for view (optional but good for compatibility)
                $komisi = $omsetPribadi * 0.10;
                $directFee = 0;
                if ($isChapter) {
                    $totalParticipantsCount = (clone $baseOmsetQuery)->whereIn('salesplans.created_by', $regionalTeamIds)->count();
                    $directFee = $totalParticipantsCount * 500000;
                }
                $royalti = $omsetReseller * 0.05;
                $bonusPribadi = ($omsetPribadi >= 20000000) ? ($omsetPribadi * 0.1) : (($omsetPribadi >= 10000000) ? ($omsetPribadi * 0.05) : 0);
                $bonusTim = ($resellerMembersIds->isNotEmpty() && ($omsetPribadi + $omsetReseller) >= 30000000) ? (($omsetPribadi + $omsetReseller) * 0.1) : 0;

                $totalPenghasilan = $monthEarnings;

                // Progress towards a goal (using 50M or Team Target)
                $targetBonus = (float) (\App\Models\Setting::where('key', 'target_omset_chapter')->value('value') ?? 50000000);
                $progressProgress = $targetBonus > 0 ? round(($omsetBulanIni / $targetBonus) * 100, 2) : 0;

                // 9. Reseller/Chapter Specific Table Data
                $kelasOmsetFiltered = collect();
                $kelasOmsetFiltered->push([
                    'nama_kelas' => 'M1T',
                    'tanggal' => '-',
                    'omset' => $omsetBulanIni,
                    'omset_pribadi' => $omsetPribadi,
                    'omset_reseller' => $omsetReseller,
                    'royalti' => $royalti,
                    'komisi' => $komisi,
                    'direct_fee' => $directFee,
                    'bonus_pribadi' => $bonusPribadi,
                    'bonus_tim' => $bonusTim,
                    'total_penghasilan' => $totalPenghasilan,
                    'target' => $targetBonus,
                ]);

                // Placeholder values for view compatibility
                $kpiData = [];
                $totalBobot = $totalNilai = $totalLeadAktif = 0;
                $labels = $values = [];
                $databaseBaru = $databaseTotal = 0;
                $persentaseDatabaseBaru = $persentaseDatabaseLama = 0;
                $totalNilaiHasil = 0; $historyNilai = array_fill(1, 12, 0);
                $notifikasi = []; $notifCount = 0; $totalKomisi = $komisi;
                $manual = null; $skorDaily = 0; $nilaiOmset = 0;
                $nilaiClosingPaket = 0; $nilaiDatabaseBaru = 0; $nilaiManualPart = 0; $nilaiIntakePart = 0;
                $pencapaianOmset = $omsetBulanIni; $pencapaianClosingPaket = $totalPesertaAktif;
                $pencapaianDatabaseBaru = $totalLeads; $closingPaket = $totalPesertaAktif;
                $cold = $tertarik = $mau_transfer = $sudah_transfer = $no = 0;
                $komisiBulanIni = $komisi; $bonus = $bonusPribadi + $bonusTim;

                // 10. Wallet Data for Dashboard Integration
                $wallet = $user->ensureWalletExists();
                $totalEarningsAllTime = \App\Services\EarningsService::calculateTotalEarnings($userId);
                $totalWithdrawnAllTime = $wallet->transactions()
                    ->where('type', 'withdrawal')
                    ->whereIn('status', ['success', 'pending', 'rejected'])
                    ->sum('amount');
                $availableBalance = $totalEarningsAllTime - $totalWithdrawnAllTime;
                $currentPending = $wallet->transactions()
                    ->where('type', 'withdrawal')
                    ->whereIn('status', ['pending', 'rejected'])
                    ->sum('amount');
                
                // Recent Transactions (Limit 5 for dashboard)
                $walletTransactions = $wallet->transactions()->latest()->take(5)->get();
                $savedBankName = $wallet->bank_name;
                $savedAccountNumber = $wallet->account_number;
                $savedAccountName = $wallet->account_name;

                return view('home', compact(
                    'role', 'chapterName', 'totalPesertaAktif', 'totalLeads', 'omsetBulanIni',
                    'komisiBulanIni', 'bonus', 'totalPenghasilan', 'progressProgress', 'targetBonus', 'bulanStr',
                    'directFee', 'royalti', 'bonusPribadi', 'bonusTim', 'komisi', 'kelasOmsetFiltered',
                    'omsetPribadi', 'omsetReseller', 'leadsPribadi', 'leadsTeam', 'bulanLabel', 'totalPesertaAktifAllTime',
                    'availableBalance', 'currentPending', 'walletTransactions', 'wallet',
                    'savedBankName', 'savedAccountNumber', 'savedAccountName',
                    'kpiData', 'totalBobot', 'totalNilai', 'labels', 'values', 'databaseBaru', 'databaseTotal',
                    'persentaseDatabaseBaru', 'persentaseDatabaseLama', 'totalNilaiHasil', 'historyNilai',
                    'notifikasi', 'notifCount', 'totalKomisi', 'manual', 'skorDaily', 'nilaiOmset',
                    'nilaiClosingPaket', 'nilaiDatabaseBaru', 'nilaiManualPart', 'nilaiIntakePart',
                    'pencapaianOmset', 'pencapaianClosingPaket', 'pencapaianDatabaseBaru', 'closingPaket',
                    'cold', 'tertarik', 'mau_transfer', 'sudah_transfer', 'no', 'totalLeadAktif'
                ));
            }
        }

        // ====================== 📅 FILTER BULAN ======================
        $bulan = $request->input('bulan') ?? Carbon::now()->format('Y-m');
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $tahun = $carbonBulan->year;
        $bulanNum = $carbonBulan->month;

        // ====================== 👤 USER LOGIN ======================
        $csId   = auth()->id();
        $csName = optional(auth()->user())->name;

        // ====================== 🔔 NOTIFIKASI ======================
        $notifikasi = Notifikasi::where('user_id', $csId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $notifCount = Notifikasi::where('user_id', $csId)
            ->where('is_read', false)
            ->count();

        // ====================== 💰 OMSET & KOMISI ======================
        $isCsSmi = optional(auth()->user())->role === 'cs-smi';
        $isCsMbc = optional(auth()->user())->role === 'cs-mbc';

        if ($isCsSmi) {
            // Khusus CS SMI: Ambil kelas Start-Up Muda Indonesia, Start-Up Muslim Indonesia & Kelas Privat (tanpa filter tanggal)
            $kelasOmset = Kelas::where(function($q) {
                    $q->where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                      ->orWhere('nama_kelas', 'like', '%Start-Up Muslim Indonesia%')
                      ->orWhere('nama_kelas', 'like', '%Zoom Privat%');
                })
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->whereYear('updated_at', $tahun)
                        ->whereMonth('updated_at', $bulanNum)
                        ->where('status', 'sudah_transfer');
                }])
                ->get();
        } elseif ($isCsMbc) {
            // CS MBC - M1T (Start-Up Muslim Indonesia): filter by tanggal_closing agar cocok dengan Daftar Peserta
            $kelasSmi = Kelas::where('nama_kelas', 'like', '%Start-Up Muslim Indonesia%')
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->where('status', 'sudah_transfer')
                        ->whereNotNull('tanggal_closing')
                        ->whereYear('tanggal_closing', $tahun)
                        ->whereMonth('tanggal_closing', $bulanNum);
                }])
                ->get();

            // CS MBC - Kelas lain (Deal Maker, Operasional, Zoom Privat, dll): pakai updated_at (sudah benar)
            $kelasLain = Kelas::where(function ($q) use ($tahun, $bulanNum) {
                    $q->where('nama_kelas', 'like', '%Zoom Privat%')
                      ->orWhere(function ($sub) use ($tahun, $bulanNum) {
                          $sub->whereYear('tanggal_mulai', $tahun)
                              ->whereMonth('tanggal_mulai', $bulanNum);
                      });
                })
                ->where('nama_kelas', 'not like', '%Start-Up Muslim Indonesia%')
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->where('status', 'sudah_transfer')
                        ->whereYear('updated_at', $tahun)
                        ->whereMonth('updated_at', $bulanNum);
                }])
                ->get();

            $kelasOmset = $kelasSmi->merge($kelasLain);
        } else {
            // Role Lain: Ambil kelas sesuai bulan berjalan + Kelas Privat (tanpa filter tanggal)
            $kelasOmset = Kelas::where(function ($q) use ($tahun, $bulanNum) {
                    $q->where(function ($sub) use ($tahun, $bulanNum) {
                        $sub->whereYear('tanggal_mulai', $tahun)
                            ->whereMonth('tanggal_mulai', $bulanNum);
                    })->orWhere('nama_kelas', 'like', '%Zoom Privat%');
                })
                ->with(['salesplans' => function ($query) use ($csId, $tahun, $bulanNum) {
                    $query->where('created_by', $csId)
                        ->whereYear('updated_at', $tahun)
                        ->whereMonth('updated_at', $bulanNum);
                }])
                ->get();
        }

        $kelasOmsetFiltered = $kelasOmset->groupBy('nama_kelas')->map(function ($group) use ($isCsMbc) {
            // Ambil data pertama untuk info nama & tanggal (asumsi tanggal sama/mirip)
            $kelas = $group->first();
            
            // Hitung total omset dari SEMUA kelas yang namanya sama
            $omset = $group->sum(function ($k) use ($isCsMbc) {
                return $k->salesplans->sum(function($p) use ($isCsMbc) {
                    // [USER_REQUEST] Exclude non-approved participants that need approval
                    if ($p->pesertaSmi) {
                        $creatorRole = strtolower($p->pesertaSmi->closingCs->role ?? $p->pesertaSmi->createdBy->role ?? $p->created_by_role ?? '');
                        $needsApproval = in_array($creatorRole, ['reseller', 'chapter', 'agen']);
                        if ($needsApproval && $p->pesertaSmi->approval_status !== 'Approved') {
                            return 0;
                        }
                    }
                    return $p->pesertaSmi ? (
                        $isCsMbc
                            ? (float)($p->pesertaSmi->total_pembayaran ?? $p->pesertaSmi->pembayaran_spp ?? 0)
                            : (float)$p->pesertaSmi->pembayaran_spp
                    ) : (float)$p->nominal;
                });
            });

            $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
            $targetSmi = \App\Models\Setting::where('key', 'target_omset_smi')->value('value') ?? 50000000;

            if (str_contains($kelas->nama_kelas, 'Start-Up Muda Indonesia') || str_contains($kelas->nama_kelas, 'Start-Up Muslim Indonesia')) {
                $target = $targetSmi;
            } else {
                $target = $targetGlobal / 2;
            }

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
        })->values();

        if ($isCsMbc) {
            $pendapatanLainnya = LabaRugi::where('bulan', str_pad($bulanNum, 2, '0', STR_PAD_LEFT))
                ->where('tahun', $tahun)
                ->where('keterangan', 'Pendapatan Lainnya')
                ->where('type', 'pendapatan')
                ->where('created_by', auth()->id())
                ->sum('jumlah');

            $kelasOmsetFiltered->push([
                'nama_kelas' => 'Pendapatan Lainnya',
                'tanggal'    => '-',
                'omset'      => $pendapatanLainnya,
                'target'     => 0,
                'persen'     => 0,
                'komisi'     => $pendapatanLainnya * 0.05,
                'is_manual'  => true
            ]);
        }

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

        $activityQuery = Activity::orderBy('categories_id');
        
        if ($csName === 'Nisa') {
            $activityQuery->whereIn('categories_id', [6, 7]);
        } elseif ($csName === 'Felmi') {
            $activityQuery->whereHas('kategori', function($q) {
                $q->where('nama', 'LIKE', '%INTAKE%');
            });
        } else {
            // Standard CS MBC
            $activityQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                          ->whereHas('kategori', function($q) {
                              $q->where('nama', 'NOT LIKE', '%INTAKE%');
                          });
        }

        $activities = $activityQuery->with('kategori')->get()->groupBy('categories_id');

        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
            'A. Aktivitas Harian (NON-NEGOTIABLE)' => 50,
            'B. Aktivitas Mingguan' => 50,
            'DAILY INTAKE' => 33.33,
            'WEEKLY INTAKE' => 33.33,
            'MONTHLY INTAKE' => 33.34,
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



        // ============ Database Baru ============
        $databaseBaru = Data::where('created_by', $csName)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->count();
            
        // ====================== DATABASE PERSEN ======================
        $databaseTotal = Data::where('created_by', $csName)->count();
        $persentaseDatabaseBaru = $databaseTotal > 0 ? round(($databaseBaru / $databaseTotal) * 100, 2) : 0;
        $persentaseDatabaseLama = 100 - $persentaseDatabaseBaru;

        // ====================== SUMBER LEADS ======================
        $sumberDatabase = Data::select('leads', DB::raw('COUNT(*) as total'))
            ->where('created_by', $csName)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->groupBy('leads')
            ->pluck('total', 'leads')
            ->toArray();

        $labels = array_keys($sumberDatabase);
        $values = array_values($sumberDatabase);

        // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
        // OMSET
        $totalOmset = $kelasOmsetFiltered->sum('omset'); 
        $targetBulananOmset = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
        $pencapaianOmset = $totalOmset;
        $nilaiOmsetSkor = $targetBulananOmset > 0 ? min(100, round(($totalOmset / $targetBulananOmset) * 100)) : 0;
        $nilaiOmset = round(($nilaiOmsetSkor / 100) * 40, 2);
        
        // Closing Paket
        $closingPaket = SalesPlan::where('created_by', $csId)
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulanNum)
            ->where('status', 'sudah_transfer')
            ->count();
        $pencapaianClosingPaket = $closingPaket;
        $nilaiClosingPaketSkor = $closingPaket >= 1 ? 100 : 0;
        $nilaiClosingPaket = round(($nilaiClosingPaketSkor / 100) * 10, 2);
        
        // Database Baru
        $pencapaianDatabaseBaru = $databaseBaru;
        $nilaiDatabaseBaruSkor = $databaseBaru >= 50 ? 100 : ($databaseBaru * 2);
        if ($nilaiDatabaseBaruSkor > 100) $nilaiDatabaseBaruSkor = 100;
        $nilaiDatabaseBaru = round(($nilaiDatabaseBaruSkor / 100) * 10, 2);

        // MANUAL ASSESSMENT
        $manual = \App\Models\PenilaianManual::where('user_id', $csId)
            ->where('bulan', $bulanNum)
            ->where('tahun', $tahun)
            ->first();
        $nilaiManualPart = 0;
        if ($manual) {
            $sum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
            $bobotManual = ($isCsSmi) ? 30 : 20;
            $nilaiManualPart = round(($sum / 500) * $bobotManual);
        }

        // INTAKE
        $nilaiIntakePart = round(($totalNilai / 100) * 20, 2);

        // TOTAL NILAI HASIL
        $totalNilaiHasil = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru + $nilaiManualPart + $nilaiIntakePart;
    $historyNilai = [];
    $role = optional(auth()->user())->role;

    for ($m = 1; $m <= 12; $m++) {
        $historyNilai[$m] = $this->hitungTotalNilaiHasil($csId, optional(auth()->user())->name, $m, $tahun, $role);
    }

    // ====================== RETURN ======================
    $skorDaily = $totalNilai;
    return view('home', compact(
        'role',
        'kelasOmsetFiltered',
        'totalKomisi',

        // Nilai hasil CS
        'nilaiOmset',
        'nilaiClosingPaket',
        'nilaiDatabaseBaru',
        'nilaiManualPart',
        'nilaiIntakePart',
        'skorDaily',
        'totalNilaiHasil',
        'manual',
        'historyNilai',

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
        'labels',
        'values',
        'persentaseDatabaseBaru',
        'persentaseDatabaseLama',

        'pencapaianOmset',
        'pencapaianClosingPaket',
        'pencapaianDatabaseBaru',
        
        // Closing Paket
        'closingPaket',
        'bulanNum',
        'tahun',

        'notifikasi',
        'notifCount'
    ));
}

private function hitungTotalNilaiHasil($csId, $namaUserData, $bulan, $tahun, $role)
{
    // OMSET (40%)
    if ($role === 'cs-smi') {
        $kelasOmset = Kelas::where(function($q) {
                $q->where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                  ->orWhere('nama_kelas', 'like', '%Start-Up Muslim Indonesia%')
                  ->orWhere('nama_kelas', 'like', '%Zoom Privat%');
            })
            ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                $q->where('created_by', $csId)
                    ->whereYear('updated_at', $tahun)
                    ->whereMonth('updated_at', $bulan)
                    ->where('status', 'sudah_transfer');
            }])
            ->get();
    } else {
        $kelasOmset = Kelas::where(function ($q) use ($tahun, $bulan) {
                $q->where(function ($sub) use ($tahun, $bulan) {
                    $sub->whereYear('tanggal_mulai', $tahun)
                        ->whereMonth('tanggal_mulai', $bulan);
                })->orWhere('nama_kelas', 'like', '%Zoom Privat%')
                  ->orWhere('nama_kelas', 'like', '%Start-Up Muslim Indonesia%');
            })
            ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                $q->where('created_by', $csId)
                    ->whereYear('updated_at', $tahun)
                    ->whereMonth('updated_at', $bulan);
            }])
            ->get();
    }

    $totalOmset = $kelasOmset->sum(function($k) {
        return $k->salesplans->sum(function($p) {
            return $p->pesertaSmi ? (float)$p->pesertaSmi->pembayaran_spp : (float)$p->nominal;
        });
    });
    $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
    
    // OMSET (40%)
    $nilaiOmsetSkor = $targetGlobal > 0 ? min(100, round(($totalOmset / $targetGlobal) * 100)) : 0;
    $nilaiOmset = round(($nilaiOmsetSkor / 100) * 40, 2);

    // INTAKE / DAILY ACTIVITY (20%)
    $dayQuery = \App\Models\Activity::where('role', 'cs');
    if (strtolower($namaUserData) === 'nisa') {
        $dayQuery->whereIn('categories_id', [6, 7]);
    } elseif (strtolower($namaUserData) === 'felmi') {
        $dayQuery->whereHas('kategori', function($q) { $q->where('nama', 'LIKE', '%INTAKE%'); });
    } else {
        $dayQuery->whereIn('categories_id', [1, 2, 3, 4, 5])
                 ->whereHas('kategori', function($q) { $q->where('nama', 'NOT LIKE', '%INTAKE%'); });
    }
    $allActivities = $dayQuery->with('kategori')->get()->groupBy('categories_id');
    $carbon = Carbon::create($tahun, $bulan, 1);
    $daysInMonth = $carbon->daysInMonth;
    $hariKerja = 0;
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = Carbon::create($tahun, $bulan, $d);
        if ($date->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
    }
    $allKpiPercents = [];
    foreach ($allActivities as $list) {
        $rowPercents = [];
        foreach ($list as $act) {
            $targetD = (float)($act->target_daily ?? 0);
            $targetB = ($targetD > 0) ? ($targetD * $hariKerja) : (float)($act->target_bulanan ?? 0);
            if (strpos($act->nama, 'List Building / Database') !== false) {
                $real = (float) \App\Models\Data::where('created_by', $namaUserData)
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->count();
            } else {
                $real = (float) \App\Models\DailyActiviti::where('user_id', $csId)
                    ->where('activity_id', $act->id)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->sum('realisasi');
            }
            $rowPercents[] = ($targetB > 0) ? min(100, ($real / $targetB) * 100) : 0;
        }
        $allKpiPercents[] = count($rowPercents) ? (array_sum($rowPercents) / count($rowPercents)) : 0;
    }
    $skorDailyOverall = count($allKpiPercents) ? (array_sum($allKpiPercents) / count($allKpiPercents)) : 0;
    $nilaiIntake = ($skorDailyOverall / 100) * 20;

    // CLOSING PAKET (10%)
    $closing = \App\Models\SalesPlan::where('created_by', $csId)
        ->whereYear('updated_at', $tahun)
        ->whereMonth('updated_at', $bulan)
        ->where('status', 'sudah_transfer')
        ->count();
    $closingScore = $closing >= 1 ? 100 : 0;
    $nilaiClosing = round(($closingScore / 100) * 10, 2);

    // DATABASE BARU (10%)
    $dbBaru = \App\Models\Data::where('created_by', $namaUserData)
        ->whereYear('created_at', $tahun)
        ->whereMonth('created_at', $bulan)
        ->count();
    $dbScore = $dbBaru >= 50 ? 100 : ($dbBaru * 2);
    if ($dbScore > 100) $dbScore = 100;
    $nilaiDb = round(($dbScore / 100) * 10, 2);

    // MANUAL ASSESSMENT (20%)
    $manual = \App\Models\PenilaianManual::where('user_id', $csId)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
    $nilaiManualPart = 0;
    if ($manual) {
        $sum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
        $nilaiManualPart = round(($sum / 500) * 20);
    }

    return round($nilaiOmset + $nilaiClosing + $nilaiDb + $nilaiIntake + $nilaiManualPart, 2);
}
}
