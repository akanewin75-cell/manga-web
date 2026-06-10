<?php

namespace App\Services;

class MangaDiscoveryService
{
    protected $comicazenService;
    protected $comicasoService;

    public function __construct(ComicazenService $comicazenService, ComicasoService $comicasoService)
    {
        $this->comicazenService = $comicazenService;
        $this->comicasoService = $comicasoService;
    }

    public function search($query = null, $page = 1)
    {
        \Illuminate\Support\Facades\Log::info("MangaDiscoveryService: Searching - Query: '$query', Page: $page");
        $results = [];

        // 1. Search Comicaso
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
        }

        return null;
    }

    public function getChapterImages($sourceType, $mangaSourceId, $chapterSourceId)
    {
        \Illuminate\Support\Facades\Log::info("DiscoveryService: Fetching images - Type: $sourceType, Manga: $mangaSourceId, Chapter: $chapterSourceId");
        if ($sourceType === 'comicaso') {
            return $this->comicasoService->getChapterImages($mangaSourceId, $chapterSourceId);
        } elseif ($sourceType === 'comicazen') {
            return $this->comicazenService->getChapterImages($mangaSourceId, $chapterSourceId);
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
