<?php
$html = file_get_contents('manga_lunar.html');
if (preg_match('/<script id=\"__NEXT_DATA__\"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
    $data = json_decode($matches[1], true);
    // Print keys of the data
    echo "Keys: " . implode(', ', array_keys($data)) . "\n";
    if (isset($data['props'])) {
        echo "Props keys: " . implode(', ', array_keys($data['props'])) . "\n";
        if (isset($data['props']['pageProps'])) {
            echo "PageProps keys: " . implode(', ', array_keys($data['props']['pageProps'])) . "\n";
            print_r($data['props']['pageProps']);
        }
    }
} else {
    // Try to find any JSON-like strings
    echo "Searching for self.__next_f.push...\n";
    preg_match_all('/self\.__next_f\.push\(\[1,\"(.*?)\"\]\)/', $html, $matches);
    foreach ($matches[1] as $match) {
        $decoded = str_replace(['\\"', '\\\\'], ['"', '\\'], $match);
        if (str_contains($decoded, 'chapter_number')) {
            echo "Found chapter data in next_f.push!\n";
            echo substr($decoded, 0, 1000) . "...\n";
        }
    }
}
