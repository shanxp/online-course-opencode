<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'course_id',
        'parent_id',
        'sort_order',
        'is_sticky',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_sticky' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Folder $folder) {
            if ($folder->sort_order === null) {
                $folder->sort_order = static::where('course_id', $folder->course_id)
                    ->where('parent_id', $folder->parent_id)
                    ->max('sort_order') + 1;
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_sticky', 'desc')->orderBy('sort_order');
    }

    public function scopeSiblings($query)
    {
        return $query->where('course_id', $this->course_id)
            ->where('parent_id', $this->parent_id);
    }

    public function siblingBefore(): ?Folder
    {
        return static::where('course_id', $this->course_id)
            ->where('parent_id', $this->parent_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderByDesc('sort_order')
            ->first();
    }

    public function siblingAfter(): ?Folder
    {
        return static::where('course_id', $this->course_id)
            ->where('parent_id', $this->parent_id)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id')->orderBy('is_sticky', 'desc')->orderBy('sort_order');
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    public function youtubeVideos(): HasMany
    {
        return $this->hasMany(YouTubeVideo::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_folder')
            ->withPivot('permission')
            ->withTimestamps();
    }
}
