<?php

namespace App\Services;

class MangaDiscoveryService
{
    protected $webtoonService;
    protected $comicazenService;
    protected $lunarService;

    public function __construct(WebtoonService $webtoonService, ComicazenService $comicazenService, LunarAnimeService $lunarService)
    {
        $this->webtoonService = $webtoonService;
        $this->comicazenService = $comicazenService;
        $this->lunarService = $lunarService;
    }

    public function search($query = null, $page = 1)
    {
        $results = [];

        // 0. Search LunarAnime (Priority)
        try {
            $lunarMangas = $this->lunarService->searchManga($query, $page);
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

        // 1. Search Comicazen
        try {
            $comicazenMangas = $this->comicazenService->searchManga($query, $page);
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

        // 2. Search MangaDex (Webtoon) - Wrapped in Try-Catch to prevent timeout from killing the page
        try {
            $webtoonData = $this->webtoonService->searchWebtoon($query, 12, ($page - 1) * 12);
            if (isset($webtoonData['data'])) {
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
            \Illuminate\Support\Facades\Log::warning("Webtoon (MangaDex) Search Timed Out or Failed: " . $e->getMessage());
        }

        return $results;
    }

    public function getDetails($sourceType, $sourceId)
    {
        if ($sourceType === 'lunar') {
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
        if ($sourceType === 'lunar') {
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
