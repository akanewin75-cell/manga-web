<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$query = "Perks";
$url = "https://api.lunaranime.ru/api/manga/search?query=" . urlencode($query);

$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Referer' => 'https://lunaranime.ru/',
])->get($url);

if ($response->successful()) {
    $data = $response->json();
    print_r($data);
}
