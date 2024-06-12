<?php
namespace Ambax\CryptoTrade\Api;
class CoinMC implements Api
{
    private string $url;
    private string $params;
    private array $headers;
    public function __construct(int $start, int $limit, string $convert)
    {
        if (isset($_ENV['COINMC'])) {
            $this->headers = [
                'Accepts: application/json',
                'X-CMC_PRO_API_KEY: ' . $_ENV['COINMC'],
            ];
        } else {
            throw new \Exception("Fatal Error! Coinmarketcap API key not loaded! Unable to load crypto database.\n");
        }
        $this->url = 'https://pro-api.coinmarketcap.com/';
        $this->params = http_build_query([
            'start' => $start,
            'limit' => $limit,
            'convert' => $convert
        ]);
    }
    public function get($method): string
    {
        $url = $this->url . $method . "?" . $this->params;
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        if( ! $result = curl_exec($request))
        {
            trigger_error(curl_error($request));
        }
        curl_close($request);
        return $result;
    }
}