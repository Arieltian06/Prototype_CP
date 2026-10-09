<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JenjangController;
use App\Http\Controllers\Admin\KesesuaianController;
use App\Http\Controllers\Admin\KriteriaController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SawController;
use App\Http\Controllers\Admin\SekolahController;
use App\Http\Controllers\Admin\TempatBelajarController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });
    Route::middleware('auth:admin')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::resource('tempat', TempatBelajarController::class);
        foreach (['jenjang' => JenjangController::class, 'mapel' => MataPelajaranController::class, 'sekolah' => SekolahController::class, 'kriteria' => KriteriaController::class, 'skala' => ResourceController::class] as $resource => $controller) {
            Route::resource($resource, $controller)->parameters([$resource => $resource]);
        }
        Route::get('kesesuaian', [KesesuaianController::class, 'index'])->name('kesesuaian.index');
        Route::get('perhitungan', [SawController::class, 'index'])->name('saw.index');
        Route::get('perhitungan/{perhitungan}', [SawController::class, 'show'])->name('saw.show');
    });
});
