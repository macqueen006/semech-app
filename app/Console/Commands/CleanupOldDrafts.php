<?php

namespace App\Console\Commands;

use App\Models\SavedPost;
use Illuminate\Console\Command;

class CleanupOldDrafts extends Command
{
    protected $signature = 'drafts:cleanup {--days=30 : Delete drafts older than this many days}';
    protected $description = 'Delete old saved post drafts and their orphaned images';

    public function handle()
    {
        $days = $this->option('days');

        $count = SavedPost::where('created_at', '<', now()->subDays($days))
            ->where('updated_at', '<', now()->subDays($days))
            ->count();

        if ($count === 0) {
            $this->info('No old drafts to clean up.');
            return 0;
        }

        $this->info("Found {$count} drafts older than {$days} days.");

        if ($this->confirm('Delete these drafts and their images?')) {
            SavedPost::where('created_at', '<', now()->subDays($days))
                ->where('updated_at', '<', now()->subDays($days))
                ->delete(); // Observer handles image cleanup

            $this->info("Deleted {$count} old drafts successfully.");
        }

        return 0;
    }
}
