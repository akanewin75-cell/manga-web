<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'source_type' => 'required|string',
            'source_id' => 'required|string',
            'content' => 'required|string|max:1000',
            'chapter_id' => 'nullable|string'
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'source_type' => $request->source_type,
            'source_id' => $request->source_id,
            'chapter_id' => $request->chapter_id,
            'content' => $request->content
        ]);

        return back()->with('success', 'Transmission received!');
    }

    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update([
            'content' => $request->content
        ]);

        return back()->with('success', 'Transmission updated!');
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Transmission deleted!');
    }
}
