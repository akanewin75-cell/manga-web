<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$mangaId = '5d0c7546-e522-4217-9c97-060d4b998246';
$url = "https://api.mangadex.org/manga/{$mangaId}/feed?limit=100&offset=0&order[chapter]=desc&translatedLanguage[]=id&translatedLanguage[]=en";

echo "Fetching: $url\n";
$response = Http::withHeaders([
    'User-Agent' => 'RuanaManwha/1.0 (contact@ruanamanwha.com)'
])->get($url);

echo "Status: " . $response->status() . "\n";
$data = $response->json();

if (isset($data['data']) && !empty($data['data'])) {
    echo "Found " . count($data['data']) . " chapters.\n";
    foreach (array_slice($data['data'], 0, 5) as $ch) {
        echo " - Chapter " . ($ch['attributes']['chapter'] ?? '?') . " (Lang: " . $ch['attributes']['translatedLanguage'] . ") ID: " . $ch['id'] . "\n";
    }
} else {
    echo "No chapters found.\n";
}
