<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlideshowImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlideshowImageController extends Controller
{
    public function index(): View
    {
        $images = SlideshowImage::orderBy('sort_order')->get();
        return view('admin.slideshow-images.index', compact('images'));
    }

    public function create(): View
    {
        return view('admin.slideshow-images.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image_path' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        SlideshowImage::create([
            'image_path' => $validated['image_path'],
            'sort_order' => $validated['sort_order'] ?? SlideshowImage::max('sort_order') + 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.slideshow-images.index')
            ->with('success', 'Slideshow image added.');
    }

    public function edit(SlideshowImage $slideshowImage): View
    {
        return view('admin.slideshow-images.edit', compact('slideshowImage'));
    }

    public function update(Request $request, SlideshowImage $slideshowImage): RedirectResponse
    {
        $validated = $request->validate([
            'image_path' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $slideshowImage->update([
            'image_path' => $validated['image_path'],
            'sort_order' => $validated['sort_order'] ?? $slideshowImage->sort_order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.slideshow-images.index')
            ->with('success', 'Slideshow image updated.');
    }

    public function destroy(SlideshowImage $slideshowImage): RedirectResponse
    {
        $slideshowImage->delete();
        return redirect()->route('admin.slideshow-images.index')
            ->with('success', 'Slideshow image removed.');
    }
}
