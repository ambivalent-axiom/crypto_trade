<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Response;
use Ambax\CryptoTrade\Services\CurrencyService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class CurrencyController
{
    private array $latestCurrencyUpdate;
    public function __construct(
        Logger                       $logger,
        StreamHandler                $loggerStreamHandler,
        Api                          $api
    )
    {
        $this->logger = $logger->withName('Controller');
        $this->logger->pushHandler($loggerStreamHandler);
        $this->exchangeApi = $api;
        $this->latestCurrencyUpdate = $this->exchangeApi->get();
    }
    public function index(): Response
    {
        return new Response(
            ['currencies' => $this->latestCurrencyUpdate],
            'index'
        );
    }
    public function show(): Response
    {
        $vars = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
        return new Response(['currencies' => [CurrencyService::searchBySymbol($vars, $this->latestCurrencyUpdate)]], 'show');
    }
}