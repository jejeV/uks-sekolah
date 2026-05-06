<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\ColorSchemeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\ExportController;

// Utility
Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])
    ->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])
    ->name('color-scheme-switcher');

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'loginView'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Auth
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // -------------------------------------------------------
    // Dashboard UKS
    // -------------------------------------------------------
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD kunjungan dari dashboard
    Route::post('/kunjungan-dashboard',
        [DashboardController::class, 'store'])->name('dashboard.kunjungan.store');
    Route::put('/kunjungan-dashboard/{kunjungan}',
        [DashboardController::class, 'update'])->name('dashboard.kunjungan.update');
    Route::delete('/kunjungan-dashboard/{kunjungan}',
        [DashboardController::class, 'destroy'])->name('dashboard.kunjungan.destroy');

    // -------------------------------------------------------
    // Anggota
    // -------------------------------------------------------
    Route::resource('anggota', AnggotaController::class);

    // -------------------------------------------------------
    // Kunjungan UKS
    // -------------------------------------------------------
    Route::resource('kunjungan', KunjunganController::class)
         ->except(['edit', 'update']);

    // -------------------------------------------------------
    // Pemeriksaan Kesehatan
    // -------------------------------------------------------
    Route::resource('pemeriksaan', PemeriksaanController::class)
         ->except(['edit', 'update']);

    // -------------------------------------------------------
    // Riwayat Penyakit
    // -------------------------------------------------------
    Route::get('riwayat',           [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('riwayat/{anggota}', [RiwayatController::class, 'show'])->name('riwayat.show');

    // -------------------------------------------------------
    // Export
    // -------------------------------------------------------
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('kunjungan',   [ExportController::class, 'kunjungan'])->name('kunjungan');
        Route::get('riwayat',     [ExportController::class, 'riwayat'])->name('riwayat');
        Route::get('pemeriksaan', [ExportController::class, 'pemeriksaan'])->name('pemeriksaan');
    });

    // -------------------------------------------------------
    // Route bawaan template Rubick
    // -------------------------------------------------------
    Route::get('dashboard-overview-2-page', [PageController::class, 'dashboardOverview2'])->name('dashboard-overview-2');
    Route::get('dashboard-overview-3-page', [PageController::class, 'dashboardOverview3'])->name('dashboard-overview-3');
    Route::get('inbox-page',                [PageController::class, 'inbox'])->name('inbox');
    Route::get('file-manager-page',         [PageController::class, 'fileManager'])->name('file-manager');
    Route::get('point-of-sale-page',        [PageController::class, 'pointOfSale'])->name('point-of-sale');
    Route::get('chat-page',                 [PageController::class, 'chat'])->name('chat');
    Route::get('post-page',                 [PageController::class, 'post'])->name('post');
    Route::get('calendar-page',             [PageController::class, 'calendar'])->name('calendar');
    Route::get('crud-data-list-page',       [PageController::class, 'crudDataList'])->name('crud-data-list');
    Route::get('crud-form-page',            [PageController::class, 'crudForm'])->name('crud-form');
    Route::get('users-layout-1-page',       [PageController::class, 'usersLayout1'])->name('users-layout-1');
    Route::get('users-layout-2-page',       [PageController::class, 'usersLayout2'])->name('users-layout-2');
    Route::get('users-layout-3-page',       [PageController::class, 'usersLayout3'])->name('users-layout-3');
    Route::get('profile-overview-1-page',   [PageController::class, 'profileOverview1'])->name('profile-overview-1');
    Route::get('profile-overview-2-page',   [PageController::class, 'profileOverview2'])->name('profile-overview-2');
    Route::get('profile-overview-3-page',   [PageController::class, 'profileOverview3'])->name('profile-overview-3');
    Route::get('update-profile-page',       [PageController::class, 'updateProfile'])->name('update-profile');
    Route::get('change-password-page',      [PageController::class, 'changePassword'])->name('change-password');
    Route::get('regular-table-page',        [PageController::class, 'regularTable'])->name('regular-table');
    Route::get('tabulator-page',            [PageController::class, 'tabulator'])->name('tabulator');
    Route::get('modal-page',                [PageController::class, 'modal'])->name('modal');
    Route::get('slide-over-page',           [PageController::class, 'slideOver'])->name('slide-over');
    Route::get('notification-page',         [PageController::class, 'notification'])->name('notification');
    Route::get('accordion-page',            [PageController::class, 'accordion'])->name('accordion');
    Route::get('button-page',               [PageController::class, 'button'])->name('button');
    Route::get('alert-page',                [PageController::class, 'alert'])->name('alert');
    Route::get('regular-form-page',         [PageController::class, 'regularForm'])->name('regular-form');
    Route::get('datepicker-page',           [PageController::class, 'datepicker'])->name('datepicker');
    Route::get('tom-select-page',           [PageController::class, 'tomSelect'])->name('tom-select');
    Route::get('file-upload-page',          [PageController::class, 'fileUpload'])->name('file-upload');
    Route::get('validation-page',           [PageController::class, 'validation'])->name('validation');
    Route::get('chart-page',                [PageController::class, 'chart'])->name('chart');
    Route::get('icon-page',                 [PageController::class, 'icon'])->name('icon');
    Route::get('error-page-page',           [PageController::class, 'errorPage'])->name('error-page');
    Route::get('login-page',                [PageController::class, 'login'])->name('login-page');
    Route::get('register-page',             [PageController::class, 'register'])->name('register');
});
