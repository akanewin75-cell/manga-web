<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$mangaId = '0609ad33-389d-4f63-8bd3-6164497a1909';
$url = "https://api.mangadex.org/manga/{$mangaId}/feed?limit=100&offset=0&order[chapter]=desc&translatedLanguage[]=id&translatedLanguage[]=en";

echo "Fetching from MangaDex: $url\n";
try {
    $response = Http::timeout(30)->get($url);
    echo "Status: " . $response->status() . "\n";
    $data = $response->json();

    if (isset($data['data']) && !empty($data['data'])) {
        echo "Found " . count($data['data']) . " chapters.\n";
        foreach (array_slice($data['data'], 0, 5) as $ch) {
            echo " - Chapter " . ($ch['attributes']['chapter'] ?? '?') . " (Lang: " . $ch['attributes']['translatedLanguage'] . ") ID: " . $ch['id'] . "\n";
        }
    } else {
        echo "No chapters found or error: " . json_encode($data) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
