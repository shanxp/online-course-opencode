<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\View\View;

class PublicCourseController extends Controller
{
    public function show(string $slug): View
    {
        $course = Course::where('slug', $slug)->where('is_published', true)->firstOrFail();

        $course->load(['mediaFiles' => fn($q) => $q->whereNull('folder_id'),
            'youtubeVideos' => fn($q) => $q->whereNull('folder_id'),
            'folders' => fn($q) => $q->ordered(),
            'folders.mediaFiles',
            'folders.youtubeVideos',
            'folders.children' => fn($q) => $q->ordered(),
            'folders.children.mediaFiles',
            'folders.children.youtubeVideos',
        ]);

        return view('public.courses.show', compact('course'));
    }
}
