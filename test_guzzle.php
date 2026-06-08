<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$client = new Client([
    'verify' => false,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
    ],
    'cookies' => true,
]);

$mangaUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/';
$chapterUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';

echo "Visiting manga page...\n";
$res1 = $client->get($mangaUrl);
echo "Status 1: " . $res1->getStatusCode() . "\n";

echo "Visiting chapter page...\n";
try {
    $res2 = $client->get($chapterUrl, [
        'headers' => ['Referer' => $mangaUrl]
    ]);
    echo "Status 2: " . $res2->getStatusCode() . "\n";
    echo "Body Length: " . strlen($res2->getBody()) . "\n";
    if (str_contains($res2->getBody(), 'Just a moment...')) {
        echo "Still blocked by Cloudflare.\n";
    } else {
        echo "SUCCESS? Title: " . (preg_match('/<title>(.*?)<\/title>/', $res2->getBody(), $matches) ? $matches[1] : 'N/A') . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
