<?php

namespace App\Console\Commands;

use App\Models\ClubActivity;
use App\Services\ClubActivityService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringMeets extends Command
{
    protected $signature = 'clubs:generate-recurring-meets {--days=7 : So ngay tao truoc}';
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

        if ($templates->isEmpty()) {
            $this->info('Khong co lich co dinh nao can xu ly.');
            return self::SUCCESS;
        }

        $created = 0;

        foreach ($templates as $template) {
            for ($i = 0; $i < $daysAhead; $i++) {
                $date = Carbon::today()->addDays($i);

                // Chi tao vao dung thu trong tuan
                if ($date->dayOfWeek !== $template->recurrence_day) {
                    continue;
                }

                // Bo qua neu da co instance cho ngay nay
                $exists = $template->children()
                    ->whereDate('activity_date', $date)
                    ->exists();

                if ($exists) {
                    continue;
                }

                try {
                    $instance = $this->service->createRecurringInstance($template, $date);
                    $created++;
                    $this->info("Da tao: {$instance->title} ngay {$date->format('Y-m-d')} cho {$template->club->name}");
                } catch (\Exception $e) {
                    $this->error("Loi khi tao tu template #{$template->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Hoan tat. Da tao {$created} buoi choi tu dong.");
        return self::SUCCESS;
    }
}
