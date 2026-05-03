<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Data;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        
   
            
        // 1. Total User
        $totalUser = User::count();

        // 2. Total Database (semua data peserta)
        $totalDatabase = Data::count();

        // 3. Jumlah Kelas
        $totalKelas = Kelas::count();

        // 4. List CS + Total database masing-masing (berdasarkan created_by)
        $listCs = User::where('role', 'cs')
            ->select('users.id', 'users.name',
                DB::raw('(SELECT COUNT(*) FROM data WHERE data.created_by = users.id) AS total_database')
            )
            ->get();

        return view('administrator', compact(
            'totalUser',
            'totalDatabase',
            'totalKelas',
            'listCs'
        ));
    }
    
     public function salesplan($id)
    {
        $cs = User::findOrFail($id);
        $salesplan = $cs->salesplans; // relasi ke tabel salesplan
        return view('admin.cs.salesplan', compact('cs', 'salesplan'));
    }

    public function database($id)
    {
        $cs = User::findOrFail($id);
        $database = $cs->databases; // relasi ke tabel database peserta
        return view('admin.cs.database', compact('cs', 'database'));
    }
}
