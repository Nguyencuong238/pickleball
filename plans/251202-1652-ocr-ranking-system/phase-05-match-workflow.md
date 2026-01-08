# Phase 5: Match Workflow

## Context Links

- [Parent Plan](./plan.md)
- [Phase 4: API Controllers](./phase-04-api-controllers.md)
- [Code Standards](../../docs/code-standards.md)

## Overview

- **Date**: 2025-12-02
- **Priority**: High
- **Implementation Status**: Pending
- **Review Status**: Pending
- **Dependencies**: Phase 4 (API Controllers)

Detail the complete match lifecycle, state machine, notifications, and admin dispute resolution.

## Key Insights

1. State machine prevents invalid transitions
2. Both parties must confirm result (prevents fraud)
3. 24h auto-confirmation if no dispute
4. Admin can resolve disputes and adjust Elo

## Requirements

### Functional

- Complete state machine for match lifecycle
- Notification events at each transition
- Admin dispute resolution panel
- Auto-confirmation after timeout

### Non-Functional

- State transitions are atomic
- Notifications are queued
- Audit trail for all actions

## Architecture

### State Machine

```
                      ┌──────────────┐
                      │   PENDING    │ ◄─── Challenger creates
                      └──────┬───────┘
                             │
              ┌──────────────┼──────────────┐
              │ (reject)     │ (accept)     │
              ▼              ▼              ▼
      ┌───────────┐  ┌───────────┐
      │ CANCELLED │  │ ACCEPTED  │
      └───────────┘  └─────┬─────┘
                           │
                           │ (start)
                           ▼
                    ┌─────────────┐
                    │ IN_PROGRESS │
                    └──────┬──────┘
                           │
                           │ (submitResult)
                           ▼
                  ┌─────────────────┐
                  │ RESULT_SUBMITTED│
                  └────────┬────────┘
                           │
         ┌─────────────────┼─────────────────┐
         │ (dispute)       │ (confirm)       │ (timeout 24h)
         ▼                 ▼                 ▼
  ┌───────────┐     ┌───────────┐     ┌───────────┐
  │ DISPUTED  │     │ CONFIRMED │     │ CONFIRMED │
  └─────┬─────┘     └───────────┘     └───────────┘
        │                  │
        │ (admin resolve)  │
        ▼                  ▼
  ┌───────────┐     ┌─────────────┐
  │ CANCELLED │     │ Elo Updated │
  │ or        │     └─────────────┘
  │ CONFIRMED │
  └───────────┘
```

### Valid State Transitions

| Current State | Action | Next State | Who Can Do |
|---------------|--------|------------|------------|
| pending | accept | accepted | Opponent |
| pending | reject | cancelled | Opponent |
| pending | cancel | cancelled | Challenger |
| accepted | start | in_progress | Any participant |
| accepted | cancel | cancelled | Any participant |
| in_progress | submitResult | result_submitted | Any participant |
| result_submitted | confirm | confirmed | Non-submitter |
| result_submitted | dispute | disputed | Non-submitter |
| result_submitted | timeout | confirmed | System |
| disputed | resolve | confirmed/cancelled | Admin |

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Console/Commands/OcrAutoConfirmCommand.php` | Create | Auto-confirm timeout matches |
| `app/Events/OcrMatchCreated.php` | Create | Match creation event |
| `app/Events/OcrMatchAccepted.php` | Create | Match accepted event |
| `app/Events/OcrMatchResultSubmitted.php` | Create | Result submission event |
| `app/Events/OcrMatchConfirmed.php` | Create | Match confirmed event |
| `app/Listeners/SendOcrMatchNotification.php` | Create | Notification listener |
| `app/Notifications/OcrMatchInviteNotification.php` | Create | Email/database notification |
| `app/Http/Controllers/Admin/OcrDisputeController.php` | Create | Admin dispute panel |

### Files to Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Console/Kernel.php` | Modify | Schedule auto-confirm |
| `routes/web.php` | Modify | Add admin dispute routes |

## Implementation Steps

### Step 1: Create Auto-Confirm Command

