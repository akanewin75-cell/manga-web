<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$id = "a93e8ec2-faf3-4500-99a9-c14908bd9119";
$url = "https://api.lunaranime.ru/api/manga/$id";

echo "Testing URL: $url\n";
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Referer' => 'https://lunaranime.ru/',
])->get($url);

echo "Status: " . $response->status() . "\n";
$data = $response->json();
if (!empty($data['data'])) {
    echo "Found data!\n";
    print_r(array_slice($data['data'], 0, 5));
} else {
    echo "Empty data.\n";
    print_r($data);
}
