<?php
namespace Ambax\CryptoTrade\Api;
use Ambax\CryptoTrade\Currency;

class ApiAdapter {
    private const OFFSET = 1;
    private const LIMIT = 100;
    private Api $exchangeApi;

    public function __construct(Api $exchangeApi, $userCurrency) {
        $this->exchangeApi = $exchangeApi;
        $this->exchangeApi->setParams(
            [
                'start' => self::OFFSET,
                'limit' => self::LIMIT,
                'convert' => $userCurrency
            ]
        );
        $this->userCurrency = $userCurrency;
    }
    public function getLatest(): array
    {
        $currencies = [];
        if($this->exchangeApi instanceof CoinMC) {
            $request = $this->exchangeApi->get('v1/cryptocurrency/listings/latest');
            $latest= json_decode($request);
            foreach ($latest->data as $currency) {
                $currencies[] = new Currency(
                    $currency->name,
                    $currency->symbol,
                    $currency->quote->USD->price
                );
            }
        }
        if($this->exchangeApi instanceof Paprika) {
            $request = $this->exchangeApi->get('tickers');
            $latest= json_decode($request);
            foreach ($latest as $currency) {
                $currencies[] = new Currency(
                    $currency->name,
                    $currency->symbol,
                    $currency->quotes->USD->price
                );
            }
        }
        return $currencies;
    }
}