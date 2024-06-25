<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontrakanController;
use App\Http\Controllers\KostanController;
use App\Http\Controllers\ListPaymentController;
use App\Http\Controllers\PerumahanController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/user/current-byToken', [UserController::class, 'getUserByToken']);

Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get('/user/current', [UserController::class, 'getUser']);
    Route::patch('/user/current', [UserController::class, 'updateUser']);

    //perumahan 
    Route::get('/perumahan', [PerumahanController::class, 'getPerumahan']);
    Route::get('/perumahan/{id}', [PerumahanController::class, 'getPerumahanById']);
    Route::post('/perumahan', [PerumahanController::class, 'createPerumahan']);
    Route::patch('/perumahan/{id}', [PerumahanController::class, 'updatePerumahan']);
    Route::delete('/perumahan/{id}', [PerumahanController::class, 'deletePerumahan']);

    //kontrakan 
    Route::get('/kontrakan', [KontrakanController::class, 'getKontrakan']);
    Route::get('/kontrakan/{id}', [kontrakanController::class, 'getKontrakanById']);
    Route::post('/kontrakan', [kontrakanController::class, 'createKontrakan']);
    Route::patch('/kontrakan/{id}', [kontrakanController::class, 'updateKontrakan']);
    Route::delete('/kontrakan/{id}', [kontrakanController::class, 'deleteKontrakan']);

    //kostan 
    Route::get('/kostan', [KostanController::class, 'getKostan']);
    Route::get('/kostan/{id}', [KostanController::class, 'getKostanById']);
    Route::post('/kostan', [KostanController::class, 'createKostan']);
    Route::patch('/kostan/{id}', [KostanController::class, 'updateKostan']);
    Route::delete('/kostan/{id}', [KostanController::class, 'deleteKostan']);

    // units
    Route::post('/unit', [UnitController::class, 'createUnit']);
    Route::get('/units', [UnitController::class, 'getUnits']);
    Route::get('/unit/{id}', [UnitController::class, 'getUnitById']);
    Route::patch('/unit/{id}', [UnitController::class, 'updateUnit']);
    Route::delete('/unit/{id}', [UnitController::class, 'deleteUnit']);
    Route::post('/unit-payment/{id}', [UnitController::class, 'bayarUnit']);

    // dashboard
    Route::get('/data-calculation', [DashboardController::class, 'getCalculation']);

    // Payment
    Route::get('/payments', [ListPaymentController::class, 'getListPayments']);
    Route::get('/payments/search', [ListPaymentController::class, 'getListPaymentsByKeyword']);
});
