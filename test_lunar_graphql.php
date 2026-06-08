<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$id = "6c6bae4f-87e2-4b7b-9d76-e02ace02bdf5";
$url = "https://api.lunaranime.ru/api/graphql"; // or just /graphql

$query = 'query GetMangaChapters($id: ID!) {
  manga(id: $id) {
    id
    title
    chapters {
      id
      number
      title
      slug
    }
  }
}';

$variables = ['id' => $id];

echo "Testing GraphQL to URL: $url\n";
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Referer' => 'https://lunaranime.ru/',
    'Origin' => 'https://lunaranime.ru',
    'Accept' => 'application/json',
])->post($url, [
    'query' => $query,
    'variables' => $variables
]);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    print_r($response->json());
} else {
    echo "Body: " . $response->body() . "\n";
    
    // Try without /api
    $url = "https://api.lunaranime.ru/graphql";
    echo "\nTesting GraphQL to URL: $url\n";
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
        'Referer' => 'https://lunaranime.ru/',
        'Origin' => 'https://lunaranime.ru',
        'Accept' => 'application/json',
    ])->post($url, [
        'query' => $query,
        'variables' => $variables
    ]);
    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        print_r($response->json());
    } else {
        echo "Body: " . $response->body() . "\n";
    }
}
