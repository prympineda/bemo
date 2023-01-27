<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ColumnsController;
use App\Http\Controllers\API\CardsController;

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

Route::group(['as' => 'columns.', 'prefix' => 'columns', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [ColumnsController::class, 'index'])->name('index');
    Route::post('store', [ColumnsController::class, 'store'])->name('store');
    Route::post('delete', [ColumnsController::class, 'delete'])->name('delete');
});

Route::group(['as' => 'cards.', 'prefix' => 'cards', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [CardsController::class, 'index'])->name('index');
    Route::post('store', [CardsController::class, 'store'])->name('store');
    Route::post('update/{id}', [CardsController::class, 'update'])->name('update');
    Route::post('update-order', [CardsController::class, 'updateOrder'])->name('update-order');
});
