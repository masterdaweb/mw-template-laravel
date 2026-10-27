<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\PreferredLanguage;
use Illuminate\Support\Facades\Route;

Route::middleware(PreferredLanguage::class)->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/uploads', [HomeController::class, 'store'])->name('uploads.store');
});

Route::get('/status.json', [HomeController::class, 'status'])->name('status');
