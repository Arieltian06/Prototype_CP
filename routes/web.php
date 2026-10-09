<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\TempatBelajarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/tempat-belajar', [TempatBelajarController::class, 'index'])->name('places.index');
Route::get('/tempat-belajar/{tempat}', [TempatBelajarController::class, 'show'])->whereNumber('tempat')->name('places.show');
Route::get('/rekomendasi', [RecommendationController::class, 'index'])->name('recommendation.index');
Route::post('/rekomendasi', [RecommendationController::class, 'store'])->middleware('throttle:20,1')->name('recommendation.store');
Route::get('/rekomendasi/hasil/{perhitungan}', [RecommendationController::class, 'show'])->whereUuid('perhitungan')->name('recommendation.show');

require __DIR__.'/admin.php';