```php
<?php
// app/Console/Commands/OcrAutoConfirmCommand.php

namespace App\Console\Commands;

use App\Models\OcrMatch;
use App\Services\BadgeService;
use App\Services\EloService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OcrAutoConfirmCommand extends Command
{
    protected $signature = 'ocr:auto-confirm';
    protected $description = 'Auto-confirm matches pending for 24h';

    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $timeout = now()->subHours(24);

        $matches = OcrMatch::where('status', OcrMatch::STATUS_RESULT_SUBMITTED)
            ->where('result_submitted_at', '<=', $timeout)
            ->get();

        $this->info("Found {$matches->count()} matches to auto-confirm");

        foreach ($matches as $match) {
            try {
                DB::transaction(function () use ($match) {
                    $match->confirmResult();
                    $this->eloService->processMatchResult($match);

                    // Award badges
                    $challengerWon = $match->winner_team === 'challenger';
                    foreach (['challenger', 'challengerPartner', 'opponent', 'opponentPartner'] as $rel) {
                        $user = $match->$rel;
                        if ($user) {
                            $won = in_array($rel, ['challenger', 'challengerPartner'])
                                ? $challengerWon
                                : !$challengerWon;
                            $user->refresh();
                            $this->badgeService->checkBadgesAfterMatch($user, $match, $won);
                        }
                    }
                });

                $this->info("Auto-confirmed match #{$match->id}");
            } catch (\Exception $e) {
                Log::error("Failed to auto-confirm match #{$match->id}: " . $e->getMessage());
                $this->error("Failed match #{$match->id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
```

### Step 2: Create Events

```php
<?php
// app/Events/OcrMatchCreated.php

namespace App\Events;

use App\Models\OcrMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrMatchCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OcrMatch $match)
    {
    }
}
```

```php
<?php
// app/Events/OcrMatchAccepted.php

namespace App\Events;

use App\Models\OcrMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrMatchAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OcrMatch $match)
    {
    }
}
```

```php
<?php
// app/Events/OcrMatchResultSubmitted.php

namespace App\Events;

use App\Models\OcrMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrMatchResultSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OcrMatch $match)
    {
    }
}
```

```php
<?php
// app/Events/OcrMatchConfirmed.php

namespace App\Events;

use App\Models\OcrMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrMatchConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OcrMatch $match)
    {
    }
}
```

### Step 3: Create Notification

```php
<?php
// app/Notifications/OcrMatchInviteNotification.php

namespace App\Notifications;

use App\Models\OcrMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OcrMatchInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OcrMatch $match,
        public string $type
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->type) {
            'invite' => 'You have a new match invitation',
            'accepted' => 'Your match invitation was accepted',
            'result_submitted' => 'Match result submitted - please confirm',
            'confirmed' => 'Match confirmed - Elo updated',
            'disputed' => 'Match result disputed',
            default => 'OCR Match Update',
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->getMessageBody());

        if ($this->type === 'invite' || $this->type === 'result_submitted') {
            $message->action('View Match', url('/ocr/matches/' . $this->match->id));
        }

        return $message;
    }

    private function getMessageBody(): string
    {
        return match ($this->type) {
            'invite' => "{$this->match->challenger->name} has challenged you to a match!",
            'accepted' => "{$this->match->opponent->name} accepted your match invitation.",
            'result_submitted' => "A result has been submitted. Please confirm or dispute within 24 hours.",
            'confirmed' => "Match confirmed! Your Elo has been updated.",
            'disputed' => "The match result has been disputed and is under review.",
            default => "Your match has been updated.",
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'match_id' => $this->match->id,
            'type' => $this->type,
            'challenger_name' => $this->match->challenger->name,
            'opponent_name' => $this->match->opponent->name,
        ];
    }
}
```

### Step 4: Create Admin Dispute Controller

