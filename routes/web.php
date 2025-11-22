<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsController as FrontNewsController;
use App\Http\Controllers\Front\DashboardController;
use App\Http\Controllers\Front\HomeYardStadiumController;
use App\Http\Controllers\Front\HomeYardTournamentController;
use App\Http\Controllers\Front\TournamentRegistrationController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\RoundController;
use App\Http\Controllers\Front\GroupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\StadiumController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\MediaUploadController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Facebook OAuth Routes
Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// Admin Login Routes
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// Route bảo vệ
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/booking', [HomeController::class, 'booking'])->name('booking');
Route::get('/courts', [HomeController::class, 'courts'])->name('courts');
Route::get('/tournaments', [HomeController::class, 'tournaments'])->name('tournaments');
Route::get('/social', [HomeController::class, 'social'])->name('social');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/news/{slug}', [FrontNewsController::class, 'show'])->name('news.show');
Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');
Route::get('/courts-detail/{court_id}', [HomeController::class, 'courtsDetail'])->name('courts-detail');
Route::get('/tournaments-detail/{tournament_id}', [HomeController::class, 'tournamentsDetail'])->name('tournaments-detail');

// Tournament Registration
Route::post('/tournament/{tournament}/register', [TournamentRegistrationController::class, 'register'])->name('tournament.register');

