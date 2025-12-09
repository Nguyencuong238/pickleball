<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tournament;
use App\Models\MatchModel;
use App\Models\User;
use App\Models\TournamentCategory;
use App\Models\TournamentAthlete;
use App\Models\Round;
use Illuminate\Support\Facades\DB;

class TestTournamentOcr extends Command
{
    protected $signature = 'test:tournament-ocr {--clean}';
    protected $description = 'Test OCR logic for tournament matches';

    public function handle()
    {
        if ($this->option('clean')) {
            $this->cleanTestData();
            return;
        }

        $this->info('🎯 Starting Tournament OCR Test...\n');

        // Step 1: Create test users
        $this->info('Step 1: Creating test users...');
        $user1 = User::firstOrCreate(
            ['email' => 'athlete_a_test@test.com'],
            [
                'name' => 'Athlete A (Test)',
                'password' => bcrypt('password'),
                'elo_rating' => 1000,
                'total_ocr_matches' => 30,
                'ocr_wins' => 18,
                'ocr_losses' => 12,
                'elo_rank' => 'Intermediate'
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'athlete_b_test@test.com'],
            [
                'name' => 'Athlete B (Test)',
                'password' => bcrypt('password'),
                'elo_rating' => 900,
                'total_ocr_matches' => 15,
                'ocr_wins' => 8,
                'ocr_losses' => 7,
                'elo_rank' => 'Amateur'
            ]
        );

        $this->info("✓ User A: {$user1->name} (Elo={$user1->elo_rating})");
        $this->info("✓ User B: {$user2->name} (Elo={$user2->elo_rating})\n");

        // Step 2: Create tournament with is_ocr=1
        $this->info('Step 2: Creating tournament with is_ocr=1...');
        $tournament = Tournament::create([
            'user_id' => 1, // Home yard admin
            'name' => 'Test OCR Tournament',
            'description' => 'Test tournament for OCR functionality',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(1)->toDateString(),
            'is_ocr' => 1,
            'status' => 1,
            'price' => 0,
            'max_participants' => 100,
        ]);
        $this->info("✓ Tournament created: {$tournament->name} (ID={$tournament->id}, is_ocr={$tournament->is_ocr})\n");

        // Step 3: Create category
        $this->info('Step 3: Creating tournament category...');
        $category = TournamentCategory::create([
            'tournament_id' => $tournament->id,
            'category_name' => 'Men Singles Test',
            'min_age' => 18,
            'max_age' => 100,
        ]);
        $this->info("✓ Category created: {$category->category_name}\n");

        // Step 4: Create round
        $this->info('Step 4: Creating round...');
        $round = Round::create([
            'tournament_id' => $tournament->id,
            'round_name' => 'Round 1',
            'status' => 1,
            'round_number' => 1,
            'round_type' => 'knockout',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(1)->toDateString(),
        ]);
        $this->info("✓ Round created: {$round->round_name}\n");

        // Step 5: Add athletes to tournament
        $this->info('Step 5: Adding athletes to tournament...');
        $ta1 = TournamentAthlete::firstOrCreate(
            ['tournament_id' => $tournament->id, 'user_id' => $user1->id],
            ['athlete_name' => $user1->name]
        );

        $ta2 = TournamentAthlete::firstOrCreate(
            ['tournament_id' => $tournament->id, 'user_id' => $user2->id],
            ['athlete_name' => $user2->name]
        );
        $this->info("✓ Athlete A added (ID={$ta1->id})");
        $this->info("✓ Athlete B added (ID={$ta2->id})\n");

        // Step 6: Create match
        $this->info('Step 6: Creating match...');
        $match = MatchModel::create([
            'tournament_id' => $tournament->id,
            'category_id' => $category->id,
            'round_id' => $round->id,
            'athlete1_id' => $ta1->id,
            'athlete1_name' => $ta1->athlete_name,
            'athlete2_id' => $ta2->id,
            'athlete2_name' => $ta2->athlete_name,
            'match_date' => now()->toDateString(),
            'match_time' => now()->format('H:i'),
            'status' => 'in_progress',
            'athlete1_score' => 0,
            'athlete2_score' => 0,
        ]);
        $this->info("✓ Match created (ID={$match->id})\n");

        // Step 7: Simulate end match with A winning
        $this->info('Step 7: Simulating match completion (A wins 11-7)...');
        $this->info('Before:');
        $this->table(
            ['User', 'Elo', 'Matches', 'Wins', 'Losses'],
            [
                ['A', (string)$user1->elo_rating, (string)$user1->total_ocr_matches, (string)$user1->ocr_wins, (string)$user1->ocr_losses],
                ['B', (string)$user2->elo_rating, (string)$user2->total_ocr_matches, (string)$user2->ocr_wins, (string)$user2->ocr_losses],
            ]
        );

        // Update match
        $match->update([
            'status' => 'completed',
            'athlete1_score' => 11,
            'athlete2_score' => 7,
            'final_score' => '11-7',
            'winner_id' => $ta1->id,
            'actual_end_time' => now(),
        ]);

        // Manually trigger OCR processing (simulate what controller does)
        $this->info('\nProcessing OCR + OPRS...');
        $this->processOcrManually($match, $user1, $user2, $ta1, $ta2);

        // Step 8: Check results
        $this->info('\nAfter:');
        $user1->refresh();
        $user2->refresh();

        $change1 = $user1->elo_rating - 1000;
        $change2 = $user2->elo_rating - 900;
        $changeStr1 = $change1 >= 0 ? '+' . $change1 : (string)$change1;
        $changeStr2 = $change2 >= 0 ? '+' . $change2 : (string)$change2;

        $this->table(
            ['User', 'Elo', 'Matches', 'Wins', 'Losses', 'Change'],
            [
                ['A', (string)$user1->elo_rating, (string)$user1->total_ocr_matches, (string)$user1->ocr_wins, (string)$user1->ocr_losses, $changeStr1],
                ['B', (string)$user2->elo_rating, (string)$user2->total_ocr_matches, (string)$user2->ocr_wins, (string)$user2->ocr_losses, $changeStr2],
            ]
        );

        // Step 9: Check database
        $this->info('\n📊 Database Records:');

        // Check EloHistory
        $eloHistories = DB::table('elo_histories')
            ->whereIn('user_id', [$user1->id, $user2->id])
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        if ($eloHistories->count() > 0) {
            $this->info("✓ EloHistory: Found " . $eloHistories->count() . " records");
            foreach ($eloHistories as $h) {
                $reason = $h->change_reason === 'match_win' ? '✓ WIN' : '✗ LOSS';
                $change = $h->change_amount >= 0 ? '+' . $h->change_amount : $h->change_amount;
                $this->line("  User {$h->user_id}: {$h->elo_before} → {$h->elo_after} ({$change}) {$reason}");
            }
        } else {
            $this->error("✗ No EloHistory records found!");
        }

        // Check OprsHistory
        $oprsHistories = DB::table('oprs_histories')
            ->whereIn('user_id', [$user1->id, $user2->id])
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        if ($oprsHistories->count() > 0) {
            $this->info("\n✓ OprsHistory: Found " . $oprsHistories->count() . " records");
            foreach ($oprsHistories as $o) {
                $this->line("  User {$o->user_id}: OPRS={$o->total_oprs}, Level={$o->opr_level}, Reason={$o->change_reason}");
            }
        } else {
            $this->error("✗ No OprsHistory records found!");
        }

        $this->info('\n✅ Test completed!');
        $this->line("Tournament ID: {$tournament->id}");
        $this->line("Match ID: {$match->id}");
        $this->line("User A ID: {$user1->id}");
        $this->line("User B ID: {$user2->id}");

        $this->info('\nRun with --clean flag to delete test data:');
        $this->line("php artisan test:tournament-ocr --clean");
    }

