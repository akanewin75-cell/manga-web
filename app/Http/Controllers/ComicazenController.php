<?php

namespace App\Http\Controllers;

use App\Services\ComicazenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ComicazenController extends Controller
{
    protected $comicazenService;

    public function __construct(ComicazenService $comicazenService)
    {
        $this->comicazenService = $comicazenService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $page = $request->query('page', 1);
        $mangas = $this->comicazenService->searchManga($search, $page);

        return view('webtoon.index', [
            'mangas' => $mangas,
            'search' => $search,
            'page' => $page,
            'source' => 'comicazen'
        ]);
    }

    public function show($slug)
    {
        \Illuminate\Support\Facades\Log::info("ComicazenController: show hit for slug: $slug");
        $info = $this->comicazenService->getMangaDetails($slug);
        
        if (!$info) {
            \Illuminate\Support\Facades\Log::error("ComicazenController: No info found for slug: $slug");
            abort(404);
        }

        \Illuminate\Support\Facades\Log::info("ComicazenController: Info found for $slug, chapter count: " . count($info['chapters'] ?? []));

        $info['id'] = $slug;
        $info['slug'] = $slug;
        $info['source'] = 'comicazen';

        $externalChapters = $info['chapters'];
        $chapters = []; // No local chapters yet
        $source = 'comicazen';

        return view('manga', compact('info', 'chapters', 'externalChapters', 'source'));
    }

    public function read($slug, $chapterId)
    {
        $images = $this->comicazenService->getChapterImages($slug, $chapterId);
        
        if (!$images) {
            abort(404);
        }

        return view('webtoon.read', [
            'id' => $slug,
            'chapterId' => $chapterId,
            'images' => $images,
            'source' => 'comicazen'
        ]);
    }

    public function import(Request $request, $slug)
    {
        $mangaData = $this->comicazenService->getMangaDetails($slug);
        
        if (!$mangaData) {
            return back()->with('error', 'Gagal mengambil data dari Comicazen');
        }

        $title = $mangaData['title'];
        $localSlug = Str::slug($title);
        
        $path = public_path("mangas/$localSlug");
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        // Download Cover
        $coverUrl = $mangaData['cover'];
        $coverName = 'cover.jpg';
        if ($coverUrl) {
            try {
                $coverContent = Http::withHeaders($this->comicazenService->getHeaders())->get($coverUrl)->body();
                File::put("$path/$coverName", $coverContent);
            } catch (\Exception $e) {
                // Keep default or handle error
            }
        }

        $data = [
            'title' => $title,
            'description' => $mangaData['description'],
            'genre' => $mangaData['genre'],
            'cover' => $coverName,
            'slug' => $localSlug,
            'likes' => 0,
            'comicazen_slug' => $slug
        ];

        File::put(
            "$path/info.json",
            json_encode($data)
        );

        return redirect("/")->with('success', "Komik '$title' berhasil diimpor dari Comicazen! 😎");
    }

    public function importChapter(Request $request, $slug, $chapterId)
    {
        $mangaPath = null;
        $folders = File::directories(public_path('mangas'));
        
        foreach ($folders as $folder) {
            $jsonPath = $folder . '/info.json';
            if (File::exists($jsonPath)) {
                $info = json_decode(File::get($jsonPath), true);
                if (isset($info['comicazen_slug']) && $info['comicazen_slug'] === $slug) {
                    $mangaPath = $folder;
                    break;
                }
            }
        }

        if (!$mangaPath) {
            return back()->with('error', 'Komik ini belum diimpor. Impor dulu ya!');
        }

        $images = $this->comicazenService->getChapterImages($slug, $chapterId);
        if (!$images) {
            return back()->with('error', 'Gagal mengambil gambar chapter');
        }

        $chapterPath = "$mangaPath/chapters/$chapterId";
        if (!File::exists($chapterPath)) {
            File::makeDirectory($chapterPath, 0777, true, true);
        }

        foreach ($images as $index => $url) {
            try {
                $imageContent = Http::withHeaders($this->comicazenService->getHeaders())->get($url)->body();
                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                // Clean up extension if it has query params
                $ext = explode('?', $ext)[0];
                if (strlen($ext) > 4) $ext = 'jpg';
                
                $name = str_pad($index + 1, 3, '0', STR_PAD_LEFT) . '.' . $ext;
                File::put("$chapterPath/$name", $imageContent);
            } catch (\Exception $e) {
                // Continue
            }
        }

        return back()->with('success', "Chapter $chapterId berhasil diunduh ke lokal! 🔥");
    }
}
