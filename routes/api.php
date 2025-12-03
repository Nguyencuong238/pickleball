<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\TournamentRegistrationController;
use App\Http\Controllers\Api\OcrMatchController;
use App\Http\Controllers\Api\OcrUserController;
use App\Http\Controllers\Api\OcrLeaderboardController;

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

// Tournament categories API endpoint
Route::get('/tournament/{tournament}/categories', [TournamentRegistrationController::class, 'getCategories']);

/*
|--------------------------------------------------------------------------
| OCR (OnePickleball Championship Ranking) Routes
|--------------------------------------------------------------------------
*/

// Protected OCR endpoints (auth required)
Route::prefix('ocr')->middleware('auth:sanctum')->group(function () {
    // Match management
    Route::get('matches', [OcrMatchController::class, 'index']);
    Route::post('matches', [OcrMatchController::class, 'store']);
    Route::get('matches/{match}', [OcrMatchController::class, 'show']);
    Route::patch('matches/{match}/accept', [OcrMatchController::class, 'accept']);
    Route::patch('matches/{match}/reject', [OcrMatchController::class, 'reject']);
    Route::post('matches/{match}/result', [OcrMatchController::class, 'submitResult']);
    Route::post('matches/{match}/confirm', [OcrMatchController::class, 'confirmResult']);
    Route::post('matches/{match}/dispute', [OcrMatchController::class, 'dispute']);
    Route::post('matches/{match}/evidence', [OcrMatchController::class, 'uploadEvidence']);
});

// Public OCR endpoints (no auth required for viewing)
Route::prefix('ocr')->group(function () {
    Route::get('users/{user}/elo', [OcrUserController::class, 'elo']);
    Route::get('users/{user}/badges', [OcrUserController::class, 'badges']);
    Route::get('users/{user}/stats', [OcrUserController::class, 'stats']);
    Route::get('leaderboard', [OcrLeaderboardController::class, 'index']);
    Route::get('leaderboard/distribution', [OcrLeaderboardController::class, 'distribution']);
    Route::get('leaderboard/{rank}', [OcrLeaderboardController::class, 'byRank']);
});
