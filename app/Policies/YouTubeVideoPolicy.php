<?php

namespace App\Policies;

use App\Models\User;
use App\Models\YouTubeVideo;

class YouTubeVideoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, YouTubeVideo $video): bool
    {
        if ($user->isAdmin()) return true;
        return $user->canViewCourse($video->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, YouTubeVideo $video): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, YouTubeVideo $video): bool
    {
        return $user->isAdmin();
    }
}
