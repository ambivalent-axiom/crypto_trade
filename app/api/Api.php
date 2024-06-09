<?php
namespace Ambax\CryptoTrade\api;
class Api
{
    protected string $apiKey;
    protected string $url;
    public function __construct(string $url, string $apiKey = "")
    {
        $this->apiKey = $apiKey;
        $this->url = $url;
    }
    protected function getRequest(string $url, array $headers): string
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        if( ! $result = curl_exec($request))
        {
            trigger_error(curl_error($request));
        }
        curl_close($request);
        return $result;
    }
}