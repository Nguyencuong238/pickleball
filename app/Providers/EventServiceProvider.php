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

// Point Earning Events
use App\Events\SkillQuizCompleted;
use App\Events\ClubMemberAdded;
use App\Events\OcrMatchConfirmed;
use App\Events\StadiumUpdated;
use App\Events\SocialCreated;
use App\Events\TournamentCreated;
use App\Events\MatchScored;
use App\Events\EloVerified;
use App\Events\EventCheckedIn;

// Point Earning Listeners
use App\Listeners\Points\AwardReferralPoints;
use App\Listeners\Points\AwardClubJoinPoints;
use App\Listeners\Points\AwardOcrMatchPoints;
use App\Listeners\Points\AwardStadiumUpdatePoints;
use App\Listeners\Points\AwardSocialCreatePoints;
use App\Listeners\Points\AwardTournamentCreatePoints;
use App\Listeners\Points\AwardRefereeScoringPoints;
use App\Listeners\Points\AwardExpertVerifyPoints;
use App\Listeners\Points\AwardEventCheckinPoints;

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

        // Point Earning Listeners
        SkillQuizCompleted::class => [
            AwardReferralPoints::class,
        ],
        ClubMemberAdded::class => [
            AwardClubJoinPoints::class,
        ],
        OcrMatchConfirmed::class => [
            AwardOcrMatchPoints::class,
        ],
        StadiumUpdated::class => [
            AwardStadiumUpdatePoints::class,
        ],
        SocialCreated::class => [
            AwardSocialCreatePoints::class,
        ],
        TournamentCreated::class => [
            AwardTournamentCreatePoints::class,
        ],
        MatchScored::class => [
            AwardRefereeScoringPoints::class,
        ],
        EloVerified::class => [
            AwardExpertVerifyPoints::class,
        ],
        EventCheckedIn::class => [
            AwardEventCheckinPoints::class,
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
