<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\ComicazenService::class);
$slug = 'whats-wrong-with-being-the-villainess';
$details = $service->getMangaDetails($slug);

echo "Title: " . ($details['title'] ?? 'N/A') . "\n";
echo "Chapters count: " . count($details['chapters'] ?? []) . "\n";
if (!empty($details['chapters'])) {
    echo "Sample Chapters:\n";
    foreach (array_slice($details['chapters'], 0, 5) as $ch) {
        echo " - ID: " . $ch['id'] . " | Title: " . $ch['title'] . "\n";
    }
}
