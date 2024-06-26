<?php
use Ambax\CryptoTrade\Controllers\Controller;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once "vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$loader = new FilesystemLoader(__DIR__ . '/app/Templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [Controller::class, 'index']);
    $r->addRoute('GET', '/wallet', [Controller::class, 'status']);
    $r->addRoute('GET', '/hist', [Controller::class, 'history']);
    $r->addRoute('GET', '/{symbol}', [Controller::class, 'show']);


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
        [$controller, $route] = $handler;
        $items = (new $controller)->$route(...array_values($vars));
        echo $twig->render($route . '.html.twig', ['items' => $items]);
        break;
}

