<?php

namespace App\Services;

use App\Models\Course;
use App\Models\MediaFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MediaSyncService
{
    public function sync(Course $course, ?int $folderId = null): array
    {
        $syncPath = config('media.sync_path', storage_path('app/media/source'));
        $results = [
            'created' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if (!File::exists($syncPath)) {
            $results['errors'][] = "Sync path does not exist: {$syncPath}";
            return $results;
        }

        $files = File::allFiles($syncPath);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());

            if (!in_array($extension, ['mp3', 'pdf'])) {
                continue;
            }

            $relativePath = 'media/' . $course->id . '/' . $file->getFilename();
            $type = $extension === 'mp3' ? 'mp3' : 'pdf';

            $existing = MediaFile::where('course_id', $course->id)
                ->where('original_name', $file->getFilename())
                ->first();

            if ($existing) {
                $results['skipped']++;
                continue;
            }

            try {
                Storage::disk('local')->put($relativePath, File::get($file));

                MediaFile::create([
                    'name' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                    'original_name' => $file->getFilename(),
                    'path' => $relativePath,
                    'type' => $type,
                    'size' => $file->getSize(),
                    'mime_type' => $extension === 'mp3' ? 'audio/mpeg' : 'application/pdf',
                    'disk' => 'local',
                    'folder_id' => $folderId,
                    'course_id' => $course->id,
                ]);

                $results['created']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Failed to sync {$file->getFilename()}: {$e->getMessage()}";
            }
        }

        return $results;
    }
}
