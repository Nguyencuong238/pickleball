<?php

namespace App\Console\Commands;

use App\Models\OcrMatch;
use App\Services\BadgeService;
use App\Services\EloService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OcrAutoConfirmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:auto-confirm {--hours=24 : Hours to wait before auto-confirming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-confirm OCR matches pending for specified hours (default 24h)';

    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $timeout = now()->subHours($hours);

        $matches = OcrMatch::where('status', OcrMatch::STATUS_RESULT_SUBMITTED)
            ->where('result_submitted_at', '<=', $timeout)
            ->get();

        $this->info("Found {$matches->count()} matches to auto-confirm");

        $confirmed = 0;
        $failed = 0;

        foreach ($matches as $match) {
            try {
                DB::transaction(function () use ($match) {
                    // Confirm the result
                    $match->confirmResult();

                    // Process Elo changes
                    $this->eloService->processMatchResult($match);

                    // Load relationships
                    $match->load(['challenger', 'challengerPartner', 'opponent', 'opponentPartner']);

                    // Award badges
                    $challengerWon = $match->winner_team === 'challenger';
                    $participants = [
                        ['user' => $match->challenger, 'won' => $challengerWon],
                        ['user' => $match->challengerPartner, 'won' => $challengerWon],
                        ['user' => $match->opponent, 'won' => !$challengerWon],
                        ['user' => $match->opponentPartner, 'won' => !$challengerWon],
                    ];

                    foreach ($participants as $p) {
                        if ($p['user']) {
                            $p['user']->refresh();
                            $this->badgeService->checkBadgesAfterMatch($p['user'], $match, $p['won']);
                        }
                    }
                });

                $this->info("Auto-confirmed match #{$match->id}");
                Log::info("OCR: Auto-confirmed match #{$match->id}");
                $confirmed++;
            } catch (Exception $e) {
                Log::error("OCR: Failed to auto-confirm match #{$match->id}: " . $e->getMessage());
                $this->error("Failed match #{$match->id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Completed: {$confirmed} confirmed, {$failed} failed");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
