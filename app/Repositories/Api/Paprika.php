<?php
namespace Ambax\CryptoTrade\Repositories\Api;
use Ambax\CryptoTrade\Controllers\TransactionController;
use Ambax\CryptoTrade\Services\CurrencyService;
use Ambax\CryptoTrade\Services\UserService;
use Exception;
use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Paprika implements Api
{
    private Client $client;
    public function __construct()
    {
        $this->logger = new Logger('Paprika');
        $this->logger->pushHandler(new StreamHandler('app.log'));
        $this->client = new Client([
            'base_uri' => 'https://api.coinpaprika.com/v1/',
            'timeout'  => 5.0,
        ]);
    }
    public function get(): array
    {
        try {
            $response = $this->client->get('tickers?limit=' . ApiService::REQUEST_LIMIT);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
        $latest = json_decode($response->getBody()->getContents());
        foreach ($latest as $currency) {
            $currencies[] = new CurrencyService(
                $currency->name,
                $currency->symbol,
                $currency->quotes->{UserService::DEFAULT_CURRENCY}->price
            );
        }
        return $currencies;
    }
}
