<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$query = "Solo Leveling";
$url = "https://api.lunaranime.ru/api/manga/search?query=" . urlencode($query);

echo "Testing Search URL: $url\n";
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Referer' => 'https://lunaranime.ru/',
])->get($url);

if ($response->successful()) {
    $data = $response->json();
    file_put_contents('search_result.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "Search result saved to search_result.json\n";
    
    if (!empty($data['manga'])) {
        $manga = $data['manga'][0];
        print_r($manga);
    }
}
