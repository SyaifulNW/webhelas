<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PesertaSmiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Auto-sync from SalesPlan for Start-Up Muslim Indonesia
        $smiKelas = \App\Models\Kelas::where('nama_kelas', 'Start-Up Muslim Indonesia')->first();
        if ($smiKelas) {
            $salesPlansSmi = \App\Models\SalesPlan::where('status', 'sudah_transfer')
                ->where('kelas_id', $smiKelas->id)
                ->get();

            foreach ($salesPlansSmi as $plan) {
                // Check if already in PesertaSmi by sales_plan_id
                $exists = \App\Models\PesertaSmi::where('sales_plan_id', $plan->id)->exists();
                
                if (!$exists) {
                    $dataRel = $plan->data;
                    $nama = $dataRel ? $dataRel->nama : $plan->nama;
                    $cs = \App\Models\User::find($plan->created_by);
                    
                    \App\Models\PesertaSmi::create([
                        'sales_plan_id' => $plan->id,
                        'nama' => $nama,
                        'status' => 'Aktif',
                        'biaya_pendaftaran' => $plan->nominal,
                        'closing_cs_id' => $plan->created_by,
                        'cs_name' => $cs ? $cs->name : null,
                        'tanggal_masuk' => $plan->created_at ? $plan->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                        'tanggal_selesai' => $plan->created_at ? $plan->created_at->addMonths(6)->format('Y-m-d') : now()->addMonths(6)->format('Y-m-d'),
                    ]);
                }
            }
        }

        $query = \App\Models\PesertaSmi::query();

        // Filter Status
        if ($request->has('filter_status') && $request->filter_status) {
            $query->where('status', $request->filter_status);
        }

        // Filter SPP
        if ($request->has('filter_spp_month') && $request->has('filter_spp_status') && $request->filter_spp_month && $request->filter_spp_status !== null) {
            $month = $request->filter_spp_month;
            $status = $request->filter_spp_status; // 1 = lunas, 0 = belum
            
            // Validate month 1-12
            if (in_array($month, range(1, 12))) {
                if ($status == '1') {
                    $query->where('spp_' . $month, '>', 0);
                } else {
                    $query->where('spp_' . $month, 0);
                }

                // Ensure the month is within the user's active period
                // Logic: 
                // IF filtering for Month M. 
                // We want users whose ACTIVE PERIOD covers Month M.
                // Since this system uses 12 specific columns (Jan-Dec) without specific year links in the columns themselves,
                // we generally check if the month exists in the range. 
                // However, doing this strictly in SQL across years is complex without a year filter.
                // BUT, if the user explicitly also filters by YEAR, we can be precise.
                
                // If filtering by YEAR + MONTH:
                if($request->has('filter_year') && $request->filter_year) {
                    // Start <= SelectedYear-Month-End AND End >= SelectedYear-Month-Start
                    // Essentially: was the user active in that specific Month of that Year?
                     $dateStart = \Carbon\Carbon::createFromDate($request->filter_year, $month, 1)->startOfMonth()->format('Y-m-d');
                     $dateEnd = \Carbon\Carbon::createFromDate($request->filter_year, $month, 1)->endOfMonth()->format('Y-m-d');
                     
                     $query->whereDate('tanggal_masuk', '<=', $dateEnd)
                           ->whereDate('tanggal_selesai', '>=', $dateStart);

                } else {
                    // General Logic (Any Year) checks: Is Month M inside the range [Start, End]?
                    $query->where(function($q) use ($month) {
                        // Pass if dates are null (fallback to showing)
                        $q->whereNull('tanggal_masuk')
                          ->orWhereNull('tanggal_selesai')
                          // Case 1: Same Year (Start <= M <= End)
                          ->orWhere(function($sub) use ($month) {
                              $sub->whereRaw('YEAR(tanggal_masuk) = YEAR(tanggal_selesai)')
                                  ->whereRaw('MONTH(tanggal_masuk) <= ?', [$month])
                                  ->whereRaw('MONTH(tanggal_selesai) >= ?', [$month]);
                          })
                          // Case 2: Different Years
                          ->orWhere(function($sub) use ($month) {
                              $sub->whereRaw('YEAR(tanggal_masuk) < YEAR(tanggal_selesai)')
                                  ->where(function($nested) use ($month) {
                                      // If gap > 1 year, user is active for ALL months in the middle years
                                      $nested->whereRaw('YEAR(tanggal_selesai) - YEAR(tanggal_masuk) > 1')
                                             // If gap is 1 year (adjacent), M must be >= StartMonth (End of first year)
                                             // OR M <= EndMonth (Start of second year)
                                             ->orWhere(function($check) use ($month) {
                                                 $check->whereRaw('MONTH(tanggal_masuk) <= ?', [$month])
                                                       ->orWhereRaw('MONTH(tanggal_selesai) >= ?', [$month]);
                                             });
                                  });
                          });
                    });
                }
            }
        }

        // Filter Tahun (based on tanggal_masuk) - Separated from SPP filter for general use
        if ($request->has('filter_entry_year') && $request->filter_entry_year) {
            $query->whereYear('tanggal_masuk', $request->filter_entry_year);
        }

        // Filter Bulan (based on tanggal_masuk)
        if ($request->has('filter_entry_month') && $request->filter_entry_month) {
            $query->whereMonth('tanggal_masuk', $request->filter_entry_month);
        }

        // Keep existing filter_year for backward compatibility or SPP specific if needed
        if ($request->has('filter_year') && $request->filter_year && !$request->has('filter_entry_year')) {
            $query->whereYear('tanggal_masuk', $request->filter_year);
        }
        // sort pembayaran
        if ($request->has('sort_spp')) {
            $column = 'spp_' . $request->sort_spp; // e.g., spp_1
            $direction = $request->get('sort_dir', 'desc'); // desc = lunas (1) first
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $data = $query->get()->unique('nama')->values();
        
        // Get list of CS for dropdown
        $listCs = \App\Models\User::whereIn('role', ['cs-mbc', 'cs-smi', 'administrator'])->orderBy('name')->get();

        // Calculate Totals and Breakdown for Summary (Like Laba Rugi)
        $bulan = $request->get('filter_spp_month', date('m'));
        $tahun = $request->get('filter_year', date('Y'));

        // 1. SMI
        $smiQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%Muslim Indonesia%')
                  ->orWhere('nama_kelas', 'like', 'SMI - %');
            });
            
        $smiBreakdown = $smiQuery->with('kelas:id,nama_kelas')
            ->get()
            ->groupBy('kelas.nama_kelas')
            ->map(fn($group) => $group->sum('nominal'));

        $totalSmi = (clone $smiQuery)->sum('nominal');
        
        // ADD SPP income in total calculation
        if ($bulan !== 'all' && $tahun !== 'all' && $bulan > 0 && $bulan <= 12) {
            $colSpp = 'spp_' . (int)$bulan;
            $dateStart = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $dateEnd = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            
            $sppQueryTotal = \App\Models\PesertaSmi::where($colSpp, '>', 0)
                ->whereDate('tanggal_masuk', '<=', $dateEnd)
                ->whereDate('tanggal_selesai', '>=', $dateStart)
                ->whereNotIn('sales_plan_id', (clone $smiQuery)->pluck('id'));
                
            $totalSppVal = 0; // REVISION: Decouple from Laba Rugi
            $totalSmi += $totalSppVal;
            
            $smiKelasName = 'Start-Up Muslim Indonesia';
            $smiBreakdown = $smiBreakdown ?? collect();
            $smiBreakdown[$smiKelasName] = ($smiBreakdown[$smiKelasName] ?? 0) + $totalSppVal;
        }

        // Statistics for Badges (SPP specific for the selected month/year)
        $badgeStats = [
            'count_lunas' => 0,
            'count_belum' => 0,
            'total_sudah' => 0,
            'total_belum' => 0,
            'target' => 0,
        ];

        if ($bulan !== 'all' && $tahun !== 'all' && $bulan > 0 && $bulan <= 12) {
            $colSpp = 'spp_' . (int)$bulan;
            $dateStart = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $dateEnd = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            
            // Base query for active students in this month
            // EXCLUDE those already marked as LUNAS (manual or auto-processed)
            $activeInMonthQuery = \App\Models\PesertaSmi::where(function($q) use ($dateStart, $dateEnd) {
                $q->whereDate('tanggal_masuk', '<=', $dateEnd)
                  ->whereDate('tanggal_selesai', '>=', $dateStart);
            })
            ->where('status', '!=', 'Lulus')
            ->where('status', '!=', 'Cuti')
            ->where('is_lunas', '!=', 1); // Exclude fully paid participants
            
            $allActiveRaw = $activeInMonthQuery->orderBy('id', 'desc')->get()->unique('nama');
            
            // Further filter by "auto lunas" (6 months paid)
            $allActive = $allActiveRaw->filter(function($item) {
                $paidCount = 0;
                for($m=1; $m<=12; $m++) {
                    if(($item->{"spp_$m"} ?? 0) >= 1000000) {
                        $paidCount++;
                    }
                }
                return $paidCount < 6; // Only keep if not lunas (less than 6 months paid)
            });
            
            $badgeStats['count_lunas'] = $allActive->where($colSpp, '>', 0)->count();
            $badgeStats['count_belum'] = $allActive->where($colSpp, 0)->count();
            $badgeStats['total_sudah'] = $allActive->sum($colSpp);
            $badgeStats['total_belum'] = $allActive->where($colSpp, 0)->count() * 1000000;
            $badgeStats['target'] = $allActive->count();
        }

        // 2. MBC
        $mbcQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'not like', '%Muslim Indonesia%')
                  ->where('nama_kelas', 'not like', 'SMI - %')
                  ->where('nama_kelas', 'not like', '%Privat%');
            });
        
        $totalMbc = (clone $mbcQuery)->sum('nominal');
        $mbcBreakdown = $mbcQuery->with('kelas:id,nama_kelas')
            ->get()
            ->groupBy('kelas.nama_kelas')
            ->map(fn($group) => $group->sum('nominal'));

        // 3. Private Coaching
        $totalPrivate = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%Privat%');
            })
            ->sum('nominal');

        // 4. Approved Budget Proposals (Pengajuan Anggaran)
        $approvedAnggaran = \App\Models\PengajuanAnggaran::where('status', 'approved')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('tanggal_pengajuan', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('tanggal_pengajuan', $tahun); })
            ->get();

        $coachItems = [
            'Cicilan mobil Coach', 'Cicilan mobil teh Lia', 'Uang bulanan Fathin',
            'Gaji ART', 'Uang bulanan teh Lia', 'Cicilan 2 kartu kredit',
            'Paket paket ustad', 'Hutang Tajirw', 'Hutang pak Yusron', 'Biaya program Dela',
            'Biaya Pengeluaran Coach'
        ];

        $anggaranMapped = $approvedAnggaran->map(function($item) use ($coachItems) {
            $category = 'Biaya Lain-lain';
            $name = strtolower($item->nama_pengajuan);
            
            if (in_array($item->nama_pengajuan, $coachItems)) {
                $category = 'Pengeluaran Coach';
            } elseif (strpos($name, 'kuota') !== false || strpos($name, 'pulsa') !== false) {
                $category = 'Biaya Kuota';
            } elseif (strpos($name, 'listrik') !== false || strpos($name, 'token') !== false) {
                $category = 'Biaya Listrik';
            } elseif (strpos($name, 'air') !== false) {
                $category = 'Biaya Air';
            } elseif (strpos($name, 'bpjs') !== false) {
                $category = 'Biaya BPJS';
            } elseif (strpos($name, 'wifi') !== false || strpos($name, 'internet') !== false || strpos($name, 'indihome') !== false) {
                $category = 'Biaya Internet & Wifi';
            } elseif (strpos($name, 'maintenance') !== false || strpos($name, 'website') !== false) {
                $category = 'Biaya Maintenance Web';
            } elseif (strpos($name, 'gaji') !== false || strpos($name, 'upah') !== false) {
                $category = 'Biaya Gaji Karyawan';
            } elseif (strpos($name, 'iklan') !== false || strpos($name, 'ads') !== false || strpos($name, 'facebook') !== false || strpos($name, 'instagram') !== false) {
                $category = 'Biaya Iklan';
            } elseif (strpos($name, 'kebersihan') !== false || strpos($name, 'sampah') !== false || strpos($name, 'keamanan') !== false) {
                $category = 'Biaya Kebersihan & Keamanan';
            }

            return (object)[
                'id' => 'anggaran-' . $item->id,
                'tanggal' => $item->tanggal_pengajuan ? $item->tanggal_pengajuan->format('Y-m-d') : null,
                'type' => 'biaya',
                'parent_keterangan' => $category,
                'keterangan' => $item->nama_pengajuan,
                'jumlah' => $item->biaya_disetujui ?? $item->jumlah_biaya,
                'is_auto' => true
            ];
        });

        // 5. Manual Entries (LabaRugi Model)
        $manualQuery = \App\Models\LabaRugi::query();
        if ($bulan !== 'all') $manualQuery->where('bulan', str_pad($bulan, 2, '0', STR_PAD_LEFT));
        if ($tahun !== 'all') $manualQuery->where('tahun', $tahun);
        $manualData = $manualQuery->get();

        $pendapatanManual = $manualData->where('type', 'pendapatan');
        $biayaManual = $manualData->where('type', 'biaya');

        $biaya = $biayaManual->concat($anggaranMapped);
        $pendapatan = $pendapatanManual;

        // Fetch Classes for the selected month
        $kelasBulanIni = \App\Models\Kelas::when($bulan !== 'all' || $tahun !== 'all', function($q) use ($bulan, $tahun) {
            $q->where(function($sq) use ($bulan, $tahun) {
                if ($bulan !== 'all' && $tahun !== 'all') {
                    $sq->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                       ->orWhereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
                } elseif ($bulan !== 'all') {
                    $sq->whereMonth('tanggal_mulai', $bulan)->orWhereMonth('tanggal_selesai', $bulan);
                } elseif ($tahun !== 'all') {
                    $sq->whereYear('tanggal_mulai', $tahun)->orWhereYear('tanggal_selesai', $tahun);
                }
            });
        })->get();

        $semuaKelas = \App\Models\Kelas::orderBy('nama_kelas')->get();
        
        return view('admin.peserta-smi.index', compact('data', 'listCs', 'pendapatan', 'totalMbc', 'totalSmi', 'totalPrivate', 'mbcBreakdown', 'smiBreakdown', 'kelasBulanIni', 'biaya', 'semuaKelas', 'bulan', 'tahun', 'badgeStats'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'one_on_one_coaching' => 'nullable|date',
        ]);
        
        // Cek limit 5 orang per hari
        if ($request->one_on_one_coaching) {
             $date = \Carbon\Carbon::parse($request->one_on_one_coaching)->format('Y-m-d');
             $count = \App\Models\PesertaSmi::whereDate('one_on_one_coaching', $date)->count();
             if ($count >= 5) {
                 return redirect()->back()->with('error', 'Kuota One On One Coaching untuk tanggal ' . $date . ' sudah penuh (Maksimal 5 orang).');
             }
        }

        $csName = null;
        if($request->closing_cs_id) {
            $user = \App\Models\User::find($request->closing_cs_id);
            $csName = $user ? $user->name : null;
        }

        $input = $request->all();
        if (isset($input['biaya_pendaftaran'])) {
            $input['biaya_pendaftaran'] = str_replace('.', '', $input['biaya_pendaftaran']);
        }

        \App\Models\PesertaSmi::create($input + [
            'created_by' => auth()->id(),
            'cs_name' => $csName
        ]);

        return redirect()->back()->with('success', 'Data Peserta SMI berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $peserta = \App\Models\PesertaSmi::findOrFail($id);
        
        // Handle AJAX Quick Update (Detection via header or input)
        if ($request->ajax() || $request->is_ajax || $request->header('X-Requested-With') == 'XMLHttpRequest') {
            $field = $request->ajax_field;
            
            // SPECIAL: Lunas All SPP logic
            if ($field === 'spp_bulk') {
                for($i=1; $i<=12; $i++) {
                    $key = "spp_$i";
                    if ($request->has($key)) {
                        $peserta->$key = str_replace('.', '', $request->$key);
                    }
                }
                $peserta->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Lump sum SPP berhasil diperbarui'
                ]);
            }

            if ($field && $request->has($field)) {
                $value = $request->$field;
                
                // Special handling for currency fields
                if (in_array($field, ['biaya_pendaftaran']) || strpos($field, 'spp_') === 0) {
                    $value = str_replace('.', '', $value);
                }
                
                $peserta->$field = $value;
                
                // If CSV CS ID changes, also sync the cs_name
                if ($field == 'closing_cs_id') {
                    $user = \App\Models\User::find($value);
                    $peserta->cs_name = $user ? $user->name : null;
                }
                
                $peserta->save();
                
                return response()->json([
                    'success' => true,
                    'field' => $field,
                    'value' => $peserta->$field,
                    'message' => 'Data berhasil diperbarui'
                ]);
            }
        }

        // Validate Standard Request
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Cuti,Lulus',
            'one_on_one_coaching' => 'nullable|date',
        ]);
        
        // Cek limit 5 orang per hari jika tanggal berubah
        if ($request->one_on_one_coaching && $request->one_on_one_coaching != $peserta->one_on_one_coaching) {
             $date = \Carbon\Carbon::parse($request->one_on_one_coaching)->format('Y-m-d');
             $count = \App\Models\PesertaSmi::whereDate('one_on_one_coaching', $date)
                        ->where('id', '!=', $id)
                        ->count();
             if ($count >= 5) {
                 return redirect()->back()->with('error', 'Kuota One On One Coaching untuk tanggal ' . $date . ' sudah penuh (Maksimal 5 orang).');
             }
        }

        // Explicitly set data to ensure all fields are captured
        if ($request->has('nama')) $peserta->nama = $request->nama;
        if ($request->has('nama_asli')) $peserta->nama_asli = $request->nama_asli;
        if ($request->has('nama_2')) $peserta->nama_2 = $request->nama_2;
        if ($request->has('nama_asli_2')) $peserta->nama_asli_2 = $request->nama_asli_2;
        if ($request->has('status')) $peserta->status = $request->status;
        if ($request->has('one_on_one_coaching')) $peserta->one_on_one_coaching = $request->one_on_one_coaching;
        if ($request->has('tanggal_masuk')) $peserta->tanggal_masuk = $request->tanggal_masuk;
        if ($request->has('tanggal_selesai')) $peserta->tanggal_selesai = $request->tanggal_selesai;
        if ($request->has('biaya_pendaftaran')) {
            $cleaned = str_replace('.', '', $request->biaya_pendaftaran);
            $peserta->biaya_pendaftaran = $cleaned;
        }
        if ($request->has('closing_cs_id')) $peserta->closing_cs_id = $request->closing_cs_id;
        
        if($request->has('closing_cs_id') && $request->closing_cs_id) {
            $user = \App\Models\User::find($request->closing_cs_id);
            $peserta->cs_name = $user ? $user->name : null;
        }

        // Handle SPP nominal inputs
        for($i=1; $i<=12; $i++) {
            $field = 'spp_' . $i;
            if ($request->has($field)) {
                $cleanedSpp = str_replace('.', '', $request->$field);
                $peserta->$field = $cleanedSpp;
            }
        }

        $peserta->save();

        // Debug info in session to see what happened
        $received = $request->only(['nama', 'status', 'one_on_one_coaching']);
        return redirect()->back()->with('success', 'Data Peserta ' . $peserta->nama . ' (Status: ' . $peserta->status . ') berhasil diperbarui. DEBUG: Recv=' . json_encode($received));
    }

    public function destroy(Request $request, $id)
    {
        \App\Models\PesertaSmi::findOrFail($id)->delete();
        
        if ($request->ajax() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Data Peserta SMI berhasil dihapus.'
            ]);
        }

        return redirect()->back()->with('success', 'Data Peserta SMI berhasil dihapus.');
    }
}
