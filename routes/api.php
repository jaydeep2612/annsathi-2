<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\SessionController;
use App\Http\Controllers\Api\v1\MenuController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\CustomerController;
use App\Http\Controllers\Api\v1\ReservationController;
use App\Http\Controllers\Api\v1\SyncController;

Route::prefix('v1')->middleware([\App\Http\Middleware\ResolveTenant::class])->group(function () {
    // Session Routes (Public / Customer Web Ordering)
    Route::post('sessions/start', [SessionController::class, 'start']);
    Route::post('sessions/validate', [SessionController::class, 'validateSession']);
    Route::post('sessions/join', [SessionController::class, 'join']);
    Route::post('sessions/call-waiter', [SessionController::class, 'callWaiter']);
    Route::post('sessions/request-bill', [SessionController::class, 'requestBill']);

    // Menu Routes (Public / Customer Web Ordering)
    Route::get('menu', [MenuController::class, 'index']);
    Route::get('menu/categories', [MenuController::class, 'categories']);
    Route::get('menu/items/{id}/availability', [MenuController::class, 'checkAvailability']);

    // Public Order Routes
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Sanctum Authenticated Routes (Staff/Client Panel Sync)
    Route::middleware(['auth:sanctum'])->group(function () {
        // CRM / Customer Routes
        Route::get('customers', [CustomerController::class, 'index']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/{id}', [CustomerController::class, 'show']);

        // Orders Listing
        Route::get('orders', [OrderController::class, 'index']);

        // Reservation Routes
        Route::get('reservations', [ReservationController::class, 'index']);
        Route::post('reservations', [ReservationController::class, 'store']);
        Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel']);

        // Offline Sync Routes
        Route::post('sync', [SyncController::class, 'sync']);
    });
});
