<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$comicaso = new \App\Services\ComicasoService();
$comicazen = new \App\Services\ComicazenService();

echo "=== TESTING COMICASO ===\n";
$images1 = $comicaso->getChapterImages('whats-wrong-with-being-the-villainess', 'chapter-1');
echo "Comicaso images count: " . count($images1) . "\n";
print_r(array_slice($images1, 0, 5));

echo "\n=== TESTING COMICAZEN ===\n";
$images2 = $comicazen->getChapterImages('whats-wrong-with-being-the-villainess', 'chapter-1');
echo "Comicazen images count: " . count($images2) . "\n";
print_r(array_slice($images2, 0, 5));
