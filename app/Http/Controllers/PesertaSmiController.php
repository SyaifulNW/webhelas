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
        $query = \App\Models\PesertaSmi::query();

        // Filter SPP
        if ($request->has('filter_spp_month') && $request->has('filter_spp_status') && $request->filter_spp_month && $request->filter_spp_status !== null) {
            $month = $request->filter_spp_month;
            $status = $request->filter_spp_status; // 1 = lunas, 0 = belum
            
            // Validate month 1-6
            if (in_array($month, range(1, 6))) {
                $query->where('spp_' . $month, $status);
            }
        }

        // sort pembayaran
        if ($request->has('sort_spp')) {
            $column = 'spp_' . $request->sort_spp; // e.g., spp_1
            $direction = $request->get('sort_dir', 'desc'); // desc = lunas (1) first
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $data = $query->get();
        // Get list of CS for dropdown (using roles if possible, or just all users for now)
        // Assuming CS have a role 'CS' or just get all users
        $listCs = \App\Models\User::whereIn('role', ['cs-mbc', 'cs-smi'])->orderBy('name')->get();

        return view('admin.peserta-smi.index', compact('data', 'listCs'));
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

        \App\Models\PesertaSmi::create($request->all() + [
            'created_by' => auth()->id(),
            'cs_name' => $csName
        ]);

        return redirect()->back()->with('success', 'Data Peserta SMI berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $peserta = \App\Models\PesertaSmi::findOrFail($id);
        
        // Validate date limit only if date changed
        if ($request->one_on_one_coaching && $request->one_on_one_coaching != $peserta->one_on_one_coaching) {
             $date = \Carbon\Carbon::parse($request->one_on_one_coaching)->format('Y-m-d');
             // Count existing excluding self
             $count = \App\Models\PesertaSmi::whereDate('one_on_one_coaching', $date)
                        ->where('id', '!=', $id)
                        ->count();
             if ($count >= 5) {
                 return redirect()->back()->with('error', 'Kuota One On One Coaching untuk tanggal ' . $date . ' sudah penuh (Maksimal 5 orang).');
             }
        }

        // Handle checkboxes not present in request (unchecked)
        $data = $request->all();
        foreach(['spp_1', 'spp_2', 'spp_3', 'spp_4', 'spp_5', 'spp_6'] as $spp) {
            $data[$spp] = $request->has($spp) ? 1 : 0;
        }
        
        if($request->closing_cs_id) {
            $user = \App\Models\User::find($request->closing_cs_id);
            $data['cs_name'] = $user ? $user->name : null;
        }

        $peserta->update($data);

        return redirect()->back()->with('success', 'Data Peserta SMI berhasil diperbarui.');
    }

    public function destroy($id)
    {
        \App\Models\PesertaSmi::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Data Peserta SMI berhasil dihapus.');
    }
}
