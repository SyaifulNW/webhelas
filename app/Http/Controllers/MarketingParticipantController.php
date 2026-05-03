<?php

namespace App\Http\Controllers;

use App\Models\MarketingParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingParticipantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower(trim($user->role));
        $isAdministrator = $role === 'administrator';
        $isAdvertising = $role === 'advertising';
        
        // Fetch marketing users for filter
        $marketingUsers = [];
        if ($isAdministrator) {
            $marketingUsers = User::where('role', 'marketing')->get();
        } elseif ($isAdvertising) {
            $marketingUsers = User::whereIn('name', ['Felmi', 'Nisa'])->get();
        }

        // Month & Year Filter
        $currentMonth = $request->get('bulan', now()->month);
        $currentYear = $request->get('tahun', now()->year);

        $query = MarketingParticipant::with('creator');

        // Filter by Month/Year if provided (using created_at)
        $query->whereMonth('created_at', $currentMonth)
              ->whereYear('created_at', $currentYear);

        // Filtering Logic (Existing)
        if ($isAdministrator) {
            if ($request->has('marketing_user_id') && $request->marketing_user_id != 'all') {
                $query->where('created_by', $request->marketing_user_id);
            }
        } elseif ($isAdvertising) {
            $felmiNisaIds = User::whereIn('name', ['Felmi', 'Nisa'])->pluck('id')->toArray();
            
            if ($request->has('marketing_user_id') && $request->marketing_user_id != 'all') {
                $query->where('created_by', $request->marketing_user_id);
            } else {
                // Default or 'ALL' for advertising: Only show Felmi & Nisa
                $query->whereIn('created_by', $felmiNisaIds);
            }
        } else {
            // Marketing only see their own
            $query->where('created_by', $user->id);
        }

        if ($request->has('potensi') && $request->potensi != 'all') {
            $query->where('potensi', $request->potensi);
        }

        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', $request->provinsi_id);
        }

        if ($request->filled('kota_id')) {
            $query->where('kota_id', $request->kota_id);
        }

        // Clone query for stats before pagination
        $statsQuery = clone $query;
        $allForStats = $statsQuery->get();

        $participants = $query->orderBy('created_at', 'desc')->paginate(50);

        // Optional: Backfill assigned_cs for view if it's missing but transferred
        $missingCsNoWas = $participants->where('is_transferred', true)->filter(fn($p) => empty($p->assigned_cs))->pluck('no_wa')->unique();
        if ($missingCsNoWas->isNotEmpty()) {
            $dataMap = \App\Models\Data::whereIn('no_wa', $missingCsNoWas)
                ->whereIn('leads', ['Marketing', 'Event', 'Online'])
                ->latest()
                ->get()
                ->groupBy('no_wa')
                ->map(fn($items) => $items->first()->created_by);
            
            foreach ($participants as $p) {
                if ($p->is_transferred && empty($p->assigned_cs)) {
                    $p->assigned_cs = $dataMap[$p->no_wa] ?? null;
                }
            }
        }

        // Calculate Stats from the ALL filtered records (not just current page)
    $totalMarketingDatabase = $allForStats->count();
    $transferredCount = $allForStats->where('is_transferred', true)->count();
    $untransferredCount = $allForStats->where('is_transferred', false)->count();

    // Group transferred participants by their assigned CS (from all records)
    $transferredGroups = $allForStats->where('is_transferred', true)
        ->groupBy('assigned_cs');

    $csDistribution = [];
    $targetCs = ['Linda', 'Yasmin', 'Diah Putri', 'Arifa', 'Puput'];
    
    foreach ($targetCs as $name) {
        $count = 0;
        if (isset($transferredGroups[$name])) {
            $count = $transferredGroups[$name]->count();
        }
        
        // Sum old records mapped to 'Putri' into 'Diah Putri'
        if ($name === 'Diah Putri' && isset($transferredGroups['Putri'])) {
            $count += $transferredGroups['Putri']->count();
        }

        $csDistribution[] = (object)[
            'name' => $name,
            'count' => $count
        ];
    }    

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Fetch full list of provinces globally (not just existing in data)
        $filterProvinces = [];
        try {
            $resp = \Illuminate\Support\Facades\Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json');
            if ($resp->successful()) {
                $filterProvinces = $resp->json();
            }
        } catch (\Exception $e) {
            // Fallback to data-based provinces if API fails
            $filterProvinces = MarketingParticipant::whereNotNull('provinsi_nama')
                ->select('provinsi_id as id', 'provinsi_nama as name')
                ->groupBy('provinsi_id', 'provinsi_nama')
                ->get();
        }
            
        $filterCities = [];
        if ($request->filled('provinsi_id')) {
            try {
                $resp = \Illuminate\Support\Facades\Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$request->provinsi_id}.json");
                if ($resp->successful()) {
                    $filterCities = $resp->json();
                }
            } catch (\Exception $e) {
                $filterCities = MarketingParticipant::where('provinsi_id', $request->provinsi_id)
                    ->whereNotNull('kota_nama')
                    ->select('kota_id as id', 'kota_nama as name')
                    ->groupBy('kota_id', 'kota_nama')
                    ->get();
            }
        }

        return view('marketing.database_peserta', compact(
            'participants', 
            'isAdministrator', 
            'isAdvertising', 
            'marketingUsers', 
            'user', 
            'csDistribution', 
            'totalMarketingDatabase',
            'transferredCount',
            'untransferredCount',
            'currentMonth',
            'currentYear',
            'bulanNames',
            'filterProvinces',
            'filterCities'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $participant = MarketingParticipant::create([
            'nama' => '',
            'created_by' => $user->id,
            'potensi' => 'MBC' // Default
        ]);

        return response()->json([
            'success' => true,
            'data' => $participant
        ]);
    }

    public function updateInline(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:marketing_participants,id',
                'field' => 'nullable',
                'value' => 'nullable',
                'updates' => 'nullable|array'
            ]);

            $participant = MarketingParticipant::findOrFail($request->id);
            $user = Auth::user();
            
            // Authorization check (Robust integer comparison)
            if (strtolower($user->role) !== 'administrator' && (int)$participant->created_by !== (int)$user->id) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Unauthorized. Creator: ' . $participant->created_by . ', You: ' . $user->id
                ], 403);
            }

            if ($request->has('updates')) {
                $participant->update($request->updates);
            } else {
                $field = $request->field;
                $participant->$field = $request->value;
                $participant->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function moveToCs(Request $request)
    {
        try {
            $request->validate(['id' => 'required|exists:marketing_participants,id']);
            
            $participant = MarketingParticipant::findOrFail($request->id);
            $user = Auth::user();

            // Authorization check (Robust integer comparison)
            if (strtolower($user->role) !== 'administrator' && (int)$participant->created_by !== (int)$user->id) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Unauthorized. Creator: ' . $participant->created_by . ', You: ' . $user->id
                ], 403);
            }

            if ($participant->is_transferred) {
                return response()->json(['success' => false, 'error' => 'Data sudah dipindahkan ke CS'], 400);
            }

            // Rotator CS List (Based on actual DB names and requested rotation)
            $csList = [
                ['name' => 'Linda', 'role' => 'cs-mbc'],
                ['name' => 'Yasmin', 'role' => 'cs-mbc'],
                ['name' => 'Diah Putri', 'role' => 'cs-mbc'],
                ['name' => 'Arifa', 'role' => 'cs-mbc'],
                ['name' => 'Puput', 'role' => 'cs-smi'],
            ];

            // Determine leads source based on creator
            $leadsSource = 'ADS';
            if ($participant->creator) {
                if ($participant->creator->name === 'Felmi') {
                    $leadsSource = 'Open House';
                } elseif ($participant->creator->name === 'Nisa') {
                    $leadsSource = 'Sosial Media';
                }
            }

            // Find last assigned CS from rotator in the 'data' table (across Marketing/Event/Online)
            $lastLead = \App\Models\Data::whereIn('created_by', array_column($csList, 'name'))
                ->whereIn('leads', ['Marketing', 'Event', 'Online'])
                ->latest()
                ->first();

            $nextIndex = 0;
            if ($lastLead) {
                $lastName = $lastLead->created_by;
                foreach ($csList as $index => $cs) {
                    if ($cs['name'] === $lastName) {
                        $nextIndex = ($index + 1) % count($csList);
                        break;
                    }
                }
            }

            $assignedCs = $csList[$nextIndex];

            // Create entry in 'data' table (used by CS)
            $data = new \App\Models\Data();
            $data->nama = $participant->nama ?? '-';
            $data->no_wa = $participant->no_wa;
            $data->provinsi_id = $participant->provinsi_id;
            $data->provinsi_nama = $participant->provinsi_nama;
            $data->kota_id = $participant->kota_id;
            $data->kota_nama = $participant->kota_nama;
            $data->nama_bisnis = $participant->nama_bisnis;
            
            // Handle naming inconsistency in 'data' table (jenis_bisnis vs jenisbisnis)
            if (\Schema::hasColumn('data', 'jenis_bisnis')) {
                $data->jenis_bisnis = $participant->jenis_bisnis;
            } else {
                $data->jenisbisnis = $participant->jenis_bisnis;
            }
            
            if (\Schema::hasColumn('data', 'potensi')) {
                $data->potensi = $participant->potensi;
            }
            
            $data->leads = $leadsSource;
            $data->status_peserta = 'peserta_baru';
            
            // Assigned to CS via rotator
            $data->created_by = $assignedCs['name'];
            $data->created_by_role = $assignedCs['role'];
            $data->save();

            // Mark as transferred and save CS Name
            $participant->is_transferred = true;
            $participant->assigned_cs = $assignedCs['name'];
            $participant->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dipindahkan ke CS: ' . $assignedCs['name'],
                'assigned_cs' => $assignedCs['name']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $participant = MarketingParticipant::findOrFail($id);
            $user = Auth::user();
            
            // Authorization check (Robust integer comparison)
            if (strtolower($user->role) !== 'administrator' && (int)$participant->created_by !== (int)$user->id) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Unauthorized. Creator: ' . $participant->created_by . ', You: ' . $user->id
                ], 403);
            }

            $participant->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
