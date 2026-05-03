<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alumni; // Ensure you import the Alumni model
use App\Models\Data; // Ensure you import the data model
use Illuminate\Support\Facades\DB;
use App\Models\Kelas; // Ensure you import the Kelas model
use App\Models\SalesPlan; // Ensure you import the Salesplan model
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;


class AlumniController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Base query
        $query = alumni::query();

        // Filter status_peserta alumni


        // Filter role
        if ($user->email !== 'mbchamasah@gmail.com') {
            $query->where('created_by', $user->name);
        }

        $alumni = $query->get();

        $kelas = Kelas::all();

        return view('admin.alumni.alumni', compact('alumni', 'kelas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }




    public function toSalesplan(Request $request, $id)
    {
        $alumni = Alumni::findOrFail($id);
        $kelas = $alumni->kelas_yang_akan_diikuti;

        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas belum dipilih.');
        }

        // Gunakan ID dari tabel data
        $dataId = $alumni->data_id;

        if (!$dataId) {
            return redirect()->back()->with('error', 'Data ID tidak ditemukan pada alumni.');
        }

        // Cek apakah sudah ada di salesplan
        $existing = Salesplan::where('data_id', $dataId)
            ->where('keterangan', $kelas)
            ->first();

        if (!$existing) {
            Salesplan::create([
                'data_id' => $dataId,
                'keterangan' => $kelas,
                'status' => 'cold',
            ]);

            // Kosongkan field setelah dipindahkan
            $alumni->kelas_yang_akan_diikuti = null;
            $alumni->save();

            return redirect()->back()->with('success', 'Data berhasil dipindahkan ke Salesplan.');
        }

        return redirect()->back()->with('info', 'Data sudah ada di Salesplan.');
    }

    public function simpanKelas(Request $request, $id)
    {
        $request->validate([
            'kelas' => 'required|string'
        ]);

        $alumni = Alumni::findOrFail($id);
        $alumni->kelas_yang_akan_diikuti = $request->kelas;
        $alumni->save();

        return redirect()->back()->with('success', 'Kelas yang akan diikuti berhasil disimpan.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function updateInline(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:alumni,id',
            'field' => 'required|string',
            'value' => 'nullable'
        ]);

        $alumni = \App\Models\Alumni::findOrFail($request->id);

        // Pastikan field ini memang ada di tabel alumni
        if (in_array($request->field, [
            'nama',
            'leads',
            'provinsi_nama',
            'kota_nama',
            'nama_bisnis',
            'no_wa',
            'kendala'
        ])) {
            $alumni->{$request->field} = $request->value;
            $alumni->save();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Field tidak diizinkan'], 422);
    }

    public function updateKelas(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:alumni,id',
            'field' => 'required|string',
            'value' => 'nullable|array'
        ]);

        $alumni = \App\Models\Alumni::findOrFail($request->id);

        if (in_array($request->field, [
            'sudah_pernah_ikut_kelas_apa_saja',
            'kelas_yang_belum_diikuti_apa_saja'
        ])) {
            $alumni->{$request->field} = $request->value; // langsung array
            $alumni->save();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Field tidak diizinkan'], 422);
    }



    public function store(Request $request)
    {

        $alumni = new Alumni();
        $alumni->nama = $request->input('nama');

        // Enum field
        $alumni->leads = $request->input('leads');

        // Custom field
        if ($request->input('leads_custom') === null) {
            $alumni->leads_custom = '';
        } else {
            $alumni->leads_custom = $request->input('leads_custom');
        }

        $alumni->provinsi_id = $request->input('provinsi_id');
        $alumni->provinsi_nama = $request->input('provinsi_nama');
        $alumni->kota_id = $request->input('kota_id');
        $alumni->kota_nama = $request->input('kota_nama');
        $alumni->jenis_bisnis = $request->input('jenis_bisnis');
        $alumni->nama_bisnis = $request->input('nama_bisnis');
        $alumni->no_wa = $request->input('no_wa');
        $alumni->kendala = $request->input('kendala');

        // Ya atau tidak
        $alumni->kelas_id = $request->input('kelas_id');
        $alumni->sudah_pernah_ikut_kelas_apa_saja = json_encode($request->input('sudah_pernah_ikut_kelas_apa_saja', []));
        $alumni->kelas_yang_belum_diikuti_apa_saja = json_encode($request->input('kelas_yang_belum_diikuti_apa_saja', []));
        $alumni->created_by = Auth::user()->name;
        $alumni->save();

        return redirect()->route('admin.alumni.alumni')
            ->with('success', 'Data alumni berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch the specific alumni record by ID
        $kelas = Kelas::all(); // Fetch all classes for dropdowns or other purposes
        $alumni = Alumni::findOrFail($id);
        // Return a view to show the alumni details
        return view('admin.alumni.show', compact('alumni', 'kelas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the specific alumni record by ID for editing

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

        $alumni = Alumni::findOrFail($id);

        // Daftar tetap semua kelas MBC
        $kelasMBC = [
            'Sistemasi Bisnis',
            'Great Manager',
            'Scale-Up 10X',
            'Leadership',
            'CS dan Sales Jago Closing',
            'Repeat Order',
            'Keuangan',
            'HRD Mastery',
            'Public Speaking'
        ];

        // Ambil input kelas yang sudah diikuti, pastikan di-trim dan dipisah benar
        $sudah = explode(',', $request->input('sudah_pernah_ikut_kelas_apa_saja'));
        $sudah = array_map('trim', $sudah); // Hilangkan spasi
        $alumni->sudah_pernah_ikut_kelas_apa_saja = implode(', ', $sudah); // Simpan ulang rapi

        // Jika input kelas belum diikuti kosong, hitung otomatis
        if (!$request->filled('kelas_yang_belum_diikuti_apa_saja')) {
            $belum = array_diff($kelasMBC, $sudah); // Hitung kelas yang belum diikuti
            $alumni->kelas_yang_belum_diikuti_apa_saja = implode(', ', $belum);
        } else {
            $alumni->kelas_yang_belum_diikuti_apa_saja = $request->input('kelas_yang_belum_diikuti_apa_saja');
        }

        $alumni->save();

        // Tambahkan ke tabel salesplans per kelas yang sudah diikuti
        return redirect()->route('admin.alumni.alumni')->with('success', 'Data alumni & salesplan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the alumni record by ID
        $alumni = Alumni::findOrFail($id);
        // Delete the alumni record
        $alumni->delete();
        // Redirect back to the alumni index with a success message
        return redirect()->route('admin.alumni.alumni')->with('success', 'Alumni data has been deleted successfully.');
    }
}
