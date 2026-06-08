<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Services\ComicazenService;

$service = new ComicazenService();
$slug = 'whats-wrong-with-being-the-villainess';
$chapterId = 'chapter-1';

$mangaUrl = "https://comicazen.com/komik/$slug/";
$chapterUrl = "https://comicazen.com/komik/$slug/$chapterId/";

echo "1. Testing Manga Page (should work)...\n";
$res1 = Http::withHeaders($service->getHeaders())->get($mangaUrl);
echo "Status: " . $res1->status() . " | Length: " . strlen($res1->body()) . "\n";
$cookies = $res1->cookies()->toArray();

echo "\n2. Testing Chapter Page with Referer & Cookies (failing in logs)...\n";
$res2 = Http::withHeaders($service->getHeaders($mangaUrl))
    ->withCookies($cookies, 'comicazen.com')
    ->get($chapterUrl);
echo "Status: " . $res2->status() . " | Length: " . strlen($res2->body()) . "\n";
if (str_contains($res2->body(), 'Just a moment...')) echo "Blocked by Cloudflare\n";

echo "\n3. Testing Chapter Page WITHOUT Cookies (like Manga Page)...\n";
$res3 = Http::withHeaders($service->getHeaders($mangaUrl))->get($chapterUrl);
echo "Status: " . $res3->status() . " | Length: " . strlen($res3->body()) . "\n";
if (str_contains($res3->body(), 'Just a moment...')) echo "Blocked by Cloudflare\n";

echo "\n4. Testing Chapter Page with Google Proxy...\n";
$googleProxy = 'https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?container=focus&url=' . urlencode($chapterUrl);
$res4 = Http::get($googleProxy);
echo "Status: " . $res4->status() . " | Length: " . strlen($res4->body()) . "\n";
echo "Body: " . $res4->body() . "\n";
if ($res4->successful() && !str_contains($res4->body(), 'Just a moment...')) {
    echo "Google Proxy SUCCESS!\n";
}

echo "\n11. Testing Chapter Page with ROOT Referer...\n";
$res11 = Http::withHeaders($service->getHeaders('https://comicazen.com/'))->get($chapterUrl);
echo "Status: " . $res11->status() . " | Length: " . strlen($res11->body()) . "\n";
if ($res11->successful() && !str_contains($res11->body(), 'Just a moment...')) {
    echo "ROOT REFERER SUCCESS!\n";
}

echo "\n15. Testing ALTERNATIVE Chapter URL (/chapter/)...\n";
$altChapterUrl = "https://comicazen.com/chapter/$slug-$chapterId/";
$res15 = Http::withHeaders($service->getHeaders($mangaUrl))->get($altChapterUrl);
echo "Status: " . $res15->status() . " | Length: " . strlen($res15->body()) . "\n";
if ($res15->successful() && !str_contains($res15->body(), 'Just a moment...')) {
    echo "ALTERNATIVE CHAPTER SUCCESS!\n";
}
