<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\MatchModel;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\Instructor;
use App\Models\Club;
use App\Observers\MatchObserver;
use App\Observers\StadiumObserver;
use App\Observers\TournamentObserver;
use App\Observers\InstructorObserver;
use App\Observers\ClubObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        MatchModel::observe(MatchObserver::class);
        Stadium::observe(StadiumObserver::class);
        Tournament::observe(TournamentObserver::class);
        Instructor::observe(InstructorObserver::class);
        Club::observe(ClubObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
