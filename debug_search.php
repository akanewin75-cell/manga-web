<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\MangaDiscoveryService::class);
$query = "The Perks of Being a Villainess";
$results = $service->search($query);

foreach ($results as $res) {
    echo "Title: " . $res['title'] . " | Source: " . $res['source_type'] . " | ID: " . $res['source_id'] . "\n";
}
