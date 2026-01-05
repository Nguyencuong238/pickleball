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
use App\Http\Controllers\Front\BookingInstructorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\StadiumController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\InstructorRegistrationController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\OcrDisputeController;
use App\Http\Controllers\Admin\OcrBadgeController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Api\MediaUploadController;
use App\Http\Controllers\Front\OcrController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\Front\ProfileController;
use App\Http\Controllers\Front\RefereeController;
use App\Http\Controllers\Front\RefereeProfileController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ClubActivityController;

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

// Debug routes (outside middleware groups)
Route::get('/debug/check-athletes/{categoryId?}', [DebugController::class, 'checkAthletes'])->name('debug.check-athletes');
Route::get('/debug/referral', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'referral_code' => $user->referral_code,
        'referral_link' => url('/register?ref=' . $user->referral_code),
        'referral_count' => $user->referrals()->count(),
    ]);
});
Route::get('/debug/users', function () {
    $users = \App\Models\User::limit(5)->get(['id', 'name', 'email', 'referral_code']);
    return response()->json($users);
});

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
Route::get('/booking/{stadium}', [HomeController::class, 'booking'])->name('booking');
Route::get('/courts', [HomeController::class, 'courts'])->name('courts');
Route::get('/tournaments', [HomeController::class, 'tournaments'])->name('tournaments');
Route::get('/social', [HomeController::class, 'social'])->name('social');
Route::post('/social/{social}/join', [HomeController::class, 'joinSocial'])->name('social.join')->middleware('auth');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/news/{slug}', [FrontNewsController::class, 'show'])->name('news.show');
Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');
Route::get('/courts/{stadium}', [HomeController::class, 'courtsDetail'])->name('courts-detail');
Route::get('/tournaments/{tournament}', [HomeController::class, 'tournamentsDetail'])->name('tournaments-detail');
Route::get('/instructors', [HomeController::class, 'instructors'])->name('instructors');
Route::get('/instructors/{instructor}', [HomeController::class, 'instructorDetail'])->name('instructors.detail');
Route::get('/course', [HomeController::class, 'course'])->name('course');
Route::get('/course/{slug}', [HomeController::class, 'courseDetail'])->name('course.detail');

// Quiz Routes
Route::prefix('quiz')->name('quiz.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Front\QuizController::class, 'index'])->name('index');
    Route::get('/questions', [\App\Http\Controllers\Front\QuizController::class, 'getQuestions'])->name('questions');
    Route::post('/submit', [\App\Http\Controllers\Front\QuizController::class, 'submitQuiz'])->name('submit');
    Route::get('/{id}', [\App\Http\Controllers\Front\QuizController::class, 'show'])->name('show');
});

// Skill Assessment Quiz - Public route (preview for guests)
Route::get('/skill-quiz', [\App\Http\Controllers\Front\SkillQuizController::class, 'index'])->name('skill-quiz.index');

// Skill Assessment Quiz - Protected routes (require auth)
Route::middleware('auth')->prefix('skill-quiz')->name('skill-quiz.')->group(function () {
    Route::get('/start', [\App\Http\Controllers\Front\SkillQuizController::class, 'start'])->name('start');
    Route::get('/quiz', [\App\Http\Controllers\Front\SkillQuizController::class, 'quiz'])->name('quiz');
    Route::get('/result/{id}', [\App\Http\Controllers\Front\SkillQuizController::class, 'result'])->name('result');
});

// Skill Quiz AJAX Routes (Web routes for authenticated users)
Route::prefix('api/skill-quiz')->name('web.skill-quiz.')->middleware('auth')->group(function () {
    Route::post('answer', [\App\Http\Controllers\Api\SkillQuizController::class, 'answer'])->name('answer');
    Route::post('submit', [\App\Http\Controllers\Api\SkillQuizController::class, 'submit'])->name('submit');
});

// Academy Public Routes
Route::prefix('academy')->name('academy.')->group(function () {
    Route::get('referees', [RefereeProfileController::class, 'index'])->name('referees.index');
    Route::get('referees/{referee:slug}', [RefereeProfileController::class, 'show'])->name('referees.show');
});

