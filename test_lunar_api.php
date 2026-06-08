<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$query = "Solo Leveling";
$url = "https://api.lunaranime.ru/api/manga/search?query=" . urlencode($query);

echo "Testing URL: $url\n";
try {
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Referer' => 'https://lunaranime.ru/',
        'Accept' => 'application/json, text/plain, */*'
    ])->timeout(10)->get($url);
    
    echo " - Status: " . $response->status() . "\n";
    if ($response->successful() && $response->json()) {
        echo " - Valid JSON returned.\n";
        $data = $response->json();
        if (!empty($data['manga'])) {
            $manga = $data['manga'][0];
            echo "ID: " . $manga['manga_id'] . "\n";
            echo "Title: " . $manga['title'] . "\n";
            echo "Slug: " . $manga['slug'] . "\n";
            
            $mId = $manga['manga_id'];
            $detailUrls = [
                "https://api.lunaranime.ru/api/manga/id/$mId",
                "https://api.lunaranime.ru/api/manga/slug/" . $manga['slug'],
            ];
            
            foreach ($detailUrls as $dUrl) {
                echo "\nTesting Detail URL: $dUrl\n";
                $res = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                    'Referer' => 'https://lunaranime.ru/',
                ])->get($dUrl);
                echo " - Status: " . $res->status() . "\n";
                if ($res->successful()) {
                    $json = $res->json();
                    echo " - Message: " . ($json['message'] ?? 'N/A') . "\n";
                    print_r($json);
                }
            }
        }
    }
} catch (\Exception $e) {
    echo " - Failed: " . $e->getMessage() . "\n";
}
