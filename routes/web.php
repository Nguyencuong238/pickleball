<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsController as FrontNewsController;
use App\Http\Controllers\Front\DashboardController;
use App\Http\Controllers\Front\HomeYardStadiumController;
use App\Http\Controllers\Front\HomeYardTournamentController;
use App\Http\Controllers\Front\TournamentRegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\StadiumController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\CategoryController;
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
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return view('home-yard.dashboard', compact('user'));
    })->name('dashboard');
    Route::resource('stadiums', HomeYardStadiumController::class);
    Route::resource('tournaments', HomeYardTournamentController::class);
    Route::post('tournaments/{tournament}/athletes', [HomeYardTournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::delete('tournaments/{tournament}/athletes/{athlete}', [HomeYardTournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
    Route::patch('tournaments/{tournament}/athletes/{athlete}/status', [HomeYardTournamentController::class, 'updateAthleteStatus'])->name('tournaments.athletes.updateStatus');
    Route::post('tournaments/{tournament}/athletes/{athlete}/approve', [HomeYardTournamentController::class, 'approveAthlete'])->name('athletes.approve');
    Route::post('tournaments/{tournament}/athletes/{athlete}/reject', [HomeYardTournamentController::class, 'rejectAthlete'])->name('athletes.reject');
    Route::get('athletes', [HomeYardTournamentController::class, 'listAthletes'])->name('athletes.index');
    Route::get('overview', [HomeYardTournamentController::class, 'overview'])->name('overview');
    Route::get('tournaments', [HomeYardTournamentController::class, 'tournaments'])->name('tournaments');
    Route::get('matches', [HomeYardTournamentController::class, 'matches'])->name('matches');
    Route::get('athletes', [HomeYardTournamentController::class, 'athletes'])->name('athletes');
    Route::get('rankings', [HomeYardTournamentController::class, 'rankings'])->name('rankings');
    Route::get('courts', [HomeYardTournamentController::class, 'courts'])->name('courts');
    Route::get('bookings', [HomeYardTournamentController::class, 'bookings'])->name('bookings');
});

// Admin routes for managing user permissions
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserPermissionController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserPermissionController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserPermissionController::class, 'update'])->name('users.update');
    Route::resource('news', NewsController::class);
    Route::resource('pages', PageController::class);
    Route::resource('categories', CategoryController::class);
});

// Admin Stadium and Tournament CRUD
Route::middleware(['auth', 'role:admin|home_yard'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('stadiums', StadiumController::class);
    Route::resource('tournaments', TournamentController::class);
    Route::post('tournaments/{tournament}/athletes', [TournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::delete('tournaments/{tournament}/athletes/{athlete}', [TournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
});
