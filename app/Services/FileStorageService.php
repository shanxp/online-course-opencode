<?php

namespace App\Services;

use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    public function store(UploadedFile $file, array $data): MediaFile
    {
        $path = $file->store('media/' . $data['course_id'], 'local');

        return MediaFile::create([
            'name' => $data['name'] ?? $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $file->getClientOriginalExtension() === 'mp3' ? 'mp3' : 'pdf',
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'local',
            'folder_id' => $data['folder_id'] ?? null,
            'course_id' => $data['course_id'],
        ]);
    }

    public function delete(MediaFile $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);
        return $media->delete();
    }

    public function stream(MediaFile $media)
    {
        $path = Storage::disk($media->disk)->path($media->path);

        if (!file_exists($path)) {
            abort(404);
        }

        $mime = $media->mime_type;
        $size = filesize($path);

        return response()->stream(function () use ($path) {
            $stream = fopen($path, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => $size,
            'Content-Disposition' => 'inline; filename="' . $media->original_name . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-transform',
        ]);
    }

    public function download(MediaFile $media)
    {
        $path = Storage::disk($media->disk)->path($media->path);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $media->original_name, [
            'Cache-Control' => 'private, no-transform',
        ]);
    }
}
