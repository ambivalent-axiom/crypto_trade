<?php
namespace Ambax\CryptoTrade\Repositories;
use GuzzleHttp\Client;

class CoinMC implements Api
{
    private Client $client;
    private string $params;
    private array $headers;
    public function __construct()
    {
        if (isset($_ENV['COINMC'])) {
            $this->headers = [
                'Accepts' => 'application/json',
                'X-CMC_PRO_API_KEY' => $_ENV['COINMC'],
            ];
        } else {
            throw new \Exception("Fatal Error! Coinmarketcap API key not loaded! Unable to load crypto database.\n");
        }
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/',
            'timeout'  => 2.0,
        ]);
    }
    public function setParams($params)
    {
        $this->params = http_build_query($params);
    }
    public function get($method): string
    {
        $url = $method . "?" . $this->params;
        $response = $this->client->get($url, [
            'headers' => $this->headers
        ]);
        return $response->getBody()->getContents();
    }
}