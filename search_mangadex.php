<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\WebtoonService::class);
$query = "What's Wrong With Being The Villainess?";
$results = $service->searchWebtoon($query);

if (isset($results['data'])) {
    foreach ($results['data'] as $manga) {
        $title = $manga['attributes']['title']['en'] ?? array_values($manga['attributes']['title'])[0];
        $id = $manga['id'];
        echo "Title: $title | ID: $id\n";
    }
} else {
    echo "No results found on MangaDex.\n";
}
