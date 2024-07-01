<?php
namespace Ambax\CryptoTrade\Models;
class Wallet
{
    private string $id;
    private array $portfolio;

    public function __construct($id)
    {
        $this->id = $id;
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getPortfolio(): array
    {
        return $this->portfolio;
    }
    public function addPortfolio($currency, $amount): void
    {
        $this->portfolio[$currency] = $amount;
    }

}
