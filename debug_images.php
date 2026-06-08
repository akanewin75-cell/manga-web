<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\ComicazenService::class);
$slug = 'whats-wrong-with-being-the-villainess';
$chapterId = 'chapter-1';

echo "Fetching images for $slug / $chapterId...\n";
$images = $service->getChapterImages($slug, $chapterId);

echo "Images found: " . count($images) . "\n";
foreach ($images as $img) {
    echo " - $img\n";
}
if (empty($images)) {
    echo "No images found. Check logs for details.\n";
}
