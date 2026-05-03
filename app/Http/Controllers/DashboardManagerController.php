<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Data;
use App\Models\Inisiatif;
use App\Models\Kelas;
use App\Models\SalesPlan;

class DashboardManagerController extends Controller
{
    public function index()
    {
        // 1. Total Leads: kelas Start-Up Muda Indonesia
        $totalLeads = Data::where('kelas_id', '11')->count();

        // 2. Total program aktif
        $programAktif = Inisiatif::where('status', 'Progress')->count();

        // 3. Program selesai
        $programSelesai = Inisiatif::where('status', 'done')->count();

        // 4. Closing bulan ini dari Latifah & Tursia
        $closingBulanIni = SalesPlan::whereMonth('created_at', now()->month)
            ->whereIn('created_by', ['Latifah', 'Tursia'])
            ->count();

        return view('manager', compact(
            'totalLeads',
            'programAktif',
            'programSelesai',
            'closingBulanIni'
        ));
    }
}
