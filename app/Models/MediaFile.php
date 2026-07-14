<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    protected $fillable = [
        'name',
        'original_name',
        'path',
        'type',
        'size',
        'sort_order',
        'mime_type',
        'duration',
        'disk',
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
        static::creating(function (MediaFile $media) {
            if ($media->sort_order === null) {
                $query = static::where('course_id', $media->course_id);
                $media->folder_id ? $query->where('folder_id', $media->folder_id) : $query->whereNull('folder_id');
                $media->sort_order = $query->max('sort_order') + 1;
            }
        });
    }

    public function scopeOrdered($query)
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
}
