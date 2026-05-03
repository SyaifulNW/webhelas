<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\SalesPlan;
use App\Models\User;
use App\Models\Data;
use App\Models\PesertaSmi;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    /**
     * Tampilkan halaman dashboard penjualan.
     */
    public function index(Request $request)
    {
        // ======================================================
        // 📊 0. Filter & Parameters
        // ======================================================
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        $type = $request->input('type', 'all'); // all, or specific kelas_id
        $sort = $request->input('sort', 'penjualan'); // penjualan, target, realisasi

        $kelasList = Kelas::orderBy('nama_kelas', 'asc')->get();

        // ======================================================
        // 📊 1. Total Penjualan 
        // ======================================================
        // Logic sinkronisasi dengan HomeController
        $buildDateFilter = function ($query, $y, $m) {
            $query->where(function ($q) use ($y, $m) {
                $q->where(function ($qM1T) use ($y, $m) {
                    $qM1T->whereHas('kelas', fn($k) => $k->where('nama_kelas', 'like', '%Start-Up Muslim Indonesia%'))
                         ->where(function ($qDate) use ($y, $m) {
                             $qDate->whereNotNull('tanggal_closing')->whereYear('tanggal_closing', $y);
                             if ($m !== 'all') { $qDate->whereMonth('tanggal_closing', $m); }
                         });
                })->orWhere(function ($qMBC) use ($y, $m) {
                    $qMBC->whereHas('kelas', function($k) use ($y, $m) {
                        $k->where('nama_kelas', 'not like', '%Start-Up Muslim Indonesia%')
                          ->where(function($kSub) use ($y, $m) {
                              $kSub->where('nama_kelas', 'like', '%Zoom Privat%')->orWhere('nama_kelas', 'like', '%Start-Up Muda Indonesia%');
                              if ($m !== 'all') {
                                  $kSub->orWhere(function($kMul) use ($y, $m) {
                                      $kMul->whereYear('tanggal_mulai', $y)->whereMonth('tanggal_mulai', $m);
                                  });
                              } else {
                                  $kSub->orWhereYear('tanggal_mulai', $y);
                              }
                          });
                    })->where(function ($qDate) use ($y, $m) {
                        $qDate->whereYear('updated_at', $y);
                        if ($m !== 'all') { $qDate->whereMonth('updated_at', $m); }
                    });
                });
            });
        };

        $dateFilter = function ($query) use ($tahun, $bulan, $buildDateFilter) {
            $buildDateFilter($query, $tahun, $bulan);
        };

        $salesQuery = SalesPlan::where('status', 'sudah_transfer')
            ->when($type !== 'all', function ($q) use ($type) {
                if ($type === 'm1t') {
                    $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', 'Start-Up Muslim Indonesia'));
                } elseif ($type === 'mbc') {
                    $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', '!=', 'Start-Up Muslim Indonesia'));
                }
            })
            ->where($dateFilter);

        $realSales = $salesQuery->with(['kelas', 'pesertaSmi'])->get();

        // [USER_REQUEST] Include monthly SPP realizations in Sales Dashboard
        $sppData = $this->calculateSppAchievements($bulan, $tahun);
        $totalSppAchievement = $sppData['total'];
        $sppByCS = $sppData['by_cs'];

        // [USER_REQUEST] Include Pendapatan Lainnya in total calculation 
        $lainnyaBulanan = 0;
        if ($type === 'all' && $bulan !== 'all') {
            $lainnyaBulanan = \App\Models\LabaRugi::where('bulan', str_pad($bulan, 2, '0', STR_PAD_LEFT))
                ->where('tahun', $tahun)
                ->where('keterangan', 'Pendapatan Lainnya')
                ->where('type', 'pendapatan')
                ->sum('jumlah');
        }

        $totalBulanan = $realSales->sum(function ($plan) {
            if ($plan->pesertaSmi) {
                return (float) str_replace('.', '', $plan->pesertaSmi->total_pembayaran ?: ($plan->pesertaSmi->pembayaran_spp ?: $plan->pesertaSmi->spp_awal ?: 0));
            }
            return (float) str_replace('.', '', $plan->nominal ?: 0);
        }) + $totalSppAchievement + $lainnyaBulanan;

        // Total Tahunan (Sesuai kategori terpilih)
        $totalTahunan = SalesPlan::where('status', 'sudah_transfer')
            ->when($type !== 'all', function ($q) use ($type) {
                if ($type === 'm1t') {
                    $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', 'Start-Up Muslim Indonesia'));
                } elseif ($type === 'mbc') {
                    $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', '!=', 'Start-Up Muslim Indonesia'));
                }
            })
            ->where(function ($q) use ($buildDateFilter, $tahun) {
                $buildDateFilter($q, $tahun, 'all');
            })
            ->get()->sum(function ($plan) {
                if ($plan->pesertaSmi) {
                    return (float) str_replace('.', '', $plan->pesertaSmi->total_pembayaran ?: ($plan->pesertaSmi->pembayaran_spp ?: $plan->pesertaSmi->spp_awal ?: 0));
                }
                return (float) str_replace('.', '', $plan->nominal ?: 0);
            }) + (($type === 'all' || $type === 'm1t') ? $this->calculateSppAchievements('all', $tahun)['total'] : 0) + ($type === 'all' ? \App\Models\LabaRugi::where('tahun', $tahun)->where('keterangan', 'Pendapatan Lainnya')->where('type', 'pendapatan')->sum('jumlah') : 0);

        // Global Target (Sync dengan dashboard admin: 125 Juta)
        $targetBulanan = 125_000_000;

        $realisasi = $totalBulanan;
        $persentaseCapaian = $targetBulanan > 0 ? round(($realisasi / $targetBulanan) * 100, 1) : 0;

        // Rata-rata harian
        $daysInPeriod = ($bulan === 'all') ? (Carbon::now()->year == $tahun ? Carbon::now()->dayOfYear : 365) : Carbon::create($tahun, $bulan)->daysInMonth;
        $currentDay = ($bulan == Carbon::now()->month && $tahun == Carbon::now()->year) ? Carbon::now()->day : $daysInPeriod;
        $rataHarian = $currentDay > 0 ? round($realisasi / $currentDay, 0) : 0;

        // ======================================================
        // 🧑‍💼 2. Penjualan Per CS & Target Pencapaian
        // ======================================================
        // Semua CS ditampilkan - cs-mbc (Linda, Yasmin) juga bisa closing M1T
        $csUsers = User::whereIn('role', ['cs-mbc', 'cs-smi'])
            ->where('name', '!=', 'Fitra Jaya Saleh')
            ->get();
        $salesData = [];

        $calculateOmset = function ($plan) {
            if ($plan->pesertaSmi) {
                return (float) str_replace('.', '', $plan->pesertaSmi->total_pembayaran ?: ($plan->pesertaSmi->pembayaran_spp ?: $plan->pesertaSmi->spp_awal ?: 0));
            }
            return (float) str_replace('.', '', $plan->nominal ?: 0);
        };

        foreach ($csUsers as $user) {
            // Gunakan $realSales (yang sudah diproses $dateFilter tingkat global)
            // agar data CS sinkron dengan data total di header & panel kanan.
            $userSalesAll = $realSales->where('created_by', $user->id);

            $m1tSales = $userSalesAll->filter(fn($s) => optional($s->kelas)->nama_kelas === 'Start-Up Muslim Indonesia');
            $mbcSales = $userSalesAll->filter(fn($s) => optional($s->kelas)->nama_kelas !== 'Start-Up Muslim Indonesia');

            $m1tNominal = (float) $m1tSales->sum($calculateOmset);
            $m1tCount = (int) $m1tSales->count();

            $mbcNominal = (float) $mbcSales->sum($calculateOmset);
            $mbcCount = (int) $mbcSales->count();

            // Breakdown MBC per kelas
            $mbcBreakdown = $mbcSales->groupBy(function ($s) {
                return $s->kelas->nama_kelas ?? 'Tanpa Kelas';
            })->map(function ($group) use ($calculateOmset) {
                return $group->sum($calculateOmset);
            })->toArray();

            $sppNominal = (float) ($sppByCS[$user->id] ?? 0);

            // Total berdasarkan filter type
            if ($type === 'm1t') {
                $userNominal = $m1tNominal;
                $userCount = $m1tCount;
            } elseif ($type === 'mbc') {
                $userNominal = $mbcNominal;
                $userCount = $mbcCount;
            } else {
                $userNominal = $m1tNominal + $mbcNominal;
                $userCount = $m1tCount + $mbcCount;
            }

            // Target per CS (Default 50 Juta)
            $uTarget = ($bulan === 'all') ? 50000000 * 12 : 50000000;

            $totalLeads = SalesPlan::where('created_by', $user->id)
                ->whereYear('updated_at', $tahun)
                ->when($bulan !== 'all', fn($q) => $q->whereMonth('updated_at', $bulan))
                ->count();

            $uRealisasi = $uTarget > 0 ? round(($userNominal / $uTarget) * 100) : 0;
            $uConv = $totalLeads > 0 ? round(($userCount / $totalLeads) * 100) : 0;

            // [USER_REQUEST] Always show Yasmin and Linda even if 0 omset
            $isTargetCS = in_array($user->name, ['Yasmin', 'Linda']);
            if ($userNominal > 0 || $userCount > 0 || $isTargetCS) {
                $salesData[] = [
                    'nama' => $user->name,
                    'role' => $user->role,
                    'penjualan' => $userCount,
                    'total_nominal' => $userNominal,
                    'mbc_nominal' => $mbcNominal,
                    'mbc_breakdown' => $mbcBreakdown,
                    'm1t_nominal' => $m1tNominal,
                    'spp_nominal' => $sppNominal, // [USER_REQUEST] Keep track of SPP per CS
                    'target' => $uTarget,
                    'realisasi' => $uRealisasi,
                    'conversion_rate' => $uConv,
                    'komisi' => $userNominal * 0.005,
                    'bonus' => ($uRealisasi >= 100) ? 500000 : 0
                ];
            }
        }

        // Sorting CS Users
        if ($sort === 'target')
            usort($salesData, fn($a, $b) => ($b['target'] ?? 0) <=> ($a['target'] ?? 0));
        elseif ($sort === 'realisasi')
            usort($salesData, fn($a, $b) => ($b['realisasi'] ?? 0) <=> ($a['realisasi'] ?? 0));
        else
            usort($salesData, fn($a, $b) => ($b['total_nominal'] ?? 0) <=> ($a['total_nominal'] ?? 0));

        // [USER_REQUEST] Add Pendapatan Lainnya as a separate row at position 4 (consistent with Laba Rugi)
        $lainnyaNominal = 0;
        if ($type === 'all') {
            $lainnyaNominal = \App\Models\LabaRugi::where('bulan', str_pad($bulan, 2, '0', STR_PAD_LEFT))
                ->where('tahun', $tahun)
                ->where('keterangan', 'Pendapatan Lainnya')
                ->where('type', 'pendapatan')
                ->sum('jumlah');
        }

        // Always ensure Pendapatan Lainnya is at position 4 (index 3) if possible
        $lainnyaRow = [
            'nama' => 'Pendapatan Lainnya',
            'is_lainnya_row' => true,
            'role' => 'system',
            'penjualan' => null,
            'total_nominal' => $lainnyaNominal,
            'target' => 0,
            'realisasi' => 0,
            'conversion_rate' => 0,
            'komisi' => 0,
            'bonus' => 0
        ];

        if (count($salesData) >= 3) {
            array_splice($salesData, 3, 0, [$lainnyaRow]);
        } else {
            $salesData[] = $lainnyaRow;
        }

        // [USER_REQUEST] Add SPP M1T as a separate row instead of including it in CS achievement
        // [USER_REQUEST] Always show SPP M1T row even if achievement is 0
        if ($type === 'all' || $type === 'm1t') {
            $salesData[] = [
                'nama' => 'SPP M1T',
                'is_spp_row' => true,
                'role' => 'system',
                'penjualan' => null,
                'total_nominal' => $totalSppAchievement,
                'target' => 0, // No specific target for recurring SPP
                'realisasi' => 0,
                'conversion_rate' => 0,
                'komisi' => 0,
                'bonus' => 0
            ];
        }

        // ======================================================
        // 🏫 3. Data Kelas & Kontribusi
        // ======================================================
        $kelas = SalesPlan::select('kelas_id', DB::raw('count(*) as count'))
            ->where('status', 'sudah_transfer')
            ->whereYear('updated_at', $tahun)
            ->when($bulan !== 'all', fn($q) => $q->whereMonth('updated_at', $bulan))
            ->groupBy('kelas_id')
            ->orderBy('count', 'desc')
            ->with('kelas')
            ->get()
            ->map(function ($item) {
                return [
                    'nama_kelas' => $item->kelas->nama_kelas ?? 'N/A',
                    'penjualan' => $item->count,
                    'status' => $item->count >= 10 ? 'Laris' : ($item->count >= 5 ? 'Sedang' : 'Kurang Laris')
                ];
            });

        $kontribusiDatabase = [
            'SMI' => $realSales->filter(fn($s) => optional($s->kelas)->nama_kelas == 'Start-Up Muslim Indonesia')->count(),
            'MBC' => $realSales->filter(fn($s) => optional($s->kelas)->nama_kelas != 'Start-Up Muslim Indonesia')->count(),
        ];

        // ======================================================
        // 👨‍👩‍👧 4. Data Pelanggan
        // ======================================================
        $totalPelangganAktif = Data::where('status_peserta', 'peserta')->count();
        $pelangganBaru = Data::where('status_peserta', 'peserta')->whereYear('created_at', $tahun)->whereMonth('created_at', $bulan)->count();
        $pelangganLama = max(0, $totalPelangganAktif - $pelangganBaru);
        $repeatOrderRate = 45;
        $ltv = 3_650_000;

        // ======================================================
        // 📊 5. Grafik Growth & Target
        // ======================================================
        $monthlySMI = [];
        $monthlyOmset = [];

        $m1tTahun = $request->input('m1t_tahun', $tahun);
        $m1tBulan = $request->input('m1t_bulan', 'all');

        for ($m = 1; $m <= 12; $m++) {
            // [USER_REQUEST] Include SPP in monthly stats table as well
            $sppMonth = $this->calculateSppAchievements($m, $tahun);
            $sppNominal = $sppMonth['total'];

            // [USER_REQUEST] Include Pendapatan Lainnya in monthly stats too
            $lainnyaMonth = 0;
            if ($type === 'all') {
                $lainnyaMonth = \App\Models\LabaRugi::where('bulan', str_pad($m, 2, '0', STR_PAD_LEFT))
                    ->where('tahun', $tahun)
                    ->where('keterangan', 'Pendapatan Lainnya')
                    ->where('type', 'pendapatan')
                    ->sum('jumlah');
            }

            $monthlyOmset[$m] = SalesPlan::where('status', 'sudah_transfer')
                ->when($type !== 'all', function ($q) use ($type) {
                    if ($type === 'm1t') {
                        $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', 'Start-Up Muslim Indonesia'));
                    } elseif ($type === 'mbc') {
                        $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', '!=', 'Start-Up Muslim Indonesia'));
                    }
                })
                ->where(function ($q) use ($buildDateFilter, $tahun, $m) {
                    $buildDateFilter($q, $tahun, $m);
                })
                ->get()->sum(function ($plan) {
                    if ($plan->pesertaSmi) {
                        return (float) str_replace('.', '', $plan->pesertaSmi->total_pembayaran ?: ($plan->pesertaSmi->pembayaran_spp ?: $plan->pesertaSmi->spp_awal ?: 0));
                    }
                    return (float) str_replace('.', '', $plan->nominal ?: 0);
                }) + (($type === 'all' || $type === 'm1t') ? $sppNominal : 0) + $lainnyaMonth;

            // Realisasi Peserta M1T per Bulan (Match dengan daftar di Salesplan)
            // Gunakan $m1tTahun agar filter tahun di tabel Pertumbuhan bekerja
            $monthlySMI[$m] = SalesPlan::where('status', 'sudah_transfer')
                ->whereHas('kelas', function ($q) {
                    $q->where('nama_kelas', 'Start-Up Muslim Indonesia');
                })
                ->whereDoesntHave('pesertaSmi', function ($q) {
                    $q->where('status', 'Cuti');
                })
                ->where(function ($q) use ($m1tTahun, $m) {
                    $q->whereMonth('tanggal_closing', $m);
                    if ($m1tTahun !== 'all') {
                        $q->whereYear('tanggal_closing', $m1tTahun);
                    }

                    $q->orWhere(function ($q2) use ($m1tTahun, $m) {
                        $q2->whereNull('tanggal_closing')
                            ->whereHas('pesertaSmi', function ($sub) use ($m1tTahun, $m) {
                                $sub->whereNotNull('tanggal_masuk')->whereMonth('tanggal_masuk', $m);
                                if ($m1tTahun !== 'all') {
                                    $sub->whereYear('tanggal_masuk', $m1tTahun);
                                }
                            });
                    })
                        ->orWhere(function ($q3) use ($m1tTahun, $m) {
                            $q3->whereNull('tanggal_closing')
                                ->whereDoesntHave('pesertaSmi', function ($sub) {
                                    $sub->whereNotNull('tanggal_masuk'); })
                                ->whereMonth('updated_at', $m);
                            if ($m1tTahun !== 'all') {
                                $q3->whereYear('updated_at', $m1tTahun);
                            }
                        });
                })
                ->count();
        }

        // ======================================================
        // 📊 5.1 GROWTH DATA (CUMULATIVE)
        // ======================================================
        $cumulativeSMI = [];

        // Hitung saldo awal (jumlah peserta dari tahun-tahun sebelumnya)
        $initialTotal = 0;
        if ($m1tTahun !== 'all') {
            $initialTotal = SalesPlan::where('status', 'sudah_transfer')
                ->whereHas('kelas', function ($q) {
                    $q->where('nama_kelas', 'Start-Up Muslim Indonesia');
                })
                ->whereDoesntHave('pesertaSmi', function ($q) {
                    $q->where('status', 'Cuti');
                })
                ->where(function ($q) use ($m1tTahun) {
                    $q->whereYear('tanggal_closing', '<', $m1tTahun)
                        ->orWhere(function ($q2) use ($m1tTahun) {
                            $q2->whereNull('tanggal_closing')
                                ->whereHas('pesertaSmi', function ($sub) use ($m1tTahun) {
                                    $sub->whereNotNull('tanggal_masuk')->whereYear('tanggal_masuk', '<', $m1tTahun);
                                });
                        })
                        ->orWhere(function ($q3) use ($m1tTahun) {
                            $q3->whereNull('tanggal_closing')
                                ->whereDoesntHave('pesertaSmi', function ($sub) {
                                    $sub->whereNotNull('tanggal_masuk'); })
                                ->whereYear('updated_at', '<', $m1tTahun);
                        });
                })
                ->count();
        }

        $runningTotal = $initialTotal;

        for ($m = 1; $m <= 12; $m++) {
            $runningTotal += $monthlySMI[$m];
            $cumulativeSMI[$m] = $runningTotal;
        }

        $penjualanBulanan = [
            'labels' => ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            'data' => array_values($monthlyOmset)
        ];

        $targetSMI = [
            1 => 10,   // Januari
            2 => 25,   // Februari
            3 => 40,   // Maret
            4 => 50,   // April
            5 => 150,  // Mei (+100)
            6 => 300,  // Juni (+150)
            7 => 450,  // Juli (+150)
            8 => 600,  // Agustus (+150)
            9 => 750,  // September (+150)
            10 => 850, // Oktober (+100)
            11 => 950, // November (+100)
            12 => 1000 // Desember (+50)
        ];

        // ======================================================
        // 📊 5.1 Grafik Per CS (Area Chart)
        // ======================================================
        $chartDataPerCs = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6610f2', '#fd7e14', '#20c997', '#d63384'];

        foreach ($csUsers as $idx => $user) {
            $userMonthlyData = [];
            for ($m = 1; $m <= 12; $m++) {
                $sum = SalesPlan::where('status', 'sudah_transfer')
                    ->where('created_by', $user->id)
                    ->when($type !== 'all', function ($q) use ($type) {
                        if ($type === 'm1t') {
                            $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', 'Start-Up Muslim Indonesia'));
                        } elseif ($type === 'mbc') {
                            $q->whereHas('kelas', fn($kq) => $kq->where('nama_kelas', '!=', 'Start-Up Muslim Indonesia'));
                        }
                    })
                    ->where(function ($q) use ($buildDateFilter, $tahun, $m) {
                        $buildDateFilter($q, $tahun, $m);
                    })
                    ->get()->sum(function ($plan) {
                        if ($plan->pesertaSmi) {
                            return (float) str_replace('.', '', $plan->pesertaSmi->total_pembayaran ?: ($plan->pesertaSmi->pembayaran_spp ?: $plan->pesertaSmi->spp_awal ?: 0));
                        }
                        return (float) str_replace('.', '', $plan->nominal ?: 0);
                    });
                $userMonthlyData[] = $sum > 0 ? $sum : null;
            }
            $chartDataPerCs[] = [
                'label' => $user->name,
                'data' => $userMonthlyData,
                'borderColor' => $colors[$idx % count($colors)],
                'backgroundColor' => 'transparent',
                'borderWidth' => 3,
                'pointBackgroundColor' => $colors[$idx % count($colors)],
                'pointBorderColor' => '#fff',
                'pointHoverRadius' => 5,
                'pointHoverBackgroundColor' => $colors[$idx % count($colors)],
                'pointHoverBorderColor' => '#fff',
                'pointHitRadius' => 10,
                'pointBorderWidth' => 2,
                'tension' => 0.35
            ];
        }


        // ======================================================
        // 📤 6. Breakdown per KPI for Modals
        // ======================================================
        // Breakdown per KPI for Modals sudah didefinisikan $calculateOmset di atas
        $penjualanSMI = $realSales->filter(fn($s) => optional($s->kelas)->nama_kelas == 'Start-Up Muslim Indonesia')->sum($calculateOmset);
        $penjualanMBC = $realSales->filter(fn($s) => optional($s->kelas)->nama_kelas != 'Start-Up Muslim Indonesia')->sum($calculateOmset);

        $salesTahunQuery = SalesPlan::where('status', 'sudah_transfer')
            ->where(function ($q) use ($buildDateFilter, $tahun) {
                $buildDateFilter($q, $tahun, 'all');
            });
        $salesTahun = $salesTahunQuery->with(['kelas', 'pesertaSmi'])->get();

        $penjualanSMI_Tahun = $salesTahun->filter(fn($s) => optional($s->kelas)->nama_kelas == 'Start-Up Muslim Indonesia')->sum($calculateOmset);
        $penjualanMBC_Tahun = $salesTahun->filter(fn($s) => optional($s->kelas)->nama_kelas != 'Start-Up Muslim Indonesia')->sum($calculateOmset);

        $pelangganSMI = Data::where('status_peserta', 'peserta')->whereHas('salesplan.kelas', fn($q) => $q->where('nama_kelas', 'Start-Up Muslim Indonesia'))->count();
        $pelangganMBC = Data::where('status_peserta', 'peserta')->whereHas('salesplan.kelas', fn($q) => $q->where('nama_kelas', '!=', 'Start-Up Muslim Indonesia'))->count();

        $viewData = [
            'totalBulanan' => $totalBulanan,
            'totalTahunan' => $totalTahunan,
            'targetBulanan' => $targetBulanan,
            'realisasi' => $realisasi,
            'persentaseCapaian' => $persentaseCapaian,
            'rataHarian' => $rataHarian,
            'kelas' => $kelas,
            'kelasList' => $kelasList,
            'tahun' => $tahun,
            'kontribusiDatabase' => $kontribusiDatabase,
            'salesData' => $salesData,
            'totalKomisi' => array_sum(array_column($salesData, 'komisi')),
            'totalBonus' => array_sum(array_column($salesData, 'bonus')),
            'totalPelangganAktif' => $totalPelangganAktif,
            'pelangganBaru' => $pelangganBaru,
            'pelangganLama' => $pelangganLama,
            'repeatOrderRate' => $repeatOrderRate,
            'ltv' => $ltv,
            'penjualanBulanan' => $penjualanBulanan,
            'notifikasi' => $persentaseCapaian >= 100 ? '🎉 Target Tercapai!' : '⚠️ Terus Semangat!',
            'penjualanSMI' => $penjualanSMI,
            'penjualanMBC' => $penjualanMBC,
            'penjualanSMI_Tahun' => $penjualanSMI_Tahun,
            'penjualanMBC_Tahun' => $penjualanMBC_Tahun,
            'pelangganSMI' => $pelangganSMI,
            'pelangganMBC' => $pelangganMBC,
            'rataSMI' => $currentDay > 0 ? round($penjualanSMI / $currentDay) : 0,
            'rataMBC' => $currentDay > 0 ? round($penjualanMBC / $currentDay) : 0,
            'monthlyOmset' => $monthlyOmset,
            'monthlySMI' => $monthlySMI,
            'm1tTahun' => $m1tTahun,
            'm1tBulan' => $m1tBulan,
            'chartDataPerCs' => $chartDataPerCs,
            'cumulativeSMI' => $cumulativeSMI,
            'targetSMI' => $targetSMI,
        ];

        if ($request->ajax()) {
            return view('penjualan.table', $viewData);
        }

        return view('penjualan.index', $viewData);
    }

    private function calculateSppAchievements($bulan, $tahun)
    {
        $totalSpp = 0;
        $byCS = [];
        $pesertas = PesertaSmi::with('salesPlan')->get();
        
        $monthsToCheck = ($bulan === 'all') ? range(1, 12) : [(int)$bulan];
        $filterYear = ($tahun !== 'all') ? (int)$tahun : (int)date('Y');

        foreach ($pesertas as $p) {
            // Skip if not active or cuti
            if ($p->status === 'Cuti') continue;

            // Check if Lunas
            $isManualLunas = ($p->is_lunas == 1);
            $countPaidTotal = 0;
            for ($m_check = 1; $m_check <= 12; $m_check++) {
                if (($p->{"spp_$m_check"} ?? 0) >= 1000000) $countPaidTotal++;
            }
            if ($isManualLunas || $countPaidTotal >= 6) continue;

            $selectedMonths = [];
            if ($p->salesPlan && is_array($p->salesPlan->selected_months)) {
                $selectedMonths = $p->salesPlan->selected_months;
            } elseif ($p->salesPlan && is_string($p->salesPlan->selected_months)) {
                $selectedMonths = json_decode($p->salesPlan->selected_months, true) ?? [];
            }

            foreach ($monthsToCheck as $m) {
                $val = (float)($p->{"spp_$m"} ?? 0);
                $paymentDate = $p->{"tanggal_spp_$m"} ?? null;

                if ($val > 0) {
                    $pYear = $paymentDate ? (int)Carbon::parse($paymentDate)->format('Y') : null;
                    if (($tahun === 'all' || $pYear === $filterYear)) {
                        // Crucial: Exclude payments already part of the initial "Closing" package
                        // (Blue checkmarks on the SMI dashboard)
                        $isPlanChecked = (isset($selectedMonths[$filterYear]) && in_array($m, $selectedMonths[$filterYear]));
                        
                        if (!$isPlanChecked) {
                            $totalSpp += $val;
                            $csId = $p->salesPlan ? $p->salesPlan->created_by : null;
                            if ($csId) {
                                $byCS[$csId] = ($byCS[$csId] ?? 0) + $val;
                            }
                        }
                    }
                }
            }
        }

        return ['total' => $totalSpp, 'by_cs' => $byCS];
    }
}
