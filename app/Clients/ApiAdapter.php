<?php
namespace Ambax\CryptoTrade\Clients;
use Ambax\CryptoTrade\Models\Currency;

class ApiAdapter {
    private const OFFSET = 1;
    private const LIMIT = 10;
    private Api $exchangeApi;

    public function __construct($userCurrency) {
        $this->exchangeApi = new CoinMC();
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
                    $currency->quote->{$this->userCurrency}->price
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
                    $currency->quotes->{$this->userCurrency}->price
                );
            }
        }
        return $currencies;
    }
}