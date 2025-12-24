<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function operasional()
    {
        // Dummy data for Operasional
        $stats = [
            'proyek_berjalan' => 12,
            'karyawan_aktif' => 45,
            'tiket_pending' => 5,
            'inventory' => 128
        ];

        return view('admin.operasional', compact('stats'));
    }

    public function keuangan()
    {
        // Dummy data for Keuangan
        $stats = [
            'pemasukan' => 'Rp 150.000.000',
            'pengeluaran' => 'Rp 45.000.000',
            'profit' => 'Rp 105.000.000',
            'pending_invoice' => 3
        ];
        
        return view('admin.keuangan', compact('stats'));
    }
}
