<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tournament;
use Illuminate\Support\Str;

class GenerateTournamentSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for all tournaments from their names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to generate slugs for tournaments...');
        
        $tournaments = Tournament::whereNull('slug')
            ->orWhere('slug', '')
            ->get();
        
        if ($tournaments->isEmpty()) {
            $this->info('No tournaments found with missing slugs.');
            return;
        }
        
        $bar = $this->output->createProgressBar($tournaments->count());
        $bar->start();
        
        foreach ($tournaments as $tournament) {
            $slug = Str::slug($tournament->name, '-');
            
            // Check if slug already exists
            $existing = Tournament::where('slug', $slug)
                ->where('id', '!=', $tournament->id)
                ->exists();
            
            if ($existing) {
                $slug = $slug . '-' . $tournament->id;
            }
            
            $tournament->update(['slug' => $slug]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Slugs generated successfully!');
        $this->info('Total tournaments updated: ' . $tournaments->count());
    }
}
