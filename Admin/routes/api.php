<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MobileApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/informasi', [MobileApiController::class, 'informations']);

Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureActiveAccount::class])->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/laporan', [MobileApiController::class, 'reports']);
    Route::get('/riwayat', [MobileApiController::class, 'reports']);
    Route::get('/laporan-terakhir', [MobileApiController::class, 'latestReport']);
    Route::post('/lapor', [MobileApiController::class, 'storeReport']);
    Route::put('/laporan/{id}', [MobileApiController::class, 'updateReport'])->whereNumber('id');
    Route::delete('/laporan/{id}', [MobileApiController::class, 'deleteReport'])->whereNumber('id');

    Route::get('/notifikasi/{userId}', [MobileApiController::class, 'notifications'])->whereNumber('userId');
    Route::delete('/notifikasi/{userId}/{reportId}', [MobileApiController::class, 'dismissNotification'])
        ->whereNumber('userId')
        ->whereNumber('reportId');

    Route::put('/pengguna/{userId}/profil', [MobileApiController::class, 'updateProfile'])->whereNumber('userId');
    Route::put('/pengguna/{userId}/password', [MobileApiController::class, 'updatePassword'])->whereNumber('userId');
});
