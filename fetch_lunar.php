<?php
$url = 'https://lunaranime.ru/manga/the-perks-of-being-a-villainess-official';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$html = curl_exec($ch);
file_put_contents('temp_lunar.html', $html);
echo "Fetched " . strlen($html) . " bytes from $url\n";
