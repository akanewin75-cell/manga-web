<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://comicazen.com/wp-content/plugins/komik-json3.6.6/assets/js/app.js?ver=2.0.0';
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
])->withOptions(['verify' => false])->get($url);

if ($response->successful()) {
    $js = $response->body();
    preg_match_all('/(fetch|ajax|XMLHttpRequest|getJSON|get)\(/i', $js, $matches);
    echo "Found " . count($matches[0]) . " potential network calls.\n";
    // Search for URL patterns
    preg_match_all('/"([^"]*\.(json|php)[^"]*)"/', $js, $matches);
    foreach ($matches[1] as $url) {
        echo " - URL: $url\n";
    }
} else {
    echo "Failed to fetch JS. Status: " . $response->status() . "\n";
}
