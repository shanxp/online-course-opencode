<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YouTubeVideo extends Model
{
    protected $table = 'youtube_videos';
    protected $fillable = [
        'title',
        'description',
        'youtube_id',
        'url',
        'sort_order',
        'folder_id',
        'course_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (YouTubeVideo $video) {
            if ($video->sort_order === null) {
                $query = static::where('course_id', $video->course_id);
                $video->folder_id ? $query->where('folder_id', $video->folder_id) : $query->whereNull('folder_id');
                $video->sort_order = $query->max('sort_order') + 1;
            }
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function getEmbedUrlAttribute(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_id}";
    }
}
