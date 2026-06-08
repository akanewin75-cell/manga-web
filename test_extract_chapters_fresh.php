<?php
$html = file_get_contents('temp_lunar.html');
preg_match_all('/\/manga\/([^\/\s\"]+)\/([^\/\s\"]+)/', $html, $matches);
print_r(array_unique($matches[0]));
