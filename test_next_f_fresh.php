<?php
$html = file_get_contents('temp_lunar.html');
preg_match_all('/self\.__next_f\.push\(\[1,\"(.*?)\"\]\)/', $html, $matches);
foreach ($matches[1] as $match) {
    $decoded = str_replace(['\\"', '\\\\'], ['"', '\\'], $match);
    if (str_contains($decoded, 'chapter_number')) {
        echo "Found chapter data in next_f.push!\n";
        echo $decoded . "\n";
    }
}
echo "Done searching.\n";
