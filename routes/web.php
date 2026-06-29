<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\ColorSchemeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KunjunganController;
use App\Http\Controllers\PemeriksaanController;
use App\Http\Controllers\ExportController;

// Utility
Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])->name('color-scheme-switcher');

// Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Auth
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::prefix('/')->name('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('dashboard/anggota-options', [DashboardController::class, 'anggotaOptions'])->name('.anggota-options');

        Route::prefix('kunjungan-dashboard')->name('.kunjungan')->group(function () {
            Route::post('/', [DashboardController::class, 'store'])->name('.store');
            Route::put('/{kunjungan}', [DashboardController::class, 'update'])->name('.update');
            Route::delete('/{kunjungan}', [DashboardController::class, 'destroy'])->name('.destroy');
        });
    });

    // Anggota
    Route::get('anggota/import-template', [AnggotaController::class, 'importTemplate'])->name('anggota.import-template');
    Route::post('anggota/import', [AnggotaController::class, 'import'])->name('anggota.import');
    Route::resource('anggota', AnggotaController::class)->parameters(['anggota' => 'anggota']);

    // Kunjungan
    Route::resource('kunjungan', KunjunganController::class)->except(['edit', 'update']);

    // Pemeriksaan
    Route::prefix('pemeriksaan')->name('pemeriksaan.')->group(function () {
        Route::get('{pemeriksaan}/raport-pdf', [PemeriksaanController::class, 'raport'])->name('raport');
        Route::resource('/', PemeriksaanController::class)->parameters(['' => 'pemeriksaan']);
    });

    // Export
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('kunjungan/{format?}', [ExportController::class, 'kunjungan'])->where('format', 'excel|pdf')->name('kunjungan');
        Route::get('riwayat/{format?}', [ExportController::class, 'riwayat'])->where('format', 'excel|pdf')->name('riwayat');
        Route::get('pemeriksaan/{format?}', [ExportController::class, 'pemeriksaan'])->where('format', 'excel|pdf')->name('pemeriksaan');
    });
});
