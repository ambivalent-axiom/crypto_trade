<?php
namespace Ambax\CryptoTrade;
class Currency
{
    public string $name;
    public string $symbol;
    public float $price;
    public function __construct(string $name, string $symbol, float $price)
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }
}