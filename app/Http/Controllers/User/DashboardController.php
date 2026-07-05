<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $courses = $user->accessibleCourses()->with('creator');

        $sort = $request->get('sort', 'newest');

        $courses = match ($sort) {
            'default' => $courses->get(),
            'title_asc' => $courses->orderBy('title')->get(),
            'title_desc' => $courses->orderBy('title', 'desc')->get(),
            'newest' => $courses->orderBy('created_at', 'desc')->get(),
            'oldest' => $courses->orderBy('created_at')->get(),
            default => $courses->orderBy('created_at', 'desc')->get(),
        };

        return view('user.dashboard', compact('courses', 'sort'));
    }
}
