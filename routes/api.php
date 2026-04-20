<?php

use App\Http\Controllers\GoogleAdsLeadController;
use App\Http\Controllers\WebsiteLeadController;
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

// ── Google Ads Webhook ─────────────────────────────────────────────────────
Route::post('/webhooks/google-ads', [GoogleAdsLeadController::class, 'handle']);

// ── Website Contact Form ───────────────────────────────────────────────────
Route::post('/webhooks/website-lead', [WebsiteLeadController::class, 'handle']);

