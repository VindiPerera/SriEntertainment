<?php

use App\Http\Controllers\API\PrinterController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// SIM Activation Packages API
Route::get('/sim-activation-packages', [App\Http\Controllers\SimActivationPackageController::class, 'index']);
Route::post('/sim-activation-packages', [App\Http\Controllers\SimActivationPackageController::class, 'store']);
Route::put('/sim-activation-packages/{package}', [App\Http\Controllers\SimActivationPackageController::class, 'update']);
Route::delete('/sim-activation-packages/{package}', [App\Http\Controllers\SimActivationPackageController::class, 'destroy']);
Route::post('/sim-activation-packages/{package}/toggle-active', [App\Http\Controllers\SimActivationPackageController::class, 'toggleActive']);

// Operator Pricing Rules API
Route::get('/operator-pricing-rules', [App\Http\Controllers\OperatorPricingRuleController::class, 'index']);
Route::post('/operator-pricing-rules', [App\Http\Controllers\OperatorPricingRuleController::class, 'store']);
Route::put('/operator-pricing-rules/{rule}', [App\Http\Controllers\OperatorPricingRuleController::class, 'update']);
Route::delete('/operator-pricing-rules/{rule}', [App\Http\Controllers\OperatorPricingRuleController::class, 'destroy']);
Route::post('/operator-pricing-rules/{rule}/toggle-active', [App\Http\Controllers\OperatorPricingRuleController::class, 'toggleActive']);

// SIM Activation Transactions API
Route::post('/sim-activation', [App\Http\Controllers\SimActivationController::class, 'store']);
Route::post('/sim-activation/preview', [App\Http\Controllers\SimActivationController::class, 'preview']);
Route::get('/sim-activation/history', [App\Http\Controllers\SimActivationController::class, 'history']);
