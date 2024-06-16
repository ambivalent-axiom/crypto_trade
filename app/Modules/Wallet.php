<?php
namespace Ambax\CryptoTrade\Modules;

class Wallet
{
    private string $id;
    private array $portfolio;
    private const WALLET_COLUMNS = ['Symbol', 'Amount', 'Transactions', 'Profit'];

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
    public static function getWalletColumns(): array
    {
        return self::WALLET_COLUMNS;
    }
}
