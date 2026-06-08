<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['manga_id', 'source_chapter_id', 'chapter_num', 'title', 'is_local', 'local_path'])]
class Chapter extends Model
{
    public function manga()
    {
        return $this->belongsTo(Manga::class);
    }
}
