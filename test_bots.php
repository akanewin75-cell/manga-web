<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://comicazen.com/komik/whats-wrong-with-being-the-villainess/chapter-1/';
$bots = [
    'Twitterbot/1.0',
    'LinkedInBot/1.0',
    'Slackbot 1.0 (+https://api.slack.com/robots)',
    'TelegramBot (like Twitterbot)',
    'WhatsApp/2.21.12.21 A',
];

foreach ($bots as $bot) {
    echo "Testing bot: $bot\n";
    $response = Http::withHeaders(['User-Agent' => $bot])->withOptions(['verify' => false])->get($url);
    echo " - Status: " . $response->status() . "\n";
}
