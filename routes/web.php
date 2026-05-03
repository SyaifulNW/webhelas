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
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\MarketingParticipantController;
use App\Http\Controllers\DashboardManagerController;
use App\Http\Controllers\Marketing\PenilaianController as MarketingPenilaianController;
use App\Http\Controllers\Admin\PenilaianController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Admin\AdminWalletController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/form-m1t/{identifier}', [DataController::class, 'formM1t'])->name('form.m1t');
Route::post('/form-m1t/store', [DataController::class, 'storeFormM1t'])->name('form.m1t.store');

// ✅ Auth Routes
Auth::routes(['register' => false]);

// ✅ Profile Routes
Route::get('/profile', 'App\Http\Controllers\ProfileController@index')->name('profile.index');
Route::put('/profile/update', 'App\Http\Controllers\ProfileController@update')->name('profile.update');

// ✅ Reseller Registration (Disabled)
// Route::get('/register/reseller', [App\Http\Controllers\Auth\ResellerRegisterController::class, 'showRegistrationForm'])->name('reseller.register');
// Route::post('/register/reseller', [App\Http\Controllers\Auth\ResellerRegisterController::class, 'register']);

// ✅ Custom Login Pages
Route::get('/login-marketing', function () {
    return view('auth.login-marketing');
})->name('login.marketing');

Route::get('/login-smi', function () {
    return view('auth.login-SMI');
})->name('login.smi');

// ✅ Maintenance & Debug (Administrator Only)
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

            $html .= "<div>
                        <a href='" . url('/clear-system-cache') . "'>1. BERSIHKAN SEMUA CACHE</a>
                        &nbsp;
                        <a href='" . url('/force-migrate') . "'>2. PAKSA UPDATE DATABASE</a>
                      </div>";
            return $html;
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
        // Recalculate real-time balance before withdrawal
        $totalEarnings = \App\Services\EarningsService::calculateTotalEarnings($user->id);
        $totalWithdrawn = $wallet->transactions()
            ->where('type', 'withdrawal')
            ->whereIn('status', ['success', 'pending'])
            ->sum('amount');
        $availableBalance = $totalEarnings - $totalWithdrawn;

        if ($availableBalance < $request->amount) {
            return back()->with('error', 'Saldo tidak mencukupi untuk penarikan ini.');
        }
    });

    Route::get('/clear-system-cache', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            return "Semua Cache (Config, Route, View) Berhasil Dibersihkan!<br><br><a href='/debug-db'>Kembali ke Debug</a>";
        } catch (\Exception $e) {
            return "Gagal Berhasil Bersihkan Cache: " . $e->getMessage();
        }
    });

    Route::get('/force-migrate', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return "Migrasi Berhasil!<br><br><a href='/debug-db'>Kembali ke Debug</a>";
        } catch (\Exception $e) {
            return "Gagal Migrasi: " . $e->getMessage();
        }
    });
});

