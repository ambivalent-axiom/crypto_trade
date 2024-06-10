<?php
namespace Ambax\CryptoTrade;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class Client implements \JsonSerializable
{
    private string $name;
    private string $id;
    private string $currency;
    private array $wallet;
    private array $transactions;
    private const WALLET_COLUMNS = ['Symbol', 'Amount', 'Transactions'];

    public function __construct(string $name, string $currency, array $wallet = Null, array $transactions = [])
    {
        $this->name = $name;
        $this->id = Uuid::uuid4()->toString();
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
    public function addTransaction(
        string $act,
        string $symbol,
        float $cryptoAmount,
        float $localCurrency): void
    {
        $transaction = new \stdClass();
        $transaction->timestamp = Carbon::now();
        $transaction->act = $act;
        $transaction->symbol = $symbol;
        $transaction->amount = $cryptoAmount;
        $transaction->currency = $this->currency;
        $transaction->localCurrency = $localCurrency;
        $this->transactions[] = $transaction;
    }
    public function getTransactions(): array
    {
        return $this->transactions;
    }
    public function getId(): string
    {
        return $this->id;
    }
}