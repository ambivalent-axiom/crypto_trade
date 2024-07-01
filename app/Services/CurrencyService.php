<?php
namespace Ambax\CryptoTrade\Services;
class CurrencyService
{
    private string $name;
    private string $symbol;
    private float $price;
    public function __construct(string $name, string $symbol, float $price)
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getSymbol(): string
    {
        return $this->symbol;
    }
    public function getPrice(): float
    {
        return $this->price;
    }
    public static function searchBySymbol(string $query, array $currencies): ?CurrencyService
    {
        foreach ($currencies as $currency) {
            if ($currency->getSymbol() == $query) {
                return new self(
                    $currency->getName(),
                    $currency->getSymbol(),
                    $currency->getPrice()
                );
            }
        }
        return null;
    }
}