<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Folder;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(
        private readonly ActivityLoggerService $logger,
    ) {}

    public function index(): View
    {
        $courses = Course::with('creator')->withCount('folders')->orderBy('sort_order')->paginate(20);
        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('admin.courses.create');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course = Course::create($data);

        $this->logger->logCreated('course', $course->id, $course->title);

        return redirect()->route('admin.courses.index')
            ->with('success', __('messages.msg_course_created'));
    }

    public function show(Request $request, Course $course): View
    {
        $search = $request->get('search');

        $folders = $course->folders()
            ->whereNull('parent_id')
            ->with('children.children', 'mediaFiles', 'youtubeVideos')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('is_sticky', 'desc')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $course->load([
            'mediaFiles' => fn($q) => $q->whereNull('folder_id'),
            'youtubeVideos' => fn($q) => $q->whereNull('folder_id'),
        ]);

        return view('admin.courses.show', compact('course', 'folders', 'search'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        if ($request->boolean('remove_thumbnail') && $course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
            $data['thumbnail'] = null;
        }

        $course->update($data);

        $this->logger->logUpdated('course', $course->id, $course->title);

        return redirect()->route('admin.courses.index')
            ->with('success', __('messages.msg_course_updated'));
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->logger->logDeleted('course', $course->id, $course->title);

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', __('messages.msg_course_deleted'));
    }
}
