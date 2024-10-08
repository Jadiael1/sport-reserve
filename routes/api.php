<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldAvailabilityController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\UserController;
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

Route::prefix('v1')->group(function () {
    // Rotas de autenticação
    Route::prefix('auth')->group(function () {
        Route::post('/signup', [AuthController::class, 'signup']);
        Route::post('/signin', [AuthController::class, 'signin']);
        Route::post('/signout', [AuthController::class, 'signout'])->middleware('auth:sanctum');
        Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
        Route::prefix('password')->group(function () {
            Route::post('/email', [AuthController::class, 'sendResetLinkEmail']);
            Route::post('/reset', [AuthController::class, 'reset']);
        });
        Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
        Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    });

    // Rotas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        // Rotas para o recurso de reservas
        Route::middleware(['verified'])->prefix('reservations')->group(function () {
            Route::get('/', [ReservationController::class, 'index'])->name('reservations.index');
            Route::post('/', [ReservationController::class, 'store'])->name('reservations.store');
            Route::get('/{id}', [ReservationController::class, 'show'])->name('reservations.show');
            Route::patch('/{id}', [ReservationController::class, 'update'])->name('reservations.update');
            Route::delete('/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
        });

        // Rotas para o recurso de usuários
        Route::middleware(['admin'])->prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
            // Route::patch('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::patch('/{id}/toggle-active', [UserController::class, 'toggleActive']);
            Route::patch('/{id}/toggle-confirmation', [UserController::class, 'toggleConfirmation']);
        });
        Route::prefix('users')->group(function () {
            Route::patch('/{id}', [UserController::class, 'update'])->name('users.update');
        });
    });

    Route::prefix('payments')->group(function () {
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->get('/', [PaymentController::class, 'index'])->name('payments.index');
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->get('/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->patch('/{id}', [PaymentController::class, 'update'])->name('payments.update');
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->delete('/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->post('/reservations/{id}/pay', [PaymentController::class, 'store'])->name('payments.store');
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->post('/checkouts/{checkout_id}/toggle', [PaymentController::class, 'toggleCheckoutStatus'])->name('payments.toggleCheckoutStatus');
        Route::middleware(['auth:sanctum', 'verified', 'admin'])->post('/{id}/refund', [PaymentController::class, 'refundPayment'])->name('payments.refundPayment');
        Route::post('/notify', [PaymentController::class, 'paymentNotification']);
    });

    // Rotas para o recurso de campos
    Route::prefix('fields')->group(function () {
        Route::get('/', [FieldController::class, 'index'])->name('fields.index');
        Route::middleware(['auth:sanctum', 'admin'])->post('/', [FieldController::class, 'store'])->name('fields.store');
        Route::get('/{id}', [FieldController::class, 'show'])->name('fields.show');
        Route::middleware(['auth:sanctum', 'admin'])->patch('/{id}', [FieldController::class, 'update'])->name('fields.update');
        Route::middleware(['auth:sanctum', 'admin'])->delete('/{id}', [FieldController::class, 'destroy'])->name('fields.destroy');
    });

    Route::prefix('fieldAvailabilities')->group(function () {
        // Rotas para disponibilidades de campos
        Route::middleware(['auth:sanctum', 'admin'])->get('/', [FieldAvailabilityController::class, 'index'])->name('fieldAvailabilities.index');
        Route::middleware(['auth:sanctum', 'admin'])->post('/{fieldId}/availabilities', [FieldAvailabilityController::class, 'store'])->name('fieldAvailabilities.store');
        Route::middleware(['auth:sanctum', 'admin'])->patch('/{fieldId}/availabilities/{availabilityId}', [FieldAvailabilityController::class, 'update'])->name('fieldAvailabilities.update');
        Route::middleware(['auth:sanctum', 'admin'])->delete('/{fieldId}/availabilities/{availabilityId}', [FieldAvailabilityController::class, 'destroy'])->name('fieldAvailabilities.delete');
    });

    Route::middleware(['auth:sanctum', 'verified', 'admin'])->prefix('reports')->group(function () {
        Route::get('/performance', [ReportController::class, 'performance'])->name('reports.performance');
        Route::get('/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('/users', [ReportController::class, 'users'])->name('reports.users');
        Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    });
});
