<?php
namespace Ambax\CryptoTrade;
class ApiGoGo
{
    protected string $apikey;
    protected string $url;
    public function __construct(string $url, string $apikey = "")
    {
        $this->apikey = $apikey;
        $this->url = $url;
    }
    protected function getRequest(string $url): string
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        if( ! $result = curl_exec($request))
        {
            trigger_error(curl_error($request));
        }
        curl_close($request);
        return $result;
    }
    protected function postRequest(string $url, array $data): string
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        if( ! $result = curl_exec($request))
        {
            trigger_error(curl_error($request));
        }
        curl_close($request);
        return $result;
    }
}