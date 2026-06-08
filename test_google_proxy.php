<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$chapterUrl = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';
$googleProxy = 'https://images1-focus-opensocial.googleusercontent.com/gadgets/proxy?container=focus&url=' . urlencode($chapterUrl);

echo "Fetching chapter via Google Proxy: $googleProxy\n";
$response = Http::get($googleProxy);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    echo "SUCCESS? Body Length: " . strlen($response->body()) . "\n";
    echo "Snippet: " . substr($response->body(), 0, 500) . "\n";
}
