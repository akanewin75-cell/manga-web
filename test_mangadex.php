<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\WebtoonService::class);
$mangaId = '5d0c7546-e522-4217-9c97-060d4b998246'; // The Perks of Being a Villainess

echo "Fetching feed...\n";
$feed = $service->getWebtoonFeed($mangaId);

if (isset($feed['data']) && !empty($feed['data'])) {
    $chapter = $feed['data'][0];
    $chapterId = $chapter['id'];
    $chapterNum = $chapter['attributes']['chapter'];
    echo "Found Chapter $chapterNum (ID: $chapterId)\n";
    
    echo "Fetching images...\n";
    $images = $service->getChapterImages($chapterId);
    echo "Images found: " . count($images ?? []) . "\n";
    foreach (array_slice($images ?? [], 0, 3) as $img) {
        echo " - $img\n";
    }
} else {
    echo "No feed found for this ID.\n";
}
