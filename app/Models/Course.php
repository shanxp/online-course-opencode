<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail',
        'is_published',
        'show_on_homepage',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_on_homepage' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
            if ($course->sort_order === null) {
                $course->sort_order = static::max('sort_order') + 1;
            }
        });

        static::updating(function (Course $course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class)->whereNull('parent_id')->orderBy('is_sticky', 'desc')->orderBy('sort_order');
    }

    public function allFolders(): HasMany
    {
        return $this->hasMany(Folder::class)->orderBy('is_sticky', 'desc')->orderBy('sort_order');
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class)->orderBy('sort_order');
    }

    public function youtubeVideos(): HasMany
    {
        return $this->hasMany(YouTubeVideo::class)->orderBy('sort_order');
    }

    public function uncategorizedContents(): \Illuminate\Support\Collection
    {
        $media = $this->mediaFiles->whereNull('folder_id')->map(fn($m) => ['item' => $m, 'sort' => $m->sort_order ?? 0, 'kind' => 'media']);
        $youtube = $this->youtubeVideos->whereNull('folder_id')->map(fn($y) => ['item' => $y, 'sort' => $y->sort_order ?? 0, 'kind' => 'youtube']);
        return $media->concat($youtube)->sortBy('sort')->values();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_course')
            ->withPivot('permission')
            ->withTimestamps();
    }
}
