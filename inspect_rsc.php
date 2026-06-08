<?php
$data = file_get_contents('full_rsc_data.txt');
$pos = strpos($data, 'chapters');
if ($pos !== false) {
    echo "Found 'chapters' at position $pos\n";
    echo substr($data, $pos - 100, 1000) . "\n";
} else {
    echo "'chapters' not found.\n";
}
