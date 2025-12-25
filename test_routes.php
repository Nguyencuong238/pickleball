<?php

require 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing SEO Route URLs ===\n\n";

$stadium = \App\Models\Stadium::first();
if ($stadium) {
    echo "Stadium: " . $stadium->name . "\n";
    echo "  Slug: " . $stadium->slug . "\n";
    echo "  URL: " . route('courts-detail', $stadium) . "\n\n";
}

$tournament = \App\Models\Tournament::first();
if ($tournament) {
    echo "Tournament: " . $tournament->name . "\n";
    echo "  Slug: " . $tournament->slug . "\n";
    echo "  URL: " . route('tournaments-detail', $tournament) . "\n\n";
}

$instructor = \App\Models\Instructor::first();
if ($instructor) {
    echo "Instructor: " . $instructor->name . "\n";
    echo "  Slug: " . $instructor->slug . "\n";
    echo "  URL: " . route('instructors.detail', $instructor) . "\n\n";
}

$club = \App\Models\Club::first();
if ($club) {
    echo "Club: " . $club->name . "\n";
    echo "  Slug: " . $club->slug . "\n";
    echo "  URL: " . route('clubs.show', $club) . "\n\n";
}

echo "=== Checking Route Model Binding ===\n";
echo "Stadium getRouteKeyName: " . $stadium->getRouteKeyName() . "\n";
echo "Tournament getRouteKeyName: " . $tournament->getRouteKeyName() . "\n";
echo "Instructor getRouteKeyName: " . $instructor->getRouteKeyName() . "\n";
echo "Club getRouteKeyName: " . $club->getRouteKeyName() . "\n";
