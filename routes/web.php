<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsController as FrontNewsController;
use App\Http\Controllers\Front\DashboardController;
use App\Http\Controllers\Front\HomeYardStadiumController;
use App\Http\Controllers\Front\HomeYardTournamentController;
use App\Http\Controllers\Front\AthleteManagementController;
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
use App\Http\Controllers\SocialController;

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

Route::get('/athlete-debug', function() {
    $user = auth()->user();
    return response()->json([
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'roles' => $user->getRoleNames(),
            'tournaments_count' => \App\Models\Tournament::where('user_id', $user->id)->count(),
            'athletes_count' => \App\Models\TournamentAthlete::count(),
        ] : 'Not authenticated'
    ]);
});

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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/booking', [HomeController::class, 'booking'])->name('booking');
Route::get('/courts', [HomeController::class, 'courts'])->name('courts');
Route::get('/tournaments', [HomeController::class, 'tournaments'])->name('tournaments');
Route::get('/social', [HomeController::class, 'social'])->name('social');
Route::post('/social/{social}/join', [HomeController::class, 'joinSocial'])->name('social.join')->middleware('auth');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/news/{slug}', [FrontNewsController::class, 'show'])->name('news.show');
Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');
Route::get('/courts-detail/{court_id}', [HomeController::class, 'courtsDetail'])->name('courts-detail');
Route::get('/tournaments-detail/{tournament_id}', [HomeController::class, 'tournamentsDetail'])->name('tournaments-detail');

// Booking API for front-end
Route::post('/api/bookings', [HomeYardTournamentController::class, 'bookingCourt'])->name('api.bookings.store');
Route::get('/api/courts/{court}/available-slots', [HomeYardTournamentController::class, 'getAvailableSlots'])->name('api.courts.available-slots');

// Tournament Registration
Route::post('/tournament/{tournament}/register', [TournamentRegistrationController::class, 'register'])->name('tournament.register');

// Reviews Routes
Route::get('/reviews/stadium/{stadium}', [ReviewController::class, 'getStadiumReviews'])->name('reviews.list');
Route::get('/reviews/summary/{stadium}', [ReviewController::class, 'getRatingSummary'])->name('reviews.summary');

