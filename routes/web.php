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



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login-marketing', function () {
    return view('auth.login-marketing');
})->name('login.marketing');


Route::get('/login-smi', function () {
    return view('auth.login-SMI');
})->name('login.smi');


// ✅ Halaman untuk Administrator
Route::get('/administrator', [App\Http\Controllers\AdministratorController::class, 'index'])
    ->middleware('auth')
    ->name('administrator');
    
   Route::get('/marketing', function () {
    return view('marketing');
})->middleware(['auth'])->name('marketing');

    
   Route::get('/hr', function () {
    return view('hr');
})->middleware(['auth'])->name('hr');

// ✅ Halaman untuk Advertising
Route::get('/advertising', [App\Http\Controllers\AdvertisingController::class, 'index'])
    ->middleware('auth')
    ->name('advertising');


// ✅ Halaman untuk CS & Marketing
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');


Route::get('/manager', [App\Http\Controllers\DashboardManagerController::class, 'index'])
    ->middleware('auth')
    ->name('manager');

// Activity CS (Accessible by Admin, Manager, etc. - controller handles logic)
// Moved here to prevent accidental role:administrator inheritance
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/activity-cs', [AdminActivityController::class, 'index'])->name('admin.activity-cs.index');
    Route::get('/activity-cs/export-pdf-bulanan', [AdminActivityController::class, 'viewPdfBulanan'])->name('admin.activity-cs.viewPdfBulanan');
});

Route::get('/manager/penilaian-cs', [App\Http\Controllers\PenilaianCsController::class, 'managerIndex'])
    ->middleware('auth')
    ->name('manager.penilaian-cs.index');





use App\Http\Controllers\PenilaianCsController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('penilaian-cs', [PenilaianCsController::class, 'index'])
        ->name('penilaian-cs.index');
    Route::post('penilaian-cs', [PenilaianCsController::class, 'store'])
        ->name('penilaian-cs.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    // PENILAIAN CS
    Route::resource('penilaian', App\Http\Controllers\Admin\PenilaianCsController::class);

});



// routes/web.php

use App\Http\Controllers\Admin\PenilaianController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
    Route::get('penilaian/history', [PenilaianController::class, 'history'])->name('penilaian.history');
    Route::get('penilaian/export-pdf', [PenilaianController::class, 'exportPdf'])->name('penilaian.exportPdf');
});




// Route::middleware(['auth'])->group(function () {
//     Route::prefix('marketing/program-kerja')->group(function () {
//         Route::get('/', [ProgramKerjaController::class, 'index'])->name('programkerja.index');
//         Route::post('/store', [ProgramKerjaController::class, 'store'])->name('programkerja.store');
//         Route::delete('/delete/{id}', [ProgramKerjaController::class, 'destroy'])->name('programkerja.destroy');
//     });
// });

Route::post('/programkerja/update-inline', [ProgramKerjaController::class, 'updateInline'])
    ->name('programkerja.update-inline');





// ✅ Auth bawaan Laravel
Auth::routes();



// Database Routes
Route::get('/admin/database/database', [App\Http\Controllers\DataController::class, 'index'])->name('admin.database.database');
Route::get('/admin/database/create', [App\Http\Controllers\DataController::class, 'create'])->name('admin.database.create');
Route::post('/admin/database/store', [App\Http\Controllers\DataController::class, 'store'])->name('admin.database.store');
Route::get('/admin/database/{id}/edit', [App\Http\Controllers\DataController::class, 'edit'])->name('admin.database.edit');
Route::put('/admin/database/{id}', [App\Http\Controllers\DataController::class, 'update'])->name('admin.database.update');
Route::delete('/admin/database/{id}', [App\Http\Controllers\DataController::class, 'destroy'])->name('delete-database');
Route::get('/admin/database/{id}', [App\Http\Controllers\DataController::class, 'show'])->name('admin.database.show');

// Potensi

Route::post('/admin/database/update-potensi/{id}', [DataController::class, 'updatePotensi']);

Route::post('/admin/database/create-draft', [DataController::class, 'createDraft'])->name('admin.database.createDraft');



// Routes baru + Alumni
// routes/web.php
Route::get('/admin/database/database', [DataController::class, 'peserta_baru'])->name('admin.database.database');
Route::get('/admin/alumni/alumni', [DataController::class, 'alumni'])->name('admin.alumni.alumni');


// Ongkir Routes
Route::get('/ongkir/provinsi', [OngkirController::class, 'getProvinsi'])->name('ongkir.provinsi');
Route::get('/ongkir/kota', [OngkirController::class, 'getKota'])->name('ongkir.kota');

//Wilayah
Route::get('/wilayah/provinsi', [WilayahController::class, 'getProvinces']);
Route::get('/wilayah/kota/{id}', [WilayahController::class, 'getCities']);

// Alumni Routes
Route::post('/data/pindah-ke-alumni/{id}', [DataController::class, 'pindahKeAlumni'])->name('data.pindahKeAlumni');

