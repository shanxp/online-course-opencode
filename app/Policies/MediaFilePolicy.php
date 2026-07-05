<?php

namespace App\Policies;

use App\Models\MediaFile;
use App\Models\User;

class MediaFilePolicy
{
    public function view(User $user, MediaFile $media): bool
    {
        if ($user->isAdmin()) return true;
        return $user->canViewCourse($media->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, MediaFile $media): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, MediaFile $media): bool
    {
        return $user->isAdmin();
    }

    public function download(User $user, MediaFile $media): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->canDownloadCourse($media->course_id)) return true;

        if ($media->folder_id && $user->canDownloadFolder($media->folder_id)) return true;

        return false;
    }

    public function stream(User $user, MediaFile $media): bool
    {
        return $this->view($user, $media);
    }
}
