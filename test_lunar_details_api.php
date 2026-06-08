<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$id = "a93e8ec2-faf3-4500-99a9-c14908bd9119";
$slug = "the-perks-of-being-a-villainess-official";

$urls = [
    "https://api.lunaranime.ru/api/manga/id/$id",
    "https://api.lunaranime.ru/api/manga/slug/$slug",
    "https://api.lunaranime.ru/api/manga/info/$id",
    "https://api.lunaranime.ru/api/manga/details/$id",
    "https://api.lunaranime.ru/api/manga/$id/details",
    "https://api.lunaranime.ru/api/manga/$id/info",
];

foreach ($urls as $url) {
    echo "Testing URL: $url\n";
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Referer' => 'https://lunaranime.ru/',
    ])->get($url);
    
    echo " - Status: " . $response->status() . "\n";
    if ($response->successful()) {
        $json = $response->json();
        echo " - Keys: " . implode(', ', array_keys($json)) . "\n";
        if (isset($json['data']) && is_array($json['data'])) {
             echo " - Data Keys: " . implode(', ', array_keys($json['data'])) . "\n";
        }
        if (isset($json['manga']) || isset($json['data']['title'])) {
            echo " - Found details!\n";
            print_r($json);
            break;
        }
    }
}