Route::get('/admin/alumni/alumni', [App\Http\Controllers\AlumniController::class, 'index'])->name('admin.alumni.alumni');
Route::get('/admin/alumni/create', [App\Http\Controllers\AlumniController::class, 'create'])->name('admin.alumni.create');
Route::post('/admin/alumni/store', [App\Http\Controllers\AlumniController::class, 'store'])->name('admin.alumni.store');
Route::get('/admin/alumni/{id}/edit', [App\Http\Controllers\AlumniController::class, 'edit'])->name('admin.alumni.edit');
Route::put('/admin/alumni/{id}', [App\Http\Controllers\AlumniController::class, 'update'])->name('admin.alumni.update');
Route::delete('/admin/alumni/{id}', [App\Http\Controllers\AlumniController::class, 'destroy'])->name('delete-alumni');
Route::get('/admin/alumni/{id}', [App\Http\Controllers\AlumniController::class, 'show'])->name('admin.alumni.show');


// Alumni
Route::post('/admin/alumni/update-inline', [\App\Http\Controllers\AlumniController::class, 'updateInline'])->name('admin.alumni.update-inline');
Route::post('/admin/alumni/update-kelas', [\App\Http\Controllers\AlumniController::class, 'updateKelas'])->name('admin.alumni.update-kelas');

// Sales Plan Routes 
Route::post('/data/{id}/pindah-ke-salesplan', [DataController::class, 'pindahKeSalesPlan'])->name('data.pindahKeSalesPlan');
Route::get('/admin/salesplans', [SalesPlanController::class, 'index'])->name('admin.salesplan.index');
Route::get('/admin/salesplan/{id}/edit', [SalesPlanController::class, 'edit'])->name('admin.salesplan.edit');
Route::put('/admin/salesplan/{id}', [SalesPlanController::class, 'update'])->name('admin.salesplan.update');


// Export
Route::get('/salesplan/export', [SalesPlanController::class, 'export'])->name('salesplan.export');

// Daily Activities
Route::get('/admin/dailyactivity/index', [App\Http\Controllers\DailyController::class, 'index'])->name('admin.dailyactivity.index');
Route::post('/admin/daily-activity', [App\Http\Controllers\DailyController ::class, 'store'])->name('admin.daily-activity.store');


// Pindah Salesplan dari Alumni
Route::post('/admin/alumni/to-salesplan/{id}', [AlumniController::class, 'toSalesplan'])->name('admin.alumni.toSalesplan');
Route::post('/admin/alumni/{id}/simpan-kelas', [AlumniController::class, 'simpanKelas'])->name('admin.alumni.simpanKelas');

// Per FU
Route::put('/salesplan/{id}/fu/{fu}', [SalesPlanController::class, 'updateFU'])->name('admin.salesplan.update-fu');

// Update Potensi Kelas


//Edit Ajax
Route::post('/admin/database/update-inline', [DataController::class, 'updateInline']);
Route::post('/admin/database/update-location', [DataController::class, 'updateLocation']);
Route::post('/admin/database/update-potensi/{id}', [DataController::class, 'updatePotensi']);


// InlineSalesplan
Route::post('/admin/salesplan/inline-update', [SalesPlanController::class, 'inlineUpdate'])
    ->name('admin.salesplan.inline-update');

    // Salesplan update status
    Route::post('/admin/salesplan/update-status/{id}', [SalesPlanController::class, 'updateStatus']);
    // Route::put('/admin/salesplan/{id}', [SalesPlanController::class, 'updateStatus']);


// Tambah ke salesplan
Route::post('/admin/database/{id}/tambah-salesplan', [DataController::class, 'tambahkeSalesplan'])
    ->name('admin.database.tambahSalesplan');
    
// Salesplan destroy
Route::resource('salesplan', SalesPlanController::class);


Route::delete('/admin/salesplan/{id}', [App\Http\Controllers\SalesPlanController::class, 'destroy'])
    ->name('admin.salesplan.destroy');


    // Salesplan filter
    // routes/web.php
Route::get('/sales-plan/{kelas}', [SalesPlanController::class, 'filter'])->name('salesplan.filter');

Route::put('/admin/salesplan/{id}', [SalesPlanController::class, 'update'])->name('admin.salesplan.update');

// Data filter
Route::get('/admin/database/filter', [DataController::class, 'filter'])->name('admin.database.filter');

// Route update sumber leads

// Update Sumber Leads
Route::post('/update-sumber-leads/{id}', [dataController::class, 'updateSumberLeads'])->name('update.sumber.leads');

// Daily Activity Export PDF
Route::get('/admin/daily-activity/export-pdf/{bulan}', [DailyController::class, 'exportPdf'])
    ->name('admin.daily-activity.exportPdf');


use App\Http\Controllers\AdminController;

Route::get('/administrator', [AdminController::class, 'index'])->name('administrator');

use App\Http\Controllers\DashboardController;
Route::get('/admin/operasional', [DashboardController::class, 'operasional'])->name('admin.operasional')->middleware('auth');
Route::get('/admin/keuangan', [DashboardController::class, 'keuangan'])->name('admin.keuangan')->middleware('auth');

