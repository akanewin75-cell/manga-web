<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LunarAnimeService
{
    protected $apiBase = 'https://api.lunaranime.ru/api';
    protected $baseUrl = 'https://lunaranime.ru';

    public function getHeaders($referer = null)
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        ];

        if ($referer) {
            $headers['Referer'] = $referer;
        }

        return $headers;
    }

    public function searchManga($query = null, $page = 1)
    {
        $url = "{$this->apiBase}/manga/search";
        
        try {
            $response = Http::withHeaders($this->getHeaders())->get($url, [
                'query' => $query ?? '',
                'page' => $page
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $mangas = [];
                
                foreach ($data['manga'] ?? [] as $manga) {
                    $mangas[] = [
                        'id' => $manga['slug'], // Use slug as ID for easier routing
                        'source_id' => $manga['manga_id'],
                        'source_type' => 'lunar',
                        'title' => $manga['title'],
                        'slug' => $manga['slug'],
                        'cover' => $manga['cover_url'],
                        'description' => $manga['description'] ?? '',
                        'genres' => $manga['genres'] ? json_decode($manga['genres'], true) : [],
                    ];
                }
                return $mangas;
            }
        } catch (\Exception $e) {
            Log::error("LunarAnime Search Error: " . $e->getMessage());
        }

        return [];
    }

    public function getMangaDetails($slug)
    {
        $url = "{$this->baseUrl}/manga/{$slug}";
        
        try {
            $response = Http::withHeaders($this->getHeaders())->get($url);
            if (!$response->successful()) {
                Log::error("LunarAnime Detail Failed for $slug. Status: " . $response->status());
                return null;
            }

            $html = $response->body();
            
            // Basic info from meta tags
            $info = [
                'source_id' => $slug,
                'source_type' => 'lunar',
                'title' => $slug,
                'description' => '',
                'cover' => '',
                'genre' => '',
                'chapters' => []
            ];

            if (preg_match('/<title>(.*?)<\/title>/', $html, $matches)) {
                $info['title'] = str_replace(' | Lunar | Mangas', '', $matches[1]);
            }

            if (preg_match('/<meta property="og:image" content="(.*?)"/', $html, $matches)) {
                $info['cover'] = $matches[1];
            }

            if (preg_match('/<meta name="description" content="(.*?)"/', $html, $matches)) {
                $info['description'] = $matches[1];
            }

            // Extract chapters from __next_f data
            // Look for patterns like ["manga_id","slug","chapter_number","chapter_title"]
            // LunarAnime seems to push data in chunks
            if (preg_match_all('/self\.__next_f\.push\(\[1,"(.*?)"\]\)/', $html, $matches)) {
                foreach ($matches[1] as $match) {
                    $decoded = str_replace(['\\"', '\\\\'], ['"', '\\'], $match);
                    
                    // Try to find chapter structures
                    // Pattern: {\"chapter_number\":1,\"chapter_subnumber\":null,\"chapter_title\":\"\",\"slug\":\"1\",...}
                    if (preg_match_all('/\\\\?"chapter_number\\\\?":(\d+),.*?\\\\?"slug\\\\?":\\\\?"([^\\\\"]+)\\\\?"/s', $decoded, $chMatches, PREG_SET_ORDER)) {
                        foreach ($chMatches as $ch) {
                            $info['chapters'][] = [
                                'id' => $ch[2],
                                'title' => 'Chapter ' . $ch[1],
                                'chapter_num' => $ch[1]
                            ];
                        }
                    }
                }
            }

            // Fallback for chapters if not found in JS data (some sites hide it)
            if (empty($info['chapters'])) {
                // We can try to use the search API to get more info or assume chapters 1-latest if we can find the latest
            }

            // Sort chapters descending
            usort($info['chapters'], function($a, $b) {
                return $b['chapter_num'] <=> $a['chapter_num'];
            });

            return $info;

        } catch (\Exception $e) {
            Log::error("LunarAnime Detail Error: " . $e->getMessage());
            return null;
        }
    }

    public function getChapterImages($mangaSlug, $chapterId)
    {
        $urls = [
            "{$this->apiBase}/manga/slug/{$mangaSlug}/{$chapterId}",
            "{$this->apiBase}/manga/slug/{$mangaSlug}", // Fallback for chapter 1 or if chapter is default
        ];
        
        foreach ($urls as $url) {
            try {
                Log::info("LunarAnime: Fetching chapter images from $url");
                $response = Http::withHeaders($this->getHeaders())->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']['images'])) {
                        return $data['data']['images'];
                    }
                }
            } catch (\Exception $e) {
                Log::error("LunarAnime Chapter Images Error for $url: " . $e->getMessage());
            }
        }

        return [];
    }
}
