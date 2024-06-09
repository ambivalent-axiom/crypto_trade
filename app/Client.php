<?php
namespace Ambax\CryptoTrade;

class Client implements \JsonSerializable
{
    private string $name;
    private string $currency;
    private array $wallet;
    private array $transactions;
    private const WALLET_COLUMNS = ['Symbol', 'Amount', 'Transactions'];

    public function __construct(string $name, string $currency, array $wallet = Null, array $transactions = [])
    {
        $this->name = $name;
        $this->currency = $currency;
        $this->wallet = $wallet ? : [$this->currency => 1000];
        $this->transactions = $transactions;
    }
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'wallet' => $this->wallet,
            'transactions' => $this->transactions
        ];
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getCurrency(): string
    {
        return $this->currency;
    }
    public function addToWallet(string $symbol, float $amount): void
    {
        if(isset($this->wallet[$symbol])) {
            $this->wallet[$symbol] += $amount;
        } else {
            $this->wallet[$symbol] = $amount;
        }
    }
    public function takeFromWallet(string $symbol, float $amount): void
    {
        if(isset($this->wallet[$symbol])) {
            $this->wallet[$symbol] -= $amount;
        } else {
            $this->wallet[$symbol] = $amount;
        }
        if($symbol != 'Eur' && $this->wallet[$symbol] == 0) {
            unset($this->wallet[$symbol]);
        }
    }
    public function getWalletCurrencies(): array
    {
        $wallet = array_keys($this->wallet);
        unset($wallet[array_search($this->currency, $wallet)]);
        return $wallet;
    }
    public function getCurrencyAmount(string $symbol): float
    {
        return $this->wallet[$symbol] ?? 0;
    }
    public function getWallet(): array
    {
        return $this->wallet;
    }
    public function getWalletColumns(): array
    {
        return self::WALLET_COLUMNS;
    }
}