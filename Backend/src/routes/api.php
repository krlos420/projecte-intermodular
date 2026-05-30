<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\ExpenseController;

// Autenticación
Route::prefix('auth')->name('auth.')->group(function (){
    Route::post('/register', [AuthController::class, 'createUser'])->name('register');
    Route::post('/login', [AuthController::class, 'loginUser'])->name('login');
});

// Rutas protegidas (requieren token)
Route::middleware('auth:sanctum')->group(function() {

    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Usuario
    Route::prefix('users')->name('users.')->group(function (){
        Route::get('/me', [UserController::class, 'show'])->name('me');
        Route::put('/update', [UserController::class, 'update'])->name('update');
        Route::delete('/destroy', [UserController::class, 'destroy'])->name('destroy');
    });
    
    // Houses
    Route::prefix('houses')->name('houses.')->group(function (){
        Route::post('/create', [HouseController::class, 'store'])->name('create');
        Route::post('/join', [HouseController::class, 'join'])->name('join');
        Route::get('/my-house', [HouseController::class, 'myHouse'])->name('my-house');
        Route::post('/leave', [HouseController::class, 'leave'])->name('leave');
        Route::put('/update-name', [HouseController::class, 'updateName'])->name('update-name');
        Route::put('/update-details', [HouseController::class, 'updateDetails'])->name('update-details');
        Route::delete('/remove-user/{id}', [HouseController::class, 'removeUser'])->name('remove-user');
        Route::delete('/destroy', [HouseController::class, 'destroy'])->name('destroy');
        // Nuevas rutas de geolocalización y peticiones
        Route::get('/available', [HouseController::class, 'availableHouses'])->name('available');
        Route::post('/request-join', [HouseController::class, 'requestJoin'])->name('request-join');
        Route::get('/join-requests', [HouseController::class, 'getJoinRequests'])->name('join-requests');
        Route::put('/join-requests/{id}', [HouseController::class, 'handleJoinRequest'])->name('join.handle');
    });

    // Liquidaciones de deudas
    Route::post('/settlements', [\App\Http\Controllers\SettlementController::class, 'store'])->name('settlements.store');

    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function (){
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/store', [ExpenseController::class, 'store'])->name('store');
        Route::get('/show/{id}', [ExpenseController::class, 'show'])->name('show');
        Route::put('/update/{id}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::get('/statistics', [ExpenseController::class, 'statistics'])->name('statistics');
    });

    // Shopping List
    Route::prefix('shopping-list')->name('shopping-list.')->group(function (){
        Route::get('/', [\App\Http\Controllers\ShoppingListController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\ShoppingListController::class, 'store'])->name('store');
        Route::put('/update/{id}', [\App\Http\Controllers\ShoppingListController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [\App\Http\Controllers\ShoppingListController::class, 'destroy'])->name('destroy');
    });
});
