<?php

namespace App\Console\Commands;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class LinkInstructorsToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instructors:link-users
                            {--create : Create user accounts for unlinked instructors}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link existing instructors to user accounts and assign instructor role';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $createUsers = $this->option('create');

        $this->info('Starting instructor-user linking process...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Ensure instructor role exists
        if (!$dryRun) {
            Role::firstOrCreate(['name' => 'instructor']);
        }

        $instructors = Instructor::whereNull('user_id')->get();

        if ($instructors->isEmpty()) {
            $this->info('No unlinked instructors found.');
            return 0;
        }

        $this->info("Found {$instructors->count()} unlinked instructors.");
        $this->newLine();

        $linked = 0;
        $created = 0;
        $skipped = 0;
        $createdUsers = [];

        foreach ($instructors as $instructor) {
            // Try to find existing user by email
            $user = User::where('email', $instructor->email)->first();

            if ($user) {
                // Link existing user
                if (!$dryRun) {
                    DB::transaction(function () use ($instructor, $user) {
                        $instructor->update(['user_id' => $user->id]);
                        $user->assignRole('instructor');
                    });
                }
                $this->line("✓ Linked: <info>{$instructor->name}</info> → User #{$user->id} ({$user->email})");
                $linked++;
            } elseif ($createUsers && $instructor->email) {
                // Create new user account
                if (!$dryRun) {
                    $password = Str::random(12);

                    DB::transaction(function () use ($instructor, &$user, $password) {
                        $user = User::create([
                            'name' => $instructor->name,
                            'email' => $instructor->email,
                            'phone' => $instructor->phone,
                            'password' => Hash::make($password),
                            'status' => 'approved',
                        ]);

                        $user->assignRole('instructor');
                        $instructor->update(['user_id' => $user->id]);
                    });

                    $createdUsers[] = [
                        'instructor' => $instructor->name,
                        'email' => $instructor->email,
                        'password' => $password,
                    ];

                    $this->line("✓ Created: <info>{$instructor->name}</info> ({$instructor->email})");
                } else {
                    $this->line("→ Would create user for: <info>{$instructor->name}</info> ({$instructor->email})");
                }
                $created++;
            } else {
                $reason = !$instructor->email ? 'no email' : '--create not specified';
                $this->line("○ Skipped: <comment>{$instructor->name}</comment> ({$reason})");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Summary: Linked={$linked}, Created={$created}, Skipped={$skipped}");

        // Output created user credentials
        if (!$dryRun && count($createdUsers) > 0) {
            $this->newLine();
            $this->warn('Created user credentials (save these for sharing with instructors):');
            $this->table(
                ['Instructor', 'Email', 'Temporary Password'],
                $createdUsers
            );
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. No changes were made.');
            $this->info('Run without --dry-run to apply changes.');
        }

        return 0;
    }
}
