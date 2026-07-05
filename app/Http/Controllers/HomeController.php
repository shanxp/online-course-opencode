<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SlideshowImage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $courses = Course::where('is_published', true)
            ->where('show_on_homepage', true)
            ->withCount('folders');

        $sort = $request->get('sort', 'newest');

        $courses = match ($sort) {
            'default' => $courses->orderBy('sort_order')->orderBy('title')->get(),
            'title_asc' => $courses->orderBy('title')->get(),
            'title_desc' => $courses->orderBy('title', 'desc')->get(),
            'newest' => $courses->orderBy('created_at', 'desc')->get(),
            'oldest' => $courses->orderBy('created_at')->get(),
            default => $courses->orderBy('created_at', 'desc')->get(),
        };

        $slideshowImages = SlideshowImage::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('welcome', compact('courses', 'slideshowImages', 'sort'));
    }
}
