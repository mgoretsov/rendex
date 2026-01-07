<?php

// index.php - Entry point
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Config
define('BASE_PATH', __DIR__);
define('BGN_TO_EUR', 0.511292); // 1 BGN = 0.511292 EUR
define('EUR_TO_BGN', 1.95583);  // 1 EUR = 1.95583 BGN

// Autoloader
spl_autoload_register(function ($class) {

    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {

        if ( file_exists($path) ) {
            require_once $path;
            return;
        }
    }

});

// Router
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/index.php', '', $path);

// Routes
if ( $path === '/' || $path === '/products' ) {

    $controller = new ProductController();
    $controller->index();

} elseif ( $path === '/api/products/search' ) {

    $controller = new ProductController();
    $controller->search();

} else {

    http_response_code(404);
    echo "404 Not Found";
    
}