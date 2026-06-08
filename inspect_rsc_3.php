<?php
$data = file_get_contents('full_rsc_data.txt');
$pos = strpos($data, 'chapters');
while ($pos !== false) {
    echo "Found 'chapters' at position $pos\n";
    echo substr($data, $pos - 50, 100) . "\n\n";
    $pos = strpos($data, 'chapters', $pos + 1);
}
