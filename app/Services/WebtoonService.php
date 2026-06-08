<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebtoonService
{
    protected $baseUrl = 'https://api.mangadex.org';
    // Tag ID for "Webtoon" on MangaDex
    protected $webtoonTagId = '3e2b8dae-ca3f-488c-997a-06011efb1a6d';

    public function searchWebtoon($title = null, $limit = 20, $offset = 0)
    {
        $url = "{$this->baseUrl}/manga?limit={$limit}&offset={$offset}&contentRating[]=safe&contentRating[]=suggestive&includes[]=cover_art&includedTags[]={$this->webtoonTagId}";

        if ($title) {
            $url .= "&title=" . urlencode($title);
        }

        $response = Http::get($url);

        return $response->json();
    }

    public function getWebtoonDetails($id)
    {
        $url = "{$this->baseUrl}/manga/{$id}?includes[]=cover_art&includes[]=author&includes[]=artist";
        $response = Http::get($url);

        return $response->json();
    }

    public function getWebtoonFeed($id, $limit = 100, $offset = 0, $translatedLanguage = ['en', 'id'])
    {
        $url = "{$this->baseUrl}/manga/{$id}/feed?limit={$limit}&offset={$offset}&order[chapter]=desc&includes[]=scanlation_group";
        
        foreach ($translatedLanguage as $lang) {
            $url .= "&translatedLanguage[]={$lang}";
        }

        $response = Http::get($url);

        return $response->json();
    }

    public function getChapterImages($chapterId)
    {
        $response = Http::get("{$this->baseUrl}/at-home/server/{$chapterId}");
        $data = $response->json();

        if (!isset($data['chapter'])) {
            return null;
        }

        $baseUrl = $data['baseUrl'];
        $hash = $data['chapter']['hash'];
        $files = $data['chapter']['data'];

        $images = [];
        foreach ($files as $file) {
            $images[] = "{$baseUrl}/data/{$hash}/{$file}";
        }

        return $images;
    }

    public function getCoverUrl($mangaData)
    {
        $mangaId = $mangaData['id'];
        $fileName = '';

        foreach ($mangaData['relationships'] as $rel) {
            if ($rel['type'] === 'cover_art') {
                $fileName = $rel['attributes']['fileName'] ?? '';
                break;
            }
        }

        if ($fileName) {
            return "https://uploads.mangadex.org/covers/{$mangaId}/{$fileName}";
        }

        return null;
    }
}
