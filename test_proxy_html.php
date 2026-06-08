<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$chapterUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';
$proxyUrl = 'http://localhost/proxy-image?url=' . urlencode($chapterUrl);

echo "Fetching chapter via proxy: $proxyUrl\n";
// Since I can't call localhost via Http, I'll call the proxy logic directly
$url = $chapterUrl;
$headers = [
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Referer' => 'https://comicazen.com/',
];

$response = Http::withHeaders($headers)
    ->withOptions(['verify' => false, 'follow_redirects' => true])
    ->get($url);

echo "Status: " . $response->status() . "\n";
if (str_contains($response->body(), 'Just a moment...')) {
    echo "Blocked by Cloudflare\n";
} else {
    echo "SUCCESS? Body Length: " . strlen($response->body()) . "\n";
}
