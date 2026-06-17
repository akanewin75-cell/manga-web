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
                    ? "{$this->baseUrl}/page/{$page}/?s=" . urlencode($query) . "&post_type=wp-manga"
                    : "{$this->baseUrl}/?s=" . urlencode($query) . "&post_type=wp-manga";
            } else {
                $url = $page > 1 
                    ? "{$this->baseUrl}" . rtrim($path, '/') . "/page/{$page}/?m_paged={$page}" 
                    : "{$this->baseUrl}" . $path;
            }
            
            try {
                $response = Http::withHeaders($this->getHeaders())->timeout(20)->get($url);
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
        $paths = ['/komik', '/manga'];
        $response = null;
        $activePath = $this->mangaPath;

        foreach ($paths as $path) {
            $url = "{$this->baseUrl}{$path}/{$slug}/";
            try {
                \Illuminate\Support\Facades\Log::info("Comicazen: Trying details URL: $url");
                $res = Http::withHeaders($this->getHeaders())->timeout(30)->get($url);
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
        if ($cookies) \Illuminate\Support\Facades\Cache::put("comicazen_cookies_{$slug}", $cookies->toArray(), 600);

        try {
            $info = ['title' => $slug, 'description' => '', 'cover' => null, 'genre' => '', 'chapters' => [], 'slug' => $slug, 'id' => $slug];
            $jsonChapterIds = ['mjv2-chapters-data', 'mjv2-manga-data', 'manga-data', 'wp-manga-data'];
            foreach ($jsonChapterIds as $jid) {
                if (preg_match('/<script[^>]*id="'.$jid.'"[^>]*type="application\/json"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
                    $chaptersJson = json_decode(trim($matches[1]), true);
                    if (is_array($chaptersJson)) {
                        $items = $chaptersJson['chapters'] ?? $chaptersJson;
                        if (is_array($items)) {
                            foreach ($items as $ch) {
                                $chId = $ch['slug'] ?? $ch['id'] ?? null;
                                if ($chId) $info['chapters'][] = ['id' => $chId, 'title' => $ch['title'] ?? $ch['name'] ?? $chId];
                            }
                        }
                    }
                }
                if (!empty($info['chapters'])) break;
            }

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
                    $cover = $imgNode->getAttribute('src') ?: ($imgNode->getAttribute('data-src') ?: ($imgNode->getAttribute('data-lazy-src') ?: null));
                    if ($cover && !str_starts_with($cover, 'http')) {
                        $cover = $this->baseUrl . (str_starts_with($cover, '/') ? '' : '/') . $cover;
                    }
                    $info['cover'] = $cover;
                }
            }

            if (empty($info['genre'])) {
                $genreNodes = $xpath->query("//span[contains(@class, 'mjv2-genre-chip')] | //div[contains(@class, 'genres-content')]//a | //div[contains(@class, 'mjv2-genre')]//a");
                $genres = [];
                foreach ($genreNodes as $gn) $genres[] = trim($gn->nodeValue);
                if (!empty($genres)) $info['genre'] = implode(', ', $genres);
            }

            if (empty($info['chapters'])) {
                $selectors = ["//a[contains(@class, 'mjv2-chapter-item')]", "//a[contains(@class, 'mjv2-chapter-link')]", "//li[contains(@class, 'wp-manga-chapter')]/a", "//div[contains(@class, 'listing-chapters_wrap')]//a", "//ul[contains(@class, 'main')]//a[contains(@href, '/chapter-')]", "//div[contains(@class, 'chapter-link')]//a"];
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
                            if (!$exists && $chapterSlug && !in_array($chapterSlug, [$slug, 'manga', 'komik'])) $info['chapters'][] = ['id' => $chapterSlug, 'title' => $chapterTitle ?: $chapterSlug];
                        }
                        if (count($info['chapters']) > 0) break;
                    }
                }
            }

            if (empty($info['chapters'])) {
                $mangaId = null;
                if (preg_match('/post-(\d+)/', $html, $m)) $mangaId = $m[1];
                elseif (preg_match('/"manga_id":"(\d+)"/', $html, $m)) $mangaId = $m[1];
                if ($mangaId) {
                    $ajaxRes = Http::asForm()->withHeaders($this->getHeaders("{$this->baseUrl}{$activePath}/{$slug}/"))->post("{$this->baseUrl}/wp-admin/admin-ajax.php", ['action' => 'manga_get_chapters', 'manga' => $mangaId]);
                    if ($ajaxRes->successful()) {
                        $ajaxHtml = $ajaxRes->body();
                        libxml_use_internal_errors(true);
                        $ajaxDom = new DOMDocument(); @$ajaxDom->loadHTML($ajaxHtml);
                        $ajaxXpath = new DOMXPath($ajaxDom);
                        $ajaxNodes = $ajaxXpath->query("//li[contains(@class, 'wp-manga-chapter')]/a | //a[contains(@class, 'mjv2-chapter-item')]");
                        foreach ($ajaxNodes as $an) {
                            $href = $an->getAttribute('href');
                            $chapterSlug = basename(rtrim(parse_url($href, PHP_URL_PATH), '/'));
                            $info['chapters'][] = ['id' => $chapterSlug, 'title' => trim($an->nodeValue)];
                        }
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
        $baseUrls = [$this->baseUrl, 'https://v3.comicaso.pro', 'https://comicaso.pro', 'https://comicaso.com'];
        $paths = ['/komik', '/manga', '/ch'];
        $html = null;

        foreach ($baseUrls as $base) {
            foreach ($paths as $path) {
                $mangaUrl = "{$base}{$path}/{$slug}/";
                $urls = ["{$base}{$path}/{$slug}/{$chapterId}/?style=list", "{$base}{$path}/{$slug}/{$chapterId}/", "{$base}/v2{$path}/{$slug}/{$chapterId}/"];
                foreach ($urls as $url) {
                    try {
                        $cookies = \Illuminate\Support\Facades\Cache::get("comicazen_cookies_{$slug}") ?: [];
                        $referer = (str_contains($url, '?') || str_contains($url, 'v2')) ? $base . '/' : $mangaUrl;
                        $response = Http::withHeaders($this->getHeaders($referer))->withCookies($cookies, parse_url($base, PHP_URL_HOST))->withOptions(['verify' => false, 'follow_redirects' => true])->timeout(30)->get($url);
                        if ($response->successful()) {
                            $html = $response->body();
                            if (strlen($html) > 5000 && !str_contains($html, 'Just a moment...')) break 3;
                        }
                    } catch (\Exception $e) {}
                    usleep(100000);
                }
            }
        }

        if (!$html) return [];
        $scriptIds = ['mjv2-reader-data', 'reader-data', 'mjv2-manga-data', 'manga-data', 'obj_reader', 'wp-manga-reader-data', 'img_data'];
        foreach ($scriptIds as $sid) {
            if (preg_match('/<script[^>]*id="'.$sid.'"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
                $content = trim($matches[1]);
                $readerJson = json_decode($content, true);
                if (!$readerJson && preg_match('/(?:var|let|const|obj_reader|img_data)\s*=\s*({.*?});/s', $content, $jsMatches)) $readerJson = json_decode($jsMatches[1], true);
                if (isset($readerJson['images']) && is_array($readerJson['images'])) return array_map('trim', $readerJson['images']);
                if (isset($readerJson['sources'][0]['images']) && is_array($readerJson['sources'][0]['images'])) return array_map('trim', $readerJson['sources'][0]['images']);
            }
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument(); @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $images = [];
        $imgNodes = $xpath->query("//div[contains(@class, 'mjv2-reader-content')]//img | //div[contains(@class, 'reading-content')]//img | //div[contains(@class, 'page-break')]//img | //div[@id='chapter_view']//img | //div[@id='readerarea']//img | //div[contains(@class, 'read-content')]//img | //div[contains(@class, 'mjv2-read-content')]//img | //div[contains(@class, 'item-reading')]//img");
        foreach ($imgNodes as $in) {
            $src = $in->getAttribute('data-src') ?: ($in->getAttribute('data-lazy-src') ?: ($in->getAttribute('src') ?: ($in->getAttribute('data-cfsrc') ?: ($in->getAttribute('data-original') ?: ($in->getAttribute('data-full-url') ?: $in->getAttribute('data-src-img'))))));
            if ($src && !str_contains($src, 'data:image') && !str_contains($src, 'loading.gif') && !str_contains($src, 'ads')) {
                $src = trim($src);
                if (str_starts_with($src, '//')) {
                    $src = 'https:' . $src;
                } elseif (!str_starts_with($src, 'http')) {
                    $src = $this->baseUrl . (str_starts_with($src, '/') ? '' : '/') . $src;
                }
                $images[] = $src;
            }
        }
        return $images;
    }
}
