<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'old_password',
        'role_id',
        'is_active',
        'last_login_at',
    ];

    public function findForPassport(string $username): ?self
    {
        return $this->where('username', $username)->first();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withTimestamps();
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function accessibleCourses()
    {
        return Course::where(function ($q) {
            $q->whereHas('groups.users', fn($q) => $q->where('users.id', $this->id))
              ->orWhereHas('allFolders.groups.users', fn($q) => $q->where('users.id', $this->id));
        });
    }

    public function canViewCourse(int $courseId): bool
    {
        return $this->accessibleCourses()->where('courses.id', $courseId)->exists();
    }

    public function canDownloadCourse(int $courseId): bool
    {
        return Group::whereHas('users', fn($q) => $q->where('users.id', $this->id))
            ->whereHas('courses', fn($q) => $q->where('course_id', $courseId)->where('permission', 'download'))
            ->exists();
    }

    public function canDownloadFolder(int $folderId): bool
    {
        return Group::whereHas('users', fn($q) => $q->where('users.id', $this->id))
            ->whereHas('folders', fn($q) => $q->where('folder_id', $folderId)->where('permission', 'download'))
            ->exists();
    }

    public function hasCourseLevelAccess(int $courseId): bool
    {
        return Group::whereHas('users', fn($q) => $q->where('users.id', $this->id))
            ->whereHas('courses', fn($q) => $q->where('course_id', $courseId))
            ->exists();
    }

    public function accessibleFolderIdsInCourse(int $courseId): array
    {
        $groups = Group::whereHas('users', fn($q) => $q->where('users.id', $this->id))
            ->whereHas('folders', fn($q) => $q->where('course_id', $courseId))
            ->with(['folders' => fn($q) => $q->where('course_id', $courseId)])
            ->get();

        $directIds = $groups->flatMap->folders->pluck('id')->unique()->values()->toArray();

        if (empty($directIds)) {
            return [];
        }

        $allCourseFolders = Folder::where('course_id', $courseId)->get()->keyBy('id');

        $ids = collect($directIds);
        foreach ($directIds as $id) {
            $folder = $allCourseFolders->get($id);
            while ($folder && $folder->parent_id) {
                $ids->push($folder->parent_id);
                $folder = $allCourseFolders->get($folder->parent_id);
            }
        }

        return $ids->unique()->values()->toArray();
    }
}
