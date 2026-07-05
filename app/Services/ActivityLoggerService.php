<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLoggerService
{
    public function log(
        string $action,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?int $userId = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function logCreated(string $subjectType, int $subjectId, string $identifier): void
    {
        $this->log('created', "Created {$subjectType}: {$identifier}", $subjectType, $subjectId);
    }

    public function logUpdated(string $subjectType, int $subjectId, string $identifier): void
    {
        $this->log('updated', "Updated {$subjectType}: {$identifier}", $subjectType, $subjectId);
    }

    public function logDeleted(string $subjectType, int $subjectId, string $identifier): void
    {
        $this->log('deleted', "Deleted {$subjectType}: {$identifier}", $subjectType, $subjectId);
    }

    public function logDownload(string $subjectType, int $subjectId, string $identifier): void
    {
        $this->log('downloaded', "Downloaded {$subjectType}: {$identifier}", $subjectType, $subjectId);
    }

    public function logViewed(string $subjectType, int $subjectId, string $identifier): void
    {
        $this->log('viewed', "Viewed {$subjectType}: {$identifier}", $subjectType, $subjectId);
    }

    public function logLogin(string $email): void
    {
        $this->log('login', "User logged in: {$email}");
    }

    public function logLogout(string $email): void
    {
        $this->log('logout', "User logged out: {$email}");
    }
}
