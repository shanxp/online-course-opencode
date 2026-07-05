<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Services\ActivityLoggerService;
use App\Services\FileStorageService;
use Illuminate\Http\Request;

class MediaStreamController extends Controller
{
    public function __construct(
        private readonly FileStorageService $storageService,
        private readonly ActivityLoggerService $logger,
    ) {}

    public function stream(Request $request, MediaFile $media)
    {
        $this->authorize('stream', $media);

        $this->logger->logViewed('media_file', $media->id, $media->name);

        return $this->storageService->stream($media);
    }

    public function download(Request $request, MediaFile $media)
    {
        $this->authorize('download', $media);

        $this->logger->logDownload('media_file', $media->id, $media->name);

        return $this->storageService->download($media);
    }
}
