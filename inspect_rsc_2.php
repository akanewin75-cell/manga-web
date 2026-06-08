<?php
$data = file_get_contents('full_rsc_data.txt');
$pos = strpos($data, 'chapter_number');
if ($pos !== false) {
    echo "Found 'chapter_number' at position $pos\n";
    echo substr($data, $pos - 100, 200) . "\n";
} else {
    echo "'chapter_number' not found.\n";
}
