<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$mangaUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/';
$chapterUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';

$headers = [
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
];

echo "Fetching manga page...\n";
$response1 = Http::withHeaders($headers)->withOptions(['verify' => false])->get($mangaUrl);
echo "Status 1: " . $response1->status() . "\n";
// print_r($response1->headers());
$cookies = $response1->cookies()->toArray();
echo "Cookies obtained: " . count($cookies) . "\n";
foreach($cookies as $name => $value) echo " - $name: $value\n";

echo "Fetching chapter page with cookies...\n";
$headers['Referer'] = $mangaUrl;
$response2 = Http::withHeaders($headers)
    ->withCookies($cookies, 'comicazen.com')
    ->withOptions(['verify' => false])
    ->get($chapterUrl);

echo "Status: " . $response2->status() . "\n";
echo "Body Length: " . strlen($response2->body()) . "\n";
if ($response2->status() == 403) {
    echo "Snippet: " . substr($response2->body(), 0, 500) . "\n";
} else {
    echo "Title: " . (preg_match('/<title>(.*?)<\/title>/', $response2->body(), $matches) ? $matches[1] : 'N/A') . "\n";
}
