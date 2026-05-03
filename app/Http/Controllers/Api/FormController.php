<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Data;

class FormController extends Controller
{
    public function store(Request $request)
    {
        try {

            // 🔥 mapping chapter → user & wilayah
            $chapters = [
                'kaltim' => [
                    'user_id' => 31,
                    'nama' => 'Kalimantan Timur'
                ],
                'tangerang' => [
                    'user_id' => 47,
                    'nama' => 'Tangerang'
                ],
                'jakarta' => [
                    'user_id' => 45,
                    'nama' => 'Jakarta'
                ],
                'makassar' => [
                    'user_id' => 46,
                    'nama' => 'Makassar'
                ],
                'depok' => [
                    'user_id' => 44,
                    'nama' => 'Depok'
                ],
                'cirebon' => [
                    'user_id' => 43,
                    'nama' => 'Cirebon'
                ],
                'lampung' => [
                    'user_id' => 48,
                    'nama' => 'Lampung'
                ],
                'kediri' => [
                    'user_id' => 52,
                    'nama' => 'Karesidenan Kediri'
                ],
            ];

            // 🔥 ambil chapter dari Apps Script
            $key = strtolower($request->chapter);
            $chapterInfo = $chapters[$key] ?? null;

            if (!$chapterInfo) {
                return response()->json([
                    'error' => 'Chapter tidak ditemukan'
                ], 400);
            }

            // Get User Name for accountability
            $user = \App\Models\User::find($chapterInfo['user_id']);
            $createdBy = $user ? $user->name : $chapterInfo['user_id'];

            // Overrides for specific IDs to ensure correct name display as requested
            $overrides = [
                31 => 'FARID MANAF',
                43 => 'ALIF RINGGA PERSADA',
                44 => 'AGUNG H. WIBOWO',
                45 => 'Phingki Surya',
                46 => 'SARWANDI EKA SARBINI',
                47 => 'ASEP MAULIDIANSYAH',
                48 => 'JIHAN',
                52 => 'Yulia'
            ];

            if (isset($overrides[$chapterInfo['user_id']])) {
                $createdBy = $overrides[$chapterInfo['user_id']];
            }

            // 🔥 simpan data
            Data::create([
                'nama' => $request->nama ?? '-',
                'no_wa' => $request->no_wa ?? '-',
                'nama_bisnis' => $request->nama_bisnis ?? '-',

                'leads' => 'open_house',
                'kendala' => $request->harapan ?? '-',

                'created_by' => $createdBy, // ✅ Nama user (for display consistency)
                'created_by_role' => 'chapter',

                'chapter' => $chapterInfo['nama'], // ✅ wilayah

                'potensi' => 'ALL',
            ]);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            \Log::error('FORM ERROR: ' . $e->getMessage());

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}