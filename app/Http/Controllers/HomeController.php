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

    public function index()
    {
        try {
            // 1. Get Trending from Database (recently added or most likes)
            $trending = Manga::orderBy('likes_count', 'desc')->take(5)->get();
        } catch (\Illuminate\Database\QueryException $e) {
            // If table doesn't exist, show a friendly init page instead of crashing
            if (str_contains($e->getMessage(), 'no such table')) {
                return response("
                    <div style='background:#050505; color:white; height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; font-family:sans-serif;'>
                        <h1 style='color:#7b7bff'>RUANA SYSTEM OFFLINE</h1>
                        <p style='color:#888'>The core database tables are missing. Please initialize the manwha realm.</p>
                        <a href='/init-db' style='background:#7b7bff; color:white; padding:15px 30px; border-radius:10px; text-decoration:none; font-weight:bold; margin-top:20px;'>
                            INITIALIZE DATABASE
                        </a>
                    </div>
                ", 200);
            }
            throw $e;
        }

        // 2. Get Discoveries (latest from Comicazen/MangaDex)
        $discoveries = $this->discoveryService->search(null, 1);

        return view('home', compact('trending', 'discoveries'));
    }

    public function search(Request $request)
    {
        $query = $request->query('q');
        $results = $this->discoveryService->search($query);

        return view('explore', compact('results', 'query'));
    }
}
