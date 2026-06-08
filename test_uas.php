<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';
$uas = [
    'curl/7.68.0',
    'Wget/1.20.3 (linux-gnu)',
    'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
];

foreach ($uas as $ua) {
    echo "Testing UA: $ua\n";
    $response = Http::withHeaders(['User-Agent' => $ua])->withOptions(['verify' => false])->get($url);
    echo " - Status: " . $response->status() . "\n";
}