Route::get('/admin/cs/{id}', [AdminController::class, 'detailCS'])->name('admin.cs.detail');
// Route::prefix('admin/cs')->group(function () {
//     Route::get('{id}/salesplan', [App\Http\Controllers\Admin\CSController::class, 'salesplan'])->name('admin.cs.salesplan');
//     Route::get('{id}/database', [App\Http\Controllers\Admin\CSController::class, 'database'])->name('admin.cs.database');
// });

// Route untuk admin lihat database CS
Route::get('/koordinasi/{id}', [App\Http\Controllers\KoordinasiController::class, 'show'])
    ->name('koordinasi.show')
    ->middleware('auth');

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/koordinasi/{csId}', [App\Http\Controllers\HomeController::class, 'showDashboardCs'])->name('koordinasi.cs');
});

    
// Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');

// Manajemen kelas
use App\Http\Controllers\KelasController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
    Route::put('/kelas/{id}', [KelasController::class, 'update'])->name('kelas.update');
    Route::delete('/kelas/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');
});


// Penjualan controller
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/penjualan', [App\Http\Controllers\PenjualanController::class, 'index'])
        ->name('penjualan.index');
});




Route::post('/koordinasi/komentar', [KoordinasiController::class, 'kirimKomentar'])
    ->name('komentar.store');
    
    // routes/web.php notifikasi
use App\Http\Controllers\NotifikasiController;

Route::middleware(['auth'])->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'show'])->name('notifikasi.show');
});

// Hanya untuk administrator
use App\Http\Controllers\AdminActivityController;

Route::prefix('admin')->middleware(['auth','role:administrator'])->group(function () {
    // --- SETTINGS ---
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings/users', [App\Http\Controllers\Admin\SettingController::class, 'storeUser'])->name('admin.settings.users.store');
    Route::put('/settings/users/{id}', [App\Http\Controllers\Admin\SettingController::class, 'updateUser'])->name('admin.settings.users.update');
    Route::delete('/settings/users/{id}', [App\Http\Controllers\Admin\SettingController::class, 'destroyUser'])->name('admin.settings.users.destroy');
    Route::post('/settings/target', [App\Http\Controllers\Admin\SettingController::class, 'updateTarget'])->name('admin.settings.target.update');
    Route::post('/settings/menus/toggle', [App\Http\Controllers\Admin\SettingController::class, 'toggleMenu'])->name('admin.settings.menus.toggle');
    Route::post('/settings/role-menus/update', [App\Http\Controllers\Admin\SettingController::class, 'updateRoleMenu'])->name('admin.settings.role-menus.update');

});

// Activity CS routes moved to top 
// Route::prefix('admin')->middleware(['auth'])->group(function () {
//     Route::get('/activity-cs', [AdminActivityController::class, 'index'])->name('admin.activity-cs.index');
//     Route::get('/activity-cs/export-pdf-bulanan', [AdminActivityController::class, 'viewPdfBulanan'])->name('admin.activity-cs.viewPdfBulanan');
// });


Route::get('/chat/{id}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{id}', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');


use App\Http\Controllers\MessageController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{id}/reply', [MessageController::class, 'reply'])->name('messages.reply');
});

// routes/web.php
// routes/web.php
Route::get('/admin/database/statistik', [DataController::class, 'getStatistik'])
    ->name('admin.database.statistik');

use App\Http\Controllers\ProgramKerjaController;

Route::get('/programkerja', [ProgramKerjaController::class, 'index'])->name('programkerja.index');
Route::post('/programkerja', [ProgramKerjaController::class, 'store'])->name('programkerja.store');
Route::delete('/programkerja/{id}', [ProgramKerjaController::class, 'destroy'])->name('programkerja.destroy');

// inisiatif CRUD tetap di controller yang sama
Route::post('/inisiatif', [ProgramKerjaController::class, 'storeInisiatif'])->name('inisiatif.store');
Route::put('/inisiatif/{id}', [ProgramKerjaController::class, 'updateInisiatif'])->name('inisiatif.update');
Route::delete('/inisiatif/{id}', [ProgramKerjaController::class, 'destroyInisiatif'])->name('inisiatif.destroy');

// Edit Inline

Route::post('/programkerja/update-inline', [ProgramKerjaController::class, 'updateInline'])
    ->name('programkerja.updateInline');



// Hapus Inisiatif
Route::delete('/inisiatif/delete', [ProgramKerjaController::class, 'deleteInisiatif'])
    ->name('inisiatif.delete');


Route::post('/gantt/inisiatif/{id}/done', [GanttChartController::class, 'markDone'])
    ->name('gantt.done');

Route::get('/marketing/gantt-chart', [GanttChartController::class, 'index'])
     ->name('gantt.index')
     ->middleware('auth');

use App\Http\Controllers\Marketing\PenilaianController as MarketingPenilaianController;

Route::prefix('marketing')->name('marketing.')->middleware(['auth'])->group(function () {
    Route::get('penilaian', [MarketingPenilaianController::class, 'index'])->name('penilaian.index');
    Route::get('penilaian/export-pdf', [MarketingPenilaianController::class, 'exportPdf'])->name('penilaian.exportPdf');
});
