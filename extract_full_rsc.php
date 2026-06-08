<?php
$url = 'https://lunaranime.ru/manga/solo-leveling-official';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$html = curl_exec($ch);

// Find all self.__next_f.push calls
preg_match_all('/self\.__next_f\.push\(\[1,\"(.*?)\"\]\)/s', $html, $matches);
$fullData = "";
foreach ($matches[1] as $match) {
    $decoded = str_replace(['\\"', '\\\\'], ['"', '\\'], $match);
    $fullData .= $decoded;
}

file_put_contents('full_rsc_data.txt', $fullData);
echo "Extracted " . strlen($fullData) . " bytes of RSC data.\n";

// Search for anything interesting
if (str_contains($fullData, 'chapter_number')) {
    echo "Found chapter_number!\n";
}
if (str_contains($fullData, 'chapters')) {
    echo "Found chapters!\n";
}
if (str_contains($fullData, 'slug')) {
    echo "Found slug!\n";
}
