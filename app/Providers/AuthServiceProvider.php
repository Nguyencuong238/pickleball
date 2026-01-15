<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Tournament;
use App\Models\VideoComment;
use App\Models\ClubPost;
use App\Models\ClubPostComment;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\SpecialChallenge;
use App\Policies\TournamentPolicy;
use App\Policies\VideoCommentPolicy;
use App\Policies\ClubPostPolicy;
use App\Policies\ClubPostCommentPolicy;
use App\Policies\PointSubmissionPolicy;
use App\Policies\PointTaskPolicy;
use App\Policies\SpecialChallengePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Tournament::class => TournamentPolicy::class,
        VideoComment::class => VideoCommentPolicy::class,
        ClubPost::class => ClubPostPolicy::class,
        ClubPostComment::class => ClubPostCommentPolicy::class,
        PointSubmission::class => PointSubmissionPolicy::class,
        PointTask::class => PointTaskPolicy::class,
        SpecialChallenge::class => SpecialChallengePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
