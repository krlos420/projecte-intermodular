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
    });

    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function (){
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/store', [ExpenseController::class, 'store'])->name('store');
        Route::get('/show/{id}', [ExpenseController::class, 'show'])->name('show');
        Route::put('/update/{id}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ExpenseController::class, 'destroy'])->name('destroy');
    });
});
