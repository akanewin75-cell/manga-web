<?php
$html = file_get_contents('https://lunaranime.ru/manga/the-perks-of-being-a-villainess-official');
preg_match_all('/(https:\/\/api\.lunaranime\.ru\/api\/[^\"\'>\s]+)/', $html, $matches);
print_r(array_unique($matches[0]));

echo "\nSearching for any UUIDs...\n";
preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $html, $matches);
print_r(array_unique($matches[0]));
