<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ComicasoService
{
    protected $baseUrl = 'https://v3.comicaso.pro';

    public function __construct()
    {
        Log::info("ComicasoService: Initialized");
    }

    protected function getHeaders()
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer' => $this->baseUrl . '/',
            'Origin' => $this->baseUrl,
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-origin',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
        ];
    }

    public function searchManga($query = null, $page = 1)
    {
        $params = [
            'page' => $page,
            'paged' => $page,
            'offset' => ($page - 1) * 20, // Some APIs use offset
            'source' => 'all',
            'mode' => $query ? 'search' : 'latest',
            'q' => $query ?: '',
        ];

        try {
            // Log the request to debug pagination
            Log::info("Comicaso Request: " . "{$this->baseUrl}/api/home.php?" . http_build_query($params));
            
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/api/home.php", $params);

            if (!$response->successful()) {
                Log::error("Comicaso Search Failed: " . $response->status());
                return [];
            }

            $json = $response->json();
            if (!$json || !isset($json['data'])) {
                return [];
            }

            $mangas = [];
            foreach ($json['data'] as $item) {
                $cover = $item['thumbnail'];
                if ($cover && !str_starts_with($cover, 'http')) {
                    $cover = $this->baseUrl . (str_starts_with($cover, '/') ? '' : '/') . $cover;
                }
                
                $mangas[] = [
                    'id' => $item['slug'],
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'cover' => $cover,
                    'source_type' => 'comicaso',
                    'original_source' => $item['source'] ?? 'unknown',
                    'genre' => $item['genre'] ?? '',
                    'status' => $item['status'] ?? ''
                ];
            }

            return $mangas;
        } catch (\Exception $e) {
            Log::error("Comicaso Search Exception: " . $e->getMessage());
            return [];
        }
    }

    public function getMangaDetails($slug)
    {
        try {
            $source = 'comicazen'; // Defaulting to comicazen as it's the most common on the site
            $explicitSource = false;
            if (str_contains($slug, '__')) {
                $parts = explode('__', $slug, 2);
                $source = $parts[0];
                $slug = $parts[1];
                $explicitSource = true;
            }
            
            Log::info("Comicaso Detail Fetching: source=$source, slug=$slug");
            
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/api/manga.php", [
                    'source' => $source,
                    'slug' => $slug
                ]);

            if (!$response->successful() && !$explicitSource) {
                // Try 'medusa' if comicazen fails
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(30)
                    ->get("{$this->baseUrl}/api/manga.php", [
                        'source' => 'medusa',
                        'slug' => $slug
                    ]);
            }

            if (!$response->successful() && $explicitSource) {
                $fallbackSources = array_filter(['comicazen', 'medusa'], fn($s) => $s !== $source);
                foreach ($fallbackSources as $fSource) {
                    $response = Http::withHeaders($this->getHeaders())
                        ->timeout(30)
                        ->get("{$this->baseUrl}/api/manga.php", [
                            'source' => $fSource,
                            'slug' => $slug
                        ]);
                    if ($response->successful()) break;
                }
            }

            if (!$response->successful()) {
                Log::error("Comicaso Details Failed for $slug");
                return null;
            }

            $json = $response->json();
            if (!$json || !isset($json['data'])) {
                return null;
            }

            $data = $json['data'];
            
            $cover = $data['thumbnail'];
            if ($cover && !str_starts_with($cover, 'http')) {
                $cover = $this->baseUrl . (str_starts_with($cover, '/') ? '' : '/') . $cover;
            }
            
            return [
                'id' => $data['slug'],
                'title' => $data['title'],
                'description' => $data['synopsis'] ?? '',
                'cover' => $cover,
                'genre' => is_array($data['genres'] ?? null) ? implode(', ', $data['genres']) : ($data['genres'] ?? ''),
                'slug' => $data['slug'],
                'chapters' => array_map(function($ch) {
                    return [
                        'id' => $ch['slug'],
                        'title' => $ch['title'],
                        'chapter_num' => $this->extractChapterNum($ch['title'])
                    ];
                }, $data['chapters'] ?? [])
            ];
        } catch (\Exception $e) {
            Log::error("Comicaso Details Exception: " . $e->getMessage());
            return null;
        }
    }

    public function getChapterImages($mangaSlug, $chapterSlug)
    {
        try {
            $source = 'comicazen'; // Default
            $explicitSource = false;
            if (str_contains($mangaSlug, '__')) {
                $parts = explode('__', $mangaSlug, 2);
                $source = $parts[0];
                $mangaSlug = $parts[1];
                $explicitSource = true;
            }
            
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/api/chapter.php", [
                    'source' => $source,
                    'manga' => $mangaSlug,
                    'chapter' => $chapterSlug
                ]);

            if (!$response->successful() && !$explicitSource) {
                // Try 'medusa'
                $response = Http::withHeaders($this->getHeaders())
                    ->timeout(30)
                    ->get("{$this->baseUrl}/api/chapter.php", [
                        'source' => 'medusa',
                        'manga' => $mangaSlug,
                        'chapter' => $chapterSlug
                    ]);
            }

            if (!$response->successful() && $explicitSource) {
                $fallbackSources = array_filter(['comicazen', 'medusa'], fn($s) => $s !== $source);
                foreach ($fallbackSources as $fSource) {
                    $response = Http::withHeaders($this->getHeaders())
                        ->timeout(30)
                        ->get("{$this->baseUrl}/api/chapter.php", [
                            'source' => $fSource,
                            'manga' => $mangaSlug,
                            'chapter' => $chapterSlug
                        ]);
                    if ($response->successful()) break;
                }
            }

            if (!$response->successful()) {
                Log::error("Comicaso Chapter Failed for $mangaSlug / $chapterSlug");
                return [];
            }

            $json = $response->json();
            if (!$json || !isset($json['data']['images'])) {
                return [];
            }

            $images = $json['data']['images'];
            return array_map(function($img) {
                if ($img && !str_starts_with($img, 'http')) {
                    return $this->baseUrl . (str_starts_with($img, '/') ? '' : '/') . $img;
                }
                return $img;
            }, $images);
        } catch (\Exception $e) {
            Log::error("Comicaso Chapter Exception: " . $e->getMessage());
            return [];
        }
    }

    protected function extractChapterNum($title)
    {
        if (preg_match('/Chapter\s+(\d+(\.\d+)?)/i', $title, $matches)) {
            return $matches[1];
        }
        return $title;
    }
}
