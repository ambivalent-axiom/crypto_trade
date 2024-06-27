<?php
namespace Ambax\CryptoTrade\Repositories;
use Ambax\CryptoTrade\Controllers\Controller;
use Ambax\CryptoTrade\Models\Currency;
use Ambax\CryptoTrade\Models\User;
use Exception;
use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CoinMC implements Api
{
    private Client $client;
    private array $headers;
    public function __construct()
    {
        $this->logger = new Logger('CoinMC');
        $this->logger->pushHandler(new StreamHandler('app.log'));
        if (isset($_ENV['COINMC'])) {
            $this->headers = [
                'Accepts' => 'application/json',
                'X-CMC_PRO_API_KEY' => $_ENV['COINMC'],
            ];
        } else {
            throw new Exception("Fatal Error! Coinmarketcap API key not loaded! Unable to load crypto database.\n");
        }
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/',
            'timeout'  => 5.0,
        ]);
    }
    public function get(): array
    {
        $url = 'v1/cryptocurrency/listings/latest?limit=' . Controller::REQUEST_LIMIT;
        try {
            $response = $this->client->get($url, ['headers' => $this->headers]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
        $latest = json_decode($response->getBody()->getContents());
        foreach ($latest->data as $currency) {
            $currencies[] = new Currency(
                $currency->name,
                $currency->symbol,
                $currency->quote->{User::DEFAULT_CURRENCY}->price
            );
        }
        return $currencies;
    }
}