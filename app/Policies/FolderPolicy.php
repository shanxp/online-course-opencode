<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Folder $folder): bool
    {
        if ($user->isAdmin()) return true;
        return $user->canViewCourse($folder->course_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }
}
