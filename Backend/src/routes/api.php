<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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

    // TODO: Añadir rutas de Houses y Expenses
});
