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

    public function storeFromPath(array $data): MediaFile
    {
        $path = $data['path'];

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (!in_array($extension, ['mp3', 'pdf'])) {
            throw new \InvalidArgumentException(__('messages.msg_invalid_file_type') . ' (' . $path . ')');
        }

        $type = $extension === 'mp3' ? 'mp3' : 'pdf';
        $mime = $type === 'mp3' ? 'audio/mpeg' : 'application/pdf';
        $size = file_exists($path) ? filesize($path) : 0;

        return MediaFile::create([
            'name' => $data['name'] ?? pathinfo($path, PATHINFO_FILENAME),
            'original_name' => basename($path),
            'path' => $path,
            'type' => $type,
            'size' => $size,
            'mime_type' => $mime,
            'disk' => 'local',
            'folder_id' => $data['folder_id'] ?? null,
            'course_id' => $data['course_id'],
        ]);
    }

    public function delete(MediaFile $media): bool
    {
        if (!str_starts_with($media->path, '/')) {
            Storage::disk($media->disk)->delete($media->path);
        }
        return $media->delete();
    }

    private function resolvePath(MediaFile $media): string
    {
        if (str_starts_with($media->path, '/')) {
            return $media->path;
        }
        return Storage::disk($media->disk)->path($media->path);
    }

    public function stream(MediaFile $media)
    {
        $path = $this->resolvePath($media);

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
        $path = $this->resolvePath($media);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $media->original_name, [
            'Cache-Control' => 'private, no-transform',
        ]);
    }
}
