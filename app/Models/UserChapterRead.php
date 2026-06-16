<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChapterRead extends Model
{
    protected $fillable = ['user_id', 'source_type', 'source_id', 'chapter_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
