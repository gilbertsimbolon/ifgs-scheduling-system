<?php

use App\Http\Controllers\PenggunaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('pengguna')->name('pengguna.')->group(function () {
    Route::get('/', [PenggunaController::class, 'index'])->name('index');
    Route::post('/', [PenggunaController::class, 'store'])->name('store');
    Route::put('/{user:slug}', [PenggunaController::class, 'update'])->name('update');
    Route::patch('/{user:slug}/status', [PenggunaController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{user:slug}', [PenggunaController::class, 'destroy'])->name('destroy');
});
