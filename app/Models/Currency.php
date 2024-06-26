<?php
namespace Ambax\CryptoTrade\Models;
use JsonSerializable;

class Currency implements JsonSerializable
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
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'symbol' => $this->symbol,
            'price' => $this->price
        ];
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
}