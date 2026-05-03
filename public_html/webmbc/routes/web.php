<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataController;
use App\Http\Controllers\OngkirController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\SalesPlanController;
use App\Http\Controllers\DailyController;
use App\Http\Controllers\KoordinasiController;
use App\Http\Controllers\GanttChartController;
use App\Http\Controllers\PenilaianCsController;
use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AdvertisingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasKecilController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProgramKerjaController;
use App\Http\Controllers\AdsActivityController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PesertaSmiController;
use App\Http\Controllers\PembelajaranSiswaController;
use App\Http\Controllers\MarketingParticipantController;
use App\Http\Controllers\DashboardManagerController;
use App\Http\Controllers\Marketing\PenilaianController as MarketingPenilaianController;
use App\Http\Controllers\Admin\PenilaianController;
use App\Http\Controllers\Admin\SettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// ✅ Auth Routes (Laravel Default)
Auth::routes();

// ✅ Login Pages (Custom)
Route::get('/login-marketing', function () {
    return view('auth.login-marketing');
})->name('login.marketing');

Route::get('/login-smi', function () {
    return view('auth.login-SMI');
})->name('login.smi');

// ✅ Debug & Maintenance Routes (Admin Only)
Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/debug-db', function () {
        try {
            $table = 'peserta_smis';
            \Illuminate\Support\Facades\Schema::refresh(); 
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            $conn = \Illuminate\Support\Facades\DB::getDatabaseName();
            $dbHost = config('database.connections.' . config('database.default') . '.host');
            
            $html = "<h3>Diagnostik Koneksi Database</h3>";
            $html .= "<b>Database Aktif:</b> " . $conn . " (Host: $dbHost)<br>";
            $html .= "<b>Tabel:</b> " . $table . "<br><br>";
            
            $html .= "<div style='margin-bottom:20px;'>
                        <a href='".url('/clear-system-cache')."' style='padding:8px; background:orange; color:white; text-decoration:none; border-radius:4px;'>1. BERSIHKAN CACHE SISTEM</a>
                        &nbsp;
                        <a href='".url('/force-migrate')."' style='padding:8px; background:blue; color:white; text-decoration:none; border-radius:4px;'>2. PAKSA UPDATE DATABASE</a>
                      </div>";

            $html .= "<b>Kolom yang terdeteksi Laravel:</b><ol>";
            foreach($columns as $col) {
                $html .= "<li>$col</li>";
            }
            $html .= "</ol>";
            
            if (!in_array('nama_asli', $columns)) {
                $html .= "<div style='color:red; font-weight:bold; border:2px solid red; padding:10px;'>PERINGATAN: Kolom 'nama_asli' TIDAK TERDETEKSI oleh Laravel!</div>";
            } else {
                $html .= "</div>";
            }
            
            return $html;
        } catch (\Exception $e) {
            return "Error saat pengecekan: " . $e->getMessage();
        }
    });

    Route::get('/clear-system-cache', function() {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            return "Cache Berhasil Dibersihkan!<br><br><a href='/debug-db'>Kembali ke Debug</a>";
        } catch (\Exception $e) {
            return "Gagal Bersihkan Cache: " . $e->getMessage();
        }
    });

    Route::get('/force-migrate', function() {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return "Proses Migrasi Selesai!<br><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre><br><a href='/debug-db'>Kembali ke Debug</a>";
        } catch (\Exception $e) {
            return "Gagal Migrasi: " . $e->getMessage();
        }
    });
    
    Route::get('/run-migration', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return "Database berhasil diperbarui!<br><br>Output:<br>" . nl2br(\Illuminate\Support\Facades\Artisan::output());
        } catch (\Exception $e) {
            return "Gagal memperbarui database: " . $e->getMessage();
        }
    });
});

