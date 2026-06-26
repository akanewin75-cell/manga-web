<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class ComicazenService
{
    protected $baseUrl = 'https://v3.comicaso.pro';
    protected $mangaPath = '/manga';

    public function __construct()
    {
        \Illuminate\Support\Facades\Log::info("ComicazenService: Initialized");
    }

    public function getHeaders($referer = null)
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Ch-Ua' => '"Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Priority' => 'u=0, i',
        ];

        if ($referer) {
            $headers['Referer'] = $referer;
            $headers['Sec-Fetch-Site'] = 'same-origin';
        }

        return $headers;
    }

    public function searchManga($query = null, $page = 1)
    {
        $searchPaths = $query ? ["/"] : ["/manga/", "/komik/", "/"];
        $mangas = [];

        foreach ($searchPaths as $path) {
            if ($query) {
                $url = $page > 1 
                    ? "{$this->baseUrl}/page/{$page}/?s=" . urlencode($query) . "&post_type=wp-manga&m_paged={$page}&paged={$page}"
                    : "{$this->baseUrl}/?s=" . urlencode($query) . "&post_type=wp-manga";
            } else {
                $url = $page > 1 
                    ? "{$this->baseUrl}" . rtrim($path, '/') . "/page/{$page}/?m_paged={$page}&paged={$page}&page={$page}" 
                    : "{$this->baseUrl}" . $path;
            }
            
            try {
                $response = Http::withHeaders($this->getHeaders())
                    ->withOptions(['verify' => false])
                    ->timeout(20)
                    ->get($url);
                if (!$response->successful()) continue;

                $html = $response->body();
                \Illuminate\Support\Facades\Log::info("Comicazen: Loaded $url, HTML length: " . strlen($html));

                $jsonIds = ['mjv2-manga-data', 'mjv2-komik-data', 'mjv2-home-data', 'madara-manga-data', 'wp-manga-data', 'manga-data'];
                foreach ($jsonIds as $id) {
                    $searchStr = 'id="' . $id . '"';
                    $pos = strpos($html, $searchStr);
                    if ($pos === false) {
                        $searchStr = "id='" . $id . "'";
                        $pos = strpos($html, $searchStr);
                    }

                    if ($pos !== false) {
                        $startTagPos = strrpos(substr($html, 0, $pos), '<script');
                        if ($startTagPos !== false) {
                            $openingTagEnd = strpos($html, '>', $pos);
                            if ($openingTagEnd !== false) {
                                $closingTagPos = strpos($html, '</script>', $openingTagEnd);
                                if ($closingTagPos !== false) {
                                    $jsonText = trim(substr($html, $openingTagEnd + 1, $closingTagPos - $openingTagEnd - 1));
                                    $jsonData = json_decode($jsonText, true);
                                    if (!$jsonData && preg_match('/({.*})|(\[.*\])/s', $jsonText, $jsonMatches)) {
                                        $jsonData = json_decode($jsonMatches[0], true);
                                    }

                                    if ($jsonData) {
                                        $items = $jsonData['items'] ?? $jsonData['allItems'] ?? $jsonData['latest_updates'] ?? $jsonData['trending'] ?? $jsonData['data'] ?? $jsonData ?? [];
                                        if (is_array($items)) {
                                            if (isset($items[0]['items'])) $items = $items[0]['items'];
                                            foreach ($items as $item) {
                                                if (!is_array($item)) continue;
                                                $itemUrl = $item['url'] ?? $item['link'] ?? $item['href'] ?? null;
                                                $slug = $item['slug'] ?? ($itemUrl ? basename(rtrim(parse_url($itemUrl, PHP_URL_PATH), '/')) : null);
                                                if (!$slug || strlen($slug) < 2) continue;
                                                
                                                $cover = $item['thumbnail'] ?? $item['cover'] ?? $item['image'] ?? $item['img'] ?? $item['thumb'] ?? null;
                                                if ($cover && !str_starts_with($cover, 'http')) {
                                                    $cover = $this->baseUrl . (str_starts_with($cover, '/') ? '' : '/') . $cover;
                                                }
                                                $mangas[$slug] = [
                                                    'id' => $slug,
                                                    'title' => trim($item['title'] ?? $item['name'] ?? $slug),
                                                    'slug' => $slug,
                                                    'cover' => $cover,
                                                    'genre' => is_array($item['genres'] ?? null) ? implode(', ', $item['genres']) : ($item['genres'] ?? $item['category'] ?? ''),
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // DOM Scraping
                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new DOMXPath($dom);
                $nodes = $xpath->query("//div[contains(@class, 'page-item-detail')] | //div[contains(@class, 'manga-item')] | //div[contains(@class, 'mjv2-card')] | //div[contains(@class, 'c-tabs-item__content')] | //div[contains(@class, 'item-summary')] | //div[contains(@class, 'post-item')] | //div[contains(@class, 'mjv2-detail-card')]");
                
                foreach ($nodes as $node) {
                    $titleNode = $xpath->query(".//h3/a | .//h4/a | .//h5/a | .//div[contains(@class, 'post-title')]//a | .//a[contains(@class, 'mjv2-card-title')] | .//a[contains(@class, 'mjv2-detail-title')]", $node)->item(0);
                    $imgNode = $xpath->query(".//img", $node)->item(0);
                    if ($titleNode) {
                        $href = $titleNode->getAttribute('href');
                        $slug = basename(rtrim(parse_url($href, PHP_URL_PATH), '/'));
                        if (!$slug || in_array($slug, ['komik', 'manga', 'category', 'genre', 'page']) || strlen($slug) < 2) continue;

                        if (!isset($mangas[$slug])) {
                            $genreNodes = $xpath->query(".//div[contains(@class, 'genres-content')]//a | .//div[contains(@class, 'mg-category')]//a | .//span[contains(@class, 'genre')]//a", $node);
                            $genres = [];
                            foreach ($genreNodes as $gNode) $genres[] = trim($gNode->nodeValue);
                            
                            $cover = $imgNode ? ($imgNode->getAttribute('data-src') ?: ($imgNode->getAttribute('data-lazy-src') ?: ($imgNode->getAttribute('src') ?: $imgNode->getAttribute('data-cfsrc')))) : null;
                            if ($cover && !str_starts_with($cover, 'http')) {
                                $cover = $this->baseUrl . (str_starts_with($cover, '/') ? '' : '/') . $cover;
                            }
                            
                            $mangas[$slug] = [
                                'id' => $slug,
                                'title' => trim($titleNode->nodeValue),
                                'slug' => $slug,
                                'cover' => $cover,
                                'genre' => implode(', ', $genres),
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Comicazen Search Error for $url: " . $e->getMessage());
            }
            if (!empty($mangas)) break; // Found something, stop trying other paths
        }

        $results = array_values($mangas);
        if ($query && !empty($results)) {
            $queryLower = strtolower($query);
            $results = array_values(array_filter($results, function($m) use ($queryLower) {
                return str_contains(strtolower($m['title'] ?? ''), $queryLower) || str_contains(strtolower($m['slug'] ?? ''), $queryLower);
            }));
        }

        return $results;
    }

    public function getMangaDetails($slug)
    {
        \Illuminate\Support\Facades\Log::info("ComicazenService: Delegating getMangaDetails to ComicasoService for $slug");
        try {
            $comicasoService = app(\App\Services\ComicasoService::class);
            $details = $comicasoService->getMangaDetails("comicazen__" . $slug);
            
            if ($details) {
                return [
                    'id' => $details['id'],
                    'title' => $details['title'],
                    'slug' => $details['slug'],
                    'description' => $details['description'],
                    'cover' => $details['cover'],
                    'genre' => $details['genre'],
                    'chapters' => array_map(fn($ch) => [
                        'id' => $ch['id'],
                        'title' => $ch['title']
                    ], $details['chapters'] ?? [])
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Comicazen Details Delegation Error: " . $e->getMessage());
        }
        return null;
    }

    public function getChapterImages($slug, $chapterId)
    {
        \Illuminate\Support\Facades\Log::info("ComicazenService: Delegating getChapterImages to ComicasoService for $slug / $chapterId");
        try {
            $comicasoService = app(\App\Services\ComicasoService::class);
            return $comicasoService->getChapterImages("comicazen__" . $slug, $chapterId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Comicazen Chapter Delegation Error: " . $e->getMessage());
            return [];
        }
    }
}
