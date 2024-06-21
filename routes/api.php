<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas de autenticação
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');


// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Rotas para o recurso de reservas
    Route::prefix('reservations')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/{id}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/{id}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::delete('/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    });

    // Rotas para o recurso de usuários
    Route::middleware('AdminMiddleware')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
        Route::patch('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Rotas para o recurso de campos
    Route::middleware('AdminMiddleware')->prefix('fields')->group(function () {
        Route::get('/', [FieldController::class, 'index'])->name('fields.index');
        Route::post('/', [FieldController::class, 'store'])->name('fields.store');
        Route::get('/{id}', [FieldController::class, 'show'])->name('fields.show');
        Route::patch('/{id}', [FieldController::class, 'update'])->name('fields.update');
        Route::delete('/{id}', [FieldController::class, 'destroy'])->name('fields.destroy');
    });
});
