<?php
$html = file_get_contents('list_lunar.html');
preg_match_all('/self\.__next_f\.push\(\[1,\"(.*?)\"\]\)/s', $html, $matches);
foreach ($matches[1] as $match) {
    // Unescape the string
    $decoded = json_decode('"' . $match . '"');
    if ($decoded) {
        // Search for title or slug
        if (str_contains($decoded, 'title') || str_contains($decoded, 'slug')) {
            echo "Found potential data block:\n";
            echo substr($decoded, 0, 500) . "...\n";
            
            // Extract all UUIDs
            preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $decoded, $uuids);
            if (!empty($uuids[0])) {
                echo "UUIDs found: " . implode(', ', array_unique($uuids[0])) . "\n";
            }
        }
    }
}
