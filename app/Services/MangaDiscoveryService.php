<?php

namespace App\Services;

class MangaDiscoveryService
{
    protected $webtoonService;
    protected $comicazenService;
    protected $lunarService;
    protected $comicasoService;

    public function __construct(WebtoonService $webtoonService, ComicazenService $comicazenService, LunarAnimeService $lunarService, ComicasoService $comicasoService)
    {
        $this->webtoonService = $webtoonService;
        $this->comicazenService = $comicazenService;
        $this->lunarService = $lunarService;
        $this->comicasoService = $comicasoService;
    }

    public function search($query = null, $page = 1)
    {
        \Illuminate\Support\Facades\Log::info("MangaDiscoveryService: Searching - Query: '$query', Page: $page");
        $results = [];

        // 0. Search Comicaso (New Source)
        try {
            $comicasoMangas = $this->comicasoService->searchManga($query, $page);
            \Illuminate\Support\Facades\Log::info("Comicaso: Found " . count($comicasoMangas) . " items for page $page");
            foreach ($comicasoMangas as $manga) {
                $results[] = [
                    'id' => $manga['id'],
                    'source_id' => $manga['id'],
                    'source_type' => 'comicaso',
                    'title' => $manga['title'],
                    'slug' => $manga['slug'],
                    'cover' => $manga['cover'],
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Comicaso Search Failed: " . $e->getMessage());
        }

        // 1. Search LunarAnime
        try {
            $lunarMangas = $this->lunarService->searchManga($query, $page);
            \Illuminate\Support\Facades\Log::info("LunarAnime: Found " . count($lunarMangas) . " items for page $page");
            foreach ($lunarMangas as $manga) {
                $results[] = [
                    'id' => $manga['id'],
                    'source_id' => $manga['id'],
                    'source_type' => 'lunar',
                    'title' => $manga['title'],
                    'slug' => $manga['slug'],
                    'cover' => $manga['cover'],
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("LunarAnime Search Failed: " . $e->getMessage());
        }

        // 2. Search Comicazen
        try {
            $comicazenMangas = $this->comicazenService->searchManga($query, $page);
            \Illuminate\Support\Facades\Log::info("Comicazen: Found " . count($comicazenMangas) . " items for page $page");
            foreach ($comicazenMangas as $manga) {
                $results[] = [
                    'id' => $manga['id'],
                    'source_id' => $manga['id'],
                    'source_type' => 'comicazen',
                    'title' => $manga['title'],
                    'slug' => $manga['slug'],
                    'cover' => $manga['cover'],
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Comicazen Search Failed: " . $e->getMessage());
        }

        // 2. Search MangaDex (Webtoon)
        try {
            $webtoonData = $this->webtoonService->searchWebtoon($query, 12, ($page - 1) * 12);
            if (isset($webtoonData['data'])) {
                \Illuminate\Support\Facades\Log::info("MangaDex: Found " . count($webtoonData['data']) . " items for page $page");
                foreach ($webtoonData['data'] as $manga) {
                    $results[] = [
                        'id' => $manga['id'],
                        'source_id' => $manga['id'],
                        'source_type' => 'dex',
                        'title' => $manga['attributes']['title']['en'] ?? array_values($manga['attributes']['title'])[0] ?? 'Unknown',
                        'slug' => $manga['id'],
                        'cover' => $this->webtoonService->getCoverUrl($manga),
                    ];
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Webtoon (MangaDex) Search Failed: " . $e->getMessage());
        }

        return $results;
    }

    public function getDetails($sourceType, $sourceId)
    {
        if ($sourceType === 'comicaso') {
            $details = $this->comicasoService->getMangaDetails($sourceId);
            if ($details) {
                return [
                    'source_id' => $sourceId,
                    'source_type' => 'comicaso',
                    'title' => $details['title'],
                    'description' => $details['description'],
                    'cover' => $details['cover'],
                    'genre' => $details['genre'],
                    'chapters' => array_map(fn($ch) => [
                        'id' => $ch['id'],
                        'title' => $ch['title'],
                        'chapter_num' => $ch['chapter_num']
                    ], $details['chapters'])
                ];
            }
        } elseif ($sourceType === 'lunar') {
            $details = $this->lunarService->getMangaDetails($sourceId);
            if ($details) {
                return [
                    'source_id' => $sourceId,
                    'source_type' => 'lunar',
                    'title' => $details['title'],
                    'description' => $details['description'],
                    'cover' => $details['cover'],
                    'genre' => $details['genre'],
                    'chapters' => array_map(fn($ch) => [
                        'id' => $ch['id'],
                        'title' => $ch['title'],
                        'chapter_num' => $ch['chapter_num']
                    ], $details['chapters'])
                ];
            }
        } elseif ($sourceType === 'comicazen') {
            $details = $this->comicazenService->getMangaDetails($sourceId);
            if ($details) {
                return [
                    'source_id' => $sourceId,
                    'source_type' => 'comicazen',
                    'title' => $details['title'],
                    'description' => $details['description'],
                    'cover' => $details['cover'],
                    'genre' => $details['genre'],
                    'chapters' => array_map(fn($ch) => [
                        'id' => $ch['id'],
                        'title' => $ch['title'],
                        'chapter_num' => $this->extractChapterNum($ch['id'])
                    ], $details['chapters'])
                ];
            }
        } elseif ($sourceType === 'dex') {
            $details = $this->webtoonService->getWebtoonDetails($sourceId);
            if (isset($details['data'])) {
                $manga = $details['data'];
                $feed = $this->webtoonService->getWebtoonFeed($sourceId);
                return [
                    'source_id' => $sourceId,
                    'source_type' => 'dex',
                    'title' => $manga['attributes']['title']['en'] ?? array_values($manga['attributes']['title'])[0] ?? 'Unknown',
                    'description' => $manga['attributes']['description']['en'] ?? array_values($manga['attributes']['description'])[0] ?? '',
                    'cover' => $this->webtoonService->getCoverUrl($manga),
                    'genre' => implode(', ', array_map(fn($tag) => $tag['attributes']['name']['en'], array_filter($manga['attributes']['tags'], fn($tag) => $tag['attributes']['group'] === 'genre'))),
                    'chapters' => isset($feed['data']) ? array_map(fn($ch) => [
                        'id' => $ch['id'],
                        'title' => $ch['attributes']['title'] ?? ('Chapter ' . ($ch['attributes']['chapter'] ?? '?')),
                        'chapter_num' => $ch['attributes']['chapter'] ?? '0'
                    ], $feed['data']) : []
                ];
            }
        }

        return null;
    }

    public function getChapterImages($sourceType, $mangaSourceId, $chapterSourceId)
    {
        \Illuminate\Support\Facades\Log::info("DiscoveryService: Fetching images - Type: $sourceType, Manga: $mangaSourceId, Chapter: $chapterSourceId");
        if ($sourceType === 'comicaso') {
            return $this->comicasoService->getChapterImages($mangaSourceId, $chapterSourceId);
        } elseif ($sourceType === 'lunar') {
            return $this->lunarService->getChapterImages($mangaSourceId, $chapterSourceId);
        } elseif ($sourceType === 'comicazen') {
            return $this->comicazenService->getChapterImages($mangaSourceId, $chapterSourceId);
        } elseif ($sourceType === 'dex') {
            return $this->webtoonService->getChapterImages($chapterSourceId);
        }
        return [];
    }

    protected function extractChapterNum($slug)
    {
        if (preg_match('/chapter-(\d+(\.\d+)?)/i', $slug, $matches)) {
            return $matches[1];
        }
        return $slug;
    }
}
