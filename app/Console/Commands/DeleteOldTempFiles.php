<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeleteOldTempFiles extends Command
{
    protected $signature = 'media:clean-temp';
    protected $description = 'Delete all files and folders in storage/media-library/temp created before yesterday';

    public function handle()
    {
        $tempPath = storage_path('media-library/temp');
        
        if (!File::isDirectory($tempPath)) {
            $this->info("Directory {$tempPath} does not exist.");
            return;
        }

        $yesterday = now()->subDay()->timestamp;
        $deletedCount = 0;

        // Get all files and directories
        $items = File::allFiles($tempPath);
        
        foreach ($items as $item) {
            $lastModified = File::lastModified((string)$item);
            
            if ($lastModified < $yesterday) {
                File::delete((string)$item);
                $this->line("Deleted: {$item}");
                $deletedCount++;
            }
        }

        // Clean up empty directories
        $directories = File::directories($tempPath);
        foreach ($directories as $dir) {
            if (count(File::allFiles($dir)) === 0 && count(File::directories($dir)) === 0) {
                File::deleteDirectory($dir);
                $this->line("Deleted directory: {$dir}");
            }
        }

        $this->info("Successfully deleted {$deletedCount} files.");
    }
}
