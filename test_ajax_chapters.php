<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$ajaxUrl = 'https://comicazen.com/wp-admin/admin-ajax.php';
$data = [
    'action' => 'manga_get_chapters',
    'manga' => '2543'
];

echo "Testing action: manga_get_chapters\n";
$response = Http::asForm()->withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Referer' => 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/'
])->withOptions(['verify' => false])->post($ajaxUrl, $data);

echo " - Status: " . $response->status() . "\n";
echo " - Body snippet: " . substr($response->body(), 0, 500) . "\n";
