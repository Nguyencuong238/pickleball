<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MatchModel;
use App\Models\Tournament;

// Get first tournament
$tournament = Tournament::first();

if (!$tournament) {
    echo "No tournaments found\n";
    exit;
}

// Create a test in_progress match
$match = MatchModel::create([
    'tournament_id' => $tournament->id,
    'match_date' => now()->format('Y-m-d'),
    'match_time' => now()->format('H:i:s'),
    'status' => 'in_progress',
    'athlete1_id' => 1,
    'athlete2_id' => 2,
    'athlete1_score' => 5,
    'athlete2_score' => 3,
]);

echo "Created test match with ID: " . $match->id . "\n";
echo "Status: " . $match->status . "\n";

// Query live matches
$liveMatches = MatchModel::where('tournament_id', $tournament->id)
    ->where('status', 'in_progress')
    ->get();

echo "Live matches count: " . $liveMatches->count() . "\n";
