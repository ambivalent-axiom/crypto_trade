<?php
namespace Ambax\CryptoTrade;

class Client implements \JsonSerializable
{
    private string $name;
    private string $currency;
    private array $wallet;
    private array $transactions;

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


    public function showStatus(): void
    {
        $currencies = array_keys($this->wallet);
        echo 'Client: ' . $this->name . "\n";
        echo 'Currency: ' . $this->currency . "\n";
        echo 'Transactions: ' . count($this->transactions) . "\n";
        echo 'Wallet: ' . "\n";
        for ($i = 0; $i < count($this->wallet); $i++) {
            echo $currencies[$i] . ": " . $this->wallet[$currencies[$i]] . "\n";
        }
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
}