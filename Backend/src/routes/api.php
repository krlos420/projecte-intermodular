<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Delivery_pointController;
use App\Http\Controllers\ReviewController;

// 1. Autenticación
Route::prefix('auth')->name('auth.')->group(function (){
    Route::post('/register', [AuthController::class, 'createUser'])->name('register');
    Route::post('/login', [AuthController::class, 'loginUser'])->name('login');
});

// 2. Catálogo
Route::prefix('products')->name('products.')->group(function (){
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/show/{id}', [ProductController::class, 'show'])->name('show');
});

// 3. Categorías
Route::prefix('categories')->name('categories.')->group(function (){
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/show/{id}', [CategoryController::class, 'show'])->name('show');
});

// 4. Mapa y Puntos de Entrega
Route::prefix('delivery_points')->name('delivery_points.')->group(function (){
    Route::get('/', [Delivery_pointController::class, 'index'])->name('index');
});

// 5. Reviews
Route::prefix('reviews')->name('reviews.')->group(function (){
    Route::get('/', [ReviewController::class, 'index'])->name('index');
    Route::get('/show/{id}', [ReviewController::class, 'show'])->name('show');
});

Route::middleware('auth:sanctum')->group(function() {

    // USUARIO
    Route::prefix('users')->name('users.')->group(function (){
        Route::get('/me', [UserController::class, 'show'])->name('me');
        Route::put('/update', [UserController::class, 'update'])->name('update');
        Route::delete('/destroy', [UserController::class, 'destroy'])->name('destroy');
    });
    //MAPA ESPECIFICO
    Route::get('/map', [UserController::class, 'mostrarMapa'])->name('map');
    // VENTAS
    Route::prefix('sales')->name('sales.')->group(function (){
        Route::post('/store', [SaleController::class, 'store'])->name('store'); 
        Route::get('/my-orders', [SaleController::class, 'myOrders'])->name('my-orders');
        Route::get('/show/{id}', [SaleController::class, 'show'])->name('show'); 
    });

    // PRODUCTOS
    Route::prefix('products')->name('products.')->group(function (){
        Route::get('/mine', [ProductController::class, 'myProducts'])->name('mine');
        Route::post('/store', [ProductController::class, 'store'])->name('store');
        Route::put('/update/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // PUNTOS DE ENTREGA
    Route::prefix('delivery_points')->name('delivery_points.')->group(function (){
        Route::post('/store', [Delivery_pointController::class, 'store'])->name('store');
        Route::put('/update/{id}', [Delivery_pointController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [Delivery_pointController::class, 'destroy'])->name('destroy');
    });

    // REVIEWS
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
    
    // CATEGORIAS
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/destroy/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    


    
});
