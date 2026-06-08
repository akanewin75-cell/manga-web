<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['source_id', 'source_type', 'title', 'slug', 'description', 'cover_url', 'genre', 'status', 'likes_count'])]
class Manga extends Model
{
    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
