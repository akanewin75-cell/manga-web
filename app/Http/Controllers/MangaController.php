<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use App\Services\MangaDiscoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class MangaController extends Controller
{
    protected $discoveryService;

    public function __construct(MangaDiscoveryService $discoveryService)
    {
        $this->discoveryService = $discoveryService;
    }

    /**
     * Show manga details.
     * Supports both local (imported) and external (cloud) discovery.
     */
    public function show($type, $id)
    {
        // 1. Try to find local manga by source reference
        $localManga = Manga::where('source_type', $type)->where('source_id', $id)->first();
        
        // 2. Get external data
        $externalData = $this->discoveryService->getDetails($type, $id);
        
        if (!$externalData && !$localManga) {
            abort(404);
        }

        // 3. Prepare info object
        $info = (object) ($externalData ?? [
            'title' => $localManga->title,
            'description' => $localManga->description,
            'cover' => $localManga->cover_url,
            'genre' => $localManga->genre,
            'source_type' => $localManga->source_type,
            'source_id' => $localManga->source_id,
            'slug' => $localManga->slug,
            'chapters' => []
        ]);

        // 4. Merge chapters
        $localChapters = $localManga ? $localManga->chapters()->pluck('chapter_num')->toArray() : [];
        $chapters = $info->chapters ?? [];

        return view('manga', compact('info', 'localManga', 'localChapters', 'chapters'));
    }

    public function read($type, $mangaId, $chapterId)
    {
        \Illuminate\Support\Facades\Log::info("MangaController: Reading - Type: $type, Manga: $mangaId, Chapter: $chapterId");
        
        $info = $this->discoveryService->getDetails($type, $mangaId);
        if (!$info) abort(404);

        $chapters = $info['chapters'] ?? [];
        
        $currentIndex = -1;
        foreach ($chapters as $index => $ch) {
            if ($ch['id'] == $chapterId) {
                $currentIndex = $index;
                break;
            }
        }

        // Chapters usually come newest first (Index 0 is latest)
        // Next chapter (newer) is index - 1
        // Previous chapter (older) is index + 1
        $prevChapter = ($currentIndex !== -1 && $currentIndex < count($chapters) - 1) ? $chapters[$currentIndex + 1] : null;
        $nextChapter = ($currentIndex !== -1 && $currentIndex > 0) ? $chapters[$currentIndex - 1] : null;

        // 1. Check local chapter first
        $localManga = Manga::where('source_type', $type)->where('source_id', $mangaId)->first();
        $images = [];

        if ($localManga) {
            $localChapter = $localManga->chapters()
                ->where('source_chapter_id', $chapterId)
                ->orWhere('chapter_num', $chapterId)
                ->first();

            if ($localChapter && $localChapter->is_local) {
                $path = public_path($localChapter->local_path);
                if (File::exists($path)) {
                    $files = File::files($path);
                    $fileNames = [];
                    foreach ($files as $file) {
                        $fileNames[] = $file->getFilename();
                    }
                    natcasesort($fileNames);
                    
                    foreach ($fileNames as $fileName) {
                        $images[] = asset($localChapter->local_path . '/' . $fileName);
                    }
                }
            }
        }

        // 2. Fetch images from cloud if local not found or empty
        if (empty($images)) {
            $images = $this->discoveryService->getChapterImages($type, $mangaId, $chapterId);
        }
        
        if (!$images || count($images) == 0) {
            \Illuminate\Support\Facades\Log::error("MangaController: No images found for $mangaId chapter $chapterId");
            return view('read', [
                'images' => [],
                'type' => $type,
                'mangaId' => $mangaId,
                'chapterId' => $chapterId,
                'nextChapter' => $nextChapter,
                'prevChapter' => $prevChapter,
                'mangaTitle' => $info['title'] ?? 'Unknown Manga',
                'error' => 'Gagal mengambil gambar. Sumber mungkin memblokir akses atau chapter tidak ditemukan.'
            ]);
        }

        return view('read', [
            'images' => $images,
            'type' => $type,
            'mangaId' => $mangaId,
            'chapterId' => $chapterId,
            'nextChapter' => $nextChapter,
            'prevChapter' => $prevChapter,
            'mangaTitle' => $info['title'] ?? 'Unknown Manga'
        ]);
    }

    public function import($type, $id)
    {
        $data = $this->discoveryService->getDetails($type, $id);
        if (!$data) return back()->with('error', 'Gagal mengambil data sumber');

        $slug = Str::slug($data['title']);
        
        $manga = Manga::updateOrCreate(
            ['source_type' => $type, 'source_id' => $id],
            [
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'cover_url' => $data['cover'],
                'genre' => $data['genre'],
            ]
        );

        // Optional: Localize cover
        $this->localizeCover($manga);

        return back()->with('success', "'{$manga->title}' berhasil ditambahkan ke database! 😎");
    }

    protected function localizeCover(Manga $manga)
    {
        if (str_starts_with($manga->cover_url, 'http')) {
            try {
                $path = public_path("mangas/{$manga->slug}");
                if (!File::exists($path)) File::makeDirectory($path, 0777, true);
                
                $response = Http::get($manga->cover_url);
                if ($response->successful()) {
                    File::put("$path/cover.jpg", $response->body());
                    $manga->update(['cover_url' => asset("mangas/{$manga->slug}/cover.jpg")]);
                }
            } catch (\Exception $e) {}
        }
    }

    public function delete($slug)
    {
        $manga = Manga::where('slug', $slug)->firstOrFail();
        
        // Delete local files
        $folder = public_path("mangas/$slug");
        if (File::exists($folder)) {
            File::deleteDirectory($folder);
        }

        $manga->delete();

        return redirect('/')->with('success', 'Manga berhasil dihapus dari database');
    }
}
