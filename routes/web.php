<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| RUANA MANWHA ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// TEMPORARY: Auto-migrate if tables are missing
Route::get('/init-db', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return "Database initialized successfully! <a href='/'>Go to Home</a>";
    } catch (\Exception $e) {
        return "Migration failed: " . $e->getMessage();
    }
});
Route::get('/explore', [HomeController::class, 'index'])->name('explore'); // Shared for now
Route::get('/search', [HomeController::class, 'search'])->name('search');

// Unified Manga Routes
Route::get('/manga/{type}/{id}', [MangaController::class, 'show'])->name('manga.show');
Route::get('/read/{type}/{mangaId}/{chapterId}', [MangaController::class, 'read'])->name('manga.read');

// Legacy redirect for slugs if we have them in DB
Route::get('/manga/{slug}', function($slug) {
    $manga = \App\Models\Manga::where('slug', $slug)->first();
    if ($manga) {
        return redirect()->route('manga.show', ['type' => $manga->source_type, 'id' => $manga->source_id]);
    }
    abort(404);
});

// Admin/User Actions
Route::middleware('auth')->group(function () {
    Route::get('/admin/migrate-data', function() {
        if(auth()->user()->role !== 'admin') abort(403);
        
        $folders = \Illuminate\Support\Facades\File::directories(public_path('mangas'));
        $count = 0;
        
        foreach($folders as $folder) {
            $infoPath = $folder . '/info.json';
            if(\Illuminate\Support\Facades\File::exists($infoPath)) {
                $data = json_decode(\Illuminate\Support\Facades\File::get($infoPath), true);
                
                $manga = \App\Models\Manga::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'title' => $data['title'],
                        'description' => $data['description'] ?? '',
                        'genre' => $data['genre'] ?? '',
                        'cover_url' => asset('mangas/' . $data['slug'] . '/' . ($data['cover'] ?? 'cover.jpg')),
                        'source_id' => $data['comicazen_slug'] ?? ($data['webtoon_id'] ?? $data['slug']),
                        'source_type' => isset($data['comicazen_slug']) ? 'comicazen' : (isset($data['webtoon_id']) ? 'dex' : 'local'),
                        'likes_count' => $data['likes'] ?? 0
                    ]
                );

                // Migrate Chapters
                $chapterPath = $folder . '/chapters';
                if(\Illuminate\Support\Facades\File::exists($chapterPath)) {
                    $chFolders = \Illuminate\Support\Facades\File::directories($chapterPath);
                    foreach($chFolders as $chFolder) {
                        $chName = basename($chFolder);
                        $manga->chapters()->updateOrCreate(
                            ['chapter_num' => $chName],
                            [
                                'title' => ucfirst(str_replace('-', ' ', $chName)),
                                'is_local' => true,
                                'local_path' => "mangas/{$manga->slug}/chapters/$chName"
                            ]
                        );
                    }
                }
                $count++;
            }
        }
        
        return "Migration complete! Processed $count mangas. Please delete this route after use.";
    });

    Route::post('/import/{type}/{id}', [MangaController::class, 'import'])->name('manga.import');
    Route::delete('/admin/delete-manga/{slug}', [MangaController::class, 'delete']);
    
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Image Proxy (To prevent Hotlinking/CORS issues)
Route::get('/proxy-image', function(Request $request) {
    $url = $request->query('url');
    if (!$url) abort(404);

    // Support for relative URLs
    if (str_starts_with($url, '/')) {
        $url = 'https://comicazen.com' . $url;
    }

    try {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control' => 'no-cache',
        ];

        if (str_contains($url, 'comicazen')) {
            $headers['Referer'] = 'https://comicazen.com/';
        } elseif (str_contains($url, 'mangadex')) {
            $headers['Referer'] = 'https://mangadex.org/';
        } elseif (str_contains($url, 'lunaranime')) {
            $headers['Referer'] = 'https://lunaranime.ru/';
        }

        $response = Http::withHeaders($headers)
            ->withOptions(['verify' => false, 'follow_redirects' => true])
            ->timeout(15)
            ->get($url);

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::warning("Proxy failed for $url: " . $response->status());
            abort(404);
        }

        $contentType = $response->header('Content-Type');
        
        return response($response->body())
            ->header('Content-Type', $contentType)
            ->header('Cache-Control', 'public, max-age=86400');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Proxy exception for $url: " . $e->getMessage());
        abort(404);
    }
})->name('proxy.image');

require __DIR__.'/auth.php';
