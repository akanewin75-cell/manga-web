<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\ComicazenService::class);
$query = "Solo Leveling";
$results = $service->searchManga($query);

foreach ($results as $res) {
    echo "Title: " . $res['title'] . " | Slug: " . $res['slug'] . "\n";
}
