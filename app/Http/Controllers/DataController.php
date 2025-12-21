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

class DataController extends Controller
{
    public function createDraft()
    {
        try {
            $user = Auth::user();
            $newData = new Data();
            $newData->nama = '(Edit Nama)';
            $newData->status_peserta = 'peserta_baru';
            $newData->created_by = $user->name;
            $newData->created_by_role = $user->role;
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
    } elseif ($userRole === 'administrator' || $user->name === 'Agus Setyo') {
        // Administrator & Agus Setyo boleh lihat semua CS
        $csQuery->whereIn('role', ['cs', 'CS', 'customer_service']);
    } else {
        // CS biasa hanya bisa lihat dirinya sendiri
        $csQuery->where('id', $userId);
    }

    $csList = $csQuery->select('id', 'name')->orderBy('name')->get();

    // --- Ambil filter ---
    $kelasFilter = $request->input('kelas');
    $csFilter    = $request->input('cs_name');
    $bulanFilter = $request->input('bulan');
    $perPage     = $request->get('per_page', 100);

    // --- Query utama ---
    $query = \App\Models\Data::orderBy('created_at', 'desc');

    // Jika admin MBC → hanya 6 CS tertentu
    if (in_array($userId, $adminMbcIds)) {
        $query->whereIn('created_by', $allowedCsNames);
    }

    // Manager → hanya bisa lihat data Latifah & Tursia
    if ($userRole === 'manager') {
        $query->whereIn('created_by', ['Latifah', 'Tursia']);
    }

    // Jika belum pilih CS → jangan tampilkan data
    if (empty($csFilter)) {
        $query->whereNull('id');
    } else {
        $query->where('created_by', $csFilter);
    }

    // Filter kelas & bulan
    if (!empty($kelasFilter)) {
        $query->where('kelas_id', $kelasFilter);
    }

    if (!empty($bulanFilter)) {
        $query->whereMonth('created_at', $bulanFilter);
    }

    // CS biasa → hanya datanya sendiri
    if (!in_array($userRole, ['administrator', 'manager']) && !in_array($userId, $adminMbcIds) && $user->name !== 'Agus Setyo') {
        $query->where('created_by', $user->name);
    }

    // Khusus Agus Setyo: Hanya kelas Start-Up Muslim/Muda Indonesia
    if ($user->name === 'Agus Setyo') {
        $query->whereHas('kelas', function($q) {
             $q->where('nama_kelas', 'Start-Up Muda Indonesia')
               ->orWhere('nama_kelas', 'Start-Up Muslim Indonesia');
        });
    }

    $data = $query->paginate($perPage);
    $kelas = \App\Models\Kelas::select('id', 'nama_kelas')->orderBy('nama_kelas')->get();

    return view('admin.database.database', [
        'data' => $data,
        'kelas' => $kelas,
        'csList' => $csList,
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

        $data = Data::findOrFail($request->id);
        $field = $request->field;
        $data->$field = $request->value;
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
        $data->jenisbisnis = $request->input('jenisbisnis');
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
        $data->jenisbisnis = $request->input('jenisbisnis');
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
            $data = data::where('status_peserta', 'peserta_baru')->get();
        } else {
            $data = data::where('status_peserta', 'peserta_baru')
                ->where('created_by', Auth::user()->name)
                ->get();
        }
        return view('admin.database.database', compact('data'));
    }

    public function alumni()
    {
        if (Auth::user()->email === 'mbchamasah@gmail.com') {
            $data = data::where('status_peserta', 'alumni')->get();
        } else {
            $data = data::where('status_peserta', 'alumni')
                ->where('created_by', Auth::user()->name)
                ->get();
        }
        return view('admin.database.database', compact('data'));
    }


private function filterKelasByUser($user)
{
    // Jika Administrator atau Fitra Jaya Saleh: tampil semua
    if ($user->role == 'Administrator' || $user->name == 'Fitra Jaya Saleh') {
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

    public function pindahkesalesplan($id)
    {
        // Ambil data peserta dari tabel data
        $data = Data::findOrFail($id);
        $salesPlan = new SalesPlan();
        $salesPlan->nama = $data->nama;          // dari tabel peserta
        $salesPlan->situasi_bisnis      = $data->situasi_bisnis; // dari tabel peserta
        $salesPlan->kendala      = $data->kendala;       // dari tabel peserta
        $salesPlan->kelas_id     = $data->kelas_id;
       $salesPlan->data_id      = $data->id; // Link ke data asli 
        $salesPlan->created_by   = auth()->id();
        $salesPlan->status       = 'cold'; // default awal

        // Kolom tambahan biarkan kosong dulu, admin yang isi nant
        $salesPlan->save();


        // Kalau mau pindahkan (hapus dari tabel data) bisa tambahkan:
        // $data->delete();

          return redirect()->back()
            ->with('success', 'Peserta berhasil dipindahkan ke Sales Plan.');
        
    }
}
