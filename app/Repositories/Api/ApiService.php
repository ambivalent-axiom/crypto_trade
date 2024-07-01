<?php
namespace Ambax\CryptoTrade\Repositories\Api;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ApiService implements Api
{
    public const REQUEST_LIMIT = 100;
    public function __construct() {
        $this->logger = new Logger('ApiService');
        $this->streamHandler = new STreamHandler('app.log');
    }
    public function get(): array
    {
        try {
            $exchangeApi = new CoinMC();
            $payload = $exchangeApi->get();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $exchangeApi = new Paprika();
            $payload = $exchangeApi->get();
        }
        return $payload;
    }
}