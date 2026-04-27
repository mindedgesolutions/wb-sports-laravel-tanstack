<?php

use App\Http\Controllers\YctcUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [YctcUploadController::class, 'index'])->name('home');
Route::post('/', [YctcUploadController::class, 'uploadTemp'])->name('upload.temp');
Route::post('/transfer', [YctcUploadController::class, 'transfer'])->name('transfer.main');
