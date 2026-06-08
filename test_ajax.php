<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$ajaxUrl = 'https://comicazen.com/wp-admin/admin-ajax.php';
$actions = [
    ['action' => 'mjv2_get_chapter', 'slug' => 'whats-wrong-with-being-the-villainess', 'chapter' => 'chapter-1'],
    ['action' => 'mjv2_load_chapter', 'slug' => 'whats-wrong-with-being-the-villainess', 'chapter' => 'chapter-1'],
    ['action' => 'mjv2_get_images', 'slug' => 'whats-wrong-with-being-the-villainess', 'chapter' => 'chapter-1'],
    ['action' => 'get_images', 'slug' => 'whats-wrong-with-being-the-villainess', 'chapter' => 'chapter-1'],
    ['action' => 'mjv2_chapter_data', 'slug' => 'whats-wrong-with-being-the-villainess', 'chapter' => 'chapter-1'],
];

foreach ($actions as $data) {
    echo "Testing action: " . $data['action'] . "\n";
    $response = Http::asForm()->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Referer' => 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/'
    ])->withOptions(['verify' => false])->post($ajaxUrl, $data);
    
    echo " - Status: " . $response->status() . "\n";
    echo " - Body: " . substr($response->body(), 0, 100) . "...\n";
}
