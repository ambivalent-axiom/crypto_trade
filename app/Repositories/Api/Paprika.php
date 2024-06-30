<?php
namespace Ambax\CryptoTrade\Repositories\Api;
use Ambax\CryptoTrade\Controllers\Controller;
use Ambax\CryptoTrade\Services\Currency;
use Ambax\CryptoTrade\Services\User;
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
            $response = $this->client->get('tickers?limit=' . Controller::REQUEST_LIMIT);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
        $latest = json_decode($response->getBody()->getContents());
        foreach ($latest as $currency) {
            $currencies[] = new Currency(
                $currency->name,
                $currency->symbol,
                $currency->quotes->{User::DEFAULT_CURRENCY}->price
            );
        }
        return $currencies;
    }
}
