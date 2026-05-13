<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PesertaSmiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Auto-sync from SalesPlan for M1T related classes
        $m1tClasses = \App\Models\Kelas::whereIn('nama_kelas', [
            'Start-Up Muslim Indonesia',
            'Grow Up',
            'Mentoring 1 Tahun',
            'Mentoring 1 Tahun (M1T)',
            'M1T - Grow Up',
            'M1T - Start-Up'
        ])->pluck('id')->toArray();

        if (!empty($m1tClasses)) {
            $salesPlansSmi = \App\Models\SalesPlan::where('status', 'sudah_transfer')
                ->whereIn('kelas_id', $m1tClasses)
                ->get();

            foreach ($salesPlansSmi as $plan) {
                // Check if already in PesertaSmi by sales_plan_id (including trashed to prevent auto-recreate)
                $exists = \App\Models\PesertaSmi::withTrashed()->where('sales_plan_id', $plan->id)->exists();

                if (!$exists) {
                    $dataRel = $plan->data;
                    $nama = $dataRel ? $dataRel->nama : $plan->nama;
                    $cs = \App\Models\User::find($plan->created_by);

                    \App\Models\PesertaSmi::create([
                        'sales_plan_id' => $plan->id,
                        'level' => $plan->level,
                        'nama' => $nama,
                        'status' => 'Aktif',
                        'biaya_pendaftaran' => $plan->nominal,
                        'closing_cs_id' => $plan->created_by,
                        'created_by' => $plan->created_by,
                        'cs_name' => $cs ? $cs->name : null,
                        'tanggal_masuk' => $plan->tanggal_closing ?? ($plan->created_at ? $plan->created_at->format('Y-m-d') : now()->format('Y-m-d')),
                        'tanggal_selesai' => $plan->tanggal_closing ? \Carbon\Carbon::parse($plan->tanggal_closing)->addMonths(6)->format('Y-m-d') : ($plan->created_at ? $plan->created_at->addMonths(6)->format('Y-m-d') : now()->addMonths(6)->format('Y-m-d')),
                        'approval_status' => in_array(strtolower($cs->role ?? ''), ['reseller', 'chapter', 'agen', 'chapter ']) ? 'Pending' : 'Approved',
                    ]);
                } else {
                    // Update level if missing
                    $pesertaSmi = \App\Models\PesertaSmi::where('sales_plan_id', $plan->id)->first();
                    if ($pesertaSmi && !$pesertaSmi->level) {
                        $pesertaSmi->level = $plan->level;
                        $pesertaSmi->save();
                    }
                }
            }
        }

        $monthsRaw = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // --- CLEANUP & BASE QUERY ---
        // 1. Cleanup: Trash PesertaSmi records that are linked to non-M1T classes
        if (!empty($m1tClasses)) {
            \App\Models\PesertaSmi::whereHas('salesPlan', function($q) use ($m1tClasses) {
                $q->whereNotIn('kelas_id', $m1tClasses);
            })->delete();
        }

        // 2. Base Query: Only show M1T related participants
        $query = \App\Models\PesertaSmi::whereHas('salesPlan', function($q) use ($m1tClasses) {
            $q->whereIn('kelas_id', $m1tClasses);
        });

        // 0. ROLE BASED FILTER (Chapter sees everything in region, Reseller only sees own team)
        $user = auth()->user();
        $role = strtolower($user->role);
        
        if ($role === 'chapter' || in_array($role, ['reseller', 'agen'])) {
            $chapterName = $user->chapter;
            $userId = $user->id;
            
            // Identify Direct Team Members (only those personally recruited)
            $resellerMembersIds = \App\Models\User::whereIn('role', ['reseller', 'agen'])
                ->where('created_by', $userId)
                ->pluck('id');
            $allTeamIds = $resellerMembersIds->merge([$userId])->unique();
            
            if (in_array($role, ['reseller', 'agen'])) {
                // RESELLER/AGEN: Strict Personal + Direct Downline ownership only
                $query->where(function($q) use ($allTeamIds) {
                    $q->whereIn('closing_cs_id', $allTeamIds)
                      ->orWhereIn('created_by', $allTeamIds)
                      ->orWhereHas('salesPlan', function($sq) use ($allTeamIds) {
                          $sq->whereIn('created_by', $allTeamIds);
                      });
                });
            } else {
                // CHAPTER: Maintain Chapter-wide Regional Visibility
                $cleanChapter = str_replace('CHAPTER ', '', strtoupper($chapterName ?: ''));

                $query->where(function ($q) use ($chapterName, $userId, $cleanChapter) {
                    // Daftar CS-MBC yang harus disembunyikan dari Chapter
                    $excludeNames = ['Yasmin', 'Linda', 'Puput', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];

                    if ($chapterName) {
                        $q->where(function ($sq) use ($chapterName, $cleanChapter, $excludeNames) {
                            // 1. Match by Participant's City (MUST NOT be from CS-MBC)
                            $sq->whereHas('salesPlan.data', function ($tsq) use ($chapterName, $cleanChapter, $excludeNames) {
                                $tsq->where(function ($ssq) use ($chapterName, $cleanChapter) {
                                    $ssq->where('kota_nama', 'LIKE', '%' . $chapterName . '%')
                                        ->orWhere('kota_nama', 'LIKE', '%' . $cleanChapter . '%');
                                })
                                ->whereNotIn('created_by', $excludeNames)
                                ->where('created_by_role', '!=', 'cs-mbc');
                            });

                            // 2. Match by Closer's/Creator's Chapter (Regional visibility)
                            $sq->orWhereHas('closingCs', function ($tsq) use ($chapterName, $cleanChapter, $excludeNames) {
                                $tsq->where(function ($ssq) use ($chapterName, $cleanChapter) {
                                    $ssq->where('chapter', 'LIKE', '%' . $chapterName . '%')
                                        ->orWhere('chapter', 'LIKE', '%' . $cleanChapter . '%');
                                })
                                ->whereNotIn('name', $excludeNames)
                                ->where('role', '!=', 'cs-mbc');
                            });
                        });
                    }

                    // 3. Always show direct ownership (Show self-input data even if name is in exclude list, though unlikely)
                    $q->orWhere('closing_cs_id', $userId)
                        ->orWhere('created_by', $userId)
                        ->orWhereHas('salesPlan', function ($sq) use ($userId) {
                            $sq->where('created_by', $userId);
                        });
                });
            }
        }

        // 1. SCOPE FILTERS (Global Dashboard Scope: Month, Year, Entry Period)
        $sppMonth = $request->get('filter_spp_month', date('n'));
        $yearFilter = $request->get('filter_year', date('Y'));
        
        // Ensure month is numeric for internal logic
        $mNum = ($sppMonth !== 'all') ? (int)$sppMonth : null;
        $yNum = ($yearFilter !== 'all') ? (int)$yearFilter : (int)date('Y');
        
        if ($sppMonth !== 'all') {
            $month = (int) $sppMonth;
            if ($month >= 1 && $month <= 12) {
                // Filter by active period (In that month and year)
                if ($yearFilter !== 'all') {
                    $dateStart = \Carbon\Carbon::createFromDate($yearFilter, $month, 1)->startOfMonth()->format('Y-m-d');
                    $dateEnd = \Carbon\Carbon::createFromDate($yearFilter, $month, 1)->endOfMonth()->format('Y-m-d');

                    $query->where(function ($q) use ($dateStart, $dateEnd) {
                        $q->whereDate('tanggal_masuk', '<=', $dateEnd)
                            ->where(function ($sq) use ($dateStart) {
                                $sq->whereDate('tanggal_selesai', '>=', $dateStart)
                                    ->orWhereNull('tanggal_selesai');
                            });
                    });
                }
            }
        } else {
            // Still apply Year filter if active (show people active in this year)
            if ($yearFilter !== 'all') {
                $dateStart = \Carbon\Carbon::createFromDate($yearFilter, 1, 1)->startOfYear()->format('Y-m-d');
                $dateEnd = \Carbon\Carbon::createFromDate($yearFilter, 12, 31)->endOfYear()->format('Y-m-d');

                $query->where(function ($q) use ($dateStart, $dateEnd) {
                    $q->whereDate('tanggal_masuk', '<=', $dateEnd)
                        ->where(function ($sq) use ($dateStart) {
                            $sq->whereDate('tanggal_selesai', '>=', $dateStart)
                                ->orWhereNull('tanggal_selesai');
                        });
                });
            }
        }

        // Filter Bulan & Tahun Masuk (Strict start date filtering)
        if ($request->has('filter_entry_year') && $request->filter_entry_year && $request->filter_entry_year !== 'all') {
            $query->whereYear('tanggal_masuk', $request->filter_entry_year);
        }
        if ($request->has('filter_entry_month') && $request->filter_entry_month && $request->filter_entry_month !== 'all') {
            $query->whereMonth('tanggal_masuk', $request->filter_entry_month);
        }

        // Filter Status (Aktif, Cuti, Lulus)
        if ($request->has('filter_status') && $request->filter_status && $request->filter_status !== 'all') {
            $query->where('status', $request->filter_status);
        }

        // Filter Approval Status
        if ($request->has('filter_approval') && $request->filter_approval && $request->filter_approval !== 'all') {
            $fApp = $request->filter_approval;
            
            if ($fApp === 'Pending') {
                $query->where(function($q) {
                    $q->where('approval_status', 'Pending')
                      ->orWhereNull('approval_status');
                });
            } else {
                $query->where('approval_status', $fApp);
            }
            
            // [USER_REQUEST] If filtering for 'Pending', only show those that actually need approval (Reseller/Chapter/Agen)
            // This prevents CS-MBC/Admin records that might be stuck in 'Pending' state from cluttering the verification list.
            if ($fApp === 'Pending') {
                $query->where(function ($q) {
                    $roles = ['reseller', 'chapter', 'agen', 'chapter '];
                    $q->whereHas('closingCs', function ($sq) use ($roles) {
                        $sq->whereIn('role', $roles);
                    })->orWhereHas('createdBy', function ($sq) use ($roles) {
                        $sq->whereIn('role', $roles);
                    })->orWhereHas('salesPlan.createdBy', function ($sq) use ($roles) {
                        $sq->whereIn('role', $roles);
                    });
                });
            }
        }

        // Filter Chapter (For Admin and Linda)
        if ($request->has('filter_chapter') && $request->filter_chapter && $request->filter_chapter !== 'all') {
            $chapter = $request->filter_chapter;
            $cleanChapter = str_replace('CHAPTER ', '', strtoupper($chapter));

            // Identify all users in this chapter's tree (recursive)
            // Enhanced to also check user name as fallback for chapter managers named after their region
            $directChapterUsers = \App\Models\User::where(function($q) use ($chapter, $cleanChapter) {
                    $q->where('chapter', 'LIKE', '%' . $chapter . '%')
                      ->orWhere('chapter', 'LIKE', '%' . $cleanChapter . '%')
                      ->orWhere('name', 'LIKE', '%' . $chapter . '%')
                      ->orWhere('name', 'LIKE', '%' . $cleanChapter . '%');
                })
                ->pluck('id')->toArray();
            
            $allChapterTeamIds = $directChapterUsers;
            
            // Recursive team identification (up to 3 levels)
            $level1 = \App\Models\User::whereIn('created_by', $allChapterTeamIds)->pluck('id')->toArray();
            if (!empty($level1)) {
                $allChapterTeamIds = array_unique(array_merge($allChapterTeamIds, $level1));
                $level2 = \App\Models\User::whereIn('created_by', $level1)->pluck('id')->toArray();
                if (!empty($level2)) {
                    $allChapterTeamIds = array_unique(array_merge($allChapterTeamIds, $level2));
                    $level3 = \App\Models\User::whereIn('created_by', $level2)->pluck('id')->toArray();
                    if (!empty($level3)) {
                        $allChapterTeamIds = array_unique(array_merge($allChapterTeamIds, $level3));
                    }
                }
            }

            $query->where(function($q) use ($chapter, $cleanChapter, $allChapterTeamIds) {
                // 1. Match by Participant's City
                $q->whereHas('salesPlan.data', function($sq) use ($chapter, $cleanChapter) {
                    $sq->where('kota_nama', 'LIKE', '%' . $chapter . '%')
                       ->orWhere('kota_nama', 'LIKE', '%' . $cleanChapter . '%');
                });
                
                // 2. Match by Participant's Name (Direct fallback for "Chapter Kaltim" etc.)
                $q->orWhere('nama', 'LIKE', '%' . $chapter . '%')
                  ->orWhere('nama', 'LIKE', '%' . $cleanChapter . '%');

                // 3. Match by Team Membership (Hierarchical)
                $q->orWhereIn('closing_cs_id', $allChapterTeamIds)
                  ->orWhereIn('created_by', $allChapterTeamIds)
                  ->orWhereHas('salesPlan', function($sq) use ($allChapterTeamIds) {
                      $sq->whereIn('created_by', $allChapterTeamIds);
                  });

                // 4. Fallback: Match by cs_name string (for cases where user association is missing but name contains region)
                $q->orWhere('cs_name', 'LIKE', '%' . $chapter . '%')
                  ->orWhere('cs_name', 'LIKE', '%' . $cleanChapter . '%');
            });
        }

        // --- CUMULATIVE STATS (Based on Global Scope only) ---
        $totalStats = (clone $query)->with(['salesPlan.createdBy', 'closingCs', 'createdBy'])->get();

        // [USER_REQUEST] Dashboard stats should only include Approved participants if they need approval
        // Only apply this restriction for the general view (when not filtering for a specific approval status)
        if (!$request->has('filter_approval') || $request->filter_approval === 'all') {
            $totalStats = $totalStats->filter(function($item) {
                $creatorRole = strtolower($item->closingCs->role ?? $item->createdBy->role ?? $item->salesPlan->createdBy->role ?? '');
                $needsApproval = in_array($creatorRole, ['reseller', 'chapter', 'agen']);
                
                if ($needsApproval) {
                    return $item->approval_status === 'Approved';
                }
                return true; // Auto-include others (CS-MBC etc)
            });
        }

        $stats = [
            'total' => $totalStats->count(),
            'aktif' => $totalStats->where('status', 'Aktif')->count(),
            'cuti' => $totalStats->where('status', 'Cuti')->count(),
            'count_closing' => 0,
            'nominal_closing' => 0,
            'count_spp' => 0,
            'nominal_spp' => 0,
            'count_belum' => 0,
            'nominal_belum' => 0,
            'count_total_spp' => 0,
            'nominal_total_spp' => 0,
            'count_total_income' => 0,
            'nominal_total_income' => 0,
            'is_month_filter' => ($sppMonth !== 'all'),
            'filter_month_name' => ($sppMonth !== 'all' ? ($monthsRaw[(int) $sppMonth] ?? '') : '')
        ];

        // 2. DISPLAY FILTERS (Only affects the Table below, not the Cards)
        $sppStatus = $request->get('filter_spp_status', 'all'); // 1 = lunas, 0 = belum
        if ($sppMonth !== 'all') {
            // NOTE: Do NOT filter spp_N here — Blue Checklist is computed in-memory below.
            // Filtering by spp_N = 0 would incorrectly exclude Blue Checklist participants.
            $month = (int)$sppMonth;
        } else {
             // Handle Lunas Badge filtering when Month is ALL
             if ($sppStatus === '1') {
                $query->where(function ($q) {
                    $q->where('is_lunas', 1)
                        ->orWhereRaw("((CASE WHEN spp_1>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_2>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_3>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_4>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_5>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_6>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_7>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_8>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_9>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_10>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_11>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_12>=1000000 THEN 1 ELSE 0 END)) >= 6");
                });
            } elseif ($sppStatus === '0') {
                $query->where('is_lunas', 0)
                    ->whereRaw("((CASE WHEN spp_1>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_2>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_3>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_4>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_5>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_6>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_7>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_8>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_9>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_10>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_11>=1000000 THEN 1 ELSE 0 END)+(CASE WHEN spp_12>=1000000 THEN 1 ELSE 0 END)) < 6");
            }
        }

        // Server-side Search (Display Filter)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nama_2', 'like', "%{$search}%")
                    ->orWhere('cs_name', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%");
            });
        }

        if ($sppMonth !== 'all') {
            $mNum = (int) $sppMonth;

            foreach ($totalStats as $p) {
                // [USER_REQUEST] Dashboard should show payment status for ALL active participants in the selected month
                $entryMonth = null;
                $entryYear = null;
                try {
                    $entryDate = \Carbon\Carbon::parse($p->tanggal_masuk);
                    $entryMonth = $entryDate->month;
                    $entryYear = $entryDate->year;
                } catch (\Exception $e) {
                }

                // Calculate Lunas Badge status locally for stat
                $paidMonthsCount = 0;
                for ($i = 1; $i <= 12; $i++) {
                    if ((float) ($p->{"spp_$i"} ?? 0) >= 1000000)
                        $paidMonthsCount++;
                }
                $isLunasBadge = ($p->is_lunas == 1 || $paidMonthsCount >= 6);

                // [USER_REQUEST] Exclude Lunas Badge participants from monthly dashboard stats entirely
                if ($isLunasBadge)
                    continue;

                $val = 0;
                $tglSpp = $p->{"tanggal_spp_$mNum"};
                $paymentYear = $tglSpp ? \Carbon\Carbon::parse($tglSpp)->format('Y') : null;
                
                // Only count payment if it belongs to the currently filtered year
                if (($p->{"spp_$mNum"} ?? 0) > 0 && (!$paymentYear || $paymentYear == $yearFilter)) {
                    $val = (float) $p->{"spp_$mNum"};
                }

                // [USER_REQUEST] Closing (Blue) statistics logic must align with SalesPlan dashboard.
                // Priority: tanggal_closing -> tanggal_masuk -> updated_at
                $effectiveDate = null;
                if ($p->salesPlan) {
                    if ($p->salesPlan->tanggal_closing) {
                        $effectiveDate = \Carbon\Carbon::parse($p->salesPlan->tanggal_closing);
                    } else {
                        // Fallback to tanggal_masuk or updated_at
                        $effectiveDate = $p->tanggal_masuk ? \Carbon\Carbon::parse($p->tanggal_masuk) : $p->salesPlan->updated_at;
                    }
                } else {
                    $effectiveDate = $p->tanggal_masuk ? \Carbon\Carbon::parse($p->tanggal_masuk) : $p->created_at;
                }

                $effM = (int)$effectiveDate->month;
                $effY = (int)$effectiveDate->year;

                // Detect if it should be a Blue Checklist (Closing month or Planned in custom schedule)
                $isClosing = ($effM == $mNum && $effY == $yearFilter);
                
                $customSch = $p->spp_custom_schedule ?? [];
                $isPlanned = false;
                foreach ($customSch as $sch) {
                    if ((int)$sch['month'] === $mNum && (int)($sch['year'] ?? $yearFilter) === (int)$yearFilter) {
                        $isPlanned = true;
                        break;
                    }
                }

                // [USER_REQUEST] Also check selected_months in SalesPlan (Planned Installments = Blue)
                if (!$isPlanned && $p->salesPlan) {
                    $selectedMonths = $p->salesPlan->selected_months;
                    if (is_string($selectedMonths)) {
                        $selectedMonths = json_decode($selectedMonths, true) ?? [];
                    }
                    if (isset($selectedMonths[$yearFilter]) && is_array($selectedMonths[$yearFilter])) {
                        if (in_array($mNum, $selectedMonths[$yearFilter])) {
                            $isPlanned = true;
                        }
                    }
                }

                $isBlue = $isClosing || $isPlanned;

                // [USER_REQUEST] Determine nominal based on program level
                // Grow Up: 1.500.000, Start Up/Default: 1.000.000
                $pLevel = strtolower($p->level ?: ($p->salesPlan->level ?? ''));
                $levelNominal = str_contains($pLevel, 'grow') ? 1500000 : 1000000;

                // [USER_REQUEST] Exclude 'Cuti' status from monthly dashboard stats entirely
                if ($p->status === 'Cuti') {
                    continue;
                }

                if ($isBlue) {
                    // [USER_REQUEST] Closing (Blue) card MUST match Laba Rugi 'Pendaftaran M1T'
                    // Laba Rugi only counts actual new closings in that month.
                    if ($isClosing) {
                        $stats['count_closing']++;
                        // Align with DashboardController@labaRugi calculation logic
                        $closingNominal = (float)($p->biaya_pendaftaran ?? $p->salesPlan->nominal ?? 0);
                        if (isset($p->pembayaran_spp) && (float)$p->pembayaran_spp > 0) {
                            $closingNominal += (float)$p->pembayaran_spp;
                        }
                        $stats['nominal_closing'] += $closingNominal;
                    }
                } elseif ($val > 0) {
                    // [USER_REQUEST] "yang dihitung di card hijau itu adalah yang checklist hijau di bulan itu juga"
                    // Match Laba Rugi: use actual payment value ($val)
                    $stats['count_spp']++;
                    $stats['nominal_spp'] += $val;
                } else {
                    // [USER_REQUEST] Belum bayar/potensi logic:
                    // exclude: Cuti, Badge Lunas (already excluded above), Checklist Biru (Closing/Planned)
                    if ($p->status === 'Aktif' && !$isBlue) {
                        $stats['count_belum']++;
                        $stats['nominal_belum'] += $levelNominal;
                    }
                }
            }

            // Totals
            $stats['nominal_total_paid'] = $stats['nominal_closing'] + $stats['nominal_spp'];
            
            $stats['count_total_spp'] = $stats['count_spp'] + $stats['count_belum'];
            $stats['nominal_total_spp'] = $stats['nominal_spp'] + $stats['nominal_belum'];
            $stats['count_total_income'] = $stats['count_closing'] + $stats['count_spp'];
            $stats['nominal_total_income'] = $stats['nominal_total_paid'];

            // For the cards (specifically keeping them separate as requested)
            // 'sudah_bayar' in backward-compat will now mean strictly the Green one if that's what view expects
            $stats['sudah_bayar'] = $stats['count_spp'];
            $stats['belum_bayar'] = $stats['count_belum'];
        }

        $data = $query->with(['salesPlan.createdBy', 'closingCs', 'createdBy'])->get();

        // [USER_REQUEST] In-memory filter for SPP status (month-specific)
        // Must be done in-memory because Blue Checklist (isBlue) is a computed value, not a DB column.
        // "Belum Lunas" = no Green (spp_N = 0) AND no Blue (not closing/planned month)
        // "Sudah Lunas" = has Green (spp_N > 0) OR has Blue
        if ($sppMonth !== 'all' && $sppStatus !== 'all') {
            $mNum = (int)$sppMonth;
            $yNum = (int)$yearFilter;
            $data = $data->filter(function($p) use ($mNum, $yNum, $sppStatus) {
                $val = (float)($p->{"spp_$mNum"} ?? 0);
                // [USER_REQUEST] Level-based threshold: Grow Up = 1.500.000, Start Up = 1.000.000
                $pLevel = strtolower($p->level ?? $p->salesPlan->level ?? '');
                $pLevelNominal = str_contains($pLevel, 'grow') ? 1500000 : 1000000;
                $isGreen = ($val >= $pLevelNominal); // Green Checklist = paid at/above level nominal

                // Blue Checklist: Closing month
                $effDate = $p->tanggal_masuk ? \Carbon\Carbon::parse($p->tanggal_masuk) : $p->created_at;
                $isClosing = ($effDate->month == $mNum && $effDate->year == $yNum);

                // Blue Checklist: Planned schedule (custom or salesPlan selected_months)
                $isPlanned = false;
                $customSch = $p->spp_custom_schedule ?? [];
                foreach ((array)$customSch as $sch) {
                    if ((int)($sch['month'] ?? 0) === $mNum && (int)($sch['year'] ?? $yNum) === $yNum) {
                        $isPlanned = true;
                        break;
                    }
                }
                if (!$isPlanned && $p->salesPlan) {
                    $sel = $p->salesPlan->selected_months;
                    if (is_string($sel)) $sel = json_decode($sel, true) ?? [];
                    if (isset($sel[$yNum]) && in_array($mNum, (array)$sel[$yNum])) {
                        $isPlanned = true;
                    }
                }

                $isBlue = $isClosing || $isPlanned;
                $isSudahBayar = $isGreen || $isBlue;

                if ($sppStatus === '1') return $isSudahBayar;   // Sudah Lunas: hanya yang hijau/biru
                if ($sppStatus === '0') return !$isSudahBayar;  // Belum Lunas: yang tidak ada hijau/biru
                return true;
            });
        }

        // [USER_REQUEST] Sort participants by priority:
        // 1. Aktif (non-lunas) -> Top
        // 2. Lunas -> Middle/Bottom
        // 3. Cuti -> Below Lunas
        // 4. Lulus -> Bottom-most
        $data = $data->map(function ($item) {
            $isManualLunas = ($item->is_lunas == 1);
            $countPaid = 0;
            for ($m = 1; $m <= 12; $m++) {
                if (($item->{"spp_$m"} ?? 0) >= 1000000)
                    $countPaid++;
            }
            $item->is_all_paid = $isManualLunas || ($countPaid >= 6);

            $weight = 0; // Default: Aktif
            if ($item->is_all_paid)
                $weight = 1;
            if ($item->status === 'Cuti')
                $weight = 2;
            if ($item->status === 'Lulus')
                $weight = 3;

            $item->sort_weight = $weight;
            return $item;
        });

        // [USER_REQUEST] Filter out participants with 'LUNAS' badge when set to 'Belum Lunas' (status=0)
        if ($request->get('filter_spp_status') === '0') {
            $data = $data->reject(fn($item) => $item->is_all_paid);
        }

        // [USER_REQUEST] Dynamic Sorting
        $sort = $request->get('filter_sort', 'priority');
        if ($sort === 'name_asc') {
            $data = $data->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE);
        } elseif ($sort === 'name_desc') {
            $data = $data->sortByDesc('nama', SORT_NATURAL | SORT_FLAG_CASE);
        } elseif ($sort === 'newest') {
            $data = $data->sortByDesc('id');
        } elseif ($sort === 'oldest') {
            $data = $data->sortBy('id');
        } else {
            // Default: priority weight (Aktif -> Lunas -> Cuti -> Lulus)
            $data = $data->sort(function ($a, $b) {
                if ($a->sort_weight === $b->sort_weight) {
                    return $b->id <=> $a->id;
                }
                return $a->sort_weight <=> $b->sort_weight;
            });
        }
        $data = $data->values();

        // --- AJAX RESPONSE ---
        if ($request->ajax() || $request->has('ajax')) {
            $html = view('admin.peserta-smi.table-rows', compact('data', 'monthsRaw'))->render();
            $monthSpp = $sppMonth;
            $yearSpp = $yearFilter;

            $sppHeader = "MONITORING SPP ";
            if ($monthSpp && $monthSpp !== 'all') {
                $sppHeader .= "BULAN " . strtoupper($monthsRaw[(int)$monthSpp] ?? '') . " ";
            }
            if ($yearSpp && $yearSpp !== 'all') {
                $sppHeader .= $yearSpp;
            }
            if (($monthSpp === 'all' || !$monthSpp) && ($yearSpp === 'all' || !$yearSpp)) {
                $sppHeader = "MONITORING SPP SELURUH PERIODE";
            }

            return response()->json([
                'success' => true,
                'html' => $html,
                'stats' => $stats,
                'spp_header' => $sppHeader
            ]);
        }


        // Get list of CS for dropdown
        $listCs = \App\Models\User::whereIn('role', ['cs-mbc', 'cs-smi', 'administrator'])->orderBy('name')->get();


        // Calculate Totals and Breakdown for Summary (Like Laba Rugi)
        $bulan = $request->get('filter_spp_month', 'all');
        $tahun = $request->get('filter_year', 'all');
        $kelasFilter = $request->get('kelas');
        $csFilter = $request->get('created_by');
        $isCsMbc = auth()->check() && auth()->user()->role == 'cs-mbc';

        // 1. SMI
        $smiQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function ($q) use ($bulan) {
                $q->whereMonth('updated_at', $bulan);
            })
            ->when($tahun !== 'all', function ($q) use ($tahun) {
                $q->whereYear('updated_at', $tahun);
            })
            ->whereHas('kelas', function ($q) {
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
            $colSpp = 'spp_' . (int) $bulan;
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
            'kekurangan' => 0,
        ];

        if ($bulan !== 'all' && $tahun !== 'all' && $bulan > 0 && $bulan <= 12) {
            $colSpp = 'spp_' . (int) $bulan;
            $dateStart = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $dateEnd = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

            // Base query for active students in this month
            // EXCLUDE those already marked as LUNAS (manual or auto-processed)
            $activeInMonthQuery = \App\Models\PesertaSmi::where(function ($q) use ($dateStart, $dateEnd) {
                $q->whereDate('tanggal_masuk', '<=', $dateEnd)
                    ->whereDate('tanggal_selesai', '>=', $dateStart);
            })
                ->where('status', '!=', 'Lulus')
                ->where('status', '!=', 'Cuti')
                ->where('is_lunas', '!=', 1); // Exclude fully paid participants

            $allActiveRaw = $activeInMonthQuery->orderBy('id', 'desc')->get()->unique('nama');

            // Further filter by "auto lunas" (6 months paid)
            $allActive = $allActiveRaw->filter(function ($item) {
                $paidCount = 0;
                for ($m = 1; $m <= 12; $m++) {
                    if (($item->{"spp_$m"} ?? 0) >= 1000000) {
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
            $badgeStats['kekurangan'] = $badgeStats['total_belum']; // Based on user request "jumlah yang belum bayar" (I'll use the total nominal missed)
        }

        // 2. MBC
        $mbcQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function ($q) use ($bulan) {
                $q->whereMonth('updated_at', $bulan);
            })
            ->when($tahun !== 'all', function ($q) use ($tahun) {
                $q->whereYear('updated_at', $tahun);
            })
            ->whereHas('kelas', function ($q) {
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
            ->when($bulan !== 'all', function ($q) use ($bulan) {
                $q->whereMonth('updated_at', $bulan);
            })
            ->when($tahun !== 'all', function ($q) use ($tahun) {
                $q->whereYear('updated_at', $tahun);
            })
            ->whereHas('kelas', function ($q) {
                $q->where('nama_kelas', 'like', '%Privat%');
            })
            ->sum('nominal');

        // 4. Approved Budget Proposals (Pengajuan Anggaran)
        $approvedAnggaran = \App\Models\PengajuanAnggaran::where('status', 'approved')
            ->when($bulan !== 'all', function ($q) use ($bulan) {
                $q->whereMonth('tanggal_pengajuan', $bulan);
            })
            ->when($tahun !== 'all', function ($q) use ($tahun) {
                $q->whereYear('tanggal_pengajuan', $tahun);
            })
            ->get();

        $coachItems = [
            'Cicilan mobil Coach',
            'Cicilan mobil teh Lia',
            'Uang bulanan Fathin',
            'Gaji ART',
            'Uang bulanan teh Lia',
            'Cicilan 2 kartu kredit',
            'Paket paket ustad',
            'Hutang Tajirw',
            'Hutang pak Yusron',
            'Biaya program Dela',
            'Biaya Pengeluaran Coach'
        ];

        $anggaranMapped = $approvedAnggaran->map(function ($item) use ($coachItems) {
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

            return (object) [
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
        if ($bulan !== 'all')
            $manualQuery->where('bulan', str_pad($bulan, 2, '0', STR_PAD_LEFT));
        if ($tahun !== 'all')
            $manualQuery->where('tahun', $tahun);
        $manualData = $manualQuery->get();

        $pendapatanManual = $manualData->where('type', 'pendapatan');
        $biayaManual = $manualData->where('type', 'biaya');

        $biaya = $biayaManual->concat($anggaranMapped);
        $pendapatan = $pendapatanManual;

        // Fetch Classes for the selected month
        $kelasBulanIni = \App\Models\Kelas::when($bulan !== 'all' || $tahun !== 'all', function ($q) use ($bulan, $tahun) {
            $q->where(function ($sq) use ($bulan, $tahun) {
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
        $kelasList = $semuaKelas; // Alias for compatibility
        $statusFilter = request('filter_status'); // Alias for compatibility

        // ðŸš€ Add $salesplans, $csList, $kelasList, and $statusFilter to avoid undefined variable errors in view
        $csList = \App\Models\User::whereIn('role', ['cs-mbc', 'cs-smi', 'administrator'])->orderBy('name')->get();
        $listCs = $csList; // Alias for compatibility

        $salesplans = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function ($q) use ($bulan) {
                $q->whereMonth('updated_at', $bulan);
            })
            ->when($tahun !== 'all', function ($q) use ($tahun) {
                $q->whereYear('updated_at', $tahun);
            })
            ->with(['kelas', 'data', 'createdBy'])
            ->get();

        return view('admin.peserta-smi.index', compact('data', 'listCs', 'csList', 'kelasList', 'monthsRaw', 'statusFilter', 'pendapatan', 'totalMbc', 'totalSmi', 'totalPrivate', 'mbcBreakdown', 'smiBreakdown', 'kelasBulanIni', 'biaya', 'semuaKelas', 'bulan', 'tahun', 'badgeStats', 'kelasFilter', 'csFilter', 'isCsMbc', 'salesplans', 'stats'));
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
        if ($request->closing_cs_id) {
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

        return redirect()->back()->with('success', 'Peserta M1T berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $peserta = \App\Models\PesertaSmi::findOrFail($id);

        // Handle AJAX Quick Update (Detection via header or input)
        if ($request->ajax() || $request->is_ajax || $request->header('X-Requested-With') == 'XMLHttpRequest') {
            $field = $request->ajax_field;

            // SPECIAL: Lunas All SPP logic
            if ($field === 'spp_bulk') {
                $updatedDates = [];
                for ($i = 1; $i <= 12; $i++) {
                    $key = "spp_$i";
                    if ($request->has($key)) {
                        $val = str_replace('.', '', $request->$key);
                        $peserta->$key = $val;

                        $dateKey = "tanggal_spp_$i";
                        if ($val >= 1000000) {
                            if (!$peserta->$dateKey) {
                                $peserta->$dateKey = now()->format('Y-m-d');
                            }
                        } else {
                            $peserta->$dateKey = null;
                        }
                        $updatedDates[$dateKey] = $peserta->$dateKey ? \Carbon\Carbon::parse($peserta->$dateKey)->format('d/m/y') : null;
                    }
                }
                $peserta->save();
                return response()->json([
                    'success' => true,
                    'updated_dates' => $updatedDates,
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

                // Auto Update Tanggal SPP (Recording when it happens)
                if (strpos($field, 'spp_') === 0 && strpos($field, 'tanggal') === false) {
                    $monthNum = (int) str_replace('spp_', '', $field);
                    $dateField = 'tanggal_spp_' . $monthNum;

                    if ((float) $value > 0) {
                        if (!$peserta->$dateField) {
                            // Default to today
                            $finalDate = now()->format('Y-m-d');

                            // If matched in custom schedule, use that date instead
                            $schedule = $peserta->spp_custom_schedule ?? [];
                            foreach ($schedule as $sch) {
                                if ((int) $sch['month'] === $monthNum) {
                                    $finalDate = $sch['date'];
                                    break;
                                }
                            }
                            $peserta->$dateField = $finalDate;
                        }
                    } else {
                        $peserta->$dateField = null;
                    }
                }

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
                    'date_field' => isset($dateField) ? $dateField : null,
                    'date_value' => isset($dateField) ? ($peserta->$dateField ? \Carbon\Carbon::parse($peserta->$dateField)->format('d/m/y') : null) : null,
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
        if ($request->has('nama'))
            $peserta->nama = $request->nama;
        if ($request->has('nama_asli'))
            $peserta->nama_asli = $request->nama_asli;
        if ($request->has('nama_2'))
            $peserta->nama_2 = $request->nama_2;
        if ($request->has('nama_asli_2'))
            $peserta->nama_asli_2 = $request->nama_asli_2;
        if ($request->has('status'))
            $peserta->status = $request->status;
        if ($request->has('one_on_one_coaching'))
            $peserta->one_on_one_coaching = $request->one_on_one_coaching;
        if ($request->has('tanggal_masuk'))
            $peserta->tanggal_masuk = $request->tanggal_masuk;
        if ($request->has('tanggal_selesai'))
            $peserta->tanggal_selesai = $request->tanggal_selesai;
        if ($request->has('biaya_pendaftaran')) {
            $cleaned = str_replace('.', '', $request->biaya_pendaftaran);
            $peserta->biaya_pendaftaran = $cleaned;
        }
        if ($request->has('closing_cs_id'))
            $peserta->closing_cs_id = $request->closing_cs_id;

        if ($request->has('closing_cs_id') && $request->closing_cs_id) {
            $user = \App\Models\User::find($request->closing_cs_id);
            $peserta->cs_name = $user ? $user->name : null;
        }

        // Handle SPP nominal inputs
        for ($i = 1; $i <= 12; $i++) {
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
                'message' => 'Peserta M1T berhasil dihapus.'
            ]);
        }

        return redirect()->back()->with('success', 'Peserta M1T berhasil dihapus.');
    }

    public function restore($id)
    {
        if (strtolower(auth()->user()->role) === 'administrator') {
            return back()->with('error', 'Akses ditolak.');
        }

        $peserta = \App\Models\PesertaSmi::withTrashed()->findOrFail($id);
        $peserta->restore();

        return back()->with('success', 'Data peserta berhasil dipulihkan.');
    }

    public function uploadBuktiTransfer(Request $request, $id)
    {
        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $peserta = \App\Models\PesertaSmi::findOrFail($id);

        if ($request->hasFile('bukti_transfer')) {
            // Robust path detection for various server environments (Shared Hosting, XAMPP, etc.)
            $basePublic = public_path();
            if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'])) {
                $basePublic = $_SERVER['DOCUMENT_ROOT'];
            } elseif (is_dir(base_path('public_html'))) {
                $basePublic = base_path('public_html');
            }

            $subFolder = 'uploads/bukti_transfer';
            $destinationPath = rtrim($basePublic, '/') . '/' . $subFolder;

            // Delete old file if exists
            if ($peserta->bukti_transfer && file_exists(rtrim($basePublic, '/') . '/' . $peserta->bukti_transfer)) {
                @unlink(rtrim($basePublic, '/') . '/' . $peserta->bukti_transfer);
            }

            $file = $request->file('bukti_transfer');
            $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            
            $peserta->bukti_transfer = $subFolder . '/' . $filename;
            $peserta->approval_status = 'Pending'; 
            $peserta->save();

            return back()->with('success', 'Bukti transfer berhasil diunggah. Menunggu persetujuan admin.');
        }

        return back()->with('error', 'Gagal mengunggah bukti transfer.');
    }

    public function approveBuktiTransfer(Request $request, $id)
    {
        if (strtolower(auth()->user()->role) !== 'administrator') {
            return back()->with('error', 'Akses ditolak. Hanya administrator yang dapat menyetujui.');
        }

        $peserta = \App\Models\PesertaSmi::findOrFail($id);
        $status = $request->status; // 'Approved' or 'Rejected'

        if (!in_array($status, ['Approved', 'Rejected'])) {
            return back()->with('error', 'Status tidak valid.');
        }

        $peserta->approval_status = $status;
        $peserta->save();

        if ($status === 'Approved' && $peserta->closing_cs_id) {
            // Find the payment amount
            // Since approval usually happens after editing a nominal, we'll try to find the relevant amount
            // For now, we'll take the highest SPP value recorded or the pendaftaran fee
            $nominal = 0;
            for ($i = 1; $i <= 12; $i++) {
                $f = 'spp_' . $i;
                if ($peserta->$f > $nominal) $nominal = $peserta->$f;
            }
            if ($peserta->biaya_pendaftaran > $nominal) $nominal = $peserta->biaya_pendaftaran;
            
            if ($nominal > 0) {
                $commission = $nominal * 0.1; // 10% Default Commission
                \App\Services\WalletService::creditCommission(
                    $peserta->closing_cs_id,
                    $commission,
                    'Komisi M1T - ' . $peserta->nama,
                    'Approval Pembayaran SPP/Pendaftaran'
                );
            }
        }

        return back()->with('success', 'Status approval berhasil diperbarui menjadi ' . $status . '.');
    }
}