<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF; // barryvdh dompdf
use Carbon\Carbon;

class PenilaianCsController extends Controller
{
    public function index(Request $request)
    {
        // ================================
        //          DATA REAL
        // (Ganti dengan query DB Anda)
        // ================================
        $totalOmset = 52000000; 
        $nilaiOmset = 40;

        $closingPaket = 2;
        $nilaiClosingPaket = 25;

        $databaseBaru = 48;
        $nilaiDatabaseBaru = 27;

        // Total nilai keseluruhan
        $totalNilai = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru;


        // ================================
        //      DATA GRAFIK BULANAN
        // ================================
        $labels = [];
        $scores = [];

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $labels[] = $dt->format('M Y');

            // dummy score — nanti ambil dari DB
            $scores[] = rand(60, 95);
        }


        // ================================
        //     RIWAYAT PENILAIAN BULANAN
        // ================================
        $history = collect([
            [
                'month'   => Carbon::now()->format('M Y'),
                'total'   => $totalNilai,
                'omset'   => $totalOmset,
                'closing' => $closingPaket,
                'db_baru' => $databaseBaru
            ],
            [
                'month'   => Carbon::now()->subMonth()->format('M Y'),
                'total'   => rand(60,90),
                'omset'   => 48000000,
                'closing' => 1,
                'db_baru' => 40
            ],
            [
                'month'   => Carbon::now()->subMonth(2)->format('M Y'),
                'total'   => rand(60,90),
                'omset'   => 50000000,
                'closing' => 3,
                'db_baru' => 52
            ],
        ]);


        return view('admin.penilaian.index', compact(
            'totalOmset',
            'nilaiOmset',
            'closingPaket',
            'nilaiClosingPaket',
            'databaseBaru',
            'nilaiDatabaseBaru',
            'totalNilai',
            'labels',
            'scores',
            'history'
        ));
    }



    // ================================
    //      JSON HISTORY (opsional)
    // ================================
    public function history()
    {
        $history = [
            ['month' => Carbon::now()->format('M Y'), 'total' => 92],
            ['month' => Carbon::now()->subMonth()->format('M Y'), 'total' => 78],
            ['month' => Carbon::now()->subMonth(2)->format('M Y'), 'total' => 85],
        ];

        return response()->json($history);
    }



    // ================================
    //      EXPORT PDF PENILAIAN
    // ================================
    public function exportPdf(Request $request)
    {
        // Data sama seperti index (bisa pakai query real)
        $totalOmset = 52000000;
        $nilaiOmset = 40;

        $closingPaket = 2;
        $nilaiClosingPaket = 25;

        $databaseBaru = 48;
        $nilaiDatabaseBaru = 27;

        $totalNilai = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru;

        $data = compact(
            'totalOmset',
            'nilaiOmset',
            'closingPaket',
            'nilaiClosingPaket',
            'databaseBaru',
            'nilaiDatabaseBaru',
            'totalNilai'
        );

        $pdf = PDF::loadView('admin.penilaian.pdf', $data)
                    ->setPaper('a4', 'portrait');

        $filename = 'penilaian_cs_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
