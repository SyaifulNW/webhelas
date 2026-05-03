<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabaRugi;
use PDF;
use Illuminate\Support\Facades\Auth;

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

    public function labaRugi(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Fetch manual entries
        $manualQuery = LabaRugi::query();
        if ($bulan !== 'all') $manualQuery->where('bulan', $bulan);
        if ($tahun !== 'all') $manualQuery->where('tahun', $tahun);
        $manualData = $manualQuery->get();

        $pendapatan = $manualData->where('type', 'pendapatan');
        $biaya = $manualData->where('type', 'biaya');

        // 1. SMI (Manual Override)
        $smiQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%Muslim Indonesia%')
                  ->orWhere('nama_kelas', 'like', 'SMI - %');
            });
            
        $smiBreakdown = $smiQuery->with('kelas:id,nama_kelas')
            ->get()
            ->groupBy('kelas.nama_kelas')
            ->map(fn($group) => $group->sum('nominal'));

        // REVISION: Make SMI input manual, using LabaRugi model for storage
        $rowPendaftaran = $pendapatan->where('keterangan', 'Pendaftaran SMI')->first();
        $rowSpp = $pendapatan->where('keterangan', 'SPP SMI')->first();
        $rowTunggakan = $pendapatan->where('keterangan', 'Tunggakan SPP')->first();

        $totalSmiPendaftaran = $rowPendaftaran ? $rowPendaftaran->jumlah : ((clone $smiQuery)->sum('nominal') ?? 0);
        $totalSmiSpp = $rowSpp ? $rowSpp->jumlah : 0;
        $totalSmiTunggakan = $rowTunggakan ? $rowTunggakan->jumlah : 0;

        $totalSmi = $totalSmiPendaftaran + $totalSmiSpp;

        // 2. MBC (Everything else except SMI and Private)
        $mbcQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'not like', '%Muslim Indonesia%')
                  ->where('nama_kelas', 'not like', 'SMI - %')
                  ->where('nama_kelas', 'not like', '%Privat%');
            });
        
        $totalMbc = (clone $mbcQuery)->sum('nominal');
        $mbcBreakdown = $mbcQuery->with('kelas:id,nama_kelas')
            ->get()
            ->groupBy('kelas.nama_kelas')
            ->map(fn($group) => $group->sum('nominal'));

        // 3. Private Coaching
        $totalPrivate = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%Privat%');
            })
            ->sum('nominal');

        // 4. Approved Budget Proposals (Pengajuan Anggaran)
        $approvedAnggaran = \App\Models\PengajuanAnggaran::where('status', 'approved')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('tanggal_pengajuan', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('tanggal_pengajuan', $tahun); })
            ->get();

        $coachItems = [
            'Cicilan mobil Coach', 'Cicilan mobil teh Lia', 'Uang bulanan Fathin',
            'Gaji ART', 'Uang bulanan teh Lia', 'Cicilan 2 kartu kredit',
            'Paket paket ustad', 'Hutang Tajirw', 'Hutang pak Yusron', 'Biaya program Dela',
            'Biaya Pengeluaran Coach'
        ];

        $anggaranMapped = $approvedAnggaran->map(function($item) use ($coachItems) {
            $category = 'Biaya Lain-lain';
            $name = strtolower($item->nama_pengajuan);
            
            if (in_array($item->nama_pengajuan, $coachItems)) {
                $category = 'Pengeluaran Coach';
            } elseif (strpos($name, 'kuota') !== false || strpos($name, 'pulsa') !== false) {
                $category = 'Biaya Kuota';
            } elseif (strpos($name, 'listrik') !== false || strpos($name, 'token') !== false) {
                $category = 'Biaya Listrik';
            } elseif (strpos($name, 'air') !== false) {
                $category = 'Biaya Air';
            } elseif (strpos($name, 'bpjs') !== false) {
                $category = 'Biaya BPJS';
            } elseif (strpos($name, 'wifi') !== false || strpos($name, 'internet') !== false || strpos($name, 'indihome') !== false) {
                $category = 'Biaya Internet & Wifi';
            } elseif (strpos($name, 'maintenance') !== false || strpos($name, 'website') !== false) {
                $category = 'Biaya Maintenance Web';
            } elseif (strpos($name, 'gaji') !== false || strpos($name, 'upah') !== false) {
                $category = 'Biaya Gaji Karyawan';
            } elseif (strpos($name, 'iklan') !== false || strpos($name, 'ads') !== false || strpos($name, 'facebook') !== false || strpos($name, 'instagram') !== false) {
                $category = 'Biaya Iklan';
            } elseif (strpos($name, 'kebersihan') !== false || strpos($name, 'sampah') !== false || strpos($name, 'keamanan') !== false) {
                $category = 'Biaya Kebersihan & Keamanan';
            }

            return (object)[
                'id' => 'anggaran-' . $item->id,
                'tanggal' => $item->tanggal_pengajuan ? $item->tanggal_pengajuan->format('Y-m-d') : null,
                'type' => 'biaya',
                'parent_keterangan' => $category,
                'keterangan' => $item->nama_pengajuan,
                'jumlah' => $item->biaya_disetujui ?? $item->jumlah_biaya,
                'is_auto' => true
            ];
        });

        $biaya = $biaya->concat($anggaranMapped);

        // Fetch Classes for the selected month for auto-population of "Biaya Event Kelas"
        $kelasBulanIni = \App\Models\Kelas::when($bulan !== 'all' || $tahun !== 'all', function($q) use ($bulan, $tahun) {
            $q->where(function($sq) use ($bulan, $tahun) {
                if ($bulan !== 'all' && $tahun !== 'all') {
                    $sq->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                       ->orWhereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
                } elseif ($bulan !== 'all') {
                    $sq->whereMonth('tanggal_mulai', $bulan)->orWhereMonth('tanggal_selesai', $bulan);
                } elseif ($tahun !== 'all') {
                    $sq->whereYear('tanggal_mulai', $tahun)->orWhereYear('tanggal_selesai', $tahun);
                }
            });
        })->get();

        // Fetch All Classes for "Biaya Iklan" expand/collapse
        $semuaKelas = \App\Models\Kelas::orderBy('nama_kelas')->get();

        return view('admin.keuangan.laba-rugi', compact('pendapatan', 'biaya', 'bulan', 'tahun', 'totalSmi', 'totalSmiPendaftaran', 'totalSmiSpp', 'totalSmiTunggakan', 'smiBreakdown', 'totalMbc', 'mbcBreakdown', 'totalPrivate', 'kelasBulanIni', 'semuaKelas'));
    }

    public function getSmiDetails(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $namaKelas = $request->get('kelas');

        // 1. Peserta Baru (from SalesPlan closing this month)
        $salesQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) use ($namaKelas) {
                $q->where('nama_kelas', $namaKelas);
            });
        $baru = $salesQuery->select('id', 'nama', 'nominal')->get();

        // 2. SPP (from PesertaSmi checklist)
        $spp = collect();
        if ($bulan !== 'all' && $tahun !== 'all') {
            $calcPeriod = (int)($tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT));
            $currentPeriod = (int)date('Ym');

            // Assume SPP currently is mostly for 'Start-Up Muslim Indonesia' or matching class names
            if ($calcPeriod < $currentPeriod && (str_contains($namaKelas, 'Start-Up') || str_contains($namaKelas, 'Muslim Indonesia'))) {
                $colSpp = 'spp_' . (int)$bulan;
                $dateStart = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
                $dateEnd = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
                
                $spp = \App\Models\PesertaSmi::where($colSpp, '>', 0)
                    ->whereRaw("PERIOD_DIFF(?, EXTRACT(YEAR_MONTH FROM tanggal_masuk)) BETWEEN 0 AND 5", [$calcPeriod])
                    ->whereDate('tanggal_selesai', '>=', $dateStart)
                    ->whereNotIn('sales_plan_id', $baru->pluck('id'))
                    ->select('nama', "{$colSpp} as nominal")
                    ->get();
            }
        }

        return response()->json([
            'success' => true,
            'kelas' => $namaKelas,
            'baru' => $baru,
            'spp' => $spp,
            'total_baru' => $baru->sum('nominal'),
            'total_spp' => $spp->sum('nominal')
        ]);
    }

    public function exportLabaRugiPdf(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Fetch manual entries
        $manualQuery = LabaRugi::query();
        if ($bulan !== 'all') $manualQuery->where('bulan', $bulan);
        if ($tahun !== 'all') $manualQuery->where('tahun', $tahun);
        $manualData = $manualQuery->get();

        $pendapatan = $manualData->where('type', 'pendapatan');
        $biaya = $manualData->where('type', 'biaya');

        // 1. SMI
        $smiQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%Muslim Indonesia%')
                  ->orWhere('nama_kelas', 'like', 'SMI - %');
            });
            
        $smiBreakdown = $smiQuery->with('kelas:id,nama_kelas')
            ->get()
            ->groupBy('kelas.nama_kelas')
            ->map(fn($group) => $group->sum('nominal'));

        $totalSmiPendaftaran = (clone $smiQuery)->sum('nominal');
        $totalSmiSpp = 0;
        $totalSmiTunggakan = 0;
        
        // ADD SPP income in PDF total calculation
        if ($bulan !== 'all' && $tahun !== 'all') {
            $colSpp = 'spp_' . (int)$bulan;
            $dateStart = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
            $dateEnd = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            
            // Query period for PDF calc
            $calcPeriod = (int)($tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT));
            $currentPeriod = (int)date('Ym');

            if ($calcPeriod >= $currentPeriod) {
                $totalSmiSpp = 0;
                $totalSmiTunggakan = 0;
            } else {
                $totalSmiSpp = 0; // REVISION: Decouple from Laba Rugi
                $totalSmiTunggakan = 0; // REVISION: Decouple from Laba Rugi
            }
        }

        $totalSmi = $totalSmiPendaftaran + $totalSmiSpp;

        // 2. MBC
        $mbcQuery = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'not like', '%Muslim Indonesia%')
                  ->where('nama_kelas', 'not like', 'SMI - %')
                  ->where('nama_kelas', 'not like', '%Privat%');
            });

        $totalMbc = (clone $mbcQuery)->sum('nominal');
        $mbcBreakdown = $mbcQuery->with('kelas:id,nama_kelas')
            ->get()
            ->groupBy('kelas.nama_kelas')
            ->map(fn($group) => $group->sum('nominal'));

        $totalPrivate = \App\Models\SalesPlan::where('status', 'sudah_transfer')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('updated_at', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('updated_at', $tahun); })
            ->whereHas('kelas', function($q) {
                $q->where('nama_kelas', 'like', '%Privat%');
            })
            ->sum('nominal');

        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $namaBulan = $bulan === 'all' ? 'Semua Bulan' : ($months[$bulan] ?? 'Unknown');
        $tahunDisplay = $tahun === 'all' ? 'Semua Tahun' : $tahun;

        // Fetch Classes for the selected month
        $kelasBulanIni = \App\Models\Kelas::when($bulan !== 'all' || $tahun !== 'all', function($q) use ($bulan, $tahun) {
            $q->where(function($sq) use ($bulan, $tahun) {
                if ($bulan !== 'all' && $tahun !== 'all') {
                    $sq->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                       ->orWhereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
                } elseif ($bulan !== 'all') {
                    $sq->whereMonth('tanggal_mulai', $bulan)->orWhereMonth('tanggal_selesai', $bulan);
                } elseif ($tahun !== 'all') {
                    $sq->whereYear('tanggal_mulai', $tahun)->orWhereYear('tanggal_selesai', $tahun);
                }
            });
        })->get();

        // 4. Approved Budget Proposals (Pengajuan Anggaran)
        $approvedAnggaran = \App\Models\PengajuanAnggaran::where('status', 'approved')
            ->when($bulan !== 'all', function($q) use ($bulan) { $q->whereMonth('tanggal_pengajuan', $bulan); })
            ->when($tahun !== 'all', function($q) use ($tahun) { $q->whereYear('tanggal_pengajuan', $tahun); })
            ->get();

        $coachItems = [
            'Cicilan mobil Coach', 'Cicilan mobil teh Lia', 'Uang bulanan Fathin',
            'Gaji ART', 'Uang bulanan teh Lia', 'Cicilan 2 kartu kredit',
            'Paket paket ustad', 'Hutang Tajirw', 'Hutang pak Yusron', 'Biaya program Dela',
            'Biaya Pengeluaran Coach'
        ];

        $anggaranMapped = $approvedAnggaran->map(function($item) use ($coachItems) {
            $category = 'Biaya Lain-lain';
            $name = strtolower($item->nama_pengajuan);
            
            if (in_array($item->nama_pengajuan, $coachItems)) {
                $category = 'Pengeluaran Coach';
            } elseif (strpos($name, 'kuota') !== false) {
                $category = 'Biaya Kuota';
            } elseif (strpos($name, 'listrik') !== false) {
                $category = 'Biaya Listrik';
            } elseif (strpos($name, 'air') !== false) {
                $category = 'Biaya Air';
            } elseif (strpos($name, 'bpjs') !== false) {
                $category = 'Biaya BPJS';
            } elseif (strpos($name, 'wifi') !== false || strpos($name, 'internet') !== false) {
                $category = 'Biaya Internet & Wifi';
            } elseif (strpos($name, 'maintenance') !== false) {
                $category = 'Biaya Maintenance Web';
            } elseif (strpos($name, 'gaji') !== false) {
                $category = 'Biaya Gaji Karyawan';
            } elseif (strpos($name, 'iklan') !== false) {
                $category = 'Biaya Iklan';
            }

            return (object)[
                'id' => 'anggaran-' . $item->id,
                'tanggal' => $item->tanggal_pengajuan ? $item->tanggal_pengajuan->format('Y-m-d') : null,
                'type' => 'biaya',
                'parent_keterangan' => $category,
                'keterangan' => $item->nama_pengajuan,
                'jumlah' => $item->biaya_disetujui ?? $item->jumlah_biaya,
                'is_auto' => true
            ];
        });

        $biaya = $biaya->concat($anggaranMapped);

        // Fetch All Classes for PDF consistency
        $semuaKelas = \App\Models\Kelas::orderBy('nama_kelas')->get();

        $pdf = PDF::loadView('admin.keuangan.laba-rugi-pdf', compact(
            'pendapatan', 'biaya', 'bulan', 'tahun', 'totalSmi', 'totalSmiPendaftaran', 'totalSmiSpp', 'totalSmiTunggakan', 'smiBreakdown', 'totalMbc', 'mbcBreakdown', 'totalPrivate', 'namaBulan', 'kelasBulanIni', 'semuaKelas', 'tahunDisplay'
        ));
        
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('LaporanLabaRugi_' . $namaBulan . '_' . $tahunDisplay . '.pdf');
    }

    public function storeLabaRugi(Request $request)
    {
        // Allow Linda even if role is administrator (might be a mismatch on server)
        $user = Auth::user();
        $isAdmin = strtolower($user->role ?? '') === 'administrator';
        $isLinda = stripos($user->name ?? '', 'Linda') !== false;

        if ($isAdmin && !$isLinda) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menambah data.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        // Clean input: trim strings and convert empty to null for parent_keterangan
        $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT); // Ensure '01' instead of '1'
        $tahun = $request->tahun;
        $type = $request->type;
        $keterangan = trim($request->keterangan);
        $parent = $request->has('parent_keterangan') ? trim($request->parent_keterangan) : null;
        if ($parent === '') $parent = null;

        // Fallback for type if not set by AJAX
        if (!$type && $keterangan) {
            $pendapatanCats = ['Pendapatan Lainnya'];
            $type = in_array($keterangan, $pendapatanCats) ? 'pendapatan' : 'biaya';
        }

        try {
            $request->validate([
                'bulan' => 'required',
                'tahun' => 'required',
                'tanggal' => 'nullable|date',
                'type' => 'required|in:pendapatan,biaya',
                'keterangan' => 'required',
                'jumlah' => 'required|numeric'
            ]);

            // Robust search to handle NULL vs ""
            $query = LabaRugi::where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->where('keterangan', $keterangan)
                ->where('type', $type);

            if (empty($parent)) {
                $query->where(function($q) {
                    $q->whereNull('parent_keterangan')->orWhere('parent_keterangan', '');
                });
            } else {
                $query->where('parent_keterangan', $parent);
            }

            $labaRugi = $query->first();

            if ($labaRugi) {
                $labaRugi->update([
                    'tanggal' => $request->tanggal,
                    'jumlah' => $request->jumlah,
                    'parent_keterangan' => $parent, // Sync it to what we have now
                    'created_by' => Auth::id()
                ]);
            } else {
                $labaRugi = LabaRugi::create([
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tanggal' => $request->tanggal,
                    'type' => $type,
                    'keterangan' => $keterangan,
                    'parent_keterangan' => $parent,
                    'jumlah' => $request->jumlah,
                    'created_by' => Auth::id()
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $labaRugi
                ]);
            }

            return redirect()->back()->with('success', 'Data berhasil disimpan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroyLabaRugi($id)
    {
        $userName = Auth::user()->name;
        if (strtolower(Auth::user()->role) === 'administrator' && $userName !== 'Linda') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus data.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus data.');
        }

        $labaRugi = LabaRugi::findOrFail($id);
        $labaRugi->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        }

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function kas(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        $kas = \App\Models\Kas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('admin.keuangan.kas', compact('kas', 'bulan', 'tahun'));
    }

    public function storeKas(Request $request)
    {
        $user = Auth::user();
        // Check if user is Linda or similar permission logic
        $isLinda = stripos($user->name ?? '', 'Linda') !== false;
        $isManager = strtolower($user->role ?? '') === 'manager';
        $isAdmin = strtolower($user->role ?? '') === 'administrator';
        $isYasmin = $user->name === 'Yasmin';

        if (!$isLinda && !$isManager && !$isAdmin && !$isYasmin) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data.');
        }

        try {
            $request->validate([
                'tanggal' => 'required|date',
                'deskripsi' => 'required|string',
                'nominal' => 'required|numeric',
                'type' => 'required|in:masuk,keluar'
            ]);

            \App\Models\Kas::create([
                'tanggal' => $request->tanggal,
                'deskripsi' => $request->deskripsi,
                'nominal' => $request->nominal,
                'type' => $request->type,
                'created_by' => Auth::id()
            ]);

            return redirect()->back()->with('success', 'Kas berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan kas: ' . $e->getMessage());
        }
    }

    public function destroyKas($id)
    {
        $user = Auth::user();
        $isLinda = stripos($user->name ?? '', 'Linda') !== false;
        $isYasmin = $user->name === 'Yasmin';
        
        if (!$isLinda && !$isYasmin && strtolower($user->role) === 'administrator') {
             return redirect()->back()->with('error', 'Hanya Linda dan Yasmin yang dapat menghapus data kas.');
        }

        $kas = \App\Models\Kas::findOrFail($id);
        $kas->delete();

        return redirect()->back()->with('success', 'Kas berhasil dihapus');
    }
}
