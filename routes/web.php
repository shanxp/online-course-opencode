<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SlideshowImageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\YouTubeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MediaStreamController;
use App\Http\Controllers\User\CourseController as UserCourseController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'de'])) {
        session()->put('locale', $locale);
    }
    return back();
})->name('locale.switch');

Route::get('/', App\Http\Controllers\HomeController::class)->name('home');

Route::get('course/{slug}', [App\Http\Controllers\PublicCourseController::class, 'show'])->name('courses.public.show');

Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store']);

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('media/{media}/stream', [MediaStreamController::class, 'stream'])->name('media.stream');
    Route::get('media/{media}/download', [MediaStreamController::class, 'download'])->name('media.download');

    Route::get('dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('courses', fn() => redirect()->route('dashboard'))->name('courses.index');
    Route::get('profile', [UserProfileController::class, 'index'])->name('profile');
    Route::post('password', [UserProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('user/courses', fn() => redirect()->route('dashboard'));
    Route::get('user/courses/{course:slug}', [UserCourseController::class, 'show'])->name('courses.show');

    Route::redirect('admin', '/admin/dashboard');
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('courses', CourseController::class);
        Route::post('courses/{course}/move-up', [CourseController::class, 'moveUp'])->name('courses.move-up');
        Route::post('courses/{course}/move-down', [CourseController::class, 'moveDown'])->name('courses.move-down');
        Route::post('folders/{folder}/move-up', [FolderController::class, 'moveUp'])->name('folders.move-up');
        Route::post('folders/{folder}/move-down', [FolderController::class, 'moveDown'])->name('folders.move-down');
        Route::post('folders/{folder}/toggle-sticky', [FolderController::class, 'toggleSticky'])->name('folders.toggle-sticky');
        Route::resource('folders', FolderController::class)->except(['show']);
        Route::resource('media', MediaController::class)->except(['show'])->parameters(['media' => 'media']);
        Route::post('media/sync', [MediaController::class, 'sync'])->name('media.sync');
        Route::post('media/{media}/move-up', [MediaController::class, 'moveUp'])->name('media.move-up');
        Route::post('media/{media}/move-down', [MediaController::class, 'moveDown'])->name('media.move-down');
        Route::resource('youtube-videos', YouTubeController::class)->except(['show']);
        Route::post('youtube-videos/{youtube_video}/move-up', [YouTubeController::class, 'moveUp'])->name('youtube-videos.move-up');
        Route::post('youtube-videos/{youtube_video}/move-down', [YouTubeController::class, 'moveDown'])->name('youtube-videos.move-down');
        Route::get('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('users/{user}', fn(User $user) => redirect()->route('admin.users.edit', $user))->name('users.show');
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::post('permissions/{group}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('permissions/{group}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        Route::post('permissions/{group}/add-user', [PermissionController::class, 'addUser'])->name('permissions.add-user');
        Route::post('permissions/{group}/remove-user/{user}', [PermissionController::class, 'removeUser'])->name('permissions.remove-user');
        Route::post('permissions/{group}/add-course', [PermissionController::class, 'addCourse'])->name('permissions.add-course');
        Route::post('permissions/{group}/remove-course/{course}', [PermissionController::class, 'removeCourse'])->name('permissions.remove-course');
        Route::post('permissions/{group}/add-folder', [PermissionController::class, 'addFolder'])->name('permissions.add-folder');
        Route::post('permissions/{group}/remove-folder/{folder}', [PermissionController::class, 'removeFolder'])->name('permissions.remove-folder');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::resource('slideshow-images', SlideshowImageController::class)->except(['show']);
    });
});
