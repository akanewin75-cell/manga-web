<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$slug = "solo-leveling-official";
$id = "6c6bae4f-87e2-4b7b-9d76-e02ace02bdf5";

$urls = [
    "https://lunaranime.ru/api/manga/$slug",
    "https://lunaranime.ru/api/manga/$id",
    "https://api.lunaranime.ru/api/manga/$id",
    "https://api.lunaranime.ru/api/manga/slug/$slug",
    "https://api.lunaranime.ru/api/manga/$id/chapters",
    "https://api.lunaranime.ru/api/manga/slug/$slug/chapters",
];

foreach ($urls as $url) {
    echo "Testing URL: $url\n";
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Referer' => 'https://lunaranime.ru/',
    ])->get($url);
    
    echo " - Status: " . $response->status() . "\n";
    if ($response->successful()) {
        $json = $response->json();
        if (isset($json['chapters']) || isset($json['data']['chapters'])) {
            echo " - Found chapters!\n";
            print_r(array_slice($json['chapters'] ?? $json['data']['chapters'], 0, 5));
            break;
        } else {
            echo " - Success but no chapters found.\n";
            // print_r(array_keys($json));
        }
    }
}
