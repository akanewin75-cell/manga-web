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
        $headers = [
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

        $cookie = config('services.comicaso.cookie');
        if ($cookie) {
            $headers['Cookie'] = $cookie;
        }

        return $headers;
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
                ->withOptions(['verify' => false])
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
                ->withOptions(['verify' => false])
                ->timeout(30)
                ->get("{$this->baseUrl}/api/manga.php", [
                    'source' => $source,
                    'slug' => $slug,
                    'platform' => 'web'
                ]);

            if (!$response->successful() && !$explicitSource) {
                // Try 'medusa' if comicazen fails
                $response = Http::withHeaders($this->getHeaders())
                    ->withOptions(['verify' => false])
                    ->timeout(30)
                    ->get("{$this->baseUrl}/api/manga.php", [
                        'source' => 'medusa',
                        'slug' => $slug,
                        'platform' => 'web'
                    ]);
            }

            if (!$response->successful() && $explicitSource) {
                $fallbackSources = array_filter(['comicazen', 'medusa'], fn($s) => $s !== $source);
                foreach ($fallbackSources as $fSource) {
                    $response = Http::withHeaders($this->getHeaders())
                        ->withOptions(['verify' => false])
                        ->timeout(30)
                        ->get("{$this->baseUrl}/api/manga.php", [
                            'source' => $fSource,
                            'slug' => $slug,
                            'platform' => 'web'
                        ]);
                    if ($response->successful()) break;
                }
            }

            if (!$response->successful()) {
                Log::error("Comicaso Details Failed for $slug. Status: " . $response->status() . " Response: " . substr($response->body(), 0, 300));
                return null;
            }

            $json = $response->json();
            if ($json && isset($json['locked']) && $json['locked'] == 1) {
                Log::warning("Comicaso Details Locked for $slug: " . ($json['message'] ?? 'Login required') . ". Response: " . json_encode($json));
                return null;
            }

            if (!$json || !isset($json['data'])) {
                Log::warning("Comicaso Details response data missing for $slug. Response: " . substr($response->body(), 0, 300));
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
                        'chapter_token' => $ch['chapter_token'] ?? null,
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

            Log::info("Comicaso getChapterImages: Fetching token for source=$source, manga=$mangaSlug, chapter=$chapterSlug");

            $cookieJar = new \GuzzleHttp\Cookie\CookieJar();

            // 1. Fetch details to get a fresh chapter token and initiate the session (PHPSESSID)
            $detailsResponse = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => false,
                    'cookies' => $cookieJar
                ])
                ->timeout(30)
                ->get("{$this->baseUrl}/api/manga.php", [
                    'source' => $source,
                    'slug' => $mangaSlug,
                    'platform' => 'web'
                ]);

            if (!$detailsResponse->successful()) {
                Log::error("Comicaso getChapterImages: Failed to fetch details for token. Status: " . $detailsResponse->status());
                return [];
            }

            $detailsJson = $detailsResponse->json();
            $chapters = $detailsJson['data']['chapters'] ?? [];
            $chapterToken = null;

            foreach ($chapters as $ch) {
                if (($ch['slug'] ?? '') === $chapterSlug) {
                    $chapterToken = $ch['chapter_token'] ?? null;
                    break;
                }
            }

            if (!$chapterToken) {
                Log::warning("Comicaso getChapterImages: Token not found in details for chapter: $chapterSlug");
            }

            // 2. Fetch the actual chapter images using the same session cookies and the token
            $params = [
                'source' => $source,
                'manga' => $mangaSlug,
                'chapter' => $chapterSlug,
                'platform' => 'web'
            ];
            if ($chapterToken) {
                $params['token'] = $chapterToken;
            }

            $response = Http::withHeaders($this->getHeaders())
                ->withOptions([
                    'verify' => false,
                    'cookies' => $cookieJar
                ])
                ->timeout(30)
                ->get("{$this->baseUrl}/api/chapter.php", $params);

            if (!$response->successful() && !$explicitSource) {
                // Try 'medusa' source if default failed and not explicit
                return $this->getChapterImages("medusa__" . $mangaSlug, $chapterSlug);
            }

            if (!$response->successful()) {
                Log::error("Comicaso Chapter Failed for $mangaSlug / $chapterSlug. Status: " . $response->status() . " Response: " . substr($response->body(), 0, 300));
                return [];
            }

            $json = $response->json();
            if ($json && isset($json['locked']) && $json['locked'] == 1) {
                Log::warning("Comicaso Chapter Locked for $mangaSlug / $chapterSlug: " . ($json['message'] ?? 'Login required'));
                return [];
            }

            if (!$json || !isset($json['data']['images'])) {
                Log::warning("Comicaso Chapter response images missing for $mangaSlug / $chapterSlug. Response: " . substr($response->body(), 0, 300));
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
