<?php
namespace Ambax\CryptoTrade\api;

class CoinMC extends Api
{
    private const PARAMS = [
        'start' => '1',
        'limit' => '100',
        'convert' => 'EUR'
    ];
    private const URL = 'https://pro-api.coinmarketcap.com/';
    public function __construct()
    {
        parent::__construct(self::URL, getenv('COINMC'));
    }
    public function getLatest(): string
    {
        $urlSuffix = 'v1/cryptocurrency/listings/latest';
        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $this->apiKey,
        ];
        $params = http_build_query(self::PARAMS);
        $endpoint = self::URL . $urlSuffix . "?" . $params;
        return Api::getRequest($endpoint, $headers);
    }
}