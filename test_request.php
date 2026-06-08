<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-2/';
$headers = [
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
    'Referer' => 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/',
];

$response = Http::withHeaders($headers)->withOptions(['verify' => false, 'follow_redirects' => true])->get($url);

echo "Status: " . $response->status() . "\n";
echo "Final URL: " . $response->effectiveUri() . "\n";
if ($response->successful()) {
    $html = $response->body();
    // Look for script tags or div with ID related to MJV2
    preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $html, $matches);
    echo "Found " . count($matches[0]) . " script tags.\n";
    foreach ($matches[0] as $script) {
        if (str_contains($script, 'mjv2') || str_contains($script, 'chapter')) {
            echo " - Script: " . substr($script, 0, 200) . "...\n";
        }
    }
}
