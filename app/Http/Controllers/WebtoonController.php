<?php

namespace App\Http\Controllers;

use App\Services\WebtoonService;
use Illuminate\Http\Request;

class WebtoonController extends Controller
{
    protected $webtoonService;

    public function __construct(WebtoonService $webtoonService)
    {
        $this->webtoonService = $webtoonService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $mangasData = $this->webtoonService->searchWebtoon($search);
        
        $mangas = [];
        if (isset($mangasData['data'])) {
            foreach ($mangasData['data'] as $manga) {
                $mangas[] = [
                    'id' => $manga['id'],
                    'title' => $manga['attributes']['title']['en'] ?? array_values($manga['attributes']['title'])[0] ?? 'Unknown',
                    'description' => $manga['attributes']['description']['en'] ?? array_values($manga['attributes']['description'])[0] ?? '',
                    'cover' => $this->webtoonService->getCoverUrl($manga),
                    'is_webtoon' => true
                ];
            }
        }

        return view('webtoon.index', compact('mangas', 'search'));
    }

    public function show($id)
    {
        \Illuminate\Support\Facades\Log::info("WebtoonController: show hit for id: $id");
        $mangaData = $this->webtoonService->getWebtoonDetails($id);
        
        if (!isset($mangaData['data'])) {
            \Illuminate\Support\Facades\Log::error("WebtoonController: No data found for id: $id");
            abort(404);
        }

        $manga = $mangaData['data'];
        $info = [
            'id' => $manga['id'],
            'title' => $manga['attributes']['title']['en'] ?? array_values($manga['attributes']['title'])[0] ?? 'Unknown',
            'description' => $manga['attributes']['description']['en'] ?? array_values($manga['attributes']['description'])[0] ?? '',
            'cover' => $this->webtoonService->getCoverUrl($manga),
            'genre' => implode(', ', array_map(fn($tag) => $tag['attributes']['name']['en'], array_filter($manga['attributes']['tags'], fn($tag) => $tag['attributes']['group'] === 'genre'))),
            'slug' => $id,
            'source' => 'webtoon'
        ];

        $feedData = $this->webtoonService->getWebtoonFeed($id);
        $externalChapters = $feedData['data'] ?? [];
        
        \Illuminate\Support\Facades\Log::info("WebtoonController: Info found for $id, chapter count: " . count($externalChapters));

        $chapters = [];
        $source = 'webtoon';

        return view('manga', compact('info', 'chapters', 'externalChapters', 'source'));
    }

    public function read($id, $chapterId)
    {
        $images = $this->webtoonService->getChapterImages($chapterId);
        
        if (!$images) {
            abort(404);
        }

        return view('webtoon.read', compact('id', 'chapterId', 'images'));
    }

    public function importWebtoon(Request $request, $id)
    {
        $mangaData = $this->webtoonService->getWebtoonDetails($id);
        
        if (!isset($mangaData['data'])) {
            return back()->with('error', 'Gagal mengambil data dari Webtoon');
        }

        $manga = $mangaData['data'];
        $title = $manga['attributes']['title']['en'] ?? array_values($manga['attributes']['title'])[0] ?? 'Unknown';
        $slug = \Illuminate\Support\Str::slug($title);
        
        $path = public_path("mangas/$slug");
        if (!\Illuminate\Support\Facades\File::exists($path)) {
            \Illuminate\Support\Facades\File::makeDirectory($path, 0777, true, true);
        }

        // Download Cover
        $coverUrl = $this->webtoonService->getCoverUrl($manga);
        $coverName = 'cover.jpg';
        if ($coverUrl) {
            try {
                $coverContent = \Illuminate\Support\Facades\Http::get($coverUrl)->body();
                \Illuminate\Support\Facades\File::put("$path/$coverName", $coverContent);
            } catch (\Exception $e) {
                // Keep default or handle error
            }
        }

        $data = [
            'title' => $title,
            'description' => $manga['attributes']['description']['en'] ?? array_values($manga['attributes']['description'])[0] ?? '',
            'genre' => implode(', ', array_map(fn($tag) => $tag['attributes']['name']['en'], array_filter($manga['attributes']['tags'], fn($tag) => $tag['attributes']['group'] === 'genre'))),
            'cover' => $coverName,
            'slug' => $slug,
            'likes' => 0,
            'webtoon_id' => $id
        ];

        \Illuminate\Support\Facades\File::put(
            "$path/info.json",
            json_encode($data)
        );

        return redirect("/")->with('success', "Webtoon '$title' berhasil diimpor ke lokal! 😎");
    }

    public function importChapter(Request $request, $id, $chapterId)
    {
        $mangaPath = null;
        $folders = \Illuminate\Support\Facades\File::directories(public_path('mangas'));
        
        foreach ($folders as $folder) {
            $jsonPath = $folder . '/info.json';
            if (\Illuminate\Support\Facades\File::exists($jsonPath)) {
                $info = json_decode(\Illuminate\Support\Facades\File::get($jsonPath), true);
                if (isset($info['webtoon_id']) && $info['webtoon_id'] === $id) {
                    $mangaPath = $folder;
                    break;
                }
            }
        }

        if (!$mangaPath) {
            return back()->with('error', 'Webtoon ini belum diimpor ke lokal. Impor dulu ya!');
        }

        $images = $this->webtoonService->getChapterImages($chapterId);
        if (!$images) {
            return back()->with('error', 'Gagal mengambil gambar chapter');
        }

        // Get chapter info
        $feed = $this->webtoonService->getWebtoonFeed($id);
        $chapterInfo = collect($feed['data'])->firstWhere('id', $chapterId);
        $chapterNum = $chapterInfo['attributes']['chapter'] ?? 'unknown';
        
        $chapterPath = "$mangaPath/chapters/chapter-$chapterNum";
        if (!\Illuminate\Support\Facades\File::exists($chapterPath)) {
            \Illuminate\Support\Facades\File::makeDirectory($chapterPath, 0777, true, true);
        }

        foreach ($images as $index => $url) {
            try {
                $imageContent = \Illuminate\Support\Facades\Http::get($url)->body();
                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $name = ($index + 1) . '.' . $ext;
                \Illuminate\Support\Facades\File::put("$chapterPath/$name", $imageContent);
            } catch (\Exception $e) {
                // Continue
            }
        }

        return back()->with('success', "Chapter $chapterNum berhasil diunduh ke lokal! 🔥");
    }
}
