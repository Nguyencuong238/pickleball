<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateStorageToSpaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:migrate-to-spaces
                            {--dry-run : Show what would be uploaded without actually uploading}
                            {--folder= : Migrate only specific folder (e.g., avatars, clubs)}
                            {--all : Migrate ALL folders (auto-detect, excluding Spatie Media Library)}
                            {--force : Overwrite existing files on DO Spaces}
                            {--delete-local : Delete local files after successful upload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate files from local public storage to Digital Ocean Spaces (excluding Spatie Media Library files)';

    /**
     * Folders to migrate (excluding Spatie Media Library folders)
     */
    protected array $foldersToMigrate = [
        'avatars',
        'clubs',
        'clubs/banners',
        'tournament_images',
        'tournament_gallery',
        'tournament_banner',
        'instructors',
        'videos',
        'news_images',
        'club-posts/images',
        'club-posts/videos',
    ];

    /**
     * Folders/patterns to exclude (Spatie Media Library uses numeric folder names)
     */
    protected array $excludePatterns = [
        'media',              // Spatie default folder
        'conversions',        // Spatie conversions
        'responsive-images',  // Spatie responsive images
    ];

    /**
     * Statistics
     */
    protected int $totalFiles = 0;
    protected int $uploadedFiles = 0;
    protected int $skippedFiles = 0;
    protected int $failedFiles = 0;
    protected int $totalSize = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->info('===========================================');
        $this->info('  Storage Migration: Local -> DO Spaces');
        $this->info('===========================================');
        $this->info('');

        // Check if DO disk is configured
        if (!$this->checkDoConfiguration()) {
            return Command::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $specificFolder = $this->option('folder');
        $migrateAll = $this->option('all');
        $force = $this->option('force');
        $deleteLocal = $this->option('delete-local');

        if ($dryRun) {
            $this->warn('[DRY RUN] No files will be uploaded');
            $this->info('');
        }

        // Get folders to process
        if ($specificFolder) {
            $folders = [$specificFolder];
        } elseif ($migrateAll) {
            $folders = $this->detectAllFolders();
            $this->info('Auto-detected ' . count($folders) . ' folders to migrate');
            $this->info('');
        } else {
            $folders = $this->foldersToMigrate;
        }

        // Process each folder
        foreach ($folders as $folder) {
            $this->migrateFolder($folder, $dryRun, $force, $deleteLocal);
        }

        // Show summary
        $this->showSummary($dryRun);

        return Command::SUCCESS;
    }

    /**
     * Check if DO Spaces is properly configured
     */
    protected function checkDoConfiguration(): bool
    {
        $requiredEnvVars = [
            'DO_SPACES_KEY',
            'DO_SPACES_SECRET',
            'DO_SPACES_BUCKET',
            'DO_SPACES_ENDPOINT',
        ];

        $missing = [];
        foreach ($requiredEnvVars as $var) {
            if (empty(env($var))) {
                $missing[] = $var;
            }
        }

        if (!empty($missing)) {
            $this->error('Missing Digital Ocean Spaces configuration:');
            foreach ($missing as $var) {
                $this->line("  - {$var}");
            }
            $this->info('');
            $this->info('Please configure these in your .env file');
            return false;
        }

        // Test connection
        try {
            Storage::disk('do')->put('_migration_test.txt', 'test');
            Storage::disk('do')->delete('_migration_test.txt');
            $this->info('[OK] DO Spaces connection verified');
            $this->info('');
        } catch (\Exception $e) {
            $this->error('Failed to connect to DO Spaces: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Migrate a single folder
     */
    protected function migrateFolder(string $folder, bool $dryRun, bool $force, bool $deleteLocal): void
    {
        $this->info("Processing folder: {$folder}");

        // Check if folder exists in local storage
        if (!Storage::disk('public')->exists($folder)) {
            $this->warn("  Folder not found, skipping...");
            $this->info('');
            return;
        }

        // Get all files in folder (recursive)
        $files = Storage::disk('public')->allFiles($folder);

        if (empty($files)) {
            $this->warn("  No files found, skipping...");
            $this->info('');
            return;
        }

        $this->info("  Found " . count($files) . " files");

        // Create progress bar
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $this->totalFiles++;

            // Skip if file is in excluded folders
            if ($this->isExcluded($file)) {
                $this->skippedFiles++;
                $bar->advance();
                continue;
            }

            // Check if file already exists on DO
            if (!$force && Storage::disk('do')->exists($file)) {
                $this->skippedFiles++;
                $bar->advance();
                continue;
            }

            // Get file size
            $size = Storage::disk('public')->size($file);
            $this->totalSize += $size;

            if ($dryRun) {
                $this->uploadedFiles++;
                $bar->advance();
                continue;
            }

            // Upload file
            try {
                $content = Storage::disk('public')->get($file);
                $mimeType = Storage::disk('public')->mimeType($file);

                Storage::disk('do')->put($file, $content, [
                    'visibility' => 'public',
                    'ContentType' => $mimeType,
                ]);

                $this->uploadedFiles++;

                // Delete local file if requested
                if ($deleteLocal) {
                    Storage::disk('public')->delete($file);
                }

            } catch (\Exception $e) {
                $this->failedFiles++;
                $this->newLine();
                $this->error("  Failed: {$file} - " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    /**
     * Auto-detect all folders in public storage (excluding Spatie Media Library)
     */
    protected function detectAllFolders(): array
    {
        $directories = Storage::disk('public')->directories();
        $folders = [];

        foreach ($directories as $dir) {
            // Skip numeric folders (Spatie Media Library uses media IDs as folder names)
            if (is_numeric(basename($dir))) {
                continue;
            }

            // Skip excluded patterns
            $skip = false;
            foreach ($this->excludePatterns as $pattern) {
                if (Str::startsWith($dir, $pattern)) {
                    $skip = true;
                    break;
                }
            }

            if (!$skip) {
                $folders[] = $dir;
            }
        }

        return $folders;
    }

    /**
     * Check if file should be excluded
     */
    protected function isExcluded(string $path): bool
    {
        // Get first segment of path
        $firstSegment = explode('/', $path)[0];

        // Skip numeric folders (Spatie Media Library)
        if (is_numeric($firstSegment)) {
            return true;
        }

        // Skip excluded patterns
        foreach ($this->excludePatterns as $excluded) {
            if (Str::startsWith($path, $excluded . '/') || $path === $excluded) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show migration summary
     */
    protected function showSummary(bool $dryRun): void
    {
        $this->info('===========================================');
        $this->info('  Migration Summary');
        $this->info('===========================================');
        $this->info('');

        $action = $dryRun ? 'Would upload' : 'Uploaded';

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total files scanned', $this->totalFiles],
                [$action, $this->uploadedFiles],
                ['Skipped (exists/excluded)', $this->skippedFiles],
                ['Failed', $this->failedFiles],
                ['Total size', $this->formatBytes($this->totalSize)],
            ]
        );

        $this->info('');

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to actually migrate files.');
        } else {
            if ($this->failedFiles > 0) {
                $this->warn("Some files failed to upload. Check the errors above.");
            } else {
                $this->info('Migration completed successfully!');
            }
        }

        $this->info('');
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
