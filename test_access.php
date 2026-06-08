<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://comicazen.com/';
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
])->withOptions(['verify' => false])->get($url);

echo "Status: " . $response->status() . "\n";
if (str_contains($response->body(), 'Just a moment...')) {
    echo "Blocked by Cloudflare\n";
} else {
    echo "Accessible\n";
}