    private function processOcrManually(MatchModel $match, User $user1, User $user2, $ta1, $ta2)
    {
        try {
            $eloService = app(\App\Services\EloService::class);
            $oprsService = app(\App\Services\OprsService::class);

            $athlete1Won = $match->winner_id === $match->athlete1_id;

            DB::beginTransaction();

            $elo1Before = $user1->elo_rating;
            $elo2Before = $user2->elo_rating;

            $kFactor1 = $eloService->getKFactor($user1);
            $kFactor2 = $eloService->getKFactor($user2);

            $change1 = $eloService->calculateRatingChange(
                $elo1Before,
                $elo2Before,
                $athlete1Won,
                $kFactor1
            );

            $change2 = $eloService->calculateRatingChange(
                $elo2Before,
                $elo1Before,
                !$athlete1Won,
                $kFactor2
            );

            $elo1After = max(100, $elo1Before + $change1);
            $elo2After = max(100, $elo2Before + $change2);

            $user1->update([
                'elo_rating' => $elo1After,
                'total_ocr_matches' => $user1->total_ocr_matches + 1,
                'ocr_wins' => $athlete1Won ? $user1->ocr_wins + 1 : $user1->ocr_wins,
                'ocr_losses' => !$athlete1Won ? $user1->ocr_losses + 1 : $user1->ocr_losses,
            ]);

            $user2->update([
                'elo_rating' => $elo2After,
                'total_ocr_matches' => $user2->total_ocr_matches + 1,
                'ocr_wins' => !$athlete1Won ? $user2->ocr_wins + 1 : $user2->ocr_wins,
                'ocr_losses' => $athlete1Won ? $user2->ocr_losses + 1 : $user2->ocr_losses,
            ]);

            $user1->updateEloRank();
            $user2->updateEloRank();

            DB::table('elo_histories')->insert([
                'user_id' => $user1->id,
                'ocr_match_id' => null,
                'elo_before' => $elo1Before,
                'elo_after' => $elo1After,
                'change_amount' => $change1,
                'change_reason' => $athlete1Won ? 'match_win' : 'match_loss',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('elo_histories')->insert([
                'user_id' => $user2->id,
                'ocr_match_id' => null,
                'elo_before' => $elo2Before,
                'elo_after' => $elo2After,
                'change_amount' => $change2,
                'change_reason' => !$athlete1Won ? 'match_win' : 'match_loss',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ========== SAVE OprsHistory ==========
            $oprs1After = $oprsService->calculateOprs($user1);
            $oprs2After = $oprsService->calculateOprs($user2);

            DB::table('oprs_histories')->insert([
                'user_id' => $user1->id,
                'elo_score' => $user1->elo_rating,
                'challenge_score' => $user1->challenge_score ?? 0,
                'community_score' => $user1->community_score ?? 0,
                'total_oprs' => $oprs1After,
                'opr_level' => $oprsService->calculateOprLevel($oprs1After),
                'change_reason' => 'match_result',
                'metadata' => json_encode([
                    'match_id' => $match->id,
                    'tournament_id' => $match->tournament_id,
                    'elo_change' => $change1,
                    'match_result' => $athlete1Won ? 'win' : 'loss',
                    'opponent_id' => $user2->id,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('oprs_histories')->insert([
                'user_id' => $user2->id,
                'elo_score' => $user2->elo_rating,
                'challenge_score' => $user2->challenge_score ?? 0,
                'community_score' => $user2->community_score ?? 0,
                'total_oprs' => $oprs2After,
                'opr_level' => $oprsService->calculateOprLevel($oprs2After),
                'change_reason' => 'match_result',
                'metadata' => json_encode([
                    'match_id' => $match->id,
                    'tournament_id' => $match->tournament_id,
                    'elo_change' => $change2,
                    'match_result' => !$athlete1Won ? 'win' : 'loss',
                    'opponent_id' => $user1->id,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->info("✓ OCR processed successfully");
            $changeStr1 = $change1 >= 0 ? '+' . $change1 : (string)$change1;
            $changeStr2 = $change2 >= 0 ? '+' . $change2 : (string)$change2;
            $this->line("  User A: K={$kFactor1}, Change={$changeStr1}, {$elo1Before}→{$elo1After}");
            $this->line("  User B: K={$kFactor2}, Change={$changeStr2}, {$elo2Before}→{$elo2After}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ OCR processing failed: " . $e->getMessage());
        }
    }

    private function cleanTestData()
    {
        $this->info('🧹 Cleaning test data...');

        // Delete test users
        $user1 = User::where('email', 'athlete_a_test@test.com')->first();
        $user2 = User::where('email', 'athlete_b_test@test.com')->first();

        if ($user1 || $user2) {
            // Delete related records
            if ($user1) {
                DB::table('elo_histories')->where('user_id', $user1->id)->delete();
                $user1->delete();
                $this->info("✓ Deleted User A");
            }
            if ($user2) {
                DB::table('elo_histories')->where('user_id', $user2->id)->delete();
                $user2->delete();
                $this->info("✓ Deleted User B");
            }
        }

        // Delete test tournament
        $tournament = Tournament::where('name', 'Test OCR Tournament')->first();
        if ($tournament) {
            $tournament->delete();
            $this->info("✓ Deleted tournament");
        }

        $this->info('✅ Cleanup completed!');
    }
}
