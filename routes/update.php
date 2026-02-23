<?php

use App\Http\Controllers\UpdateController;

Route::get('/', [UpdateController::class, 'step0']);
Route::get('/step1', [UpdateController::class, 'step1'])->name('update.step1');
Route::post('/purchase_code', [UpdateController::class, 'purchase_code'])->name('update.purchase_code');
Route::get('/step2', [UpdateController::class, 'step2'])->name('update.step2');
Route::get('/step3', [UpdateController::class, 'step3'])->name('update.step3');
