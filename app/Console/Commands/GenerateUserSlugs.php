<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUserSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for users with empty slugs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to generate slugs for users...');

        $users = User::whereNull('slug')
            ->orWhere('slug', '')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found with missing slugs.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $slug = Str::slug($user->name, '-');

            // Ensure uniqueness
            $originalSlug = $slug;
            $count = 1;
            while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $user->update(['slug' => $slug]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Slugs generated successfully!');
        $this->info('Total users updated: ' . $users->count());

        return Command::SUCCESS;
    }
}
