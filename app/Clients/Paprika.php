<?php
namespace Ambax\CryptoTrade\Clients;
use GuzzleHttp\Client;

class Paprika implements Api
{
    private Client $client;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coinpaprika.com/v1/',
            'timeout'  => 10.0,
        ]);
    }
    public function setParams($params)
    {
        $this->params = http_build_query($params);
    }
    public function get($method): string
    {
        $response = $this->client->get($method);
        return $response->getBody()->getContents();
    }
}