```php
<?php
// app/Http/Controllers/Admin/OcrDisputeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OcrMatch;
use App\Services\EloService;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OcrDisputeController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {
    }

    /**
     * List disputed matches
     */
    public function index()
    {
        $disputes = OcrMatch::where('status', OcrMatch::STATUS_DISPUTED)
            ->with(['challenger', 'opponent', 'challengerPartner', 'opponentPartner', 'media'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.ocr.disputes.index', compact('disputes'));
    }

    /**
     * Show dispute details
     */
    public function show(OcrMatch $match)
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return redirect()->route('admin.ocr.disputes.index')
                ->with('error', 'Match is not disputed');
        }

        $match->load(['challenger', 'opponent', 'challengerPartner', 'opponentPartner', 'media']);

        return view('admin.ocr.disputes.show', compact('match'));
    }

    /**
     * Resolve dispute - confirm result
     */
    public function confirmResult(OcrMatch $match, Request $request)
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return back()->with('error', 'Match is not disputed');
        }

        try {
            DB::transaction(function () use ($match) {
                $match->update([
                    'status' => OcrMatch::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);

                $this->eloService->processMatchResult($match);

                // Award badges
                $challengerWon = $match->winner_team === 'challenger';
                foreach (['challenger', 'challengerPartner', 'opponent', 'opponentPartner'] as $rel) {
                    $user = $match->$rel;
                    if ($user) {
                        $won = in_array($rel, ['challenger', 'challengerPartner'])
                            ? $challengerWon
                            : !$challengerWon;
                        $user->refresh();
                        $this->badgeService->checkBadgesAfterMatch($user, $match, $won);
                    }
                }
            });

            return redirect()->route('admin.ocr.disputes.index')
                ->with('success', 'Match confirmed and Elo updated');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm: ' . $e->getMessage());
        }
    }

    /**
     * Resolve dispute - override result
     */
    public function overrideResult(OcrMatch $match, Request $request)
    {
        $validated = $request->validate([
            'challenger_score' => 'required|integer|min:0|max:99',
            'opponent_score' => 'required|integer|min:0|max:99|different:challenger_score',
        ]);

        try {
            DB::transaction(function () use ($match, $validated) {
                $match->update([
                    'challenger_score' => $validated['challenger_score'],
                    'opponent_score' => $validated['opponent_score'],
                    'winner_team' => $validated['challenger_score'] > $validated['opponent_score']
                        ? 'challenger'
                        : 'opponent',
                    'status' => OcrMatch::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);

                $this->eloService->processMatchResult($match);

                // Award badges
                $challengerWon = $match->winner_team === 'challenger';
                foreach (['challenger', 'challengerPartner', 'opponent', 'opponentPartner'] as $rel) {
                    $user = $match->$rel;
                    if ($user) {
                        $won = in_array($rel, ['challenger', 'challengerPartner'])
                            ? $challengerWon
                            : !$challengerWon;
                        $user->refresh();
                        $this->badgeService->checkBadgesAfterMatch($user, $match, $won);
                    }
                }
            });

            return redirect()->route('admin.ocr.disputes.index')
                ->with('success', 'Result overridden and Elo updated');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to override: ' . $e->getMessage());
        }
    }

    /**
     * Resolve dispute - cancel match
     */
    public function cancelMatch(OcrMatch $match)
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return back()->with('error', 'Match is not disputed');
        }

        $match->update(['status' => OcrMatch::STATUS_CANCELLED]);

        return redirect()->route('admin.ocr.disputes.index')
            ->with('success', 'Match cancelled');
    }
}
```

### Step 5: Schedule Auto-Confirm

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Auto-confirm OCR matches after 24h
    $schedule->command('ocr:auto-confirm')->hourly();
}
```

### Step 6: Add Admin Routes

Add to `routes/web.php` in admin group:

```php
// OCR Dispute Management
Route::prefix('ocr/disputes')->name('ocr.disputes.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\OcrDisputeController::class, 'index'])->name('index');
    Route::get('/{match}', [App\Http\Controllers\Admin\OcrDisputeController::class, 'show'])->name('show');
    Route::post('/{match}/confirm', [App\Http\Controllers\Admin\OcrDisputeController::class, 'confirmResult'])->name('confirm');
    Route::post('/{match}/override', [App\Http\Controllers\Admin\OcrDisputeController::class, 'overrideResult'])->name('override');
    Route::post('/{match}/cancel', [App\Http\Controllers\Admin\OcrDisputeController::class, 'cancelMatch'])->name('cancel');
});
```

## Todo List

- [ ] Create OcrAutoConfirmCommand
- [ ] Create event classes (4 events)
- [ ] Create OcrMatchInviteNotification
- [ ] Create OcrDisputeController
- [ ] Update Kernel.php with schedule
- [ ] Update routes/web.php with admin routes
- [ ] Create admin dispute views
- [ ] Register event listeners in EventServiceProvider

## Success Criteria

1. State machine enforces valid transitions
2. Notifications sent at each stage
3. Auto-confirm runs hourly
4. Admin can resolve disputes
5. Elo correctly rolled back on override

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Missed auto-confirm | Medium | Hourly schedule, logging |
| Notification spam | Low | Queued, rate limited |
| Admin abuse | High | Audit log all admin actions |

## Security Considerations

- Only admin can resolve disputes
- All admin actions logged
- State machine prevents invalid ops
- Email verification recommended

## Next Steps

After workflow complete, proceed to [Phase 6: Badge System](./phase-06-badge-system.md)
