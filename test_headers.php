<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$mangaUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/';

echo "Fetching manga page: $mangaUrl\n";
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
])->withOptions(['verify' => false])->get($mangaUrl);

echo "Status: " . $response->status() . "\n";
print_r($response->headers());
