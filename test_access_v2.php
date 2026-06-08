<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

function testUrl($url, $referer = null, $cookies = []) {
    echo "Testing URL: $url\n";
    $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    ];
    if ($referer) $headers['Referer'] = $referer;
    
    $req = Http::withHeaders($headers)->withOptions(['verify' => false]);
    if ($cookies) $req = $req->withCookies($cookies, 'comicazen.com');
    
    $response = $req->get($url);
    echo "Status: " . $response->status() . "\n";
    if (str_contains($response->body(), 'Just a moment...')) {
        echo "Blocked by Cloudflare\n";
    } else {
        echo "Accessible (Length: " . strlen($response->body()) . ")\n";
    }
    return $response;
}

$resHome = testUrl('https://comicazen.com/');
$cookies = $resHome->cookies()->toArray();

$mangaUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/';
$resManga = testUrl($mangaUrl, 'https://comicazen.com/', $cookies);

$chapterUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';
$resChapter = testUrl($chapterUrl, $mangaUrl, $cookies);
