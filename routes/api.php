<?php

use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('tests')
    ->middleware('auth')
    ->name('tests')
    ->prefix('tests')
    ->as('tests.')
    ->group(function () {
        Route::get('/', [TestController::class, 'index'])->name('index');
        Route::post('/', [TestController::class, 'store'])->name('store');
        Route::get('/{id}', [TestController::class, 'show'])->name('show');
        Route::put('/{id}', [TestController::class, 'update'])->name('update');
        Route::delete('/{id}', [TestController::class, 'destroy'])->name('destroy');
    });

Route::namespace('users')
    ->middleware('auth')
    ->name('users')
    ->prefix('users')
    ->as('users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });
