<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withTimestamps();
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'group_course')
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function folders(): BelongsToMany
    {
        return $this->belongsToMany(Folder::class, 'group_folder')
            ->withPivot('permission')
            ->withTimestamps();
    }
}
