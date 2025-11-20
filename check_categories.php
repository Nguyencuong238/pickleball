<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TournamentCategory;
use App\Models\Tournament;

echo "=== ALL TOURNAMENTS ===\n";
$tournaments = Tournament::all();
foreach ($tournaments as $t) {
    echo "ID: {$t->id}, Name: {$t->name}, User ID: {$t->user_id}\n";
}

echo "\n=== ALL CATEGORIES ===\n";
$categories = TournamentCategory::all();
echo "Total: " . $categories->count() . "\n";
foreach ($categories as $cat) {
    echo "ID: {$cat->id}, Tournament ID: {$cat->tournament_id}, Name: {$cat->category_name}\n";
}

echo "\n=== CATEGORIES BY TOURNAMENT ===\n";
foreach ($tournaments as $t) {
    echo "\nTournament: {$t->name} (ID: {$t->id})\n";
    $cats = $t->categories;
    echo "  Categories: " . $cats->count() . "\n";
    foreach ($cats as $cat) {
        echo "    - {$cat->category_name}\n";
    }
}
?>
