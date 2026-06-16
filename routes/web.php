<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Models\Manga;

/*
|--------------------------------------------------------------------------
| HOME & DISCOVERY
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index']);
Route::get('/explore', [HomeController::class, 'index'])->name('explore');
Route::get('/search', [HomeController::class, 'search'])->name('search');

/*
|--------------------------------------------------------------------------
| MANGA & CHAPTER ACCESS
|--------------------------------------------------------------------------
*/

Route::get('/manga/{type}/{id}', [MangaController::class, 'show'])->name('manga.show');
Route::get('/read/{type}/{mangaId}/{chapterId}', [MangaController::class, 'read'])->name('manga.read');
Route::get('/image-proxy', [MangaController::class, 'proxy'])->name('proxy.image');

/*
|--------------------------------------------------------------------------
| ADMIN & UPLOAD
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    Route::get('/admin/upload-manga', function () {
        if (!auth()->user()->isAdmin()) abort(403);
        $mangas = Manga::orderBy('title')->get();
        return view('upload', compact('mangas'));
    });

    Route::post('/upload-manga', function(Request $request){
        if (!auth()->user()->isAdmin()) abort(403);

        $slug = strtolower(str_replace(' ', '-', $request->title));
        $path = public_path("mangas/$slug");

        if(!File::exists($path)) File::makeDirectory($path, 0777, true, true);

        $coverName = 'cover.' . $request->cover->extension();
        $request->cover->move($path, $coverName);

        Manga::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $request->title,
                'description' => $request->description,
                'genre' => $request->genre,
                'cover_url' => asset("mangas/$slug/$coverName"),
                'source_type' => 'local',
                'source_id' => $slug
            ]
        );

        return back()->with('success', 'Manga berhasil diupload 😎');
    });

    Route::post('/upload-chapter', function(Request $request){
        if (!auth()->user()->isAdmin()) abort(403);

        $slug = $request->slug;
        $chapter = $request->chapter;
        $path = public_path("mangas/$slug/chapters/$chapter");

        if(!File::exists($path)) File::makeDirectory($path, 0777, true, true);

        foreach($request->file('images') as $image){
            $name = time().'_'.$image->getClientOriginalName();
            $image->move($path, $name);
        }

        return back()->with('success', 'Chapter berhasil diupload 🔥');
    });

    Route::delete('/admin/delete-manga/{slug}', [MangaController::class, 'delete']);
    Route::post('/manga/import/{type}/{id}', [MangaController::class, 'import'])->name('manga.import');

});

/*
|--------------------------------------------------------------------------
| USER FEATURES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    Route::get('/bookmarks', function(){
        $bookmarks = auth()->user()->bookmarkedMangas()->latest()->get();
        return view('bookmarks', compact('bookmarks'));
    })->name('bookmarks');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/toggle-nsfw', [HomeController::class, 'toggleNsfw'])->name('toggle.nsfw');
    
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
