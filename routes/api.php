<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\SessionController;
use App\Http\Controllers\Api\v1\MenuController;
use App\Http\Controllers\Api\v1\OrderController;

Route::prefix('v1')->middleware([\App\Http\Middleware\ResolveTenant::class])->group(function () {
    // Session Routes
    Route::post('sessions/start', [SessionController::class, 'start']);
    Route::post('sessions/validate', [SessionController::class, 'validateSession']);
    Route::post('sessions/join', [SessionController::class, 'join']);
    Route::post('sessions/call-waiter', [SessionController::class, 'callWaiter']);
    Route::post('sessions/request-bill', [SessionController::class, 'requestBill']);

    // Menu Routes
    Route::get('menu', [MenuController::class, 'index']);
    Route::get('menu/categories', [MenuController::class, 'categories']);
    Route::get('menu/items/{id}/availability', [MenuController::class, 'checkAvailability']);

    // Order Routes
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
});
