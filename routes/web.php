<?php

use App\Http\Controllers\{DashboardController,SiswaController,NilaiController,ProfileController};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Siswa listing
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/options', [SiswaController::class, 'options'])->name('siswa.options');

    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');

    Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');
    Route::put('/nilai/{nilai}', [NilaiController::class, 'update'])->name('nilai.update');
    Route::delete('/nilai/{nilai}', [NilaiController::class, 'destroy'])->name('nilai.destroy');

    Route::post('/nilai/import', [NilaiController::class, 'import'])->name('nilai.import');
    Route::get('/nilai/export', [NilaiController::class, 'export'])->name('nilai.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';