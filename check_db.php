<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Manga;
use App\Models\Chapter;

$manga = Manga::where('title', 'LIKE', '%What\'s Wrong With Being The Villainess%')->first();
if ($manga) {
    echo "Manga Found: " . $manga->title . "\n";
    echo "Source Type: " . $manga->source_type . "\n";
    echo "Source ID: " . $manga->source_id . "\n";
    
    $chapters = $manga->chapters()->get();
    echo "Chapters in DB: " . $chapters->count() . "\n";
    foreach ($chapters as $ch) {
        echo " - " . $ch->chapter_num . " (Local: " . ($ch->is_local ? 'Yes' : 'No') . ")\n";
    }
} else {
    echo "Manga not found in database.\n";
}
