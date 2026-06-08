<?php
$url = 'https://lunaranime.ru/manga/solo-leveling-official';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'RSC: 1',
    'Accept: text/x-component',
    'Next-Router-State-Tree: [["",{"children":["manga",{"children":[["slug","solo-leveling-official","d"],{"children":["__PAGE__",{}]}]}]},"$undefined","$undefined",true]]'
]);
$data = curl_exec($ch);
file_put_contents('rsc_stream.txt', $data);
echo "Fetched " . strlen($data) . " bytes of RSC stream.\n";

if (str_contains($data, 'chapter_number')) {
    echo "Found chapter_number in stream!\n";
}
if (str_contains($data, 'chapters')) {
    echo "Found chapters in stream!\n";
}