// Booking API for front-end
Route::post('/api/bookings', [HomeController::class, 'bookingCourt'])->name('api.bookings.store');
Route::get('/api/courts/{court}/available-slots', [HomeController::class, 'getAvailableSlots'])->name('api.courts.available-slots');

// Instructor Booking Routes
Route::post('/api/instructor-booking', [BookingInstructorController::class, 'store'])->name('api.instructor-booking.store');

// Instructor Review Routes
Route::post('/api/instructor-review', [\App\Http\Controllers\Api\InstructorReviewController::class, 'store'])->name('api.instructor-review.store')->middleware('auth');
Route::put('/api/instructor-review/{review}', [\App\Http\Controllers\Api\InstructorReviewController::class, 'update'])->name('api.instructor-review.update')->middleware('auth');
Route::delete('/api/instructor-review/{review}', [\App\Http\Controllers\Api\InstructorReviewController::class, 'destroy'])->name('api.instructor-review.destroy')->middleware('auth');
Route::get('/api/instructor/{instructorId}/reviews', [\App\Http\Controllers\Api\InstructorReviewController::class, 'getByInstructor'])->name('api.instructor-review.list');

// Video Comment & Like Routes
Route::post('/api/videos/{video}/comments', [\App\Http\Controllers\Front\VideoCommentController::class, 'store'])->name('api.video-comments.store')->middleware('auth');
Route::delete('/api/comments/{comment}', [\App\Http\Controllers\Front\VideoCommentController::class, 'destroy'])->name('api.comments.destroy')->middleware('auth');
Route::post('/api/comments/{comment}/like', [\App\Http\Controllers\Front\VideoCommentController::class, 'likeComment'])->name('api.comments.like')->middleware('auth');

// Video Like Routes
Route::post('/api/videos/{video}/like', [\App\Http\Controllers\Front\VideoLikeController::class, 'toggle'])->name('api.videos.like')->middleware('auth');

// Tournament Registration
Route::post('/tournament/{tournament}/register', [TournamentRegistrationController::class, 'register'])->name('tournament.register');

// Reviews Routes
Route::get('/reviews/stadium/{stadium}', [ReviewController::class, 'getStadiumReviews'])->name('reviews.list');
Route::get('/reviews/summary/{stadium}', [ReviewController::class, 'getRatingSummary'])->name('reviews.summary');