Route::middleware('auth')->group(function () {
    Route::get('/user/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('reviews.helpful');

    // Favorite routes
    Route::post('/stadiums/{stadium}/toggle-favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Media Upload API Routes
    Route::post('/api/upload-media', [MediaUploadController::class, 'uploadMedia'])->name('api.upload-media');
    Route::delete('/api/delete-media/{mediaId}', [MediaUploadController::class, 'deleteMedia'])->name('api.delete-media');
});

// HomeYard Routes
Route::middleware(['auth', 'role:home_yard'])->prefix('homeyard')->name('homeyard.')->group(function () {
    Route::get('/dashboard/{tournament_id?}', [DashboardController::class, 'homeYardDashboard'])->name('dashboard');
    Route::resource('stadiums', HomeYardStadiumController::class);

    // Tournament export routes (before resource route to take priority)
    Route::get('tournaments/export/list', [HomeYardTournamentController::class, 'exportTournamentsList'])->name('tournaments.export');
    Route::get('tournaments/{tournament}/athletes/export', [HomeYardTournamentController::class, 'exportAthletes'])->name('tournaments.athletes.export');
    
    // Rankings/Leaderboard API (before resource route to take priority)
    Route::get('tournaments-list', [HomeYardTournamentController::class, 'getTournamentsListJson'])->name('tournaments.list.json');
    Route::get('tournaments/stats', [HomeYardTournamentController::class, 'getTournamentStats'])->name('tournaments.stats');
    Route::get('tournaments/rankings-all', [HomeYardTournamentController::class, 'getAllTournamentsRankings'])->name('tournaments.rankings.all');
    Route::get('tournaments/{tournament}/rankings', [HomeYardTournamentController::class, 'getRankings'])->name('tournaments.rankings.api');
    Route::get('tournaments/{tournament}/rankings/export', [HomeYardTournamentController::class, 'exportRankingsExcel'])->name('tournaments.rankings.export');

    Route::resource('tournaments', HomeYardTournamentController::class);
    Route::post('tournaments/{tournament}/athletes', [HomeYardTournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::put('tournaments/{tournament}/athletes/{athlete}', [HomeYardTournamentController::class, 'updateAthlete'])->name('tournaments.athletes.update');
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
    Route::post('courts', [HomeYardTournamentController::class, 'storeCourt'])->name('courts.store');
    Route::get('courts/{court}/edit', [HomeYardTournamentController::class, 'editCourt'])->name('courts.edit');
    Route::get('courts/{court}/pricing', [HomeYardTournamentController::class, 'getPricingTiers'])->name('courts.pricing');
    Route::put('courts/{court}', [HomeYardTournamentController::class, 'updateCourt'])->name('courts.update');
    Route::get('courts/{court}/available-slots', [HomeYardTournamentController::class, 'getAvailableSlots'])->name('courts.available-slots');
    Route::get('bookings', [HomeYardTournamentController::class, 'bookings'])->name('bookings');
    Route::post('bookings', [HomeYardTournamentController::class, 'bookingCourt'])->name('bookings.store');
    Route::post('bookings/calculate-price', [HomeYardTournamentController::class, 'calculateBookingPrice'])->name('bookings.calculate-price');
    Route::get('bookings/by-date', [HomeYardTournamentController::class, 'getBookingsByDate'])->name('bookings.by-date');
    Route::get('bookings/stats', [HomeYardTournamentController::class, 'getBookingStats'])->name('bookings.stats');
    Route::get('bookings/all', [HomeYardTournamentController::class, 'getAllBookings'])->name('bookings.all');
    Route::get('bookings/search', [HomeYardTournamentController::class, 'searchBookings'])->name('bookings.search');
    Route::get('bookings/{bookingId}', [HomeYardTournamentController::class, 'getBookingDetails'])->name('bookings.show');
    Route::put('bookings/{bookingId}/cancel', [HomeYardTournamentController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::delete('bookings/{bookingId}', [HomeYardTournamentController::class, 'deleteBooking'])->name('bookings.delete');

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
    Route::get('tournaments/{tournament}/matches/{match}', [HomeYardTournamentController::class, 'getMatch'])
        ->where('match', '[0-9]+')
        ->name('tournaments.matches.show');
    Route::put('tournaments/{tournament}/matches/{match}', [HomeYardTournamentController::class, 'updateMatch'])
        ->where('match', '[0-9]+')
        ->name('tournaments.matches.update');
    Route::delete('tournaments/{tournament}/matches/{match}', [HomeYardTournamentController::class, 'destroyMatch'])
        ->where('match', '[0-9]+')
        ->name('tournaments.matches.destroy');

    // Get category athletes for match creation
    Route::get('tournaments/{tournament}/categories/{categoryId}/athletes', [HomeYardTournamentController::class, 'getCategoryAthletes'])->name('tournaments.categories.athletes');

    // Get category groups for match creation
    Route::get('tournaments/{tournament}/categories/{categoryId}/groups', [HomeYardTournamentController::class, 'getCategoryGroups'])->name('tournaments.categories.groups');

    Route::get('my-tournaments', function () {
        $tournaments = \App\Models\Tournament::where('user_id', auth()->id())->get();
        return response()->json(['tournaments' => $tournaments]);
    });

    Route::get('tournament-categories/{tournament}', function (\App\Models\Tournament $tournament) {
        $this->authorize('view', $tournament);
        return response()->json(['categories' => $tournament->categories]);
    })->name('tournaments.categories');

    // Athlete Management Routes
    Route::get('athlete-management/list', [AthleteManagementController::class, 'getAthletesByUserTournaments'])->name('athlete-management.list');
    Route::get('athlete-management/tournaments', [AthleteManagementController::class, 'getTournamentsForFilter'])->name('athlete-management.tournaments');
    Route::get('athlete-management/tournament/{tournament_id}', [AthleteManagementController::class, 'getTournamentAthletes'])->name('athlete-management.tournament');
    Route::get('athlete-management/category/{tournament_id}/{category_id}', [AthleteManagementController::class, 'getCategoryStatistics'])->name('athlete-management.category');
    Route::delete('athlete-management/athlete/{athlete_id}', [AthleteManagementController::class, 'deleteAthlete'])->name('athlete-management.delete');
    Route::put('athlete-management/athlete/{athlete_id}', [AthleteManagementController::class, 'updateAthlete'])->name('athlete-management.update');
    Route::get('athlete-management/debug', function() {
        $user = auth()->user();
        $tournaments = \App\Models\Tournament::where('user_id', $user->id)->with('athletes')->get();
        return response()->json([
            'user_id' => $user->id,
            'tournaments_count' => $tournaments->count(),
            'tournaments' => $tournaments->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'athletes_count' => $t->athletes->count(),
                'athletes' => $t->athletes->take(3)
            ])
        ]);
    })->name('athlete-management.debug');

    // Social Events Routes
    Route::resource('socials', SocialController::class);
    Route::post('socials/bulk-delete', [SocialController::class, 'bulkDelete'])->name('socials.bulkDelete');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserPermissionController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserPermissionController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserPermissionController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/approve', [UserPermissionController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [UserPermissionController::class, 'reject'])->name('users.reject');
    Route::resource('news', NewsController::class);
    Route::resource('pages', PageController::class);
    Route::resource('categories', AdminCategoryController::class);

    Route::resource('stadiums', StadiumController::class)->except(['create', 'store']);
    Route::resource('tournaments', TournamentController::class)->except(['create', 'store']);
    Route::post('tournaments/{tournament}/athletes', [TournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::delete('tournaments/{tournament}/athletes/{athlete}', [TournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
});