// ✅ Authenticated User Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboards
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/administrator', [AdminController::class, 'index'])->name('administrator');
    Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing');
    Route::get('/advertising', [AdvertisingController::class, 'index'])->name('advertising');
    Route::get('/manager', [DashboardManagerController::class, 'index'])->name('manager');
    Route::get('/hr', function () { return view('hr'); })->name('hr');

    // Notifikasi & Chat
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'show'])->name('notifikasi.show');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}', [ChatController::class, 'store'])->name('chat.store');

    // Database (Data)
    Route::prefix('admin/database')->name('admin.database.')->group(function () {
        Route::get('/database', [DataController::class, 'index'])->name('database');
        Route::get('/create', [DataController::class, 'create'])->name('create');
        Route::post('/store', [DataController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [DataController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DataController::class, 'update'])->name('update');
        Route::get('/{id}', [DataController::class, 'show'])->name('show');
        Route::delete('/{id}', [DataController::class, 'destroy'])->name('destroy');
        Route::get('/export-pdf-interaksi', [DataController::class, 'exportPdfInteraksi'])->name('export-pdf-interaksi');
        Route::post('/create-draft', [DataController::class, 'createDraft'])->name('createDraft');
        Route::get('/statistik', [DataController::class, 'getStatistik'])->name('statistik');
        Route::get('/filter', [DataController::class, 'filter'])->name('filter');
        Route::post('/update-inline', [DataController::class, 'updateInline'])->name('update-inline');
        Route::post('/update-location', [DataController::class, 'updateLocation'])->name('update-location');
        Route::post('/{id}/tambah-salesplan', [DataController::class, 'tambahkeSalesplan'])->name('tambahSalesplan');
    });
    Route::post('/admin/database/update-potensi/{id}', [DataController::class, 'updatePotensi']);
    Route::post('/update-sumber-leads/{id}', [DataController::class, 'updateSumberLeads'])->name('update.sumber.leads');

    // Alumni
    Route::prefix('admin/alumni')->name('admin.alumni.')->group(function () {
        Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni');
        Route::get('/create', [AlumniController::class, 'create'])->name('create');
        Route::post('/store', [AlumniController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AlumniController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AlumniController::class, 'update'])->name('update');
        Route::get('/{id}', [AlumniController::class, 'show'])->name('show');
        Route::delete('/{id}', [AlumniController::class, 'destroy'])->name('destroy');
        Route::post('/update-inline', [AlumniController::class, 'updateInline'])->name('update-inline');
        Route::post('/update-kelas', [AlumniController::class, 'updateKelas'])->name('update-kelas');
        Route::post('/to-salesplan/{id}', [AlumniController::class, 'toSalesplan'])->name('toSalesplan');
        Route::post('/{id}/simpan-kelas', [AlumniController::class, 'simpanKelas'])->name('simpanKelas');
    });
    Route::post('/data/pindah-ke-alumni/{id}', [DataController::class, 'pindahKeAlumni'])->name('data.pindahKeAlumni');

    // Sales Plan
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/salesplans', [SalesPlanController::class, 'index'])->name('salesplan.index');
        Route::get('/salesplan/{id}/edit', [SalesPlanController::class, 'edit'])->name('salesplan.edit');
        Route::put('/salesplan/{id}', [SalesPlanController::class, 'update'])->name('salesplan.update');
        Route::post('/salesplan/inline-update', [SalesPlanController::class, 'inlineUpdate'])->name('salesplan.inline-update');
        Route::post('/salesplan/update-status/{id}', [SalesPlanController::class, 'updateStatus']);
        Route::delete('/salesplan/{id}', [SalesPlanController::class, 'destroy'])->name('salesplan.destroy');
    });
    Route::post('/data/{id}/pindah-ke-salesplan', [DataController::class, 'pindahKeSalesPlan'])->name('data.pindahKeSalesPlan');
    Route::get('/salesplan/export', [SalesPlanController::class, 'export'])->name('salesplan.export');
    Route::put('/salesplan/{id}/fu/{fu}', [SalesPlanController::class, 'updateFU'])->name('admin.salesplan.update-fu');
    Route::resource('salesplan', SalesPlanController::class)->except(['index', 'edit', 'update', 'destroy']);
    Route::get('/sales-plan/{kelas}', [SalesPlanController::class, 'filter'])->name('salesplan.filter');

    // Daily Activities
    Route::prefix('admin/dailyactivity')->name('admin.dailyactivity.')->group(function () {
        Route::get('/index', [DailyController::class, 'index'])->name('index');
    });
    Route::post('/admin/daily-activity', [DailyController::class, 'store'])->name('admin.daily-activity.store');
    Route::get('/admin/daily-activity/export-pdf/{bulan}', [DailyController::class, 'exportPdf'])->name('admin.daily-activity.exportPdf');

    // Ads Activity
    Route::prefix('admin/adsbeauty')->name('admin.ads-activity.')->group(function () {
        Route::get('/index', [AdsActivityController::class, 'index'])->name('index');
        Route::get('/export-pdf', [AdsActivityController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/kelas-info/{id}', [AdsActivityController::class, 'getKelasInfo'])->name('get-kelas-info');
    });
    Route::post('/admin/ads-activity/update-ads', [AdsActivityController::class, 'updateAds'])->name('admin.ads-activity.update-ads');
    Route::delete('/admin/ads-activity/ads/{id}', [AdsActivityController::class, 'destroyAds'])->name('admin.ads-activity.destroy-ads');

    // Penilaian & Activity Reporting
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('penilaian-cs', [PenilaianCsController::class, 'index'])->name('penilaian-cs.index');
        Route::post('penilaian-cs', [PenilaianCsController::class, 'store'])->name('penilaian-cs.store');
        Route::post('penilaian-cs/export-pdf', [PenilaianCsController::class, 'exportPdf'])->name('penilaian-cs.exportPdf');
        Route::resource('penilaian', App\Http\Controllers\Admin\PenilaianCsController::class)->except(['index']);
        Route::get('/activity-cs', [AdminActivityController::class, 'index'])->name('activity-cs.index');
        Route::get('/activity-cs/export-pdf-bulanan', [AdminActivityController::class, 'viewPdfBulanan'])->name('activity-cs.viewPdfBulanan');
    });
    Route::get('/manager/penilaian-cs', [PenilaianCsController::class, 'managerIndex'])->name('manager.penilaian-cs.index');

    // Finance & Operasional
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/operasional', [DashboardController::class, 'operasional'])->name('operasional');
        Route::get('/keuangan', [DashboardController::class, 'keuangan'])->name('keuangan');
        Route::get('/keuangan/laba-rugi', [DashboardController::class, 'labaRugi'])->name('keuangan.laba-rugi');
        Route::post('/keuangan/laba-rugi', [DashboardController::class, 'storeLabaRugi'])->name('keuangan.laba-rugi.store');
        Route::get('/keuangan/laba-rugi/export-pdf', [DashboardController::class, 'exportLabaRugiPdf'])->name('keuangan.laba-rugi.export-pdf');
        Route::delete('/keuangan/laba-rugi/{id}', [DashboardController::class, 'destroyLabaRugi'])->name('keuangan.laba-rugi.destroy');
        Route::get('/keuangan/laba-rugi/smi-details', [DashboardController::class, 'getSmiDetails'])->name('keuangan.laba-rugi.smi-details');
        
        Route::get('/keuangan/kas', [DashboardController::class, 'kas'])->name('keuangan.kas');
        Route::post('/keuangan/kas', [DashboardController::class, 'storeKas'])->name('keuangan.kas.store');
        Route::delete('/keuangan/kas/{id}', [DashboardController::class, 'destroyKas'])->name('keuangan.kas.destroy');
        
        Route::resource('keuangan/pengajuan-anggaran', App\Http\Controllers\PengajuanAnggaranController::class)->names('keuangan.pengajuan-anggaran');
        Route::get('/keuangan/pengajuan-anggaran/export-pdf', [App\Http\Controllers\PengajuanAnggaranController::class, 'exportPDF'])->name('keuangan.pengajuan-anggaran.export-pdf');
        Route::post('/keuangan/pengajuan-anggaran/{id}/status', [App\Http\Controllers\PengajuanAnggaranController::class, 'updateStatus'])->name('keuangan.pengajuan-anggaran.update-status');
        Route::post('/keuangan/pengajuan-anggaran/{id}/upload-bukti', [App\Http\Controllers\PengajuanAnggaranController::class, 'uploadBukti'])->name('keuangan.pengajuan-anggaran.upload-bukti');
        
        Route::get('/keuangan/kas-kecil', [KasKecilController::class, 'index'])->name('keuangan.kas-kecil.index');
        Route::post('/keuangan/kas-kecil', [KasKecilController::class, 'store'])->name('keuangan.kas-kecil.store');
        Route::delete('/keuangan/kas-kecil/{id}', [KasKecilController::class, 'destroy'])->name('keuangan.kas-kecil.destroy');
        
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');
    });

    // Program Kerja (Marketing & Produksi)
    Route::get('/programkerja', [ProgramKerjaController::class, 'index'])->name('programkerja.index');
    Route::post('/programkerja', [ProgramKerjaController::class, 'store'])->name('programkerja.store');
    Route::delete('/programkerja/{id}', [ProgramKerjaController::class, 'destroy'])->name('programkerja.destroy');
    Route::post('/programkerja/update-inline', [ProgramKerjaController::class, 'updateInline'])->name('programkerja.updateInline');
    Route::get('/produksi/performance', [ProgramKerjaController::class, 'performance'])->name('produksi.performance');
    
    Route::post('/inisiatif', [ProgramKerjaController::class, 'storeInisiatif'])->name('inisiatif.store');
    Route::put('/inisiatif/{id}', [ProgramKerjaController::class, 'updateInisiatif'])->name('inisiatif.update');
    Route::delete('/inisiatif/{id}', [ProgramKerjaController::class, 'destroyInisiatif'])->name('inisiatif.destroy');
    Route::delete('/inisiatif/delete', [ProgramKerjaController::class, 'deleteInisiatif'])->name('inisiatif.delete');

    Route::post('/gantt/inisiatif/{id}/done', [GanttChartController::class, 'markDone'])->name('gantt.done');
    Route::get('/marketing/gantt-chart', [GanttChartController::class, 'index'])->name('gantt.index');

    // Marketing Participants
    Route::prefix('marketing-participants')->name('marketing-participants.')->group(function () {
        Route::get('/', [MarketingParticipantController::class, 'index'])->name('index');
        Route::post('/store', [MarketingParticipantController::class, 'store'])->name('store');
        Route::post('/update-inline', [MarketingParticipantController::class, 'updateInline'])->name('update-inline');
        Route::post('/move-to-cs', [MarketingParticipantController::class, 'moveToCs'])->name('move-to-cs');
        Route::delete('/{id}', [MarketingParticipantController::class, 'destroy'])->name('destroy');
    });

    // Marketing Operations
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('penilaian', [MarketingPenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('penilaian/export-pdf', [MarketingPenilaianController::class, 'exportPdf'])->name('penilaian.exportPdf');
        Route::post('store', [MarketingController::class, 'store'])->name('store');
        Route::post('update-inline', [MarketingController::class, 'updateInline'])->name('update-inline');
        Route::post('delete/{id}', [MarketingController::class, 'destroy'])->name('delete');
        Route::get('export-pdf', [MarketingController::class, 'exportPdf'])->name('export-pdf');
        Route::get('kpi-sosmed', [MarketingPenilaianController::class, 'kpiSosmed'])->name('penilaian.kpi_sosmed');
        Route::post('kpi-sosmed', [MarketingPenilaianController::class, 'storeKpiSosmed'])->name('penilaian.kpi_sosmed.store');
    });

    // Special Features
    Route::get('/pembelajaran-siswa', [PembelajaranSiswaController::class, 'index'])->name('pembelajaran.index');
    Route::resource('peserta-smi', PesertaSmiController::class);
    
    // Coordination & Messenging
    Route::get('/koordinasi/{id}', [KoordinasiController::class, 'show'])->name('koordinasi.show');
    Route::post('/koordinasi/komentar', [KoordinasiController::class, 'kirimKomentar'])->name('komentar.store');
    Route::get('/admin/cs/{id}', [AdminController::class, 'detailCS'])->name('admin.cs.detail');
    Route::prefix('admin/cs')->group(function () {
        Route::get('{id}/salesplan', [App\Http\Controllers\Admin\CSController::class, 'salesplan'])->name('admin.cs.salesplan');
        Route::get('{id}/database', [App\Http\Controllers\Admin\CSController::class, 'database'])->name('admin.cs.database');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{id}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    });
});

// ✅ Restricted Settings (Administrator Only)
Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
        Route::put('/settings/users/{id}', [SettingController::class, 'updateUser'])->name('settings.users.update');
        Route::delete('/settings/users/{id}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
        Route::post('/settings/target', [SettingController::class, 'updateTarget'])->name('settings.target.update');
        Route::post('/settings/menus/toggle', [SettingController::class, 'toggleMenu'])->name('settings.menus.toggle');
        Route::post('/settings/role-menus/update', [SettingController::class, 'updateRoleMenu'])->name('settings.role-menus.update');
    });
});

// ✅ Additional Misc Routes
Route::get('/ongkir/provinsi', [OngkirController::class, 'getProvinsi'])->name('ongkir.provinsi');
Route::get('/ongkir/kota', [OngkirController::class, 'getKota'])->name('ongkir.kota');
Route::get('/wilayah/provinsi', [WilayahController::class, 'getProvinces']);
Route::get('/wilayah/kota/{id}', [WilayahController::class, 'getCities']);
