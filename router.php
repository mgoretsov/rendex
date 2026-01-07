<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

if ( $uri !== '/' && file_exists(__DIR__ . $uri) ) {
    return false;
}

require __DIR__ . '/index.php';