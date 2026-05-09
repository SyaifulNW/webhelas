<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas; // Ensure you import the Kelas model
use App\Models\Data;
use App\Models\Alumni; // Ensure you import the Alumni model
use App\Models\SalesPlan; // Ensure you import the Salesplan model

    use App\Models\User;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Maatwebsite\Excel\Facades\Excel;
    use Illuminate\Support\Facades\Cache;
    use App\Imports\DataImport;
    use Barryvdh\DomPDF\Facade\Pdf;

    class DataController extends Controller
    {
        public function __construct()
        {
            $this->middleware('auth')->except(['formM1t', 'storeFormM1t']);
        }

        public function createDraft()
    {
        try {
            $user = Auth::user();
            $newData = new Data();
            $newData->nama = '';
            $newData->status_peserta = 'peserta_baru';
            $newData->created_by = $user->name;
            $newData->created_by_role = $user->role;
            
            // Set default chapter city for Chapter/Reseller roles so the data is visible in their view
            if (in_array(strtolower($user->role), ['chapter', 'reseller']) && $user->chapter) {
                $newData->kota_nama = $user->chapter;
            }
            
            $newData->save();

            $kelas = Kelas::select('id', 'nama_kelas')->orderBy('nama_kelas')->get();
            // Gunakan view partial yang sama dengan loop utama untuk konsistensi
            $html = view('admin.database.partials.row', [
                'item' => $newData,
                'loop' => (object)['iteration' => 'New'], // Placeholder iteration
                'kelas' => $kelas
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = strtolower($user->role);

        // --- Admin MBC Khusus ---
        $adminMbcIds = [2, 3, 6, 10, 4, 12];
        $allowedCsNames = ['Linda', 'Yasmin', 'Shafa', 'Arifa', 'Tursia', 'Latifah'];

        // --- Ambil daftar CS sesuai role ---
        $csQuery = \App\Models\User::query();

        if (in_array($userId, $adminMbcIds)) {
            // Admin MBC hanya bisa lihat CS tertentu
            $csQuery->whereIn('name', $allowedCsNames);
        } elseif ($userRole === 'manager') {
            // Manager hanya boleh lihat Latifah & Tursia
            $csQuery->whereIn('name', ['Latifah', 'Tursia']);
        } elseif ($userRole === 'administrator' || $userRole === 'marketing' || $user->name === 'Agus Setyo') {
            // Administrator & Agus Setyo & Linda & Marketing boleh lihat daftar CS
            if ($userRole === 'marketing') {
                $csQuery->where('role', 'cs-mbc');
            } else {
                $csQuery->whereIn('role', ['cs', 'CS', 'customer_service', 'cs-mbc', 'cs-smi', 'chapter', 'reseller']);
            }
        } else {
            // CS biasa hanya bisa lihat dirinya sendiri
            $csQuery->where('id', $userId);
        }

        $csList = $csQuery->select('id', 'name')->orderBy('name')->get();

        // --- Ambil filter ---
        $kelasFilter = $request->input('kelas');
        $csFilter    = $request->input('cs_name');
        $chapterFilter = $request->input('chapter_id');
        $bulanFilter = $request->input('bulan');
        $tahunFilter = $request->input('tahun'); // Tambah filter tahun
        $searchFilter = $request->input('search');
        $perPage     = 50;

        // --- Query utama ---
        $sortByParam = $request->input('sort_by', 'created_at');
        $sortOrderParam = $request->input('order', 'desc');
        
        // Whitelist columns
        $allowedSorts = ['created_at', 'created_by', 'nama', 'status_peserta']; 
        if (!in_array($sortByParam, $allowedSorts)) {
            $sortByParam = 'created_at';
        }
        
        // Base Query: Show "Calon Peserta" (Peserta Baru) and those moved to Sales Plan
        $query = \App\Models\Data::with(['kelas', 'salesplan' => function($q) {
                $q->orderBy('updated_at', 'desc');
            }, 'salesplan.kelas', 'createdBy'])
            ->whereIn('status_peserta', ['peserta_baru', 'pindah_salesplan']);

        $viewType = $request->input('view_type');
        if ($viewType === 'cs') {
            $query->where('created_by_role', 'cs-mbc');
        } elseif ($viewType === 'chapter') {
            $query->whereIn('created_by_role', ['chapter', 'reseller', 'agen']);
        } elseif ($userRole === 'cs-mbc') {
            // Default behavior for CS-MBC role
            $query->whereNotIn('created_by_role', ['chapter', 'reseller', 'agen']);
        }

        // Filter Search
        if (!empty($searchFilter)) {
            $query->where(function($q) use ($searchFilter) {
                $q->where('nama', 'LIKE', '%'.$searchFilter.'%')
                ->orWhere('leads', 'LIKE', '%'.$searchFilter.'%')
                ->orWhere('nama_bisnis', 'LIKE', '%'.$searchFilter.'%')
                ->orWhere('no_wa', 'LIKE', '%'.$searchFilter.'%')
                ->orWhere('keterangan_spin', 'LIKE', '%'.$searchFilter.'%');
            });
        }

        $query->orderBy('is_no_potensi', 'asc')
              ->orderBy($sortByParam, $sortOrderParam); // Order By must be after search conditions if any

        // Jika admin MBC → hanya 6 CS tertentu (DISABLED/ADJUSTED: User reported CS seeing shared data is undesirable)
        // if (in_array($userId, $adminMbcIds)) {
        //     $query->whereIn('created_by', $allowedCsNames);
        // }

        // Manager → hanya bisa lihat data Latifah & Tursia
        if ($userRole === 'manager') {
            $query->whereIn('created_by', ['Latifah', 'Tursia']);
        }

        // Filter User
        if (!empty($csFilter)) {
            $query->where('created_by', $csFilter);
        }

        // Filter Chapter
        if (!empty($chapterFilter)) {
            $selectedChapter = \App\Models\User::find($chapterFilter);
            if ($selectedChapter) {
                $query->where(function($q) use ($selectedChapter) {
                    // Semua inputan dia
                    $q->where('created_by', $selectedChapter->name);

                    // Tambahan agen di bawah chapter ini:
                    $downlines = \App\Models\User::where('created_by', $selectedChapter->id)->pluck('name')->toArray();
                    if (!empty($downlines)) {
                        $q->orWhereIn('created_by', $downlines);
                    }
                    
                    // Semua data dengan kota yang sama dengan wilayah chapter
                    if (!empty($selectedChapter->chapter)) {
                        $excludeNames = ['Yasmin', 'Linda', 'Puput', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];
                        $q->orWhere(function($sq) use ($selectedChapter, $excludeNames) {
                            $sq->where('kota_nama', 'like', '%' . $selectedChapter->chapter . '%')
                               ->whereNotIn('created_by', $excludeNames)
                               ->where('created_by_role', '!=', 'cs-mbc');
                        });
                    }
                });
            }
        }

        // Filter kelas & bulan & tahun
        if (!empty($kelasFilter)) {
            $query->where('kelas_id', $kelasFilter);
        }

        if (!empty($bulanFilter)) {
            $query->whereMonth('created_at', $bulanFilter);
        }

        if (!empty($tahunFilter)) {
            $query->whereYear('created_at', $tahunFilter);
        }

        // New Filters (Server Side)
        $sumberFilter = $request->input('sumber');
        $kotaFilter = $request->input('kota');
        $provinsiFilter = $request->input('provinsi');

        if (!empty($sumberFilter)) {
            $query->where('leads', $sumberFilter);
        }

        if (!empty($kotaFilter)) {
            // Kota is stored as 'kota_nama' or linked via ID. Checking view logic, it seems to be 'kota_nama'.
            // Let's verify view usage. In row.blade.php it uses $item->kota_nama.
            $query->where('kota_nama', $kotaFilter);
        }


        if (!empty($provinsiFilter)) {
            $query->where('provinsi_nama', $provinsiFilter);
        }

        // Filter Spin (ALL = semua B,A,T tercentang, NOT_ALL = belum semua tercentang)
        $spinFilter = $request->input('filter_spin');
        if (!empty($spinFilter)) {
            if ($spinFilter === 'ALL') {
                $query->where('bant_budget', 1)
                      ->where('bant_authority', 1)
                      ->where('bant_time', 1);
            } elseif ($spinFilter === 'NOT_ALL') {
                $query->where(function($q) {
                    $q->where('bant_budget', '!=', 1)
                      ->orWhereNull('bant_budget')
                      ->orWhere('bant_authority', '!=', 1)
                      ->orWhereNull('bant_authority')
                      ->orWhere('bant_time', '!=', 1)
                      ->orWhereNull('bant_time');
                });
            }
        }

        // Filter Zoom
        $zoomFilter = $request->input('zoom');
        if ($zoomFilter !== null && $zoomFilter !== '') {
            $query->where('ikut_zoom', $zoomFilter);
        }

        // Filter Ikut Kelas (SalesPlan)
        $ikutKelasFilter = $request->input('ikut_kelas');
        $daftarKelasFilter = $request->input('daftar_kelas');

        if ($ikutKelasFilter !== null && $ikutKelasFilter !== '') {
            if ($ikutKelasFilter == '1') {
                // Sudah Pernah Ikut
                $query->whereHas('salesplan', function($q) use ($daftarKelasFilter) {
                    $q->where('status', 'sudah_transfer');
                    if (!empty($daftarKelasFilter)) {
                        $q->where('kelas_id', $daftarKelasFilter);
                    }
                });
            } else {
                // Belum Pernah Ikut
                if (!empty($daftarKelasFilter)) {
                    // Belum pernah ikut kelas spesifik
                    $query->whereDoesntHave('salesplan', function($q) use ($daftarKelasFilter) {
                        $q->where('status', 'sudah_transfer')
                          ->where('kelas_id', $daftarKelasFilter);
                    });
                } else {
                    // Belum pernah ikut kelas APAPUN
                    $query->whereDoesntHave('salesplan', function($q) {
                        $q->where('status', 'sudah_transfer');
                    });
                }
            }
        }

        // Filter BANT
        $bantFilter = $request->input('bant');
        if ($bantFilter !== null && $bantFilter !== '') {
            $query->where('bant', $bantFilter);
        }

        // Filter Potensi
        $potensiFilter = $request->input('potensi');
        $potensiKelasFilter = $request->input('potensi_kelas_id');

        if (!empty($potensiFilter) && $potensiFilter !== 'all') {
            if (in_array(strtoupper($potensiFilter), ['MBC', 'SMI'])) {
                $query->where('potensi', strtoupper($potensiFilter));
                if (!empty($potensiKelasFilter)) {
                    $query->where('kelas_id', $potensiKelasFilter);
                }
            } else {
                $query->where(function($q) use ($potensiFilter) {
                    $q->where('potensi', $potensiFilter)
                      ->orWhere('potensi', strtolower($potensiFilter))
                      ->orWhere('potensi', strtoupper($potensiFilter))
                      ->orWhere('situasi_bisnis', 'like', '%Kategori: ' . strtoupper($potensiFilter) . '%');
                });
            }
        }

        // Filter Status (Follow-up Status from SalesPlan) - Only for Chapter/Reseller/Agen as requested
        $statusFilter = $request->input('status');
        if (!empty($statusFilter) && in_array($userRole, ['chapter', 'reseller', 'agen'])) {
            $query->whereHas('salesplan', function($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });
        }


        // CS biasa → hanya datanya sendiri
        $forceMyData = $request->input('view') === 'me';
        if ($userRole === 'marketing') {
            // Khusus Felmi melihat "open_house/Event", Nisa melihat "Online/Sosmed"
            if (stripos($user->name, 'Felmi') !== false) {
                $query->whereIn('leads', ['Event', 'Open House']);
            } elseif (stripos($user->name, 'Nisa') !== false) {
                $query->whereIn('leads', ['Online', 'Sosmed']);
            } else {
                $query->whereIn('leads', ['Marketing', 'Ads', 'Sosmed', 'Zoom', 'Open House']);
            }
            $query->where('created_by_role', 'cs-mbc');
        } elseif (!in_array($userRole, ['administrator', 'manager', 'chapter', 'reseller', 'agen']) && $user->name !== 'Agus Setyo') {
            $query->where('created_by', $user->name);
        }

        // Chapter Role -> filter by user's chapter city
        if ($userRole === 'chapter') {
            $query->where(function($q) use ($user) {
                // Daftar CS-MBC yang harus disembunyikan dari Chapter
                $excludeNames = ['Yasmin', 'Linda', 'Puput', 'Arifa', 'Diah Putri', 'Shafa', 'Muthia', 'Latifah', 'Gunawan'];

                $q->where(function($sq) use ($user, $excludeNames) {
                    $sq->where('kota_nama', 'like', '%' . $user->chapter . '%')
                       ->whereNotIn('created_by', $excludeNames)
                       ->where('created_by_role', '!=', 'cs-mbc');
                })->orWhere('created_by', $user->name);
            });
        } elseif (in_array($userRole, ['reseller', 'agen'])) {
            // Reseller/Agen: See own data + downline data
            $downlineNames = \App\Models\User::where('created_by', $user->id)->pluck('name')->toArray();
            $viewNames = array_merge([$user->name], $downlineNames);
            $query->whereIn('created_by', $viewNames);
        }

        // Khusus Agus Setyo: Hanya kelas Start-Up Muslim/Muda Indonesia
        if ($user->name === 'Agus Setyo') {
            $query->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'Start-Up Muda Indonesia')
                ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
            });
        }

        // --- Stats Calculation for Dashboard Headers ---
        // KPI Query: Targets ALL data input (ignoring status_peserta) to reflect Acquisition Performance
        $kpiQuery = \App\Models\Data::query();

        // Filter by view_type or CS-MBC role for stats
        if ($viewType === 'cs' || $userRole === 'cs-mbc') {
            $kpiQuery->where('created_by_role', 'cs-mbc');
        } elseif ($viewType === 'chapter') {
            $kpiQuery->whereIn('created_by_role', ['chapter', 'reseller', 'agen']);
        }
        
        // Re-apply Permission/Ownership Logic to KPI Query
        // Manager
        if ($userRole === 'manager') {
            $kpiQuery->whereIn('created_by', ['Latifah', 'Tursia']);
        }
        // Filter User (Dropdown)
        if (!empty($csFilter)) {
            $kpiQuery->where('created_by', $csFilter);
        }
        // Strict CS View
        // Strict CS View
        if (($user->name === 'Linda' && $forceMyData) || (!in_array($userRole, ['administrator', 'manager', 'chapter', 'reseller', 'agen']) && $user->name !== 'Agus Setyo' && $user->name !== 'Linda')) {
            $kpiQuery->where('created_by', $user->name);
        }
        
        // Re-apply Permission/Ownership Logic to KPI Query for Regional Roles
        if ($userRole === 'chapter') {
            $chapterName = $user->chapter;
            $kpiQuery->where('kota_nama', 'like', '%' . $chapterName . '%');
        } elseif (in_array($userRole, ['reseller', 'agen'])) {
            $downlineNames = \App\Models\User::where('created_by', $user->id)->pluck('name')->toArray();
            $viewNames = array_merge([$user->name], $downlineNames);
            $kpiQuery->whereIn('created_by', $viewNames);
        }
        // Agus Setyo
        // Marketing Role specific KPI filter
        if ($userRole === 'marketing') {
            if (stripos($user->name, 'Felmi') !== false) {
                $kpiQuery->where('leads', 'Event');
            } else {
                $kpiQuery->whereIn('leads', ['Marketing', 'Event']);
            }
            $kpiQuery->where('created_by_role', 'cs-mbc');
        }

        if ($user->name === 'Agus Setyo') {
            $kpiQuery->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'Start-Up Muda Indonesia')
                ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
            });
        }


        $now = \Carbon\Carbon::now();
        $statsYear = $tahunFilter ? $tahunFilter : $now->year;
        $statsMonth = $bulanFilter ? $bulanFilter : $now->month;
        
        $bulanLabel = \Carbon\Carbon::createFromDate($statsYear, $statsMonth, 1)->isoFormat('MMMM YYYY');

        // Total Database: Count of current table (Queue Size)
        // We use the original $query which has 'status' & 'time' & 'search' filters applied.
        $totalDatabase = (clone $query)->count();

        // Database Baru: Performance Metric (Count of ALL inputs in period)
        $databaseBaru = $kpiQuery
            ->whereYear('created_at', $statsYear)
            ->whereMonth('created_at', $statsMonth)
            ->count();

        $target = ($userRole === 'administrator') ? 250 : 50;
        $kurang = max($target - $databaseBaru, 0);

        $data = $query->paginate($perPage)->withQueryString();
        $kelas = \App\Models\Kelas::select('id', 'nama_kelas')->orderBy('nama_kelas')->get();

        // Fetch lists for filters
        $provinsiList = \App\Models\Data::select('provinsi_nama')
            ->whereNotNull('provinsi_nama')
            ->where('provinsi_nama', '!=', '')
            ->distinct()
            ->orderBy('provinsi_nama')
            ->pluck('provinsi_nama');

        $kotaQuery = \App\Models\Data::select('kota_nama')
            ->whereNotNull('kota_nama')
            ->where('kota_nama', '!=', '')
            ->distinct()
            ->orderBy('kota_nama');

        if (!empty($provinsiFilter)) {
            $kotaQuery->where('provinsi_nama', $provinsiFilter);
        }

        $kotaList = $kotaQuery->pluck('kota_nama');

        if ($request->ajax()) {
            $paginationHtml = $data->withQueryString()->links('pagination::bootstrap-4')->toHtml();
            return response()->json([
                'html' => view('admin.database.partials.table', [
                    'data' => $data,
                    'kelas' => $kelas,
                    'csList' => $csList,
                    'provinsiList' => $provinsiList,
                    'kotaList' => $kotaList,
                    'databaseBaru' => $databaseBaru,
                    'totalDatabase' => $totalDatabase,
                    'target' => $target,
                    'kurang' => $kurang,
                    'bulanLabel' => $bulanLabel,
                ])->render(),
                'pagination' => $paginationHtml,
                'stats' => [
                    'databaseBaru' => $databaseBaru,
                    'totalDatabase' => $totalDatabase,
                    'kurang' => $kurang,
                    'bulanLabel' => $bulanLabel,
                ]
            ]);
        }

        return view('admin.database.database', [
            'data' => $data,
            'kelas' => $kelas,
            'csList' => $csList,
            'provinsiList' => $provinsiList,
            'kotaList' => $kotaList,
            'databaseBaru' => $databaseBaru,
            'totalDatabase' => $totalDatabase,
            'target' => $target,
            'kurang' => $kurang,
            'bulanLabel' => $bulanLabel,
        ]);
    }







        /**
         * Show the form for creating a new resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function create()
        {
            // Return a view to create a new resource
            return view('admin.database.create');
        }

        /**
         * Store a newly created resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function updateInline(Request $request)
        {
            try {
                $data = Data::findOrFail($request->id);
                
                if ($request->has('updates')) {
                    $updates = $request->updates;
                    
                    // Auto-update timestamps for FU ONLY if content changed
                    for ($i = 1; $i <= 10; $i++) {
                        $hasilField = "fu{$i}_hasil";
                        $tindakField = "fu{$i}_tindak_lanjut";
                        $waField = "fu{$i}_wa";
                        $telpField = "fu{$i}_telp";
                        $atField = "fu{$i}_at";

                        $hasChanged = false;

                        // Compare with current data (normalize null/empty string)
                        $newHasil = $updates[$hasilField] ?? '';
                        $oldHasil = $data->$hasilField ?? '';
                        if ($newHasil !== $oldHasil) $hasChanged = true;

                        $newTindak = $updates[$tindakField] ?? '';
                        $oldTindak = $data->$tindakField ?? '';
                        if ($newTindak !== $oldTindak) $hasChanged = true;

                        if (isset($updates[$waField]) && (int)$updates[$waField] !== (int)$data->$waField) $hasChanged = true;
                        if (isset($updates[$telpField]) && (int)$updates[$telpField] !== (int)$data->$telpField) $hasChanged = true;

                        // If manual timestamp is provided, parse it
                        $manualDate = null;
                        if (isset($updates[$atField]) && !empty($updates[$atField])) {
                            try {
                                $manualDate = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $updates[$atField]);
                            } catch (\Exception $e) {}
                        }

                        // Check if it's actually changed from current database value (ignore seconds)
                        $currentDate = $data->$atField ? \Carbon\Carbon::parse($data->$atField) : null;
                        $isDateManuallyChanged = false;
                        if ($manualDate) {
                            if (!$currentDate || $manualDate->format('d/m/Y H:i') !== $currentDate->format('d/m/Y H:i')) {
                                $isDateManuallyChanged = true;
                            }
                        }

                        if ($isDateManuallyChanged) {
                            $updates[$atField] = $manualDate;
                        } elseif ($hasChanged) {
                            $updates[$atField] = now();
                        } else {
                            // If neither manual change nor content change, we shouldn't touch the date
                            unset($updates[$atField]);
                        }
                    }
                    
                    $data->update($updates);
                    $data->refresh(); // Ensure we have the updated timestamps

                    // Recalculate Daily Activity for all affected dates
                    try {
                        $affectedDates = [];
                        for ($i = 1; $i <= 10; $i++) {
                            $atField = "fu{$i}_at";
                            if ($data->$atField && $data->$atField instanceof \Carbon\Carbon) {
                                $affectedDates[] = $data->$atField->toDateString();
                            }
                        }
                        // Also include today's date just in case
                        $affectedDates[] = now()->toDateString();
                        
                        $userId = auth()->id();
                        foreach (array_unique($affectedDates) as $date) {
                            \App\Models\DailyActiviti::updateAutomated($userId, $date);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to update daily activity: " . $e->getMessage());
                    }

                    // Return the updated timestamps for frontend sync
                    $timestamps = [];
                    for ($i = 1; $i <= 10; $i++) {
                        $field = "fu{$i}_at";
                        $timestamps[$field] = $data->$field ? $data->$field->format('d/m/Y H:i') : null;
                    }
                    return response()->json(['success' => true, 'timestamps' => $timestamps]);
                } else {
                    $field = $request->field;
                    if ($field) {
                        // Normalize field name (some parts of the app use jenisbisnis)
                        if ($field === 'jenisbisnis') {
                            $field = 'jenis_bisnis';
                        }
                        
                        // Check if the column exists in the 'data' table
                        if (!\Schema::hasColumn('data', $field)) {
                             // Fallback to jenisbisnis if jenis_bisnis doesn't exist (unlikely but safe)
                             if ($field === 'jenis_bisnis') $field = 'jenisbisnis';
                        }
                        
                        // Direct assignment and save to bypass mass-assignment issues if any
                        $data->$field = $request->value;
                        $data->save();
                        
                        // Debug log to confirm reaching this point
                        \Log::info("Update successful", ['id' => $data->id, 'field' => $field, 'value' => $request->value]);
                    }
                }

                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                \Log::error('Update Inline Error: ' . $e->getMessage(), [
                    'id' => $request->id,
                    'field' => $request->field,
                    'value' => $request->value,
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        public function updateLocation(Request $request)
        {
            $data = Data::findOrFail($request->id);
            
            if ($request->has('provinsi_id')) {
                $data->provinsi_id = $request->provinsi_id;
                $data->provinsi_nama = $request->provinsi_nama;
                // Reset kota jika provinsi berubah
                $data->kota_id = null;
                $data->kota_nama = null;
            }

            if ($request->has('kota_id')) {
                $data->kota_id = $request->kota_id;
                $data->kota_nama = $request->kota_nama;
            }

            $data->save();

            return response()->json(['success' => true]);
        }

        public function store(Request $request)
        {
            $data = new Data();
            $data->nama = $request->input('nama');
            $data->status_peserta = $request->input('status_peserta','peserta_baru');
            // Enum field
            $data->leads = $request->input('leads'); // Assuming 'leads' is an enum field
            // Custom field
            if ($request->input('leads_custom') === null) {
                $data->leads_custom = ''; // Set to empty string if null
            } else {
                $data->leads_custom = $request->input('leads_custom');
            }
            $data->provinsi_id = $request->input('provinsi_id');
            $data->provinsi_nama = $request->input('provinsi_nama');
            $data->kota_id = $request->input('kota_id');
            $data->kota_nama = $request->input('kota_nama');
            $data->jenis_bisnis = $request->input('jenis_bisnis') ?? $request->input('jenisbisnis');
            $data->nama_bisnis = $request->input('nama_bisnis');
            $data->no_wa = $request->input('no_wa');
            $data->situasi_bisnis = $request->input('situasi_bisnis');
            $data->kendala = $request->input('kendala');

            // Ya atau tidak
            // Enum Peserta Baru


            // Role
            $data->created_by = Auth::user()->name;
            $data->created_by_role = Auth::user()->role;
            $data->save();
            
            // Trigger auto update for Daily Activity
            \App\Models\DailyActiviti::updateAutomated(auth()->id(), now()->toDateString());

            return redirect()->route('admin.database.database')->with('success', 'Data has been added successfully.');
        }

        /**
         * Display the specified resource.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */

        public function updatePotensi(Request $request, $id)
        {
            $data = data::findOrFail($id);
            $data->kelas_id = $request->kelas_id;
            $data->save();

            return response()->json(['success' => true]);
        }
        
            public function updateSumberLeads(Request $request, $id)
    {
        $data = data::findOrFail($id);
        $data->leads = $request->leads;
        $data->save();

        return response()->json(['success' => true]);
    }



        public function show($id)
        {
            // Fetch the data by ID
            $data = data::findOrFail($id);
            $kelas = Kelas::all(); // Fetch all classes for the sidebar
            // Return a view to show the data
            return view('admin.database.show', compact('data', 'kelas'));
        }

        /**
         * Show the form for editing the specified resource.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function edit($id)
        {
            // Fetch the data by ID
            $data = data::findOrFail($id);

            $kelas = Kelas::all(); // Fetch all classes for the sidebar
            // Return a view to edit the data
            return view('admin.database.edit', compact('data', 'kelas'));
        }

        /**
         * Update the specified resource in storage.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function update(Request $request, $id)
        {
            // Validate the request data
            $data = data::findOrFail($id);
            $data->nama = $request->input('nama');
            $data->status_peserta = $request->input('status_peserta', 'Peserta Baru');
            // Enum field
            $data->leads = $request->input('leads'); // Assuming 'leads' is an enum field
            // Custom field
            if ($request->input('leads_custom') === null) {
                $data->leads_custom = ''; // Set to empty string if null
            } else {
                $data->leads_custom = $request->input('leads_custom');
            }
            $data->provinsi_id = $request->input('provinsi_id');

            $data->kota_nama = $request->input('kota_nama');
            $data->jenis_bisnis = $request->input('jenis_bisnis') ?? $request->input('jenisbisnis');
            $data->nama_bisnis = $request->input('nama_bisnis');
            $data->no_wa = $request->input('no_wa');
            $data->situasi_bisnis = $request->input('situasi_bisnis');
            $data->kendala = $request->input('kendala');

            // Ya atau tidak

            $data->save();



            // Redirect to the index page with a success message
            return redirect()->route('admin.database.database')->with('success', 'Data has been updated successfully.');
        }


        /**
         * Remove the specified resource from storage.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function destroy($id)
        {
            // Fetch the data by ID
            $data = Data::findOrFail($id);
            // Delete the data
            $data->delete();
            // Redirect to the index page with a success message
            return redirect()->route('admin.database.database')->with('success', 'Data has been deleted successfully.');
        }


        // app/Http/Controllers/DatabaseController.php

        public function peserta_baru()
        {
            if (Auth::user()->email === 'mbchamasah@gmail.com') {
                $data = data::whereIn('status_peserta', ['peserta_baru', 'pindah_salesplan'])->paginate(50);
            } else {
                $data = data::whereIn('status_peserta', ['peserta_baru', 'pindah_salesplan'])
                    ->where('created_by', Auth::user()->name)
                    ->paginate(50);
            }
            return view('admin.database.database', compact('data'));
        }

        public function alumni()
        {
            if (Auth::user()->email === 'mbchamasah@gmail.com') {
                $data = data::where('status_peserta', 'alumni')->paginate(50);
            } else {
                $data = data::where('status_peserta', 'alumni')
                    ->where('created_by', Auth::user()->name)
                    ->paginate(50);
            }
            return view('admin.database.database', compact('data'));
        }


    private function filterKelasByUser($user)
    {
        // Jika Administrator atau Fitra Jaya Saleh: tampil semua
        if (strtolower($user->role) == 'administrator' || $user->name == 'Fitra Jaya Saleh') {
            return Kelas::all();
        }

        // Jika Tursia atau Latifah â†’ hanya Start-Up Muda Indonesia
        if (in_array($user->name, ['Tursia', 'Latifah'])) {
            return Kelas::where('nama_kelas', 'Start-Up Muda Indonesia')->get();
        }

        // Jika Mutiah â†’ hanya Sekolah Kaya
        if ($user->name == 'Mutiah') {
            return Kelas::where('nama_kelas', 'Sekolah Kaya')->get();
        }

        // Jika Shafa â†’ semua kecuali Start-Up Muda Indonesia
        if ($user->name == 'Shafa') {
            return Kelas::where('nama_kelas', '!=', 'Start-Up Muda Indonesia')->get();
        }

        // Selain itu â†’ semua kecuali Sekolah Kaya dan Start-Up Muda Indonesia
        return Kelas::whereNotIn('nama_kelas', ['Sekolah Kaya', 'Start-Up Muda Indonesia'])->get();
    }

        public function pindahkesalesplan(Request $request, $id)
        {
            // Ambil data peserta dari tabel data
            $data = Data::findOrFail($id);
            $kelasIds = $request->input('kelas_id', []);

            if (empty($kelasIds)) {
                return redirect()->back()->with('error', 'Silakan pilih minimal satu kelas.');
            }

            // Update status ke 'pindah_salesplan' agar bisa dibedakan, 
            // namun tetap muncul di list (karena index query include pindah_salesplan)
            // Simpan kelas pertama sebagai referensi utama di tabel data
            $data->status_peserta = 'pindah_salesplan';
            $data->kelas_id = $kelasIds[0];
            $data->save();

            foreach ($kelasIds as $kId) {
                // Cek apakah sudah ada salesplan untuk peserta ini di kelas ini
                $existing = SalesPlan::where('data_id', $data->id)
                    ->where('kelas_id', $kId)
                    ->first();
                
                if (!$existing) {
                    $salesPlan = new SalesPlan();
                    $salesPlan->nama = $data->nama;          // dari tabel peserta
                    $salesPlan->situasi_bisnis      = $data->situasi_bisnis; // dari tabel peserta
                    $salesPlan->kendala      = $data->kendala;       // dari tabel peserta
                    $salesPlan->kelas_id     = $kId;
                    $salesPlan->data_id      = $data->id; // Link ke data asli 
                    $salesPlan->created_by   = auth()->id();
                    $salesPlan->status       = 'cold'; // default awal
                    $salesPlan->level        = 'Grow Up'; // Forced grow up default
                    $salesPlan->save();
                }
            }

            $userRole = strtolower(auth()->user()->role);
            $message = 'Peserta berhasil dipindahkan ke ' . count($kelasIds) . ' kelas di ' . (in_array($userRole, ['reseller', 'chapter', 'agen']) ? 'Prospek' : 'Sales Plan') . '.';

            if (in_array($userRole, ['reseller', 'chapter', 'agen'])) {
                return redirect()->route('admin.salesplan.index', ['type' => 'smi'])->with('success', $message);
            }

            return redirect()->back()->with('success', $message);
            
        }

        /**
         * Update status directly from database view (Chapter/Reseller)
         */
        public function updateStatusDirect(Request $request)
        {
            $dataId = $request->data_id;
            $newStatus = $request->status;
            
            $data = Data::findOrFail($dataId);
            
            // Assume default class if none set
            $kelasId = $data->kelas_id;
            if (!$kelasId) {
                // Find M1T (Start-Up Muslim Indonesia) class ID
                $m1tClass = Kelas::where('nama_kelas', 'like', '%Muslim Indonesia%')->first();
                $kelasId = $m1tClass ? $m1tClass->id : 1; 
            }

            // Create or Update SalesPlan
            // Find existing plan for this data
            // We prioritize the plan that matches the current kelas_id
            $plan = SalesPlan::where('data_id', $dataId)
                ->where('kelas_id', $kelasId)
                ->first();

            // If no plan matches this kelas_id, try to find ANY plan for this data to update it
            // (Avoiding duplicate plans for the same person when they just change classes)
            if (!$plan) {
                $plan = SalesPlan::where('data_id', $dataId)->orderBy('updated_at', 'desc')->first();
            }

            if (!$plan) {
                $plan = new SalesPlan();
                $plan->data_id = $dataId;
                $plan->kelas_id = $kelasId;
                $plan->nama = $data->nama;
                $plan->created_by = auth()->id();
                $plan->level = 'Grow Up';
            } else {
                // If we found an existing plan but it has a different kelas_id, update it
                $plan->kelas_id = $kelasId;
            }

            // If we are setting this plan to 'sudah_transfer', ensure other plans for this data_id 
            // are NOT 'sudah_transfer' to avoid appearing in both M1T and MBC lists.
            // Note: Removed logic that resets other 'sudah_transfer' statuses to allow 
            // a single lead to be registered as a paid participant in multiple classes.
            
            $oldStatus = $plan->status ?? 'new';
            $plan->status = $newStatus;
            $plan->save();

            // Auto update data status
            if ($data->status_peserta === 'peserta_baru') {
                $data->status_peserta = 'pindah_salesplan';
                $data->kelas_id = $kelasId;
                $data->save();
            }

            // Commission logic (copied from SalesPlanController)
            if ($newStatus === 'sudah_transfer' && $oldStatus !== 'sudah_transfer') {
                $creator = $plan->createdBy;
                $isChapter = $creator && str_contains(strtolower($creator->role), 'chapter');
                
                if (!$isChapter) {
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
            }

            return response()->json(['success' => true, 'plan_id' => $plan->id]);
        }
        public function getStatistik(Request $request)
        {
            $user = Auth::user();
            $userRole = strtolower($user->role);
            $filterUser = $request->input('user');
            
            $query = Data::query();

            // Admin & Manager Logic
            if (in_array($userRole, ['administrator', 'manager']) || $user->name === 'Agus Setyo') {
                if (!empty($filterUser)) {
                    $query->where('created_by', $filterUser);
                }
            } else {
                // CS Biasa
                $query->where('created_by', $user->name);
            }

            // Agus Setyo Filter
            if ($user->name === 'Agus Setyo') {
                $query->whereHas('kelas', function($q) {
                    $q->where('nama_kelas', 'Start-Up Muda Indonesia')
                    ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
                });
            }

            // Calculate Stats
            $now = \Carbon\Carbon::now();
            $bulanLabel = $now->isoFormat('MMMM YYYY');
            
            $databaseBaru = (clone $query)
                ->whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->count();
                
            $totalDatabase = $query->count();
            $target = (strtolower($user->role) === 'administrator') ? 250 : 50;
            $kurang = max($target - $databaseBaru, 0);

            return response()->json([
                'bulanLabel' => $bulanLabel,
                'databaseBaru' => $databaseBaru,
                'totalDatabase' => $totalDatabase,
                'target' => $target,
                'kurang' => $kurang
        ]);
    }

    public function exportPdfInteraksi(Request $request)
    {
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun', date('Y'));
        $csName = $request->input('cs_name');

        $user = Auth::user();
        // If regular CS (not admin/manager/marketing) and no filter selected, use their own name
        if (!$csName && !in_array(strtolower($user->role), ['administrator', 'manager', 'marketing']) && $user->name !== 'Agus Setyo' && $user->name !== 'Linda') {
            $csName = $user->name;
        }

        $query = Data::query()
            ->whereIn('status_peserta', ['peserta_baru', 'pindah_salesplan']);

        if ($bulan && $tahun) {
            $query->where(function($q) use ($bulan, $tahun) {
                for($i=1; $i<=10; $i++) {
                    $q->orWhere(function($subq) use ($i, $bulan, $tahun) {
                        $subq->whereMonth("fu{$i}_at", $bulan)
                             ->whereYear("fu{$i}_at", $tahun);
                    });
                }
            });
        }

        if ($csName) {
            $query->where('created_by', $csName);
        }

        $items = $query->get();

        $pdf = Pdf::loadView('admin.database.pdf-interaksi', [
            'items' => $items,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'csName' => $csName ?: 'Semua CS'
        ])->setPaper('a4', 'landscape');

        $fileName = 'Rekap_Interaksi_' . ($csName ?: 'Semua') . '_' . ($bulan ?: 'Semua') . '_' . $tahun . '.pdf';
        return $pdf->download($fileName);
    }

    public function toggleNoPotensi($id)
    {
        try {
            $data = Data::findOrFail($id);
            $data->is_no_potensi = !$data->is_no_potensi;
            $data->save();

            return response()->json([
                'success' => true,
                'is_no_potensi' => $data->is_no_potensi,
                'message' => 'Status potensi diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function formM1t($identifier)
    {
        $original = str_replace('-', ' ', $identifier);
        
        if (strpos($identifier, 'chapter-') === 0) {
            $chapterName = str_replace('chapter-', '', $identifier);
            $chapterName = str_replace('-', ' ', $chapterName);
            $user = \App\Models\User::where('role', 'chapter')
                ->where('chapter', 'LIKE', '%' . $chapterName . '%')
                ->first();
        } else {
            // Check by username, or by name if username is null
            $user = \App\Models\User::where('username', $identifier)
                ->orWhere('name', 'LIKE', '%' . $original . '%')
                ->first();
        }

        // Fallback to ID if somehow it was a numeric ID
        if (!$user && is_numeric($identifier)) {
            $user = \App\Models\User::find($identifier);
        }

        if (!$user) {
            abort(404, 'User/Chapter tidak ditemukan.');
        }

        return view('admin.database.form_m1t', compact('user'));
    }

    public function storeFormM1t(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = \App\Models\User::findOrFail($user_id);

        $data = new Data();
        $data->nama = $request->input('nama');
        $data->no_wa = $request->input('no_wa');
        $data->provinsi_id = $request->input('provinsi');
        $data->provinsi_nama = $request->input('provinsi_nama');
        $data->kota_id = $request->input('kota');
        $data->kota_nama = $request->input('kota_nama'); 
        $data->nama_bisnis = $request->input('nama_usaha');
        
        $answers = [
            "Lama bisnis: " . $request->input('lama_bisnis_label'),
            "Omset: " . $request->input('omset_label'),
            "Tantangan: " . $request->input('tantangan_label'),
            "Target: " . $request->input('target_label'),
            "Alasan: " . $request->input('alasan_label'),
            "Posisi: " . $request->input('posisi_label'),
            "Investasi: " . $request->input('investasi_label'),
            "Kesiapan: " . $request->input('kesiapan_hadir_label'),
            "Keputusan: " . $request->input('keputusan_label'),
            "Jumlah Karyawan: " . $request->input('jumlah_karyawan_label'),
            "Mengenal Coach: " . $request->input('mengenal_coach_label')
        ];

        $totalScore = $request->input('total_score');

        // Determine Category/Potensi
        $category = 'Cold';
        if ($totalScore >= 41) {
            $category = 'Hot';
        } elseif ($totalScore >= 25) {
            $category = 'Warm';
        }

        $data->situasi_bisnis = implode("\n", $answers) . "\n\nTotal Skor Form: " . $totalScore . " / 51\nKategori: " . strtoupper($category);
        $data->potensi = $category;
        $data->status_peserta = 'peserta_baru';
        $data->leads = 'Open House';
        $data->created_by = $user->name;
        $data->created_by_role = $user->role;
        $data->save();

        return redirect()->back()->with('success', 'Data Open House M1T berhasil disubmit.');
    }
    public function reuseData(Request $request)
    {
        try {
            $dataId = $request->id;
            $data = Data::findOrFail($dataId);
            
            // 1. Clear current Data model's potential tracking & BAT so view resets
            $data->potensi = null;
            $data->kelas_id = null;
            $data->bant_budget = false;
            $data->bant_authority = false;
            $data->bant_time = false;
            $data->status_peserta = 'peserta_baru'; // Reset to fresh lead status
            
            // Clear interaction history (FU1 - FU10)
            for ($i = 1; $i <= 10; $i++) {
                $data->{"fu{$i}_hasil"} = null;
                $data->{"fu{$i}_tindak_lanjut"} = null;
                $data->{"fu{$i}_wa"} = 0;
                $data->{"fu{$i}_telp"} = 0;
                $data->{"fu{$i}_at"} = null;
            }
            
            $data->save();

            // 2. Create a new SalesPlan (History entry + new active status)
            $plan = new SalesPlan();
            $plan->data_id = $dataId;
            $plan->kelas_id = null; // Clear class selection for the new cycle
            $plan->nama = $data->nama;
            $plan->created_by = auth()->id();
            $plan->status = 'cold'; // Start fresh as cold
            $plan->level = 'Grow Up';
            $plan->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
