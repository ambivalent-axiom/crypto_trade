<?php
namespace Ambax\CryptoTrade\Api;
use GuzzleHttp\Client;

class CoinMC implements Api
{
    private CLient $client;
    private string $url;
    private string $params;
    private array $headers;
    public function __construct(int $start, int $limit, string $convert)
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
        $this->params = http_build_query([
            'start' => $start,
            'limit' => $limit,
            'convert' => $convert
        ]);
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