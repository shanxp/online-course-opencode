<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFolderRequest;
use App\Models\Course;
use App\Models\Folder;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function __construct(
        private readonly ActivityLoggerService $logger,
    ) {}

    public function index(Request $request): View
    {
        $courseId = $request->get('course_id');
        $folders = Folder::with('course')
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->whereNull('parent_id')
            ->orderBy('is_sticky', 'desc')
            ->orderBy('sort_order')
            ->paginate(20);

        $courses = Course::orderBy('title')->get();

        return view('admin.folders.index', compact('folders', 'courses', 'courseId'));
    }

    public function create(Request $request): View
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->get('course_id');
        $parentId = $request->get('parent_id');
        $parentFolder = $parentId ? Folder::find($parentId) : null;

        return view('admin.folders.create', compact('courses', 'selectedCourseId', 'parentFolder'));
    }

    public function store(StoreFolderRequest $request): RedirectResponse
    {
        $folder = Folder::create($request->validated());

        $this->logger->logCreated('folder', $folder->id, $folder->name);

        return redirect()->route('admin.courses.show', $folder->course_id)
            ->with('success', 'Folder created successfully.');
    }

    public function edit(Folder $folder): View
    {
        $courses = Course::orderBy('title')->get();
        return view('admin.folders.edit', compact('folder', 'courses'));
    }

    public function update(StoreFolderRequest $request, Folder $folder): RedirectResponse
    {
        $folder->update($request->validated());

        $this->logger->logUpdated('folder', $folder->id, $folder->name);

        return redirect()->route('admin.courses.show', $folder->course_id)
            ->with('success', 'Folder updated successfully.');
    }

    public function moveUp(Folder $folder): RedirectResponse
    {
        $siblings = Folder::where('course_id', $folder->course_id)
            ->where('parent_id', $folder->parent_id)
            ->orderBy('is_sticky', 'desc')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values();

        $index = $siblings->search(fn($s) => $s->id === $folder->id);

        if ($index === false || $index === 0) {
            return redirect()->route('admin.courses.show', $folder->course_id)
                ->with('error', 'Folder is already at the top.');
        }

        $item = $siblings->pull($index);
        $siblings->splice($index - 1, 0, [$item]);

        foreach ($siblings as $i => $s) {
            $s->updateQuietly(['sort_order' => $i]);
        }

        $this->logger->logUpdated('folder', $folder->id, "{$folder->name} moved up");

        return redirect()->route('admin.courses.show', $folder->course_id)
            ->with('success', 'Folder moved up.');
    }

    public function moveDown(Folder $folder): RedirectResponse
    {
        $siblings = Folder::where('course_id', $folder->course_id)
            ->where('parent_id', $folder->parent_id)
            ->orderBy('is_sticky', 'desc')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values();

        $index = $siblings->search(fn($s) => $s->id === $folder->id);

        if ($index === false || $index === $siblings->count() - 1) {
            return redirect()->route('admin.courses.show', $folder->course_id)
                ->with('error', 'Folder is already at the bottom.');
        }

        $item = $siblings->pull($index);
        $siblings->splice($index + 1, 0, [$item]);

        foreach ($siblings as $i => $s) {
            $s->updateQuietly(['sort_order' => $i]);
        }

        $this->logger->logUpdated('folder', $folder->id, "{$folder->name} moved down");

        return redirect()->route('admin.courses.show', $folder->course_id)
            ->with('success', 'Folder moved down.');
    }

    public function toggleSticky(Folder $folder): RedirectResponse
    {
        $folder->update(['is_sticky' => !$folder->is_sticky]);

        $this->logger->logUpdated('folder', $folder->id, "{$folder->name} sticky toggled");

        return redirect()->route('admin.courses.show', $folder->course_id)
            ->with('success', $folder->is_sticky ? 'Folder pinned.' : 'Folder unpinned.');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $courseId = $folder->course_id;
        $this->logger->logDeleted('folder', $folder->id, $folder->name);

        $folder->delete();

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', 'Folder deleted successfully.');
    }
}
