<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$id = "6c6bae4f-87e2-4b7b-9d76-e02ace02bdf5"; // Solo Leveling
$url = "https://api.lunaranime.ru/api/manga/chapters";

echo "Testing POST to URL: $url\n";
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Referer' => 'https://lunaranime.ru/',
    'Content-Type' => 'application/json'
])->post($url, [
    'manga_id' => $id,
    'page' => 1
]);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    $data = $response->json();
    print_r($data);
} else {
    echo "Body: " . $response->body() . "\n";
}
