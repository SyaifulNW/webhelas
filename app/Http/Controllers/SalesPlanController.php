<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Data;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class SalesPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $kelasFilter = $request->input('kelas');
        $csFilter = $request->input('created_by');
        $typeFilter = $request->input('type'); // mbc or smi

        if (auth()->check() && optional(auth()->user())->name == 'Agus Setyo') {
            $kelasFilter = 'Start-Up Muslim Indonesia';
        }

        if ($typeFilter == 'smi') {
            $kelasFilter = 'Start-Up Muslim Indonesia';
        }

        $restrictedView = $request->input('restricted_view', false);

        // ======================================
        // 🔥 AUTO UPDATE & DELETE STATUS
        // 1. Jika status 'tertarik' sudah > 5 hari tidak berubah -> ubah jadi 'cold'
        // 2. Jika status 'no' sudah > 2 hari tidak diubah -> hapus otomatis
        // ======================================

        // 1. Auto Update Tertarik -> Cold (5 Hari)
        $cutoffUpdate = now()->subDays(5);
        $updatedCount = SalesPlan::where('status', 'tertarik')
            ->where('updated_at', '<', $cutoffUpdate)
            ->update(['status' => 'cold']);

        // 2. Auto Delete Status No (1 Hari) & Return to Database
        $cutoffDelete = now()->subDay();
        $recordsToDelete = SalesPlan::where('status', 'no')
            ->where('updated_at', '<', $cutoffDelete)
            ->get();

        $deletedCount = $recordsToDelete->count();
        if ($deletedCount > 0) {
            foreach ($recordsToDelete as $plan) {
                if ($plan->data_id) {
                    // Reset status in main database so it appears as new lead again
                    \App\Models\Data::where('id', $plan->data_id)
                        ->update(['status_peserta' => 'peserta_baru']);
                }
                $plan->delete();
            }
            session()->flash('info', "$deletedCount data dengan status 'No' telah dipindahkan kembali ke Database karena sudah lebih dari 24 jam.");
        }

        $statusFilter = $request->input('status');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun', date('Y'));
        if ($tahunFilter === 'semua' || ($request->has('tahun') && $request->input('tahun') == '')) {
            $tahunFilter = null;
        }

        $userId = auth()->id();
        $perPage = $request->get('per_page', 100);

        // Dropdown data
        $kelasList = Kelas::all();
        $csList = User::orderBy('name', 'asc')->get();

        // Filter CS List for Admin Dropdown (Specific Request)
        if (in_array(auth()->id(), [1, 2])) {
            $csList = User::whereIn('name', ['Yasmin', 'Linda', 'Arifa', 'Diah Putri', 'Shafa Zahra', 'Gunawan', 'Latifah'])
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $csList = User::orderBy('name', 'asc')->get();
        }

        $isRestrictedView = $request->input('restricted_view', false);

        // =====================================================
        // 🔥 JIKA ADMIN BELUM MEMFILTER → JANGAN TAMPILKAN DATA
        // =====================================================
        $isAdmin = in_array($userId, [1]);
        $isCsMbc = auth()->check() && auth()->user()->role == 'cs-mbc';
        $noFilter = empty($kelasFilter) && empty($csFilter) && empty($statusFilter) && empty($bulanFilter) && empty($typeFilter);

        // Initialize all variables with default empty values
        $salesplans = collect();
        $pesertaTransfer = collect();
        $salesplansByCS = collect();
        $salesplanStats = collect();
        $dataMap = collect();
        $csTargets = [];
        $stats = [
            'total' => 0,
            'aktif' => 0,
            'cuti' => 0,
            'lulus' => 0,
            'pending' => 0,
            'tertarik' => 0,
            'mau_transfer' => 0,
            'cold' => 0
        ]; // For the cards in the view

        if (($isAdmin || $isCsMbc) && $noFilter) {
            return view('admin.salesplan.index', [
                'data' => $salesplans,
                'salesplans' => $salesplans,
                'pesertaTransfer' => $pesertaTransfer,
                'kelasList' => $kelasList,
                'csList' => $csList,
                'kelasFilter' => $kelasFilter,
                'csFilter' => $csFilter,
                'statusFilter' => $statusFilter,
                'bulanFilter' => $bulanFilter,
                'tahunFilter' => $tahunFilter,
                'isCsMbc' => $isCsMbc,
                'salesplansByCS' => $salesplansByCS,
                'salesplanStats' => $salesplanStats,
                'dataMap' => $dataMap,
                'csTargets' => $csTargets,
                'stats' => $stats,
                'message' => "Silakan pilih filter untuk menampilkan data.",
                'isRestrictedView' => $isRestrictedView
            ]);
        }


        // ======================================
        // 🔥 QUERY UTAMA SALESPLAN
        // ======================================




        // Determine exempt users (who can see all data)
        $exemptUsers = ['Agus Setyo', 'Fitra Jaya Saleh'];

        // Linda & Shafa Zahra are exempt for MBC (to see DATA PESERTA ALL), but NOT for M1T (type=smi)
        if ($request->input('type') != 'smi' && request('kelas') != 'Start-Up Muslim Indonesia') {
            $exemptUsers[] = 'Linda';
            $exemptUsers[] = 'Shafa Zahra';
        }

        // Clone query logic untuk statistik agar menyertakan semua data (tidak terpotong pagination)
        $salesplanStats = SalesPlan::where('status', 'sudah_transfer')
            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })
            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })
            ->when($isCsMbc, function ($query) {
                $query->whereHas('createdBy', function($sub) {
                    $sub->whereNotIn('role', ['chapter', 'reseller', 'agen']);
                });
            })
            ->when($request->input('type') == 'mbc' || (auth()->user()->role == 'cs-mbc' && $request->input('type') != 'smi'), function ($query) {
                $query->whereHas('kelas', function ($sub) {
                    $sub->where('nama_kelas', 'NOT LIKE', '%Muslim Indonesia%')
                        ->where('nama_kelas', 'NOT LIKE', 'SMI - %');
                });
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('updated_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('updated_at', $tahunFilter);
            })
            ->when(!$isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
                if (auth()->user()->role === 'chapter') {
                    $chapter = auth()->user()->chapter;
                    $excludeNames = ['Yasmin', 'Linda', 'Shafa Zahra', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];
                    $query->where(function($q) use ($userId, $chapter, $excludeNames) {
                        $q->where('created_by', $userId)
                          ->orWhereHas('data', function ($sub) use ($chapter, $excludeNames) {
                              $sub->where('kota_nama', 'like', "%$chapter%")
                                  ->whereNotIn('created_by', $excludeNames)
                                  ->where('created_by_role', '!=', 'cs-mbc');
                          });
                    });
                } else if (auth()->user()->role === 'reseller') {
                    $downlineIds = \App\Models\User::where('created_by', auth()->id())->pluck('id')->toArray();
                    $viewIds = array_merge([auth()->id()], $downlineIds);
                    $query->whereIn('created_by', $viewIds);
                } else {
                    $query->where('created_by', $userId);
                }
            })
            ->selectRaw('created_by, SUM(nominal) as total_nominal')
            ->groupBy('created_by')
            ->pluck('total_nominal', 'created_by');
        $salesplans = SalesPlan::with([
            'kelas',
            'data',
            'pesertaSmi' => function ($q) {
                $q->withTrashed();
            }
        ])

            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })

            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })

            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })

            ->when($request->input('type') == 'mbc' || (auth()->user()->role == 'cs-mbc' && $request->input('type') != 'smi'), function ($query) {
                $query->whereHas('kelas', function ($sub) {
                    $sub->where('nama_kelas', 'NOT LIKE', '%Muslim Indonesia%')
                        ->where('nama_kelas', 'NOT LIKE', 'SMI - %');
                });
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('created_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('created_at', $tahunFilter);
            })


            ->when($isCsMbc, function ($query) {
                $query->whereHas('createdBy', function($sub) {
                    $sub->whereNotIn('role', ['chapter', 'reseller', 'agen']);
                });
            })
            ->when(!$isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
                if (auth()->user()->role === 'chapter') {
                    $chapter = auth()->user()->chapter;
                    $excludeNames = ['Yasmin', 'Linda', 'Shafa Zahra', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];
                    $query->where(function($q) use ($userId, $chapter, $excludeNames) {
                        $q->where('created_by', $userId)
                          ->orWhereHas('data', function ($sub) use ($chapter, $excludeNames) {
                              $sub->where('kota_nama', 'like', "%$chapter%")
                                  ->whereNotIn('created_by', $excludeNames)
                                  ->where('created_by_role', '!=', 'cs-mbc');
                          });
                    });
                } else if (in_array(auth()->user()->role, ['reseller', 'agen'])) {
                    $downlineIds = \App\Models\User::where('created_by', auth()->id())->pluck('id')->toArray();
                    $viewIds = array_merge([auth()->id()], $downlineIds);
                    $query->whereIn('created_by', $viewIds);
                } else {
                    $query->where('created_by', $userId);
                }
            })
            ->when($isAdmin, function ($query) {
                $query->where('status', '!=', 'sudah_transfer');
            })
            ->orderByRaw("FIELD(status, 'cold', 'tertarik', 'mau_transfer', 'sudah_transfer', 'no')")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);


        // ======================================
        // 🔥 PESERTA TRANSFER
        // ======================================
        $pesertaTransfer = SalesPlan::where('status', 'sudah_transfer')
            ->with([
                'kelas',
                'pesertaSmi' => function ($q) {
                    $q->withTrashed();
                }
            ])

            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })

            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })

            ->when($isCsMbc, function ($query) {
                $query->whereHas('createdBy', function($sub) {
                    $sub->whereNotIn('role', ['chapter', 'reseller', 'agen']);
                });
            })
            ->when($request->input('type') == 'mbc' || (auth()->user()->role == 'cs-mbc' && $request->input('type') != 'smi'), function ($query) {
                $query->whereHas('kelas', function ($sub) {
                    $sub->where('nama_kelas', 'NOT LIKE', '%Muslim Indonesia%')
                        ->where('nama_kelas', 'NOT LIKE', 'SMI - %');
                });
            })
            ->when(!$isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
                if (auth()->user()->role === 'chapter') {
                    $chapter = auth()->user()->chapter;
                    $excludeNames = ['Yasmin', 'Linda', 'Shafa Zahra', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];
                    $query->where(function($q) use ($userId, $chapter, $excludeNames) {
                        $q->where('created_by', $userId)
                          ->orWhereHas('data', function ($sub) use ($chapter, $excludeNames) {
                              $sub->where('kota_nama', 'like', "%$chapter%")
                                  ->whereNotIn('created_by', $excludeNames)
                                  ->where('created_by_role', '!=', 'cs-mbc');
                          });
                    });
                } else if (auth()->user()->role === 'reseller') {
                    $downlineIds = \App\Models\User::where('created_by', auth()->id())->pluck('id')->toArray();
                    $viewIds = array_merge([auth()->id()], $downlineIds);
                    $query->whereIn('created_by', $viewIds);
                } else {
                    $query->where('created_by', $userId);
                }
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->where(function ($q) use ($bulanFilter) {
                    $q->whereMonth('tanggal_closing', $bulanFilter)
                        ->orWhere(function ($q2) use ($bulanFilter) {
                            $q2->whereNull('tanggal_closing')
                                ->whereHas('pesertaSmi', function ($sub) use ($bulanFilter) {
                                    $sub->whereNotNull('tanggal_masuk')->whereMonth('tanggal_masuk', $bulanFilter);
                                });
                        })
                        ->orWhere(function ($q3) use ($bulanFilter) {
                            $q3->whereNull('tanggal_closing')
                                ->whereDoesntHave('pesertaSmi', function ($sub) {
                                    $sub->whereNotNull('tanggal_masuk');
                                })
                                ->whereMonth('updated_at', $bulanFilter);
                        });
                });
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->where(function ($q) use ($tahunFilter) {
                    $q->whereYear('tanggal_closing', $tahunFilter)
                        ->orWhere(function ($q2) use ($tahunFilter) {
                            $q2->whereNull('tanggal_closing')
                                ->whereHas('pesertaSmi', function ($sub) use ($tahunFilter) {
                                    $sub->whereNotNull('tanggal_masuk')->whereYear('tanggal_masuk', $tahunFilter);
                                });
                        })
                        ->orWhere(function ($q3) use ($tahunFilter) {
                            $q3->whereNull('tanggal_closing')
                                ->whereDoesntHave('pesertaSmi', function ($sub) {
                                    $sub->whereNotNull('tanggal_masuk');
                                })
                                ->whereYear('updated_at', $tahunFilter);
                        });
                });
            })
            ->get()
            ->sortBy(function ($plan) {
                if ($plan->tanggal_closing)
                    return $plan->tanggal_closing;
                return ($plan->pesertaSmi && $plan->pesertaSmi->tanggal_masuk)
                    ? $plan->pesertaSmi->tanggal_masuk
                    : $plan->updated_at;
            })->values();


        $salesplansByCS = $salesplans->groupBy('created_by');

        // Fallback: Ambil data berdasarkan nama jika data_id null (untuk data lama)
        $names = $salesplans->pluck('nama')->filter()->toArray();
        $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');


        // ======================================
        // 🔥 CALCULATE DYNAMIC TARGET PER CS
        // ======================================
        $csTargets = [];
        if (!empty($bulanFilter)) {
            // Jika filter Bulan aktif -> Target adalah SUM dari target kelas yang diikuti CS
            $distinctClasses = SalesPlan::with('kelas')
                ->select('created_by', 'kelas_id')
                ->when($kelasFilter, function ($query) use ($kelasFilter) {
                    $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                        $sub->where('nama_kelas', $kelasFilter);
                    });
                })
                ->when($csFilter, fn($q) => $q->where('created_by', $csFilter))
                ->when($bulanFilter, fn($q) => $q->whereMonth('updated_at', $bulanFilter))
                ->when($tahunFilter, fn($q) => $q->whereYear('updated_at', $tahunFilter))
                ->distinct()
                ->get()
                ->groupBy('created_by');

            foreach ($distinctClasses as $csId => $items) {
                $totalTargetCS = 0;
                foreach ($items as $item) {
                    if (!$item->kelas)
                        continue;
                    $namaKelas = $item->kelas->nama_kelas;

                    // Logic Target: SMI = 50jt, Lainnya = 25jt
                    if (str_contains($namaKelas, 'Start-Up Muda Indonesia') || str_contains($namaKelas, 'Start-Up Muslim Indonesia')) {
                        $t = 50000000;
                    } else {
                        $t = 50000000;
                    }
                    $totalTargetCS += $t;
                }
                $csTargets[$csId] = $totalTargetCS;
            }
        }



        // Build $stats for cards if needed
        $stats = [
            'total' => $salesplans->count(),
            'aktif' => $pesertaTransfer->count(), // Using pesertaTransfer for active/sudah_transfer
            'cuti' => 0,
            'lulus' => 0,
            'pending' => 0,
            'tertarik' => $salesplans->where('status', 'tertarik')->count(),
            'mau_transfer' => $salesplans->where('status', 'mau_transfer')->count(),
            'cold' => $salesplans->where('status', 'cold')->count(),
        ];

        return view('admin.salesplan.index', [
            'data' => $salesplans,
            'salesplans' => $salesplans,
            'pesertaTransfer' => $pesertaTransfer,
            'kelasList' => $kelasList,
            'csList' => $csList,
            'kelasFilter' => $kelasFilter,
            'csFilter' => $csFilter,
            'statusFilter' => $statusFilter,
            'bulanFilter' => $bulanFilter,
            'tahunFilter' => $tahunFilter,
            'csTargets' => $csTargets,
            'salesplansByCS' => $salesplansByCS,
            'salesplanStats' => $salesplanStats,
            'dataMap' => $dataMap,
            'isCsMbc' => $isCsMbc,
            'stats' => $stats,
            'message' => null,
            'isRestrictedView' => $isRestrictedView
        ]);
    }




    /**
     * FILTER â€” sekarang tetep kirim variabel yang sama seperti index()
     */
    public function filter($kelas)
    {
        $request = new Request(['kelas' => $kelas, 'restricted_view' => true]);
        return $this->index($request);
    }


    /**
     * SEARCH â€” tetep kirim variabel view yang sama
     */
    public function search(Request $request)
    {
        $q = $request->input('q');

        $kelasList = Kelas::all();

        $salesplans = SalesPlan::with(['kelas', 'data'])
            ->where('nama', 'like', "%$q%")
            ->orWhereHas('kelas', fn($q2) => $q2->where('nama_kelas', 'like', "%$q%"))
            ->orderByRaw("FIELD(status, 'cold', 'tertarik', 'mau_transfer', 'sudah_transfer', 'no')")
            ->orderBy('created_at', 'desc')
            ->paginate(100);

        $kelasFilter = null;
        $pesertaTransfer = collect([]);
        $salesplansByCS = $salesplans->groupBy('created_by');

        // Fallback: Ambil data berdasarkan nama
        $names = $salesplans->pluck('nama')->filter()->toArray();
        $dataMap = Data::whereIn('nama', $names)->get()->keyBy('nama');

        $isCsMbc = auth()->check() && auth()->user()->role == 'cs-mbc';

        return view('admin.salesplan.index', [
            'salesplans' => $salesplans,
            'kelasList' => $kelasList,
            'kelasFilter' => $kelasFilter,
            'pesertaTransfer' => $pesertaTransfer,
            'salesplansByCS' => $salesplansByCS,
            'dataMap' => $dataMap,
            'isCsMbc' => $isCsMbc,
            'message' => "Hasil pencarian: $q"
        ]);
    }


    public function inlineUpdate(Request $request)
    {
        try {
            if (auth()->user() && strtolower(auth()->user()->role) === 'administrator' && !in_array($request->field, ['tanggal_closing', 'komentar_atasan', 'nominal', 'keterangan'])) {
                return response()->json(['error' => 'Akses ditolak: Administrator hanya diizinkan melihat data kecuali field tertentu.'], 403);
            }

            $plan = SalesPlan::findOrFail($request->id);

            // Periksa hak akses: CS-MBC hanya bisa edit data miliknya sendiri
            if (auth()->user() && strtolower(auth()->user()->role) === 'cs-mbc') {
                if ($plan->created_by != auth()->id()) {
                    return response()->json(['error' => 'Akses ditolak: Anda hanya diizinkan mengedit data yang Anda input sendiri.'], 403);
                }
            }

            $allowedFields = [
                'fu1_hasil',
                'fu1_tindak_lanjut',
                'fu2_hasil',
                'fu2_tindak_lanjut',
                'fu3_hasil',
                'fu3_tindak_lanjut',
                'fu4_hasil',
                'fu4_tindak_lanjut',
                'fu5_hasil',
                'fu5_tindak_lanjut',
                'fu6_hasil',
                'fu6_tindak_lanjut',
                'fu7_hasil',
                'fu7_tindak_lanjut',
                'fu8_hasil',
                'fu8_tindak_lanjut',
                'fu9_hasil',
                'fu9_tindak_lanjut',
                'fu10_hasil',
                'fu10_tindak_lanjut',
                'fu11_hasil',
                'fu11_tindak_lanjut',
                'fu12_hasil',
                'fu12_tindak_lanjut',
                'fu1_done',
                'fu2_done',
                'fu3_done',
                'fu4_done',
                'fu5_done',
                'fu6_done',
                'fu7_done',
                'fu8_done',
                'fu9_done',
                'fu10_done',
                'fu11_done',
                'fu12_done',
                'nominal',
                'keterangan',
                'komentar_atasan',
                'kebutuhan',
                'closing_paket',
                'level',
                'created_at',
                'fu1_at',
                'fu2_at',
                'fu3_at',
                'fu4_at',
                'fu5_at',
                'fu6_at',
                'fu7_at',
                'fu8_at',
                'fu9_at',
                'fu10_at',
                'fu11_at',
                'fu12_at',
                'fu1_rtl_at',
                'fu2_rtl_at',
                'fu3_rtl_at',
                'fu4_rtl_at',
                'fu5_rtl_at',
                'fu6_rtl_at',
                'fu7_rtl_at',
                'fu8_rtl_at',
                'fu9_rtl_at',
                'fu10_rtl_at',
                'fu11_rtl_at',
                'fu12_rtl_at',
                'tanggal_masuk',
                'tanggal_closing'
            ];

            if (!in_array($request->field, $allowedFields)) {
                return response()->json(['error' => 'Field tidak diizinkan'], 400);
            }

            $value = $request->value;

            if ($request->field === 'tanggal_masuk' || $request->field === 'tanggal_closing') {
                $plan->tanggal_closing = $value;
                $plan->save();

                $peserta = $plan->pesertaSmi;
                if ($peserta) {
                    $peserta->tanggal_masuk = $value;
                    $peserta->save();
                    return response()->json(['success' => true]);
                }

                if ($request->field === 'tanggal_closing') {
                    return response()->json(['success' => true]);
                }

                return response()->json(['error' => 'Data Peserta SMI tidak ditemukan'], 404);
            }

            if ($request->field === 'nominal') {
                $value = str_replace('.', '', $value);
            }

            $plan->{$request->field} = $value;
            
            // Auto-update nominal if level is changed and nominal is empty
            if ($request->field === 'level' && (empty($plan->nominal) || $plan->nominal == 0)) {
                $pLevel = strtolower($value);
                $plan->nominal = str_contains($pLevel, 'grow') ? 1500000 : 1000000;
            }

            $plan->save();

            // Sync nominal ke PesertaSmi jika ada
            if ($request->field === 'nominal' && $plan->pesertaSmi) {
                $plan->pesertaSmi->spp_awal = $value;
                $plan->pesertaSmi->total_pembayaran = (float)$value;
                $plan->pesertaSmi->save();
            }

            $isTimestampField = preg_match('/^fu\d+(_rtl)?_at$/', $request->field);

            if (!$isTimestampField && strpos($request->field, 'fu') === 0) {
                preg_match('/\d+/', $request->field, $matches);
                if (!empty($matches)) {
                    $num = $matches[0];
                    $tsField = (strpos($request->field, 'tindak_lanjut') !== false) ? "fu{$num}_rtl_at" : "fu{$num}_at";
                    if (empty($plan->{$tsField})) {
                        $plan->{$tsField} = now();
                    }
                }
            }

            $plan->save();

            $tsValue = null;
            if ($isTimestampField && !empty($plan->{$request->field})) {
                $tsValue = \Carbon\Carbon::parse($plan->{$request->field})->format('d/m/Y H:i');
            } elseif (isset($tsField) && !empty($plan->{$tsField})) {
                $tsValue = \Carbon\Carbon::parse($plan->{$tsField})->format('d/m/Y H:i');
            }

            return response()->json([
                'success' => true,
                'timestamp' => $tsValue
            ]);
        } catch (\Exception $e) {
            \Log::error('Inline Update Error: ' . $e->getMessage(), [
                'id' => $request->id,
                'field' => $request->field,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }


    public function updateStatus(Request $request, $id)
    {
        if (strtolower(auth()->user()->role) === 'administrator') {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $plan = SalesPlan::findOrFail($id);
        $oldStatus = $plan->status;
        $plan->status = $request->status;
        $plan->save();

        if ($request->status === 'sudah_transfer' && $oldStatus !== 'sudah_transfer') {
            $nominal = $plan->nominal;
            if ($nominal > 0) {
                \App\Services\WalletService::creditCommission(
                    $plan->created_by,
                    $nominal * 0.1, // 10% Commission
                    'Closing MMA - ' . $plan->nama,
                    'Program ' . ($plan->kelas ? $plan->kelas->nama_kelas : 'MMA')
                );
            }
        }

        return response()->json(['success' => true]);
    }


    public function export()
    {
        $sales = SalesPlan::all();
        return (new FastExcel($sales))->download('sales_plan.xlsx');
    }



    public function exportPdf(Request $request)
    {
        $kelasFilter = $request->input('kelas');
        $csFilter = $request->input('created_by');
        $statusFilter = $request->input('status');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun', date('Y'));
        $typeFilter = $request->input('type');

        if ($typeFilter == 'smi') {
            $kelasFilter = 'Start-Up Muslim Indonesia';
        }

        $userId = auth()->id();
        $isAdmin = in_array($userId, [1]);
        $exemptUsers = ['Agus Setyo', 'Fitra Jaya Saleh'];
        if ($request->input('type') != 'smi' && request('kelas') != 'Start-Up Muslim Indonesia') {
            $exemptUsers[] = 'Linda';
        }

        $query = SalesPlan::with(['kelas'])
            ->when($kelasFilter, function ($query) use ($kelasFilter) {
                $query->whereHas('kelas', function ($sub) use ($kelasFilter) {
                    $sub->where('nama_kelas', $kelasFilter);
                });
            })
            ->when($csFilter, function ($query) use ($csFilter) {
                $query->where('created_by', $csFilter);
            })
            ->when($statusFilter, function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->when($bulanFilter, function ($query) use ($bulanFilter) {
                $query->whereMonth('updated_at', $bulanFilter);
            })
            ->when($tahunFilter, function ($query) use ($tahunFilter) {
                $query->whereYear('updated_at', $tahunFilter);
            })
            ->when(!$isAdmin && auth()->check() && !in_array(optional(auth()->user())->name, $exemptUsers), function ($query) use ($userId) {
                if (auth()->user()->role === 'chapter') {
                    $chapter = auth()->user()->chapter;
                    $excludeNames = ['Yasmin', 'Linda', 'Shafa Zahra', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];
                    $query->whereHas('data', function ($sub) use ($chapter, $excludeNames) {
                        $sub->where('kota_nama', 'like', "%$chapter%")
                            ->whereNotIn('created_by', $excludeNames)
                            ->where('created_by_role', '!=', 'cs-mbc');
                    });
                } else {
                    $query->where('created_by', $userId);
                }
            })
            ->orderByRaw("FIELD(status, 'cold', 'tertarik', 'mau_transfer', 'sudah_transfer', 'no')")
            ->orderBy('created_at', 'desc')
            ->get();

        // Determine type label (SMI or MBC)
        $type = 'MBC';
        if ($kelasFilter == 'Start-Up Muslim Indonesia' || $typeFilter == 'smi') {
            $type = 'SMI';
        } elseif ($kelasFilter) {
            $type = $kelasFilter;
        }

        // Determine CS name
        $csName = 'Semua CS';
        if ($csFilter) {
            $csUser = User::find($csFilter);
            $csName = $csUser ? $csUser->name : 'CS #' . $csFilter;
        } elseif (!$isAdmin && !in_array(optional(auth()->user())->name, $exemptUsers)) {
            $csName = auth()->user()->name;
        }

        $pdf = Pdf::loadView('admin.salesplan.pdf', [
            'salesplans' => $query,
            'type' => $type,
            'csName' => $csName,
        ])->setPaper('a4', 'landscape');

        $fileName = 'Salesplan_' . $type . '_' . str_replace(' ', '_', $csName) . '_' . date('Ymd') . '.pdf';
        return $pdf->download($fileName);
    }


    public function destroy($id)
    {
        $plan = SalesPlan::findOrFail($id);
        $plan->delete();

        return back()->with('success', 'Data berhasil dihapus');
    }

    public function resetFu(Request $request, $id)
    {
        try {
            if (empty($id)) {
                return response()->json(['success' => false, 'message' => 'ID tidak valid.'], 400);
            }

            $plan = SalesPlan::findOrFail($id);

            $currentFuData = [];
            $hasData = false;

            for ($i = 1; $i <= 12; $i++) {
                $hasilProp = "fu{$i}_hasil";
                $tlProp = "fu{$i}_tindak_lanjut";
                $rtlProp = "fu{$i}_rtl_at";
                $atProp = "fu{$i}_at";

                if (!empty($plan->$hasilProp) || !empty($plan->$tlProp)) {
                    $hasData = true;
                }

                $currentFuData["FU_" . $i] = [
                    'hasil' => $plan->$hasilProp,
                    'tindak_lanjut' => $plan->$tlProp,
                    'rtl_at' => ($plan->$rtlProp instanceof \Carbon\Carbon) ? $plan->$rtlProp->toDateTimeString() : $plan->$rtlProp,
                    'at' => ($plan->$atProp instanceof \Carbon\Carbon) ? $plan->$atProp->toDateTimeString() : $plan->$atProp,
                ];
            }

            if (!$hasData) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data FU yang bisa direfresh.']);
            }

            $history = is_array($plan->fu_history) ? $plan->fu_history : [];
            $history[] = [
                'reset_at' => now()->toDateTimeString(),
                'reset_by' => auth()->user() ? auth()->user()->name : 'System',
                'data' => $currentFuData
            ];

            $plan->fu_history = $history;

            // Clear existing FU data
            for ($i = 1; $i <= 12; $i++) {
                $plan->{"fu{$i}_hasil"} = null;
                $plan->{"fu{$i}_tindak_lanjut"} = null;
                $plan->{"fu{$i}_rtl_at"} = null;
                $plan->{"fu{$i}_at"} = null;
            }

            $plan->save();

            return response()->json(['success' => true, 'message' => 'FU berhasil direfresh dan disimpan ke riwayat.']);
        } catch (\Exception $e) {
            \Log::error('Reset FU Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal memproses refresh FU: ' . $e->getMessage()], 500);
        }
    }

    public function getTasksToday(Request $request)
    {
        try {
            $date = $request->input('date', now()->format('Y-m-d'));
            $userId = auth()->id();

            $user = auth()->user();
            if (!$user) {
                return response()->json([]);
            }

            $userRole = strtolower($user->role ?? '');
            $isAdmin = in_array($userRole, ['administrator']);
            $exemptUsers = ['Agus Setyo', 'Fitra Jaya Saleh'];
            if ($request->input('type') != 'smi' && request('kelas') != 'Start-Up Muslim Indonesia') {
                $exemptUsers[] = 'Linda';
            }

            $query = SalesPlan::query();

            // Security check
            if (!$isAdmin && !in_array($user->name ?? '', $exemptUsers)) {
                $query->where('created_by', $userId);
            }

            // Check if dynamic columns actually exist on production (in case migration not run)
            try {
                $hasFuDone = \Illuminate\Support\Facades\Schema::hasColumn('salesplans', 'fu1_done');
                $hasFuRtl = \Illuminate\Support\Facades\Schema::hasColumn('salesplans', 'fu1_rtl_at');

                if (!$hasFuRtl) {
                    return response()->json(['error' => 'Kolom database belum di-migrate.'], 200); // Return empty gracefully
                }

                $query->where(function ($q) use ($date, $hasFuDone) {
                    for ($i = 1; $i <= 12; $i++) {
                        $q->orWhere(function ($sub) use ($i, $date, $hasFuDone) {
                            $sub->whereDate("fu{$i}_rtl_at", $date);
                            if ($hasFuDone) {
                                $sub->where("fu{$i}_done", false);
                            }
                        });
                    }
                });
            } catch (\Exception $dbEx) {
                return response()->json(['error' => 'Database belum siap: ' . $dbEx->getMessage()], 200);
            }

            $tasks = $query->with('kelas')->get();

            $formatedTasks = [];
            foreach ($tasks as $task) {
                for ($i = 1; $i <= 12; $i++) {
                    $rtlVal = $task->{"fu{$i}_rtl_at"};
                    if (empty($rtlVal) || $rtlVal === '0000-00-00' || $rtlVal === '0000-00-00 00:00:00')
                        continue;

                    try {
                        $rtlDate = $rtlVal instanceof \Carbon\Carbon ? $rtlVal->format('Y-m-d') : \Carbon\Carbon::parse($rtlVal)->format('Y-m-d');
                        $isDone = $hasFuDone ? $task->{"fu{$i}_done"} : false;

                        if ($rtlDate === $date && !$isDone) {
                            $formatedTasks[] = [
                                'id' => $task->id,
                                'nama' => $task->nama,
                                'kelas' => $task->kelas->nama_kelas ?? '-',
                                'fu_index' => $i,
                                'tindak_lanjut' => $task->{"fu{$i}_tindak_lanjut"} ?? '-',
                                'tanggal_rtl' => ($rtlVal instanceof \Carbon\Carbon) ? $rtlVal->format('d/m/Y H:i') : \Carbon\Carbon::parse($rtlVal)->format('d/m/Y H:i'),
                                'field' => "fu{$i}_done"
                            ];
                        }
                    } catch (\Exception $e) {
                        continue; // Skip individual errored tasks
                    }
                }
            }

            return response()->json($formatedTasks);
        } catch (\Exception $e) {
            \Log::error('getTasksToday Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat: ' . $e->getMessage()], 500);
        }
    }

    public function updateSelectedMonths(Request $request)
    {
        try {
            $plan = SalesPlan::findOrFail($request->id);
            if ($request->has('selected_months')) {
                $plan->selected_months = $request->selected_months;
            }
            if ($request->has('nominal')) {
                $plan->nominal = str_replace(['.', ','], '', $request->nominal);
            } elseif ($request->has('spp_awal')) {
                $plan->nominal = str_replace(['.', ','], '', $request->spp_awal);
            }
            $plan->save();

            // Update PesertaSmi dates if provided - ONLY for M1T classes
            $m1tClasses = [
                'Start-Up Muslim Indonesia',
                'Grow Up',
                'Mentoring 1 Tahun',
                'Mentoring 1 Tahun (M1T)',
                'M1T - Grow Up',
                'M1T - Start-Up'
            ];
            
            $kelasName = $plan->kelas->nama_kelas ?? '';
            $isM1T = false;
            foreach ($m1tClasses as $m1t) {
                if (stripos($kelasName, $m1t) !== false) {
                    $isM1T = true;
                    break;
                }
            }

            if (!$isM1T) {
                return response()->json(['success' => true, 'message' => 'Not an M1T class, skipped PesertaSmi update']);
            }

            $peserta = $plan->pesertaSmi;
            if (!$peserta) {
                $peserta = new \App\Models\PesertaSmi();
                $peserta->sales_plan_id = $plan->id;
                $peserta->nama = $plan->nama;
            }

            if ($request->has('tanggal_masuk'))
                $peserta->tanggal_masuk = $request->tanggal_masuk;
            if ($request->has('tanggal_selesai'))
                $peserta->tanggal_selesai = $request->tanggal_selesai;
            if ($request->has('spp_awal')) {
                $cleanSppAwal = str_replace(['.', ','], '', $request->spp_awal);
                $peserta->spp_awal = $cleanSppAwal;
                $peserta->total_pembayaran = (float) $cleanSppAwal;
            }
            if ($request->has('biaya_pendaftaran'))
                $peserta->biaya_pendaftaran = str_replace(['.', ','], '', $request->biaya_pendaftaran);
            if ($request->has('pembayaran_spp'))
                $peserta->pembayaran_spp = str_replace(['.', ','], '', $request->pembayaran_spp);

            // Handle Custom Payments (Stored as Schedule, not yet paid)
            if ($request->has('custom_payments') && is_array($request->custom_payments)) {
                $schedule = [];
                foreach ($request->custom_payments as $payment) {
                    if (!empty($payment['date']) && !empty($payment['nominal'])) {
                        $dateObj = \Carbon\Carbon::parse($payment['date']);
                        $month = $dateObj->month;
                        $year = $dateObj->year;

                        $schedule[] = [
                            'month' => $month,
                            'year' => $year,
                            'date' => $payment['date'],
                            'nominal' => str_replace('.', '', $payment['nominal']),
                        ];
                    }
                }
                $peserta->spp_custom_schedule = $schedule;
            }

            $peserta->save();

            // Also sync SalesPlan tanggal_closing with tanggal_masuk if needed
            if ($request->has('tanggal_masuk') && !empty($request->tanggal_masuk)) {
                $plan->tanggal_closing = $request->tanggal_masuk;
                $plan->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        if ($id === 'tasks-today') {
            return $this->getTasksToday(request());
        }
        return redirect()->route('admin.salesplan.index');
    }
}
