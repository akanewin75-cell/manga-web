<?php
$html = file_get_contents('temp_lunar.html');
preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $html, $matches);
print_r(array_unique($matches[0]));
