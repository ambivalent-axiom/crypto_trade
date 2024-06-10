<?php
namespace Ambax\CryptoTrade;
use Ambax\CryptoTrade\database\JsonDatabase;
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
    private const DEFAULT_CURRENCY = 'EUR';

    public function __construct(
        string $name,
        string $id = null,
        string $currency = Null,
        array $wallet = Null,
        array $transactions = null
    )
    {
        $this->name = $name;
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->currency = $currency ?? self::DEFAULT_CURRENCY;
        $this->wallet = $wallet ?? [$this->currency => 1000];
        $this->transactions = $transactions ?? [];
    }
    public function jsonSerialize()
    {
        return [[
            'id' => $this->id,
            'name' => $this->name,
            'currency' => $this->currency,
            'wallet' => $this->wallet,
            'transactions' => $this->transactions
        ]];
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getCurrency(): string
    {
        return $this->currency;
    }
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }
    public function setWallet(array $wallet): void
    {
        $this->wallet = $wallet;
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
    public function setTransactions(array $transactions): void
    {
        $this->transactions = $transactions;
    }
    public function getId(): string
    {
        return $this->id;
    }
    public static function getClientList(): array
    {
        $clients = [];
        if ($files = glob(JsonDatabase::DB_DIR . "*"))
        {
            foreach ($files as $client) {
                $client = json_decode(file_get_contents($client))[0];
                $clients[$client->name] = $client->id;
            }
        }
        return $clients;
    }
}