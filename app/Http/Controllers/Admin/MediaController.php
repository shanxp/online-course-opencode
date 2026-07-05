<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Models\Course;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Services\ActivityLoggerService;
use App\Services\FileStorageService;
use App\Services\MediaSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly FileStorageService $storageService,
        private readonly ActivityLoggerService $logger,
        private readonly MediaSyncService $syncService,
    ) {}

    public function index(Request $request): View
    {
        $courseId = $request->get('course_id');
        $folderId = $request->get('folder_id');
        $type = $request->get('type');

        $media = MediaFile::with(['course', 'folder'])
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->when($folderId, fn($q) => $q->where('folder_id', $folderId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        $courses = Course::orderBy('title')->get();

        return view('admin.media.index', compact('media', 'courses', 'courseId', 'type'));
    }

    public function create(Request $request): View
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->get('course_id');
        $selectedFolderId = $request->get('folder_id');
        $folderOptions = collect();

        if ($selectedCourseId) {
            $allFolders = Folder::where('course_id', $selectedCourseId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $folderOptions = $this->buildFolderTree($allFolders);
        }

        return view('admin.media.create', compact('courses', 'selectedCourseId', 'selectedFolderId', 'folderOptions'));
    }

    private function buildFolderTree($folders, $parentId = null, $pathPrefix = ''): array
    {
        $result = [];
        foreach ($folders as $folder) {
            if ($folder->parent_id === $parentId) {
                $folder->display_name = $pathPrefix . $folder->name;
                $result[] = $folder;
                $children = $this->buildFolderTree($folders, $folder->id, $pathPrefix . $folder->name . ' › ');
                $result = array_merge($result, $children);
            }
        }
        return $result;
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $media = $this->storageService->store($request->file('file'), $request->validated());

        $this->logger->logCreated('media_file', $media->id, $media->name);

        return redirect()->route('admin.media.index')
            ->with('success', 'File uploaded successfully.');
    }

    public function destroy(MediaFile $media): RedirectResponse
    {
        $this->logger->logDeleted('media_file', $media->id, $media->name);
        $this->storageService->delete($media);

        return redirect()->route('admin.media.index')
            ->with('success', 'File deleted successfully.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'folder_id' => ['nullable', 'exists:folders,id'],
        ]);

        $course = Course::findOrFail($request->course_id);
        $results = $this->syncService->sync($course, $request->folder_id);

        $this->logger->log('sync', "Media sync completed: {$results['created']} created, {$results['skipped']} skipped for course '{$course->title}'");

        return redirect()->route('admin.media.index', ['course_id' => $course->id])
            ->with('success', "Sync complete. {$results['created']} files added, {$results['skipped']} skipped.");
    }
}
