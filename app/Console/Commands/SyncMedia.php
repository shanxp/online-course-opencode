<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\MediaSyncService;
use Illuminate\Console\Command;

class SyncMedia extends Command
{
    protected $signature = 'media:sync {course? : The course ID to sync to} {--folder= : Optional folder ID}';

    protected $description = 'Sync MP3/PDF files from the configured source directory into the database';

    public function handle(MediaSyncService $syncService): int
    {
        $courseId = $this->argument('course');
        $folderId = $this->option('folder');

        if ($courseId) {
            $course = Course::find($courseId);
            if (!$course) {
                $this->error("Course with ID {$courseId} not found.");
                return Command::FAILURE;
            }
            $courses = [$course];
        } else {
            $courses = Course::all();
            if ($courses->isEmpty()) {
                $this->warn('No courses found. Create a course first.');
                return Command::SUCCESS;
            }
        }

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($courses as $course) {
            $this->info("Syncing media for course: {$course->title}");

            $results = $syncService->sync($course, $folderId);

            $totalCreated += $results['created'];
            $totalSkipped += $results['skipped'];

            $this->line("  Created: {$results['created']}, Skipped: {$results['skipped']}");

            foreach ($results['errors'] as $error) {
                $this->error("  Error: {$error}");
            }
        }

        $this->newLine();
        $this->info("Sync complete. Total created: {$totalCreated}, Total skipped: {$totalSkipped}");

        return Command::SUCCESS;
    }
}
