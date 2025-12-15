<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\TournamentRegistrationController;
use App\Http\Controllers\Api\OcrMatchController;
use App\Http\Controllers\Api\OcrUserController;
use App\Http\Controllers\Api\OcrLeaderboardController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\CommunityActivityController;
use App\Http\Controllers\Api\OprsController;
use App\Http\Controllers\Api\OprsLeaderboardController;
use App\Http\Controllers\Api\MatchmakingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StadiumController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\MediaUploadController;
use App\Http\Controllers\Api\BookingController;

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

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public auth endpoints (no auth required)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
});

// Protected auth endpoints (auth required)
Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Tournament API endpoints
Route::get('/tournament/{tournament}', [TournamentController::class, 'show']);
Route::get('/tournament/{tournament}/categories', [TournamentRegistrationController::class, 'getCategories']);

/*
|--------------------------------------------------------------------------
| OCR (OnePickleball Championship Ranking) Routes
|--------------------------------------------------------------------------
*/

// Protected OCR endpoints (auth required)
Route::prefix('ocr')->middleware('auth:api')->group(function () {
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

/*
|--------------------------------------------------------------------------
| OPRS (OnePickleball Rating Score) Routes
|--------------------------------------------------------------------------
*/

// Protected OPRS endpoints (auth required)
Route::prefix('oprs')->middleware('auth:api')->group(function () {
    // User OPRS profile
    Route::get('profile', [OprsController::class, 'profile']);
    Route::get('breakdown', [OprsController::class, 'breakdown']);
    Route::get('history', [OprsController::class, 'history']);
});

// Public OPRS endpoints
Route::prefix('oprs')->group(function () {
    Route::get('levels', [OprsController::class, 'levels']);
    Route::get('leaderboard', [OprsLeaderboardController::class, 'index']);
    Route::get('leaderboard/levels', [OprsLeaderboardController::class, 'levels']);
    Route::get('leaderboard/level/{level}', [OprsLeaderboardController::class, 'byLevel']);
    Route::get('leaderboard/distribution', [OprsLeaderboardController::class, 'distribution']);
    Route::get('users/{user}', [OprsController::class, 'userProfile']);
    Route::get('matchmaking/suggest/{user}', [MatchmakingController::class, 'suggest']);
});

// Protected OPRS matchmaking endpoints
Route::prefix('oprs')->middleware('auth:api')->group(function () {
    Route::post('estimate', [MatchmakingController::class, 'estimateChange']);
});

/*
|--------------------------------------------------------------------------
| Challenge System Routes
|--------------------------------------------------------------------------
*/

// Protected challenge endpoints (auth required)
Route::prefix('challenges')->middleware('auth:api')->group(function () {
    Route::get('available', [ChallengeController::class, 'available']);
    Route::post('submit', [ChallengeController::class, 'submit']);
    Route::get('history', [ChallengeController::class, 'history']);
    Route::get('stats', [ChallengeController::class, 'stats']);
});

// Public challenge types
Route::prefix('challenges')->group(function () {
    Route::get('types', [ChallengeController::class, 'types']);
});

/*
|--------------------------------------------------------------------------
| Community Activity Routes
|--------------------------------------------------------------------------
*/

// Protected community activity endpoints (auth required)
Route::prefix('community')->middleware('auth')->group(function () {
    Route::post('check-in', [CommunityActivityController::class, 'checkIn']);
    Route::post('event', [CommunityActivityController::class, 'recordEvent']);
    Route::post('referral', [CommunityActivityController::class, 'recordReferral']);
    Route::post('social-activity', [CommunityActivityController::class, 'recordSocialActivity']);
    Route::get('history', [CommunityActivityController::class, 'history']);
    Route::get('stats', [CommunityActivityController::class, 'stats']);
});

// Public community activity types
Route::prefix('community')->group(function () {
    Route::get('types', [CommunityActivityController::class, 'types']);
});

/*
|--------------------------------------------------------------------------
| Public API Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Stadiums API
Route::prefix('stadiums')->group(function () {
    Route::get('', [StadiumController::class, 'index']);
    Route::get('{id}', [StadiumController::class, 'show']);
});

// Tournaments API
Route::prefix('tournaments')->group(function () {
    Route::get('', [TournamentController::class, 'index']);
    Route::get('{id}', [TournamentController::class, 'show']);
    Route::get('{id}/standings', [TournamentController::class, 'standings']);
    Route::post('{id}/register', [TournamentController::class, 'register']);
});

// Socials API
Route::prefix('socials')->group(function () {
    Route::get('', [SocialController::class, 'index']);
    Route::get('{id}', [SocialController::class, 'show']);
    Route::get('{id}/participants', [SocialController::class, 'participants']);
});

// News API
Route::prefix('news')->group(function () {
    Route::get('', [NewsController::class, 'index']);
    Route::get('{id}', [NewsController::class, 'show']);
    Route::get('categories', [NewsController::class, 'categories']);
});

// Court Bookings API
Route::prefix('bookings')->middleware('auth:api')->group(function () {
    Route::get('list', [BookingController::class, 'index']);
    Route::get('{id}', [BookingController::class, 'show']);
    Route::patch('{id}', [BookingController::class, 'update']);
    Route::delete('{id}', [BookingController::class, 'destroy']);
    Route::post('booking', [BookingController::class, 'bookingCourt']);
});

/*
|--------------------------------------------------------------------------
| Media Upload Routes (Works for both Web & API)
|--------------------------------------------------------------------------
*/

