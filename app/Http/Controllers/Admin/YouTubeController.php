<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreYouTubeRequest;
use App\Models\Course;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\YouTubeVideo;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class YouTubeController extends Controller
{
    public function __construct(
        private readonly ActivityLoggerService $logger,
    ) {}

    public function index(Request $request): View
    {
        $courseId = $request->get('course_id');

        $videos = YouTubeVideo::with('course')
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->latest()
            ->paginate(20);

        $courses = Course::orderBy('title')->get();

        return view('admin.youtube.index', compact('videos', 'courses', 'courseId'));
    }

    public function create(Request $request): View
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->get('course_id');
        $folderOptions = collect();

        if ($selectedCourseId) {
            $allFolders = Folder::where('course_id', $selectedCourseId)
                ->orderBy('is_sticky', 'desc')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $folderOptions = $this->buildFolderTree($allFolders);
        }

        return view('admin.youtube.create', compact('courses', 'selectedCourseId', 'folderOptions'));
    }

    public function store(StoreYouTubeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['youtube_id'] = $data['youtube_id'] ?: $this->extractYoutubeId($data['url']);
        if (empty($data['title'])) {
            $data['title'] = $data['youtube_id'] ?? __('messages.untitled_video');
        }

        $video = YouTubeVideo::create($data);

        $this->logger->logCreated('youtube_video', $video->id, $video->title);

        return redirect()->route('admin.youtube-videos.index')
            ->with('success', __('messages.msg_youtube_added'));
    }

    public function edit(YouTubeVideo $youtubeVideo): View
    {
        $courses = Course::orderBy('title')->get();
        $folderOptions = collect();

        $allFolders = Folder::where('course_id', $youtubeVideo->course_id)
            ->orderBy('is_sticky', 'desc')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $folderOptions = $this->buildFolderTree($allFolders);

        return view('admin.youtube.edit', compact('youtubeVideo', 'courses', 'folderOptions'));
    }

    public function update(StoreYouTubeRequest $request, YouTubeVideo $youtubeVideo): RedirectResponse
    {
        $data = $request->validated();
        $data['youtube_id'] = $data['youtube_id'] ?: $this->extractYoutubeId($data['url']);
        if (empty($data['title'])) {
            $data['title'] = $data['youtube_id'] ?? __('messages.untitled_video');
        }

        $youtubeVideo->update($data);

        $this->logger->logUpdated('youtube_video', $youtubeVideo->id, $youtubeVideo->title);

        return redirect()->route('admin.youtube-videos.index')
            ->with('success', __('messages.msg_youtube_updated'));
    }

    public function destroy(YouTubeVideo $youtubeVideo): RedirectResponse
    {
        $this->logger->logDeleted('youtube_video', $youtubeVideo->id, $youtubeVideo->title);
        $youtubeVideo->delete();

        return redirect()->route('admin.youtube-videos.index')
            ->with('success', __('messages.msg_youtube_deleted'));
    }

    public function moveUp(YouTubeVideo $youtubeVideo): RedirectResponse
    {
        $siblings = $this->mergedSiblings($youtubeVideo);

        $index = $siblings->search(fn($c) => $c['kind'] === 'youtube' && $c['item']->id === $youtubeVideo->id);

        if ($index === false || $index === 0) {
            return redirect()->route('admin.courses.show', $youtubeVideo->course_id)
                ->with('error', __('messages.msg_video_at_top'));
        }

        $current = $siblings->pull($index);
        $siblings->splice($index - 1, 0, [$current]);

        foreach ($siblings as $i => $content) {
            $content['item']->updateQuietly(['sort_order' => $i]);
        }

        $courseId = $youtubeVideo->course_id;
        $this->logger->logUpdated('youtube_video', $youtubeVideo->id, "{$youtubeVideo->title} moved up");

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', __('messages.msg_video_moved_up'));
    }

    public function moveDown(YouTubeVideo $youtubeVideo): RedirectResponse
    {
        $siblings = $this->mergedSiblings($youtubeVideo);

        $index = $siblings->search(fn($c) => $c['kind'] === 'youtube' && $c['item']->id === $youtubeVideo->id);

        if ($index === false || $index === $siblings->count() - 1) {
            return redirect()->route('admin.courses.show', $youtubeVideo->course_id)
                ->with('error', __('messages.msg_video_at_bottom'));
        }

        $current = $siblings->pull($index);
        $siblings->splice($index + 1, 0, [$current]);

        foreach ($siblings as $i => $content) {
            $content['item']->updateQuietly(['sort_order' => $i]);
        }

        $courseId = $youtubeVideo->course_id;
        $this->logger->logUpdated('youtube_video', $youtubeVideo->id, "{$youtubeVideo->title} moved down");

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', __('messages.msg_video_moved_down'));
    }

    private function mergedSiblings(YouTubeVideo $youtubeVideo): Collection
    {
        $scope = fn($q) => $q->where('course_id', $youtubeVideo->course_id)
            ->where(function($sq) use ($youtubeVideo) {
                $youtubeVideo->folder_id ? $sq->where('folder_id', $youtubeVideo->folder_id) : $sq->whereNull('folder_id');
            });

        $media = MediaFile::where($scope)->get()
            ->map(fn($m) => ['item' => $m, 'sort' => $m->sort_order ?? 0, 'kind' => 'media']);
        $youtube = YouTubeVideo::where($scope)->get()
            ->map(fn($y) => ['item' => $y, 'sort' => $y->sort_order ?? 0, 'kind' => 'youtube']);

        return $media->concat($youtube)->sortBy('sort')->values();
    }

    private function extractYoutubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
        return $matches[1] ?? null;
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
}