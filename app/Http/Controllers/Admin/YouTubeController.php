<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreYouTubeRequest;
use App\Models\Course;
use App\Models\YouTubeVideo;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.youtube.create', compact('courses', 'selectedCourseId'));
    }

    public function store(StoreYouTubeRequest $request): RedirectResponse
    {
        $video = YouTubeVideo::create($request->validated());

        $this->logger->logCreated('youtube_video', $video->id, $video->title);

        return redirect()->route('admin.youtube-videos.index')
            ->with('success', 'YouTube video added successfully.');
    }

    public function edit(YouTubeVideo $youtubeVideo): View
    {
        $courses = Course::orderBy('title')->get();
        return view('admin.youtube.edit', compact('youtubeVideo', 'courses'));
    }

    public function update(StoreYouTubeRequest $request, YouTubeVideo $youtubeVideo): RedirectResponse
    {
        $youtubeVideo->update($request->validated());

        $this->logger->logUpdated('youtube_video', $youtubeVideo->id, $youtubeVideo->title);

        return redirect()->route('admin.youtube-videos.index')
            ->with('success', 'YouTube video updated successfully.');
    }

    public function destroy(YouTubeVideo $youtubeVideo): RedirectResponse
    {
        $this->logger->logDeleted('youtube_video', $youtubeVideo->id, $youtubeVideo->title);
        $youtubeVideo->delete();

        return redirect()->route('admin.youtube-videos.index')
            ->with('success', 'YouTube video deleted successfully.');
    }
}
