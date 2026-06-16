<?php

namespace App\Http\Controllers;

use App\Services\MangaDiscoveryService;
use App\Models\Manga;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $discoveryService;

    public function __construct(MangaDiscoveryService $discoveryService)
    {
        $this->discoveryService = $discoveryService;
    }

    public function toggleNsfw(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("NSFW Toggle Request: ", $request->all());
        
        // Handle both boolean true and string "true"
        $enabled = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);
        
        if (auth()->check()) {
            try {
                auth()->user()->update(['nsfw_enabled' => $enabled]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Could not save NSFW status to DB: " . $e->getMessage());
            }
        }
        
        session(['nsfw_enabled' => $enabled]);
        
        \Illuminate\Support\Facades\Log::info("NSFW Enabled Status Set to: " . ($enabled ? 'true' : 'false'));
        return response()->json(['status' => 'success', 'nsfw_enabled' => $enabled]);
    }

    public function index(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $nsfwEnabled = auth()->check() ? auth()->user()->nsfw_enabled : session('nsfw_enabled', false);

        // 1. Get Trending from Database (recently added or most likes)
        $trendingQuery = Manga::orderBy('likes_count', 'desc');
        
        if (!$nsfwEnabled) {
            $trendingQuery->where(function($q) {
                $q->where('genre', 'NOT LIKE', '%18+%')
                    ->where('genre', 'NOT LIKE', '%Mature%')
                    ->where('genre', 'NOT LIKE', '%Adult%')
                    ->where('genre', 'NOT LIKE', '%Smut%')
                    ->where('genre', 'NOT LIKE', '%Ecchi%')
                    ->orWhereNull('genre');
            });
        }
        
        $trending = $trendingQuery->take(5)->get();
        
        // 2. Get Bookmarks if user is logged in
        $bookmarksQuery = auth()->check() 
            ? auth()->user()->bookmarkedMangas()->latest()
            : null;

        if ($bookmarksQuery && !$nsfwEnabled) {
            $bookmarksQuery->where(function($q) {
                $q->where('genre', 'NOT LIKE', '%18+%')
                    ->where('genre', 'NOT LIKE', '%Mature%')
                    ->where('genre', 'NOT LIKE', '%Adult%')
                    ->where('genre', 'NOT LIKE', '%Smut%')
                    ->where('genre', 'NOT LIKE', '%Ecchi%')
                    ->orWhereNull('genre');
            });
        }

        $bookmarks = $bookmarksQuery ? $bookmarksQuery->take(6)->get() : collect();

        // 3. Get Discoveries (latest from Comicazen/MangaDex)
        $results = $this->discoveryService->search(null, $page);

        // If it's the home page root '/', we use compact names as before
        if ($request->path() === '/') {
            $discoveries = array_slice($results, 0, 12);
            return view('home', compact('trending', 'discoveries', 'bookmarks'));
        }

        return view('explore', [
            'results' => $results,
            'trending' => $trending,
            'bookmarks' => $bookmarks,
            'page' => $page,
            'query' => null
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');
        $page = (int) $request->query('page', 1);
        $nsfwEnabled = auth()->check() ? auth()->user()->nsfw_enabled : session('nsfw_enabled', false);

        $trendingQuery = Manga::orderBy('likes_count', 'desc');
        if (!$nsfwEnabled) {
            $trendingQuery->where(function($q) {
                $q->where('genre', 'NOT LIKE', '%18+%')
                  ->where('genre', 'NOT LIKE', '%Mature%')
                  ->where('genre', 'NOT LIKE', '%Adult%')
                  ->where('genre', 'NOT LIKE', '%Smut%')
                  ->where('genre', 'NOT LIKE', '%Ecchi%')
                  ->orWhereNull('genre');
            });
        }
        $trending = $trendingQuery->take(5)->get();

        $results = $this->discoveryService->search($query, $page);

        return view('explore', compact('results', 'query', 'page', 'trending'));
    }
}
