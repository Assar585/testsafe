<?php

use App\Http\Controllers\UpdateController;

// NOTE: These routes use /system-update prefix to avoid conflicting with the home route.
// The original route was Route::get('/') which overrode the web.php home route.
Route::get('/system-update', [UpdateController::class, 'step0'])->name('update.step0');
Route::get('/system-update/step1', [UpdateController::class, 'step1'])->name('update.step1');
Route::post('/system-update/purchase_code', [UpdateController::class, 'purchase_code'])->name('update.purchase_code');
Route::get('/system-update/step2', [UpdateController::class, 'step2'])->name('update.step2');
Route::get('/system-update/step3', [UpdateController::class, 'step3'])->name('update.step3');

Route::get('/clear-view-cache', function () {
    \Artisan::call('view:clear');
    return "View cache cleared at " . date('Y-m-d H:i:s');
});