// Review Routes (Web - Using AJAX)
Route::middleware('auth')->group(function () {
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('reviews.helpful');
    
    // Favorite routes
    Route::post('/stadiums/{stadium}/toggle-favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});
Route::get('/reviews/stadium/{stadium}', [ReviewController::class, 'getStadiumReviews'])->name('reviews.list');
Route::get('/reviews/summary/{stadium}', [ReviewController::class, 'getRatingSummary'])->name('reviews.summary');

// User Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('user.dashboard');

// HomeYard Dashboard and Stadium CRUD Routes
Route::middleware(['auth', 'role:home_yard'])->prefix('homeyard')->name('homeyard.')->group(function () {
    Route::get('/dashboard/{tournament_id?}', [DashboardController::class, 'homeYardDashboard'])->name('dashboard');
    Route::resource('stadiums', HomeYardStadiumController::class);
    Route::resource('tournaments', HomeYardTournamentController::class);
    Route::post('tournaments/{tournament}/athletes', [HomeYardTournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::put('tournaments/{tournament}/athletes/{athlete}', [HomeYardTournamentController::class, 'updateAthlete'])->name('tournaments.athletes.update');
    Route::delete('tournaments/{tournament}/athletes/{athlete}', [HomeYardTournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
    Route::patch('tournaments/{tournament}/athletes/{athlete}/status', [HomeYardTournamentController::class, 'updateAthleteStatus'])->name('tournaments.athletes.updateStatus');
    Route::post('tournaments/{tournament}/athletes/{athlete}/approve', [HomeYardTournamentController::class, 'approveAthlete'])->name('athletes.approve');
    Route::post('tournaments/{tournament}/athletes/{athlete}/reject', [HomeYardTournamentController::class, 'rejectAthlete'])->name('athletes.reject');
    Route::get('tournaments/{tournament}/athletes/export', [HomeYardTournamentController::class, 'exportAthletes'])->name('tournaments.athletes.export');
    Route::get('athletes', [HomeYardTournamentController::class, 'listAthletes'])->name('athletes.index');
    Route::get('overview', [HomeYardTournamentController::class, 'overview'])->name('overview');
    Route::get('tournaments', [HomeYardTournamentController::class, 'tournaments'])->name('tournaments');
    Route::get('matches', [HomeYardTournamentController::class, 'matches'])->name('matches');
    Route::get('athletes', [HomeYardTournamentController::class, 'athletes'])->name('athletes');
    Route::get('rankings', [HomeYardTournamentController::class, 'rankings'])->name('rankings');
    Route::get('courts', [HomeYardTournamentController::class, 'courts'])->name('courts');
    Route::post('courts', [HomeYardTournamentController::class, 'storeCourt'])->name('courts.store');
    Route::get('courts/{court}/edit', [HomeYardTournamentController::class, 'editCourt'])->name('courts.edit');
    Route::put('courts/{court}', [HomeYardTournamentController::class, 'updateCourt'])->name('courts.update');
    Route::get('courts/{court}/available-slots', [HomeYardTournamentController::class, 'getAvailableSlots'])->name('courts.available-slots');
    Route::get('bookings', [HomeYardTournamentController::class, 'bookings'])->name('bookings');
    Route::post('bookings', [HomeYardTournamentController::class, 'bookingCourt'])->name('bookings.store');
    Route::get('bookings/by-date', [HomeYardTournamentController::class, 'getBookingsByDate'])->name('bookings.by-date');
    Route::get('bookings/stats', [HomeYardTournamentController::class, 'getBookingStats'])->name('bookings.stats');
    
    // Tournament Basic Info
    Route::put('tournaments/{tournament}', [HomeYardTournamentController::class, 'updateTournament'])->name('tournaments.update');
    
    // Tournament Categories, Rounds, and Groups
    Route::post('tournaments/{tournament}/categories', [CategoryController::class, 'store'])->name('tournaments.categories.store');
    Route::put('tournaments/{tournament}/categories/{category}', [CategoryController::class, 'update'])->name('tournaments.categories.update');
    Route::delete('tournaments/{tournament}/categories/{category}', [CategoryController::class, 'destroy'])->name('tournaments.categories.destroy');
    
    Route::post('tournaments/{tournament}/rounds', [RoundController::class, 'store'])->name('tournaments.rounds.store');
    Route::put('tournaments/{tournament}/rounds/{round}', [RoundController::class, 'update'])->name('tournaments.rounds.update');
    Route::delete('tournaments/{tournament}/rounds/{round}', [RoundController::class, 'destroy'])->name('tournaments.rounds.destroy');
    
    Route::post('tournaments/{tournament}/groups', [GroupController::class, 'store'])->name('tournaments.groups.store');
    Route::put('tournaments/{tournament}/groups/{group}', [GroupController::class, 'update'])->name('tournaments.groups.update');
    Route::delete('tournaments/{tournament}/groups/{group}', [GroupController::class, 'destroy'])->name('tournaments.groups.destroy');
    
    // Tournament Courts
    Route::post('tournaments/{tournament}/courts/save', [HomeYardTournamentController::class, 'saveCourts'])->name('tournaments.courts.save');
    
    // Draw/Lottery for athletes
     Route::post('tournaments/{tournament}/draw', [HomeYardTournamentController::class, 'drawAthletes'])->name('tournaments.draw');
     Route::get('tournaments/{tournament}/draw-results', [HomeYardTournamentController::class, 'getDrawResults'])->name('tournaments.draw-results');
     Route::post('tournaments/{tournament}/reset-draw', [HomeYardTournamentController::class, 'resetDraw'])->name('tournaments.reset-draw');
     
     // Match Management
     Route::post('tournaments/{tournament}/matches', [HomeYardTournamentController::class, 'storeMatch'])->name('tournaments.matches.store');
     Route::put('tournaments/{tournament}/matches/{match}', [HomeYardTournamentController::class, 'updateMatch'])
         ->where('match', '[0-9]+')
         ->name('tournaments.matches.update');
     Route::delete('tournaments/{tournament}/matches/{match}', [HomeYardTournamentController::class, 'destroyMatch'])
         ->where('match', '[0-9]+')
         ->name('tournaments.matches.destroy');
     
     // Get category athletes for match creation
     Route::get('tournaments/{tournament}/categories/{categoryId}/athletes', [HomeYardTournamentController::class, 'getCategoryAthletes'])->name('tournaments.categories.athletes');
     
     // Rankings/Leaderboard API
     Route::get('tournaments/{tournament}/rankings', [HomeYardTournamentController::class, 'getRankings'])->name('tournaments.rankings.api');
     Route::get('tournaments/{tournament}/rankings/export', [HomeYardTournamentController::class, 'exportRankingsExcel'])->name('tournaments.rankings.export');
     });

// API Routes for AJAX/Frontend
Route::middleware(['auth', 'role:home_yard'])->prefix('api/homeyard')->name('api.homeyard.')->group(function () {
    Route::get('tournaments', function () {
        $tournaments = \App\Models\Tournament::where('user_id', auth()->id())->get();
        return response()->json(['tournaments' => $tournaments]);
    })->name('tournaments');
    
    Route::get('tournaments/{tournament}/categories', function (\App\Models\Tournament $tournament) {
        $this->authorize('view', $tournament);
        return response()->json(['categories' => $tournament->categories]);
    })->name('tournaments.categories');
});

// Admin routes for managing user permissions
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserPermissionController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserPermissionController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserPermissionController::class, 'update'])->name('users.update');
    Route::resource('news', NewsController::class);
    Route::resource('pages', PageController::class);
    Route::resource('categories', AdminCategoryController::class);
});

// Admin Stadium and Tournament CRUD
Route::middleware(['auth', 'role:admin|home_yard'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('stadiums', StadiumController::class)->only(['index', 'destroy']);
    Route::resource('tournaments', TournamentController::class)->only(['index', 'destroy']);
    Route::post('tournaments/{tournament}/athletes', [TournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::delete('tournaments/{tournament}/athletes/{athlete}', [TournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
});

// Media Upload API Routes
Route::middleware('auth')->group(function () {
    Route::post('/api/upload-media', [MediaUploadController::class, 'uploadMedia'])->name('api.upload-media');
    Route::delete('/api/delete-media/{mediaId}', [MediaUploadController::class, 'deleteMedia'])->name('api.delete-media');
});
