<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class ComicazenService
{
    protected $baseUrl = 'https://comicazen.com';
    protected $mangaPath = '/manga';

    public function __construct()
    {
        \Illuminate\Support\Facades\Log::info("ComicazenService: Initialized");
    }

    public function getHeaders($referer = null)
    {
        $uas = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0'
        ];
        
        $headers = [
            'User-Agent' => $uas[array_rand($uas)],
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'DNT' => '1',
        ];

        if ($referer) {
            $headers['Referer'] = $referer;
            $headers['Sec-Fetch-Site'] = 'same-origin';
        }

        return $headers;
    }

    public function searchManga($query = null, $page = 1)
    {
        $url = $query 
            ? "{$this->baseUrl}/?s=" . urlencode($query) . "&post_type=wp-manga"
            : "{$this->baseUrl}/";
        
        if ($page > 1 && !$query) {
            $url = "{$this->baseUrl}/page/{$page}/";
        }
        
        try {
            $response = Http::withHeaders($this->getHeaders())->timeout(15)->get($url);
            if (!$response->successful()) {
                return [];
            }

            $html = $response->body();
            $mangas = [];

            // Method 1: Try multiple JSON script ID candidates (MJV2 and others)
            $jsonIds = ['mjv2-home-data', 'mjv2-manga-data', 'mjv2-komik-data', 'madara-manga-data'];
            foreach ($jsonIds as $id) {
                $searchStr = 'id="' . $id . '"';
                $pos = strpos($html, $searchStr);
                if ($pos === false) {
                    $searchStr = "id='" . $id . "'";
                    $pos = strpos($html, $searchStr);
                }

                if ($pos !== false) {
                    // Find the start of the script tag
                    $startTagPos = strrpos(substr($html, 0, $pos), '<script');
                    if ($startTagPos !== false) {
                        // Find the end of the opening tag
                        $openingTagEnd = strpos($html, '>', $pos);
                        if ($openingTagEnd !== false) {
                            // Find the end of the script tag
                            $closingTagPos = strpos($html, '</script>', $openingTagEnd);
                            if ($closingTagPos !== false) {
                                $jsonText = trim(substr($html, $openingTagEnd + 1, $closingTagPos - $openingTagEnd - 1));
                                $jsonData = json_decode($jsonText, true);
                                
                                if (!$jsonData) {
                                    // Try to clean up JSON
                                    if (preg_match('/({.*})|(\[.*\])/s', $jsonText, $jsonMatches)) {
                                        $jsonData = json_decode($jsonMatches[0], true);
                                    }
                                }

                                if ($jsonData) {
                                    $items = $jsonData['items'] ?? $jsonData['allItems'] ?? $jsonData['latest_updates'] ?? $jsonData['trending'] ?? $jsonData['data'] ?? $jsonData ?? [];
                                    if (is_array($items)) {
                                        // If it's a nested structure
                                        if (isset($items[0]['items'])) $items = $items[0]['items'];

                                        foreach ($items as $item) {
                                            if (!is_array($item)) continue;
                                            $itemUrl = $item['url'] ?? $item['link'] ?? $item['href'] ?? null;
                                            $slug = $item['slug'] ?? ($itemUrl ? basename(rtrim(parse_url($itemUrl, PHP_URL_PATH), '/')) : null);
                                            
                                            if (!$slug || strlen($slug) < 2) continue;
                                            
                                            $mangas[$slug] = [
                                                'id' => $slug,
                                                'title' => trim($item['title'] ?? $item['name'] ?? $slug),
                                                'slug' => $slug,
                                                'cover' => $item['thumbnail'] ?? $item['cover'] ?? $item['image'] ?? $item['img'] ?? $item['thumb'] ?? null,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Method 2: Improved DOM Scraping fallback (Generic Madara)
            if (empty($mangas)) {
                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new DOMXPath($dom);

                // Common selectors for Madara theme
                $nodes = $xpath->query("//div[contains(@class, 'page-item-detail')] | //div[contains(@class, 'manga-item')] | //div[contains(@class, 'mjv2-card')] | //div[contains(@class, 'c-tabs-item__content')] | //div[contains(@class, 'item-summary')] | //div[contains(@class, 'post-item')]");
                
                foreach ($nodes as $node) {
                    $titleNode = $xpath->query(".//h3/a | .//h4/a | .//h5/a | .//div[contains(@class, 'post-title')]//a | .//a[contains(@class, 'mjv2-card-title')]", $node)->item(0);
                    $imgNode = $xpath->query(".//img", $node)->item(0);

                    if ($titleNode) {
                        $href = $titleNode->getAttribute('href');
                        $slug = basename(rtrim(parse_url($href, PHP_URL_PATH), '/'));
                        
                        if (!$slug || in_array($slug, ['komik', 'manga', 'category', 'genre', 'page']) || strlen($slug) < 2) continue;

                        $mangas[$slug] = [
                            'id' => $slug,
                            'title' => trim($titleNode->nodeValue),
                            'slug' => $slug,
                            'cover' => $imgNode ? ($imgNode->getAttribute('data-src') ?: ($imgNode->getAttribute('data-lazy-src') ?: ($imgNode->getAttribute('src') ?: $imgNode->getAttribute('data-cfsrc')))) : null,
                        ];
                    }
                }
            }

            $mangas = array_values($mangas);

            // client-side filtering if search query provided and not already filtered by server
            if ($query && !empty($mangas)) {
                $queryLower = strtolower($query);
                $mangas = array_values(array_filter($mangas, function($m) use ($queryLower) {
                    return str_contains(strtolower($m['title'] ?? ''), $queryLower) || str_contains(strtolower($m['slug'] ?? ''), $queryLower);
                }));
            }

            return $mangas;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Comicazen Search Error: " . $e->getMessage());
            return [];
        }
    }

    public function getMangaDetails($slug)
    {
        $paths = ['/komik', '/manga']; // Swapped to prioritize /komik as per new structure
        $response = null;
        $activePath = $this->mangaPath;

        foreach ($paths as $path) {
            $url = "{$this->baseUrl}{$path}/{$slug}/";
            try {
                \Illuminate\Support\Facades\Log::info("Comicazen: Trying details URL: $url");
                $res = Http::withHeaders($this->getHeaders())->timeout(12)->get($url);
                if ($res->successful()) {
                    $response = $res;
                    $activePath = $path;
                    break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!$response) {
            \Illuminate\Support\Facades\Log::error("Comicazen: Failed to get response for slug: $slug");
            return null;
        }
        $this->mangaPath = $activePath;

        $html = $response->body();
        $cookies = $response->cookies();
        
        // Save cookies for later use in chapter fetching
        if ($cookies) {
            \Illuminate\Support\Facades\Cache::put("comicazen_cookies_{$slug}", $cookies->toArray(), 600);
        }

        try {
            $info = [
                'title' => $slug,
                'description' => '',
                'cover' => null,
                'genre' => '',
                'chapters' => [],
                'slug' => $slug,
                'id' => $slug
            ];

            // 1. Try JSON extraction for Chapters (Primary MJV2)
            $jsonChapterIds = ['mjv2-chapters-data', 'mjv2-manga-data', 'manga-data'];
            foreach ($jsonChapterIds as $jid) {
                if (preg_match('/<script[^>]*id="'.$jid.'"[^>]*type="application\/json"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
                    $chaptersJson = json_decode(trim($matches[1]), true);
                    if (is_array($chaptersJson)) {
                        $items = $chaptersJson['chapters'] ?? $chaptersJson;
                        if (is_array($items)) {
                            foreach ($items as $ch) {
                                $chId = $ch['slug'] ?? $ch['id'] ?? null;
                                if ($chId) {
                                    $info['chapters'][] = [
                                        'id' => $chId,
                                        'title' => $ch['title'] ?? $ch['name'] ?? $chId,
                                    ];
                                }
                            }
                        }
                    }
                }
                if (!empty($info['chapters'])) break;
            }

            // 2. Metadata Extraction (JSON or DOM)
            if (preg_match('/<script[^>]*id="mjv2-manga-data"[^>]*type="application\/json"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
                $mangaJson = json_decode(trim($matches[1]), true);
                if ($mangaJson) {
                    $info['title'] = $mangaJson['title'] ?? $info['title'];
                    $info['description'] = $mangaJson['synopsis'] ?? '';
                    $info['cover'] = $mangaJson['thumbnail'] ?? null;
                    $info['genre'] = is_array($mangaJson['genres'] ?? null) ? implode(', ', $mangaJson['genres']) : ($mangaJson['genres'] ?? '');
                }
            }

            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            if ($info['title'] == $slug) {
                $titleNode = $xpath->query("//h1[contains(@class, 'mjv2-detail-title')] | //div[contains(@class, 'post-title')]/h1")->item(0);
                if ($titleNode) $info['title'] = trim($titleNode->nodeValue);
            }

            if (empty($info['description'])) {
                $descNode = $xpath->query("//div[contains(@class, 'mjv2-synopsis-content')] | //div[contains(@class, 'mjv2-description')] | //div[contains(@class, 'description-summary')] | //div[contains(@class, 'manga-excerpt')]")->item(0);
                if ($descNode) $info['description'] = trim($descNode->nodeValue);
            }

            if (empty($info['cover'])) {
                $imgNode = $xpath->query("//div[contains(@class, 'mjv2-detail-cover')]//img | //div[contains(@class, 'summary_image')]//img | //div[contains(@class, 'mjv2-detail-thumbnail')]//img")->item(0);
                if ($imgNode) {
                    $info['cover'] = $imgNode->getAttribute('src') ?: ($imgNode->getAttribute('data-src') ?: ($imgNode->getAttribute('data-lazy-src') ?: null));
                }
            }

            if (empty($info['genre'])) {
                $genreNodes = $xpath->query("//span[contains(@class, 'mjv2-genre-chip')] | //div[contains(@class, 'genres-content')]//a | //div[contains(@class, 'mjv2-genre')]//a");
                $genres = [];
                foreach ($genreNodes as $gn) $genres[] = trim($gn->nodeValue);
                if (!empty($genres)) $info['genre'] = implode(', ', $genres);
            }

            // 3. Chapter Extraction (DOM Fallback)
            if (empty($info['chapters'])) {
                $selectors = [
                    "//a[contains(@class, 'mjv2-chapter-item')]",
                    "//a[contains(@class, 'mjv2-chapter-link')]",
                    "//li[contains(@class, 'wp-manga-chapter')]/a",
                    "//div[contains(@class, 'listing-chapters_wrap')]//a",
                    "//ul[contains(@class, 'main')]//a[contains(@href, '/chapter-')]",
                    "//div[contains(@class, 'chapter-link')]//a"
                ];

                foreach ($selectors as $selector) {
                    $chapterNodes = $xpath->query($selector);
                    if ($chapterNodes->length > 0) {
                        foreach ($chapterNodes as $cn) {
                            $href = $cn->getAttribute('href');
                            $chapterSlug = basename(rtrim(parse_url($href, PHP_URL_PATH), '/'));
                            
                            $chapterTitleNode = $xpath->query(".//span[contains(@class, 'mjv2-chapter-title')] | .//span[contains(@class, 'mjv2-chapter-name')] | .//p | .//span", $cn)->item(0);
                            $chapterTitle = trim($chapterTitleNode->nodeValue ?? $cn->nodeValue);
                            
                            $exists = false;
                            foreach($info['chapters'] as $existing) { if($existing['id'] === $chapterSlug) { $exists = true; break; } }
                            if (!$exists && $chapterSlug && !in_array($chapterSlug, [$slug, 'manga', 'komik'])) {
                                \Illuminate\Support\Facades\Log::info("Comicazen: Found chapter link: $href");
                                $info['chapters'][] = ['id' => $chapterSlug, 'title' => $chapterTitle ?: $chapterSlug];
                            }
                        }
                        if (count($info['chapters']) > 0) break;
                    }
                }
            }

            return $info;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Comicazen Detail Error: " . $e->getMessage());
            return null;
        }
    }

    public function getChapterImages($slug, $chapterId)
    {
        $paths = ['/komik', '/manga'];
        $html = null;

        // Try to get cookies from cache (from Step 1: Manga Page)
        $cookies = \Illuminate\Support\Facades\Cache::get("comicazen_cookies_{$slug}") ?: [];

        foreach ($paths as $path) {
            $mangaUrl = "{$this->baseUrl}{$path}/{$slug}/";
            $urls = [
                "{$this->baseUrl}{$path}/{$slug}/{$chapterId}/?style=list",
                "{$this->baseUrl}{$path}/{$slug}/{$chapterId}/",
                "{$this->baseUrl}/v2{$path}/{$slug}/{$chapterId}/"
            ];
            
            foreach ($urls as $url) {
                \Illuminate\Support\Facades\Log::info("ComicazenService: Fetching: $url (Referer: " . ($url == $urls[0] ? "None" : $mangaUrl) . ")");
                
                try {
                    // Refresh cookies if none found
                    if (empty($cookies)) {
                        $initRes = Http::withHeaders($this->getHeaders())->timeout(10)->get($mangaUrl);
                        if ($initRes->successful()) {
                            $cookies = $initRes->cookies()->toArray();
                            \Illuminate\Support\Facades\Cache::put("comicazen_cookies_{$slug}", $cookies, 600);
                        }
                    }

                    $referer = (str_contains($url, '?') || str_contains($url, 'v2')) ? $this->baseUrl . '/' : $mangaUrl;
                    
                    $response = Http::withHeaders($this->getHeaders($referer))
                        ->withCookies($cookies, parse_url($this->baseUrl, PHP_URL_HOST))
                        ->withOptions(['verify' => false, 'follow_redirects' => true])
                        ->timeout(15)
                        ->get($url);

                    if ($response->successful()) {
                        $html = $response->body();
                        if (strlen($html) > 5000 && !str_contains($html, 'Just a moment...')) {
                            $this->mangaPath = $path;
                            break 2;
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::warning("ComicazenService: Failed for $url. Status: " . $response->status());
                        if ($response->status() === 403) {
                            \Illuminate\Support\Facades\Log::info("ComicazenService: 403 Body Snippet: " . substr($response->body(), 0, 500));
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("ComicazenService: Exception for $url: " . $e->getMessage());
                }
                
                // Small delay to avoid rate limiting
                usleep(500000); // 0.5s
            }
        }

        if (!$html) {
            \Illuminate\Support\Facades\Log::error("ComicazenService: All paths failed for chapter fetch ($slug / $chapterId)");
            return [];
        }

        // Try to extract images from JSON script tag (MJV2 Reader)
        // Check multiple possible script ID patterns
        $scriptIds = ['mjv2-reader-data', 'reader-data', 'mjv2-manga-data', 'manga-data'];
        foreach ($scriptIds as $sid) {
            if (preg_match('/<script[^>]*id="'.$sid.'"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
                $content = trim($matches[1]);
                // If it's pure JSON
                $readerJson = json_decode($content, true);
                
                // If it's a JS variable assignment (Fallback)
                if (!$readerJson && preg_match('/(?:var|let|const)\s+[\w\d_]+\s*=\s*({.*?});/s', $content, $jsMatches)) {
                    $readerJson = json_decode($jsMatches[1], true);
                }

                if (isset($readerJson['images']) && is_array($readerJson['images'])) {
                    return array_map('trim', $readerJson['images']);
                }
                if (isset($readerJson['sources'][0]['images']) && is_array($readerJson['sources'][0]['images'])) {
                    return array_map('trim', $readerJson['sources'][0]['images']);
                }
            }
        }

        // DOM Fallback
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $images = [];
        $imgNodes = $xpath->query("//div[contains(@class, 'mjv2-reader-content')]//img | //div[contains(@class, 'reading-content')]//img | //div[contains(@class, 'page-break')]//img | //div[@id='chapter_view']//img | //div[@id='readerarea']//img");
        
        foreach ($imgNodes as $in) {
            $src = $in->getAttribute('data-src') ?: ($in->getAttribute('data-lazy-src') ?: ($in->getAttribute('src') ?: $in->getAttribute('data-cfsrc')));
            if ($src && !str_contains($src, 'data:image') && !str_contains($src, 'loading.gif')) {
                $images[] = trim($src);
            }
        }

        return $images;
    }
}
