<?php
require_once "vendor/autoload.php";

use Ambax\CryptoTrade\RedirectResponse;
use Ambax\CryptoTrade\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

$container = (require 'app/Controllers/DIConfigs/controllerDIconfig.php')();

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
$loader = new FilesystemLoader(__DIR__ . '/views');
$twig = new Environment($loader, [
    'cache' => false,
]);

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $routes = include ('routes.php');
    foreach ($routes as $route)
    {
        [$method, $path, $controller] = $route;
        $r->addRoute($method, $path, $controller);
    }
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
[$case, $handler, $vars] = $dispatcher->dispatch($httpMethod, $uri);

switch ($case) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        [$controller, $route] = $handler;
        try {
            $items = ($container->get($controller))->$route(...array_values($vars));
        } catch (Exception $e) {
            $items = new RedirectResponse('notify', $e->getMessage());
        }
        if ($items instanceof Response) {
            try {
                echo $twig->render(
                    $items->getAddress() . '.html.twig',
                    $items->getData()
                );
            } catch (LoaderError | RuntimeError | SyntaxError $e) {
                $items = new RedirectResponse('notify', $e->getMessage());
            }
        }
        if ($items instanceof RedirectResponse) {
            try {
                echo $twig->render(
                    $items->getAddress() . '.html.twig',
                    $items->getMessage()
                );
            } catch (LoaderError | RuntimeError | SyntaxError $e) {
            }
        }
        break;
}

