<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\Course;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\YouTubeVideo;
use App\Services\ActivityLoggerService;
use App\Services\FileStorageService;
use App\Services\MediaSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $data = $request->validated();

        if ($request->has('path') && $request->filled('path')) {
            $media = $this->storageService->storeFromPath($data);
        } else {
            $media = $this->storageService->store($request->file('file'), $data);
        }

        $this->logger->logCreated('media_file', $media->id, $media->name);

        return redirect()->route('admin.media.index')
            ->with('success', __('messages.msg_file_uploaded'));
    }

    public function destroy(MediaFile $media): RedirectResponse
    {
        $this->logger->logDeleted('media_file', $media->id, $media->name);
        $this->storageService->delete($media);

        return redirect()->route('admin.media.index')
            ->with('success', __('messages.msg_file_deleted'));
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
            ->with('success', __('messages.msg_sync_complete', ['created' => $results['created'], 'skipped' => $results['skipped']]));
    }

    public function edit(MediaFile $media): View
    {
        $courses = Course::orderBy('title')->get();
        $folderOptions = collect();

        $allFolders = Folder::where('course_id', $media->course_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $folderOptions = $this->buildFolderTree($allFolders);

        return view('admin.media.edit', compact('media', 'courses', 'folderOptions'));
    }

    public function update(UpdateMediaRequest $request, MediaFile $media): RedirectResponse
    {
        $data = $request->validated();

        $media->update($data);

        $this->logger->logUpdated('media_file', $media->id, $media->name);

        return redirect()->route('admin.media.index')
            ->with('success', __('messages.msg_file_updated'));
    }

    public function moveUp(MediaFile $media): RedirectResponse
    {
        $siblings = $this->mergedSiblings($media);

        $index = $siblings->search(fn($c) => $c['kind'] === 'media' && $c['item']->id === $media->id);

        if ($index === false || $index === 0) {
            return redirect()->route('admin.courses.show', $media->course_id)
                ->with('error', __('messages.msg_media_at_top'));
        }

        $current = $siblings->pull($index);
        $siblings->splice($index - 1, 0, [$current]);

        foreach ($siblings as $i => $content) {
            $content['item']->updateQuietly(['sort_order' => $i]);
        }

        $courseId = $media->course_id;
        $this->logger->logUpdated('media_file', $media->id, "{$media->name} moved up");

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', __('messages.msg_media_moved_up'));
    }

    public function moveDown(MediaFile $media): RedirectResponse
    {
        $siblings = $this->mergedSiblings($media);

        $index = $siblings->search(fn($c) => $c['kind'] === 'media' && $c['item']->id === $media->id);

        if ($index === false || $index === $siblings->count() - 1) {
            return redirect()->route('admin.courses.show', $media->course_id)
                ->with('error', __('messages.msg_media_at_bottom'));
        }

        $current = $siblings->pull($index);
        $siblings->splice($index + 1, 0, [$current]);

        foreach ($siblings as $i => $content) {
            $content['item']->updateQuietly(['sort_order' => $i]);
        }

        $courseId = $media->course_id;
        $this->logger->logUpdated('media_file', $media->id, "{$media->name} moved down");

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', __('messages.msg_media_moved_down'));
    }

    private function mergedSiblings(MediaFile $media): Collection
    {
        $scope = fn($q) => $q->where('course_id', $media->course_id)
            ->where(function($sq) use ($media) {
                $media->folder_id ? $sq->where('folder_id', $media->folder_id) : $sq->whereNull('folder_id');
            });

        $mediaItems = MediaFile::where($scope)->get()
            ->map(fn($m) => ['item' => $m, 'sort' => $m->sort_order ?? 0, 'kind' => 'media']);
        $youtube = YouTubeVideo::where($scope)->get()
            ->map(fn($y) => ['item' => $y, 'sort' => $y->sort_order ?? 0, 'kind' => 'youtube']);

        return $mediaItems->concat($youtube)->sortBy('sort')->values();
    }
}