<?php
require_once "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();



$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [\Ambax\CryptoTrade\Controllers\Controller::class, 'index']);
    $r->addRoute('GET', '/{symbol}', [\Ambax\CryptoTrade\Controllers\Controller::class, 'show']);
});


// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

[$response, $handler, $vars] = $dispatcher->dispatch($httpMethod, $uri);
switch ($response) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        [$controller, $method] = $handler;
        echo (new $controller)->$method(...array_values($vars));
        break;
}