Route::middleware('auth')->group(function () {
    Route::get('/user/dashboard', [DashboardController::class, 'index'])->name('user.dashboard')->middleware('role:user');
    
    Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('reviews.helpful');

    // Favorite routes
    Route::post('/stadiums/{stadium}/toggle-favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Media Upload API Routes
    Route::post('/api/upload-media', [MediaUploadController::class, 'uploadMedia'])->name('api.upload-media');
    Route::delete('/api/delete-media/{mediaId}', [MediaUploadController::class, 'deleteMedia'])->name('api.delete-media');

    // User Profile Routes
     Route::prefix('user/profile')->name('user.profile.')->group(function () {
         Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
         Route::put('/', [ProfileController::class, 'updateProfile'])->name('update');
         Route::put('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar');
         Route::put('/email', [ProfileController::class, 'updateEmail'])->name('email');
         Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
     });

     // User Referral Routes
     Route::prefix('user/referral')->name('user.referral.')->group(function () {
         Route::get('/', [\App\Http\Controllers\Front\ReferralController::class, 'index'])->name('index');
     });

     // Permission Request Routes
     Route::prefix('user/permission-request')->name('user.permission-request.')->group(function () {
         Route::post('/', [\App\Http\Controllers\Front\PermissionRequestController::class, 'store'])->name('store');
     });
});

// Club & Group Routes
Route::get('clubs', [ClubController::class, 'index'])->name('clubs.index');

Route::middleware('auth')->group(function () {
    Route::get('clubs/create', [ClubController::class, 'create'])->name('clubs.create');
    Route::post('clubs', [ClubController::class, 'store'])->name('clubs.store');
    Route::get('clubs/{club}/edit', [ClubController::class, 'edit'])->name('clubs.edit');
    Route::put('clubs/{club}', [ClubController::class, 'update'])->name('clubs.update');
    Route::delete('clubs/{club}', [ClubController::class, 'destroy'])->name('clubs.destroy');
    
    // Club Activities Routes
    Route::prefix('clubs/{club}/activities')->name('clubs.activities.')->group(function () {
        Route::get('/', [ClubActivityController::class, 'index'])->name('index');
        Route::get('create', [ClubActivityController::class, 'create'])->name('create');
        Route::post('/', [ClubActivityController::class, 'store'])->name('store');
        Route::get('{activity}/edit', [ClubActivityController::class, 'edit'])->name('edit');
        Route::put('{activity}', [ClubActivityController::class, 'update'])->name('update');
        Route::delete('{activity}', [ClubActivityController::class, 'destroy'])->name('destroy');
        Route::get('{activity}', [ClubActivityController::class, 'show'])->name('show');
    });
});

Route::get('clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');

// Instructor favorite routes (without auth middleware - controller checks auth internally)
Route::middleware('web')->group(function () {
    Route::post('/api/instructors/{instructor}/toggle-favorite', [FavoriteController::class, 'toggleInstructor'])->name('instructors.toggle-favorite');
    Route::get('/api/instructors/{instructor}/is-favorited', [FavoriteController::class, 'isInstructorFavorited'])->name('instructors.is-favorited');
});

// HomeYard Routes
Route::middleware(['auth', 'role:home_yard'])->prefix('homeyard')->name('homeyard.')->group(function () {
    Route::resource('stadiums', HomeYardStadiumController::class);

    // Tournament export routes (before resource route to take priority)
    Route::get('tournaments/export/list', [HomeYardTournamentController::class, 'exportTournamentsList'])->name('tournaments.export');
    Route::get('tournaments/{tournament_id}/athletes/export', [HomeYardTournamentController::class, 'exportAthletes'])->name('tournaments.athletes.export');
    
    // Rankings/Leaderboard API (before resource route to take priority)
    Route::get('tournaments-list', [HomeYardTournamentController::class, 'getTournamentsListJson'])->name('tournaments.list.json');
    Route::get('tournaments/stats', [HomeYardTournamentController::class, 'getTournamentStats'])->name('tournaments.stats');
    Route::get('tournaments/rankings-all', [HomeYardTournamentController::class, 'getAllTournamentsRankings'])->name('tournaments.rankings.all');
    Route::get('tournaments/{tournament_id}/rankings', [HomeYardTournamentController::class, 'getRankings'])->name('tournaments.rankings.api');
    Route::get('tournaments/{tournament_id}/rankings/export', [HomeYardTournamentController::class, 'exportRankingsExcel'])->name('tournaments.rankings.export');

    Route::resource('tournaments', HomeYardTournamentController::class);
    Route::get('/tournaments/{tournament_id}/config', [HomeYardTournamentController::class, 'configTournament'])->name('tournaments.config');
    Route::post('tournaments/bulk-delete', [HomeYardTournamentController::class, 'bulkDelete'])->name('tournaments.bulk-delete');
    Route::post('tournaments/{tournament_id}/athletes', [HomeYardTournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::get('tournaments/{tournament_id}/athletes/{athlete_id}', [HomeYardTournamentController::class, 'getAthlete'])->name('tournaments.athletes.get');
    Route::put('tournaments/{tournament_id}/athletes/{athlete_id}', [HomeYardTournamentController::class, 'updateAthlete'])->name('tournaments.athletes.update');
    Route::delete('tournaments/{tournament_id}/athletes/{athlete_id}', [HomeYardTournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
    Route::patch('tournaments/{tournament_id}/athletes/{athlete_id}/status', [HomeYardTournamentController::class, 'updateAthleteStatus'])->name('tournaments.athletes.updateStatus');
    Route::post('tournaments/{tournament_id}/athletes/{athlete_id}/approve', [HomeYardTournamentController::class, 'approveAthlete'])->name('athletes.approve');
    Route::post('tournaments/{tournament_id}/athletes/{athlete_id}/reject', [HomeYardTournamentController::class, 'rejectAthlete'])->name('athletes.reject');
    Route::get('athletes', [HomeYardTournamentController::class, 'listAthletes'])->name('athletes.index');
    Route::get('overview', [HomeYardTournamentController::class, 'overview'])->name('overview');
    Route::get('matches', [HomeYardTournamentController::class, 'matches'])->name('matches');
    Route::get('athletes', [HomeYardTournamentController::class, 'athletes'])->name('athletes');
    Route::get('rankings', [HomeYardTournamentController::class, 'rankings'])->name('rankings');
    Route::get('courts', [HomeYardTournamentController::class, 'courts'])->name('courts');
    Route::post('courts', [HomeYardTournamentController::class, 'storeCourt'])->name('courts.store');
    Route::get('courts/{court}/edit', [HomeYardTournamentController::class, 'editCourt'])->name('courts.edit');
    Route::get('courts/{court}/pricing', [HomeYardTournamentController::class, 'getPricingTiers'])->name('courts.pricing');
    Route::put('courts/{court}', [HomeYardTournamentController::class, 'updateCourt'])->name('courts.update');
    Route::get('courts/{court}/available-slots', [HomeYardTournamentController::class, 'getAvailableSlots'])->name('courts.available-slots');
    Route::post('courts/bulk-delete', [HomeYardTournamentController::class, 'deleteCourts'])->name('courts.bulk-delete');
    Route::get('bookings/{stadiumId?}', [HomeYardTournamentController::class, 'bookings'])->name('bookings');
    Route::post('bookings', [HomeYardTournamentController::class, 'bookingCourt'])->name('bookings.store');
    Route::post('bookings/calculate-price', [HomeYardTournamentController::class, 'calculateBookingPrice'])->name('bookings.calculate-price');
    Route::get('bookings/by-date', [HomeYardTournamentController::class, 'getBookingsByDate'])->name('bookings.by-date');
    Route::get('bookings/stats/{stadiumId}', [HomeYardTournamentController::class, 'getBookingStats'])->name('bookings.stats');
    Route::get('bookings/all', [HomeYardTournamentController::class, 'getAllBookings'])->name('bookings.all');
    Route::get('bookings/search/{stadiumId}', [HomeYardTournamentController::class, 'searchBookings'])->name('bookings.search');
    Route::get('bookings/{bookingId}', [HomeYardTournamentController::class, 'getBookingDetails'])->name('bookings.show');
    Route::put('bookings/{bookingId}/cancel', [HomeYardTournamentController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::delete('bookings/{bookingId}', [HomeYardTournamentController::class, 'deleteBooking'])->name('bookings.delete');

    // Tournament Categories, Rounds, and Groups
    Route::post('tournaments/{tournament_id}/categories', [CategoryController::class, 'store'])->name('tournaments.categories.store');
    Route::post('tournaments/{tournament_id}/categories/{category_id}', [CategoryController::class, 'update'])->name('tournaments.categories.update');
    Route::put('tournaments/{tournament_id}/categories/{category_id}', [CategoryController::class, 'update'])->name('tournaments.categories.update');
    Route::delete('tournaments/{tournament_id}/categories/{category_id}', [CategoryController::class, 'destroy'])->name('tournaments.categories.destroy');

    Route::post('tournaments/{tournament_id}/rounds', [RoundController::class, 'store'])->name('tournaments.rounds.store');
    Route::post('tournaments/{tournament_id}/rounds/{round_id}', [RoundController::class, 'update'])->name('tournaments.rounds.update');
    Route::put('tournaments/{tournament_id}/rounds/{round_id}', [RoundController::class, 'update'])->name('tournaments.rounds.update');
    Route::delete('tournaments/{tournament_id}/rounds/{round_id}', [RoundController::class, 'destroy'])->name('tournaments.rounds.destroy');

    Route::post('tournaments/{tournament_id}/groups', [GroupController::class, 'store'])->name('tournaments.groups.store');
    Route::post('tournaments/{tournament_id}/groups/{group_id}', [GroupController::class, 'update'])->name('tournaments.groups.update');
    Route::put('tournaments/{tournament_id}/groups/{group_id}', [GroupController::class, 'update'])->name('tournaments.groups.update');
    Route::delete('tournaments/{tournament_id}/groups/{group_id}', [GroupController::class, 'destroy'])->name('tournaments.groups.destroy');

    // Tournament Courts
    Route::post('tournaments/{tournament_id}/courts/save', [HomeYardTournamentController::class, 'saveCourts'])->name('tournaments.courts.save');

    // Draw/Lottery for athletes
    Route::post('tournaments/{tournament_id}/draw', [HomeYardTournamentController::class, 'drawAthletes'])->name('tournaments.draw');
    Route::get('tournaments/{tournament_id}/draw-results', [HomeYardTournamentController::class, 'getDrawResults'])->name('tournaments.draw-results');
    Route::get('tournaments/{tournament_id}/check-scheduled-matches', [HomeYardTournamentController::class, 'checkScheduledMatches'])->name('tournaments.check-scheduled-matches');
    Route::post('tournaments/{tournament_id}/reset-draw', [HomeYardTournamentController::class, 'resetDraw'])->name('tournaments.reset-draw');
    Route::get('tournaments/{tournament_id}/manual-draw', [HomeYardTournamentController::class, 'getManualDraw'])->name('tournaments.manual-draw');
    Route::post('tournaments/{tournament_id}/manual-draw-save', [HomeYardTournamentController::class, 'saveManualDraw'])->name('tournaments.manual-draw.save');

    // Match Management
    Route::post('tournaments/{tournament_id}/matches', [HomeYardTournamentController::class, 'storeMatch'])->name('tournaments.matches.store');
    Route::get('tournaments/{tournament_id}/matches', [HomeYardTournamentController::class, 'getMatches'])->name('tournaments.matches.index');
    Route::get('tournaments/{tournament_id}/matches/{match}', [HomeYardTournamentController::class, 'getMatch'])
        ->where('match', '[0-9]+')
        ->name('tournaments.matches.show');
    Route::put('tournaments/{tournament_id}/matches/{match}', [HomeYardTournamentController::class, 'updateMatch'])
        ->where('match', '[0-9]+')
        ->name('tournaments.matches.update');
    Route::delete('tournaments/{tournament_id}/matches/{match}', [HomeYardTournamentController::class, 'destroyMatch'])
        ->where('match', '[0-9]+')
        ->name('tournaments.matches.destroy');

    // Get category athletes for match creation
    Route::get('tournaments/{tournament_id}/categories/{categoryId}/athletes', [HomeYardTournamentController::class, 'getCategoryAthletes'])->name('tournaments.categories.athletes');

    // Get category groups for match creation
    Route::get('tournaments/{tournament_id}/categories/{categoryId}/groups', [HomeYardTournamentController::class, 'getCategoryGroups'])->name('tournaments.categories.groups');

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

    // Tournament Referee Management
    Route::post('tournaments/{tournament_id}/referees/add', [HomeYardTournamentController::class, 'addReferee'])->name('tournaments.referees.add');
    Route::delete('tournaments/{tournament_id}/referees/{referee}', [HomeYardTournamentController::class, 'removeReferee'])->name('tournaments.referees.remove');
    Route::get('referees/available', [HomeYardTournamentController::class, 'getAvailableReferees'])->name('referees.available');
});

// Referee Routes
Route::middleware(['auth', 'role:referee'])->prefix('referee')->name('referee.')->group(function () {
    Route::get('dashboard', [RefereeController::class, 'dashboard'])->name('dashboard');
    Route::get('matches', [RefereeController::class, 'matches'])->name('matches.index');
    Route::get('matches/{match}', [RefereeController::class, 'show'])->name('matches.show');
    Route::post('matches/{match}/start', [RefereeController::class, 'startMatch'])->name('matches.start');
    Route::put('matches/{match}/update-score', [RefereeController::class, 'updateScore'])->name('matches.update-score');

    // Match Control API Routes
    Route::post('matches/{match}/sync-events', [RefereeController::class, 'syncEvents'])->name('matches.sync-events');
    Route::post('matches/{match}/end', [RefereeController::class, 'endMatch'])->name('matches.end');
    Route::get('matches/{match}/state', [RefereeController::class, 'getMatchState'])->name('matches.state');
});

// OCR Frontend Routes
Route::prefix('ocr')->name('ocr.')->group(function () {
    Route::get('/', [OcrController::class, 'index'])->name('index');
    Route::get('/leaderboard', [OcrController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/profile/{user}', [OcrController::class, 'profile'])->name('profile');
    Route::get('/matches-list', [OcrController::class, 'matchesList'])->name('matches.list');
    Route::get('/ocr-matches', [OcrController::class, 'ocrMatches'])->name('ocr-matches');
    Route::get('/search-users', [OcrController::class, 'searchUsers'])->name('search-users')->middleware('auth');

    Route::middleware('auth')->group(function () {
        Route::get('/matches', [OcrController::class, 'matchIndex'])->name('matches.index');
        Route::get('/matches/create', [OcrController::class, 'matchCreate'])->name('matches.create');
        Route::get('/matches/{match}', [OcrController::class, 'matchShow'])->name('matches.show');

        // Challenge System Routes
        Route::get('/challenges', [OcrController::class, 'challenges'])->name('challenges.index');
        Route::get('/challenges/{type}', [OcrController::class, 'challengeSubmit'])->name('challenges.submit');
        Route::post('/challenges', [OcrController::class, 'challengeStore'])->name('challenges.store');

        // Community Hub Routes
        Route::get('/community', [OcrController::class, 'community'])->name('community.index');
        Route::get('/community/checkin', [OcrController::class, 'checkin'])->name('community.checkin');
        Route::post('/community/checkin', [OcrController::class, 'checkinStore'])->name('community.checkin.store');
        Route::post('/community/social-activity', [\App\Http\Controllers\Api\CommunityActivityController::class, 'recordSocialActivity'])->name('community.social-activity');
    });
});

// OCR Match Actions (Web Routes for forms)
Route::prefix('api/ocr')->name('api.ocr.')->middleware('auth')->group(function () {
    Route::post('matches', [\App\Http\Controllers\Api\OcrMatchController::class, 'store'])->name('matches.store');
    Route::post('matches/{match}/accept', [\App\Http\Controllers\Api\OcrMatchController::class, 'accept'])->name('matches.accept');
    Route::post('matches/{match}/reject', [\App\Http\Controllers\Api\OcrMatchController::class, 'reject'])->name('matches.reject');
    Route::post('matches/{match}/start', [\App\Http\Controllers\Api\OcrMatchController::class, 'start'])->name('matches.start');
    Route::post('matches/{match}/result', [\App\Http\Controllers\Api\OcrMatchController::class, 'submitResult'])->name('matches.result');
    Route::post('matches/{match}/confirm', [\App\Http\Controllers\Api\OcrMatchController::class, 'confirmResult'])->name('matches.confirm');
    Route::post('matches/{match}/dispute', [\App\Http\Controllers\Api\OcrMatchController::class, 'dispute'])->name('matches.dispute');
    Route::post('matches/{match}/evidence', [\App\Http\Controllers\Api\OcrMatchController::class, 'uploadEvidence'])->name('matches.evidence');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
     Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
     Route::get('/users', [UserPermissionController::class, 'index'])->name('users.index');
     Route::get('/users/{user}/edit', [UserPermissionController::class, 'edit'])->name('users.edit');
     Route::put('/users/{user}', [UserPermissionController::class, 'update'])->name('users.update');
     Route::post('/users/{user}/approve', [UserPermissionController::class, 'approve'])->name('users.approve');
     Route::post('/users/{user}/reject', [UserPermissionController::class, 'reject'])->name('users.reject');

     // Permission Requests Management
     Route::get('/permission-requests', [\App\Http\Controllers\Admin\PermissionRequestController::class, 'index'])->name('permission-requests.index');
     Route::get('/permission-requests/{permissionRequest}', [\App\Http\Controllers\Admin\PermissionRequestController::class, 'show'])->name('permission-requests.show');
     Route::post('/permission-requests/{permissionRequest}/approve', [\App\Http\Controllers\Admin\PermissionRequestController::class, 'approve'])->name('permission-requests.approve');
     Route::post('/permission-requests/{permissionRequest}/reject', [\App\Http\Controllers\Admin\PermissionRequestController::class, 'reject'])->name('permission-requests.reject');
    Route::resource('news', NewsController::class);
    Route::resource('pages', PageController::class);
    Route::resource('categories', AdminCategoryController::class);

    Route::resource('stadiums', StadiumController::class)->except(['create', 'store']);
    Route::resource('tournaments', TournamentController::class)->except(['create', 'store']);
    Route::post('tournaments/{tournament}/athletes', [TournamentController::class, 'addAthlete'])->name('tournaments.athletes.add');
    Route::delete('tournaments/{tournament}/athletes/{athlete}', [TournamentController::class, 'removeAthlete'])->name('tournaments.athletes.remove');
    
    Route::resource('instructors', InstructorController::class);
    Route::resource('instructor-registrations', InstructorRegistrationController::class)->only(['index', 'destroy']);
    Route::resource('videos', VideoController::class);
    Route::resource('quizzes', QuizController::class);

    // OCR Dispute Management
    Route::prefix('ocr')->name('ocr.')->group(function () {
        Route::get('disputes', [OcrDisputeController::class, 'index'])->name('disputes.index');
        Route::get('disputes/{match}', [OcrDisputeController::class, 'show'])->name('disputes.show');
        Route::post('disputes/{match}/confirm', [OcrDisputeController::class, 'confirmResult'])->name('disputes.confirm');
        Route::post('disputes/{match}/override', [OcrDisputeController::class, 'overrideResult'])->name('disputes.override');
        Route::post('disputes/{match}/cancel', [OcrDisputeController::class, 'cancelMatch'])->name('disputes.cancel');
        Route::get('matches', [OcrDisputeController::class, 'allMatches'])->name('matches.index');

        // OCR Badge Management
        Route::get('badges', [OcrBadgeController::class, 'index'])->name('badges.index');
        Route::get('badges/{badgeType}', [OcrBadgeController::class, 'show'])->name('badges.show');
        Route::post('badges/award', [OcrBadgeController::class, 'award'])->name('badges.award');
        Route::post('badges/revoke', [OcrBadgeController::class, 'revoke'])->name('badges.revoke');
    });

    // OPRS Management Routes
    Route::prefix('oprs')->name('oprs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\OprsController::class, 'dashboard'])->name('dashboard');
        Route::get('users', [\App\Http\Controllers\Admin\OprsController::class, 'users'])->name('users.index');
        Route::get('users/{user}', [\App\Http\Controllers\Admin\OprsController::class, 'userDetail'])->name('users.detail');
        Route::post('users/{user}/adjust', [\App\Http\Controllers\Admin\OprsController::class, 'adjustUser'])->name('users.adjust');
        Route::post('users/{user}/recalculate', [\App\Http\Controllers\Admin\OprsController::class, 'recalculateUser'])->name('users.recalculate');
        Route::get('reports/levels', [\App\Http\Controllers\Admin\OprsController::class, 'levelDistribution'])->name('reports.levels');

        // Challenge Management
        Route::get('challenges', [\App\Http\Controllers\Admin\OprsChallengeController::class, 'index'])->name('challenges.index');
        Route::post('challenges/{challenge}/verify', [\App\Http\Controllers\Admin\OprsChallengeController::class, 'verify'])->name('challenges.verify');
        Route::post('challenges/{challenge}/reject', [\App\Http\Controllers\Admin\OprsChallengeController::class, 'reject'])->name('challenges.reject');

        // Activity Management
        Route::get('activities', [\App\Http\Controllers\Admin\OprsActivityController::class, 'index'])->name('activities.index');
        Route::delete('activities/{activity}', [\App\Http\Controllers\Admin\OprsActivityController::class, 'destroy'])->name('activities.destroy');
    });

    // Skill Quiz Admin Routes
    Route::prefix('skill-quiz')->name('skill-quiz.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SkillQuizController::class, 'dashboard'])->name('dashboard');
        Route::get('attempts', [\App\Http\Controllers\Admin\SkillQuizController::class, 'index'])->name('index');
        Route::get('attempts/{attempt}', [\App\Http\Controllers\Admin\SkillQuizController::class, 'show'])->name('show');
        Route::post('attempts/{attempt}/adjust-elo', [\App\Http\Controllers\Admin\SkillQuizController::class, 'adjustElo'])->name('adjust-elo');
        Route::post('attempts/{attempt}/mark-reviewed', [\App\Http\Controllers\Admin\SkillQuizController::class, 'markReviewed'])->name('mark-reviewed');
    });
});