// ✅ Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboards
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/pendapatan-lainnya/store', [HomeController::class, 'storePendapatanLainnya'])->name('pendapatan.lainnya.store');
    Route::get('/hr', function () {
        return view('hr');
    })->name('hr');
    Route::get('/administrator', [AdminController::class, 'index'])->name('administrator');
    Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing');
    Route::get('/advertising', [AdvertisingController::class, 'index'])->name('advertising');
    Route::get('/manager', [DashboardManagerController::class, 'index'])->name('manager');
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');

    // Chat & Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'show'])->name('notifikasi.show');
    Route::get('/chat/{id}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{id}', [ChatController::class, 'store'])->name('chat.store');

    // Admin Group (Prefix & Name 'admin.')
    Route::prefix('admin')->name('admin.')->group(function () {

        // Database / Prospek
        Route::get('database/export-riwayat-pdf', [DataController::class, 'exportPdfInteraksi'])->name('database.export-pdf-interaksi');
        Route::prefix('database')->name('database.')->group(function () {
            Route::get('/', [DataController::class, 'index'])->name('database');
            Route::get('/create', [DataController::class, 'create'])->name('create');
            Route::post('/store', [DataController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [DataController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DataController::class, 'update'])->name('update');
            Route::get('/{id}', [DataController::class, 'show'])->name('show');
            Route::delete('/{id}', [DataController::class, 'destroy'])->name('destroy');
            Route::post('/create-draft', [DataController::class, 'createDraft'])->name('createDraft');
            Route::get('/statistik', [DataController::class, 'getStatistik'])->name('statistik');
            Route::get('/filter', [DataController::class, 'filter'])->name('filter');
            Route::post('/update-inline', [DataController::class, 'updateInline'])->name('update-inline');
            Route::post('/update-location', [DataController::class, 'updateLocation'])->name('update-location');
            Route::post('/update-status-direct', [DataController::class, 'updateStatusDirect'])->name('update-status-direct');
            Route::post('/{id}/tambah-salesplan', [DataController::class, 'pindahkesalesplan'])->name('tambahSalesplan');
            Route::post('/{id}/toggle-no-potensi', [DataController::class, 'toggleNoPotensi'])->name('toggleNoPotensi');
            Route::post('/reuse-data', [DataController::class, 'reuseData'])->name('reuse-data');
        });
        Route::post('/database/update-potensi/{id}', [DataController::class, 'updatePotensi']);
        Route::post('/update-sumber-leads/{id}', [DataController::class, 'updateSumberLeads'])->name('update.sumber.leads');

        // Alumni
        Route::prefix('alumni')->name('alumni.')->group(function () {
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
        });

        // Sales Plan
        Route::get('/salesplans', [SalesPlanController::class, 'index'])->name('salesplan.index');
        Route::get('/salesplan/{id}/edit', [SalesPlanController::class, 'edit'])->name('salesplan.edit');
        Route::put('/salesplan/{id}', [SalesPlanController::class, 'update'])->name('salesplan.update');
        Route::post('/salesplan/inline-update', [SalesPlanController::class, 'inlineUpdate'])->name('salesplan.inline-update');
        Route::post('/salesplan/update-status/{id}', [SalesPlanController::class, 'updateStatus'])->name('salesplan.update-status');
        Route::delete('/salesplan/{id}', [SalesPlanController::class, 'destroy'])->name('salesplan.destroy');
        Route::get('/salesplan/export', [SalesPlanController::class, 'export'])->name('salesplan.export');
        Route::get('/salesplan/export-pdf', [SalesPlanController::class, 'exportPdf'])->name('salesplan.export-pdf');
        Route::put('/salesplan/{id}/fu/{fu}', [SalesPlanController::class, 'updateFU'])->name('salesplan.update-fu');
        Route::get('/sales-plan/filter/{kelas}', [SalesPlanController::class, 'filter'])->name('salesplan.filter');
        Route::post('/salesplan/reset-fu/{id}', [SalesPlanController::class, 'resetFu'])->name('salesplan.reset-fu');
        Route::get('/salesplan/tasks-today', [SalesPlanController::class, 'getTasksToday'])->name('salesplan.tasks-today');
        Route::post('/salesplan/update-selected-months', [SalesPlanController::class, 'updateSelectedMonths'])->name('salesplan.update-selected-months');

        // FINANCE (Critical Routes)
        Route::get('/keuangan', [DashboardController::class, 'keuangan'])->name('keuangan');
        Route::get('/keuangan/laba-rugi', [DashboardController::class, 'labaRugi'])->name('keuangan.laba-rugi');
        Route::post('/keuangan/laba-rugi', [DashboardController::class, 'storeLabaRugi'])->name('keuangan.laba-rugi.store');
        Route::get('/keuangan/laba-rugi/export-pdf', [DashboardController::class, 'exportLabaRugiPdf'])->name('keuangan.laba-rugi.export-pdf');
        Route::delete('/keuangan/laba-rugi/{id}', [DashboardController::class, 'destroyLabaRugi'])->name('keuangan.laba-rugi.destroy');
        Route::get('/keuangan/laba-rugi/smi-details', [DashboardController::class, 'getSmiDetails'])->name('keuangan.laba-rugi.smi-details');
        Route::get('/keuangan/zakat', [DashboardController::class, 'zakat'])->name('keuangan.zakat');

        Route::get('/keuangan/kas', [DashboardController::class, 'kas'])->name('keuangan.kas');
        Route::post('/keuangan/kas', [DashboardController::class, 'storeKas'])->name('keuangan.kas.store');
        Route::delete('/keuangan/kas/{id}', [DashboardController::class, 'destroyKas'])->name('keuangan.kas.destroy');

        Route::get('/keuangan/kas-kecil', [KasKecilController::class, 'index'])->name('keuangan.kas-kecil.index');
        Route::get('/keuangan/kas-kecil/export-pdf', [KasKecilController::class, 'exportPdf'])->name('keuangan.kas-kecil.export-pdf');
        Route::post('/keuangan/kas-kecil/{id}/upload-bukti', [KasKecilController::class, 'uploadBukti'])->name('keuangan.kas-kecil.upload-bukti');
        Route::post('/keuangan/kas-kecil', [KasKecilController::class, 'store'])->name('keuangan.kas-kecil.store');
        Route::delete('/keuangan/kas-kecil/{id}', [KasKecilController::class, 'destroy'])->name('keuangan.kas-kecil.destroy');

        Route::get('/keuangan/pengajuan-anggaran', [App\Http\Controllers\PengajuanAnggaranController::class, 'index'])->name('keuangan.pengajuan-anggaran');
        Route::post('/keuangan/pengajuan-anggaran', [App\Http\Controllers\PengajuanAnggaranController::class, 'store'])->name('keuangan.pengajuan-anggaran.store');
        Route::put('/keuangan/pengajuan-anggaran/{id}', [App\Http\Controllers\PengajuanAnggaranController::class, 'update'])->name('keuangan.pengajuan-anggaran.update');
        Route::post('/keuangan/pengajuan-anggaran/{id}/update', [App\Http\Controllers\PengajuanAnggaranController::class, 'update'])->name('keuangan.pengajuan-anggaran.update-post');
        Route::delete('/keuangan/pengajuan-anggaran/{id}', [App\Http\Controllers\PengajuanAnggaranController::class, 'destroy'])->name('keuangan.pengajuan-anggaran.destroy');
        Route::post('/keuangan/pengajuan-anggaran/{id}/delete', [App\Http\Controllers\PengajuanAnggaranController::class, 'destroy'])->name('keuangan.pengajuan-anggaran.delete-post');
        Route::get('/keuangan/pengajuan-anggaran/export-pdf', [App\Http\Controllers\PengajuanAnggaranController::class, 'exportPDF'])->name('keuangan.pengajuan-anggaran.export-pdf');
        Route::post('/keuangan/pengajuan-anggaran/{id}/status', [App\Http\Controllers\PengajuanAnggaranController::class, 'updateStatus'])->name('keuangan.pengajuan-anggaran.update-status');
        Route::post('/keuangan/pengajuan-anggaran/{id}/upload-bukti', [App\Http\Controllers\PengajuanAnggaranController::class, 'uploadBukti'])->name('keuangan.pengajuan-anggaran.upload-bukti');

        // Monitoring & Activity
        Route::get('/operasional', [DashboardController::class, 'operasional'])->name('operasional');
        Route::get('/dailyactivity/index', [DailyController::class, 'index'])->name('dailyactivity.index');
        Route::post('/daily-activity', [DailyController::class, 'store'])->name('daily-activity.store');
        Route::get('/daily-activity/export-pdf/{bulan}', [DailyController::class, 'exportPdf'])->name('daily-activity.exportPdf');

        Route::get('/activity-cs', [AdminActivityController::class, 'index'])->name('activity-cs.index');
        Route::get('/activity-cs/export-pdf-bulanan', [AdminActivityController::class, 'viewPdfBulanan'])->name('activity-cs.viewPdfBulanan');

        Route::get('/penilaian-cs', [PenilaianCsController::class, 'index'])->name('penilaian-cs.index');
        Route::post('/penilaian-cs', [PenilaianCsController::class, 'store'])->name('penilaian-cs.store');
        Route::post('/penilaian-cs/export-pdf', [PenilaianCsController::class, 'exportPdf'])->name('penilaian-cs.exportPdf');
        Route::resource('penilaian', App\Http\Controllers\Admin\PenilaianCsController::class)->except(['index'])->names('penilaian');

        // Settings (Restricted inside group)
        Route::middleware(['role:administrator'])->group(function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
            Route::put('/settings/users/{id}', [SettingController::class, 'updateUser'])->name('settings.users.update');
            Route::delete('/settings/users/{id}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
            Route::post('/settings/target', [SettingController::class, 'updateTarget'])->name('settings.target.update');
            Route::post('/settings/menus/toggle', [SettingController::class, 'toggleMenu'])->name('settings.menus.toggle');
            Route::post('/settings/role-menus/update', [SettingController::class, 'updateRoleMenu'])->name('settings.role-menus.update');
        });

        // Misc Admin
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{id}/reply', [MessageController::class, 'reply'])->name('messages.reply');

        Route::get('/cs/{id}/salesplan', [AdminController::class, 'salesplan'])->name('cs.salesplan');
        Route::get('/cs/{id}/database', [AdminController::class, 'database'])->name('cs.database');
        Route::get('/cs/{id}', [AdminController::class, 'detailCS'])->name('cs.detail');

        // Wallet Management (Admin)
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [AdminWalletController::class, 'index'])->name('index');
            Route::get('/transactions', [AdminWalletController::class, 'transactions'])->name('transactions');
            Route::post('/withdrawal/{id}/process', [AdminWalletController::class, 'processWithdrawal'])->name('withdrawal.process');
            Route::delete('/transaction/{id}', [AdminWalletController::class, 'destroyTransaction'])->name('transaction.destroy');
        });
    });

    // Wallet Features (User: Chapter / Reseller)
    Route::middleware(['role:chapter,reseller,agen,chapter_tangerang,chapter_cirebon,chapter_jakarta,chapter_depok,chapter_kaltim,chapter_makassar,chapter_lampung'])->group(function () {
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [WalletController::class, 'index'])->name('index');
            Route::get('/history', [WalletController::class, 'history'])->name('history');
            Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
            Route::delete('/transaction/{id}', [WalletController::class, 'destroy'])->name('transaction.destroy');
        });
    });

    // Program Kerja
    Route::get('/programkerja', [ProgramKerjaController::class, 'index'])->name('programkerja.index');
    Route::post('/programkerja', [ProgramKerjaController::class, 'store'])->name('programkerja.store');
    Route::delete('/programkerja/{id}', [ProgramKerjaController::class, 'destroy'])->name('programkerja.destroy');
    Route::post('/programkerja/update-inline', [ProgramKerjaController::class, 'updateInline'])->name('programkerja.updateInline');
    Route::get('/produksi/performance', [ProgramKerjaController::class, 'performance'])->name('produksi.performance');
    Route::post('/inisiatif', [ProgramKerjaController::class, 'storeInisiatif'])->name('inisiatif.store');
    Route::put('/inisiatif/{id}', [ProgramKerjaController::class, 'updateInisiatif'])->name('inisiatif.update');
    Route::delete('/inisiatif', [ProgramKerjaController::class, 'destroyInisiatif'])->name('inisiatif.delete');

    Route::post('/gantt/inisiatif/{id}/done', [GanttChartController::class, 'markDone'])->name('gantt.done');
    Route::get('/marketing/gantt-chart', [GanttChartController::class, 'index'])->name('gantt.index');

    // Marketing Feature
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/penilaian', [MarketingPenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/penilaian/export-pdf', [MarketingPenilaianController::class, 'exportPdf'])->name('penilaian.exportPdf');
        Route::post('/store', [MarketingController::class, 'store'])->name('store');
        Route::post('/update-inline', [MarketingController::class, 'updateInline'])->name('update-inline');
        Route::post('/delete/{id}', [MarketingController::class, 'destroy'])->name('delete');
        Route::get('/export-pdf', [MarketingController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/kpi-sosmed', [MarketingPenilaianController::class, 'kpiSosmed'])->name('penilaian.kpi_sosmed');
        Route::post('/kpi-sosmed', [MarketingPenilaianController::class, 'storeKpiSosmed'])->name('penilaian.kpi_sosmed.store');
    });

    Route::prefix('marketing-participants')->name('marketing-participants.')->group(function () {
        Route::get('/', [MarketingParticipantController::class, 'index'])->name('index');
        Route::post('/store', [MarketingParticipantController::class, 'store'])->name('store');
        Route::post('/update-inline', [MarketingParticipantController::class, 'updateInline'])->name('update-inline');
        Route::post('/move-to-cs', [MarketingParticipantController::class, 'moveToCs'])->name('move-to-cs');
        Route::delete('/{id}', [MarketingParticipantController::class, 'destroy'])->name('destroy');
    });

    // Others
    Route::get('/manager/penilaian-cs', [PenilaianCsController::class, 'managerIndex'])->name('manager.penilaian-cs.index');
    Route::get('/koordinasi/{id}', [KoordinasiController::class, 'show'])->name('koordinasi.show');
    Route::post('/koordinasi/komentar', [KoordinasiController::class, 'kirimKomentar'])->name('komentar.store');
    Route::get('/pembelajaran-siswa', [PembelajaranSiswaController::class, 'index'])->name('pembelajaran.index');
    Route::post('/peserta-smi/{id}/restore', [PesertaSmiController::class, 'restore'])->name('peserta-smi.restore');
    Route::post('/peserta-smi/{id}/upload-bukti', [PesertaSmiController::class, 'uploadBuktiTransfer'])->name('peserta-smi.upload-bukti');
    Route::post('/peserta-smi/{id}/approve-bukti', [PesertaSmiController::class, 'approveBuktiTransfer'])->name('peserta-smi.approve-bukti');
    Route::resource('peserta-smi', PesertaSmiController::class)->names('peserta-smi');

    // Ads Activity
    Route::prefix('admin/adsbeauty')->name('admin.ads-activity.')->group(function () {
        Route::get('/index', [AdsActivityController::class, 'index'])->name('index');
        Route::get('/export-pdf', [AdsActivityController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/kelas-info/{id}', [AdsActivityController::class, 'getKelasInfo'])->name('get-kelas-info');
    });
    Route::post('/admin/ads-activity/update-ads', [AdsActivityController::class, 'updateAds'])->name('admin.ads-activity.update-ads');
    Route::delete('/admin/ads-activity/ads/{id}', [AdsActivityController::class, 'destroyAds'])->name('admin.ads-activity.destroy-ads');

    // Chapter Feature
    Route::middleware(['role:chapter'])->prefix('chapter')->name('chapter.')->group(function () {
        Route::get('/reseller', [App\Http\Controllers\Chapter\ResellerController::class, 'index'])->name('reseller.index');
        Route::get('/reseller/{id}', [App\Http\Controllers\Chapter\ResellerController::class, 'show'])->name('reseller.show');
        Route::post('/reseller', [App\Http\Controllers\Chapter\ResellerController::class, 'store'])->name('reseller.store');
        Route::put('/reseller/{id}', [App\Http\Controllers\Chapter\ResellerController::class, 'update'])->name('reseller.update');
        Route::delete('/reseller/{id}', [App\Http\Controllers\Chapter\ResellerController::class, 'destroy'])->name('reseller.destroy');
        
        // Setting for Chapter
        Route::get('/setting', [App\Http\Controllers\Chapter\SettingController::class, 'index'])->name('setting.index');
        Route::post('/setting/store-reseller', [App\Http\Controllers\Chapter\SettingController::class, 'storeReseller'])->name('setting.store-reseller');
        Route::put('/setting/update-reseller/{id}', [App\Http\Controllers\Chapter\SettingController::class, 'updateReseller'])->name('setting.update-reseller');
    });

    Route::middleware(['role:reseller'])->prefix('reseller')->name('reseller.')->group(function () {
        Route::get('/setting', [App\Http\Controllers\Reseller\SettingController::class, 'index'])->name('setting.index');
        Route::get('/setting/{id}', [App\Http\Controllers\Reseller\SettingController::class, 'show'])->name('setting.show');
        Route::post('/setting/store-reseller', [App\Http\Controllers\Reseller\SettingController::class, 'storeReseller'])->name('setting.store-reseller');
        Route::put('/setting/update-reseller/{id}', [App\Http\Controllers\Reseller\SettingController::class, 'updateReseller'])->name('setting.update-reseller');
        Route::delete('/setting/destroy-reseller/{id}', [App\Http\Controllers\Reseller\SettingController::class, 'destroyReseller'])->name('setting.destroy-reseller');
    });
});

Route::get('/data/{id}/pindah-ke-salesplan', [DataController::class, 'pindahkesalesplan']);
Route::post('/data/{id}/pindah-ke-salesplan', [DataController::class, 'pindahkesalesplan']);
Route::get('/pindah-ke-alumni/{id}', [DataController::class, 'alumni'])->name('data.pindahKeAlumni');
Route::get('/pindah-ke-salesplan/{id}', [DataController::class, 'pindahkesalesplan'])->name('data.pindahKeSalesPlan');
Route::delete('/admin/database/delete/{id}', [DataController::class, 'destroy'])->name('delete-database');

Route::get('/ongkir/provinsi', [OngkirController::class, 'getProvinsi'])->name('ongkir.provinsi');
Route::get('/ongkir/kota', [OngkirController::class, 'getKota'])->name('ongkir.kota');
Route::get('/wilayah/provinsi', [WilayahController::class, 'getProvinces']);
Route::get('/wilayah/kota/{id}', [WilayahController::class, 'getCities']);
