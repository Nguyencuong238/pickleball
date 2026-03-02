# Phase 5: Scheduled Command

## Context Links
- ClubActivityService: `app/Services/ClubActivityService.php` (Phase 2)
- ClubActivity model: `app/Models/ClubActivity.php`
- Laravel scheduler: `app/Console/Kernel.php`

## Overview
- **Priority:** P2
- **Status:** complete
- Create artisan command `clubs:generate-recurring-meets` that runs daily
- Finds all recurring templates, generates instances for next 7 days if not yet created
- Register in Kernel for daily execution

## Requirements
- Only generate from active recurring templates (status=upcoming, type=recurring, parent_activity_id=null)
- Skip if instance already exists for target date
- Create 7 days ahead (configurable)
- Log each generated instance
- Idempotent: safe to run multiple times

## Related Code Files

### Files to CREATE:
- `app/Console/Commands/GenerateRecurringMeets.php`

### Files to MODIFY:
- `app/Console/Kernel.php` -- register daily schedule

## Implementation Steps

### Step 1: Create `GenerateRecurringMeets` command

```php
// app/Console/Commands/GenerateRecurringMeets.php
namespace App\Console\Commands;

use App\Models\ClubActivity;
use App\Services\ClubActivityService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringMeets extends Command
{
    protected $signature = 'clubs:generate-recurring-meets {--days=7 : Days ahead to generate}';
    protected $description = 'Tao buoi choi tu dong tu lich co dinh hang tuan';

    public function __construct(private ClubActivityService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $daysAhead = (int) $this->option('days');
        $templates = ClubActivity::recurringTemplates()
            ->where('status', 'upcoming')
            ->with('club')
            ->get();

        $created = 0;

        foreach ($templates as $template) {
            for ($i = 0; $i < $daysAhead; $i++) {
                $date = Carbon::today()->addDays($i);

                // Skip if not the right day of week
                if ($date->dayOfWeek !== $template->recurrence_day) {
                    continue;
                }

                // Skip if instance already exists for this date
                $exists = $template->children()
                    ->whereDate('activity_date', $date)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $instance = $this->service->createRecurringInstance($template, $date);
                $created++;
                $this->info("Created: {$instance->title} on {$date->format('Y-m-d')} for {$template->club->name}");
            }
        }

        $this->info("Done. Created {$created} recurring meet instances.");
        return self::SUCCESS;
    }
}
```

### Step 2: Register in Kernel

```php
// app/Console/Kernel.php -- inside schedule() method
$schedule->command('clubs:generate-recurring-meets')->daily()->at('06:00');
```

## Todo List
- [x] Create GenerateRecurringMeets command
- [x] Register in Kernel.php
- [x] Test manually: `php artisan clubs:generate-recurring-meets`
- [x] Verify idempotency: run twice, no duplicates

## Success Criteria
- Command generates instances for correct day of week
- Skips existing instances (idempotent)
- Logs output for monitoring
- Runs daily at 06:00 via scheduler

## Risk Assessment
- **Timezone**: `Carbon::today()` uses app timezone -- ensure consistent
- **Orphaned templates**: If club is deleted, FK cascade handles children; template check needs active club
- **Performance**: N+1 on `children()` check -- acceptable for small dataset; add index if needed
