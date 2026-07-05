<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $user = auth()->user();
        $search = $request->get('search');

        if ($user->isAdmin() || $user->hasCourseLevelAccess($course->id)) {
            $accessibleFolderIds = null;
        } else {
            $accessibleFolderIds = $user->accessibleFolderIdsInCourse($course->id);
        }

        $folders = $course->folders()
            ->whereNull('parent_id')
            ->with('mediaFiles', 'youtubeVideos', 'children.mediaFiles', 'children.youtubeVideos')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($accessibleFolderIds !== null, fn($q) => $q->whereIn('id', $accessibleFolderIds))
            ->orderBy('is_sticky', 'desc')
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $course->load([
            'mediaFiles' => fn($q) => $q->whereNull('folder_id'),
            'youtubeVideos' => fn($q) => $q->whereNull('folder_id'),
        ]);

        return view('user.courses.show', compact('course', 'folders', 'accessibleFolderIds', 'search'));
    }
}
