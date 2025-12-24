<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stadium;
use Illuminate\Support\Str;

class UpdateStadiumSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stadium:update-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update slugs for all stadiums from their names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update stadium slugs...');

        $stadiums = Stadium::all();
        $count = 0;
        $slugs = [];

        foreach ($stadiums as $stadium) {
            $baseSlug = Str::slug($stadium->name);
            $newSlug = $baseSlug;
            $counter = 1;

            // Handle duplicate slugs
            while (isset($slugs[$newSlug]) || Stadium::where('slug', $newSlug)->where('id', '!=', $stadium->id)->exists()) {
                $newSlug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            if ($stadium->slug !== $newSlug) {
                $stadium->update(['slug' => $newSlug]);
                $this->line("Updated: {$stadium->name} → {$newSlug}");
                $count++;
            }

            $slugs[$newSlug] = true;
        }

        $this->info("Completed! Updated {$count} stadiums.");
    }
}
