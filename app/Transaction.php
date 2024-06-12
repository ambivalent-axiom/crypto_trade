<?php
namespace Ambax\CryptoTrade;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
class Transaction implements \jsonSerializable
{
    private string $id;
    private Carbon $timestamp;
    private string $act;
    private float $amount;
    private string $localCurrency;
    private const TABLE_COLUMNS = [
        'id',
        'timestamp',
        'Act',
        'Amount',
        'Crypto',
        'USD'
    ];

    public function __construct(
        string $timezone,
        string $act,
        string $symbol,
        float $amount,
        float $localCurrency,
        string $id = null,
        string $timestamp = null
        )
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->timestamp = $timestamp ? Carbon::parse($timestamp) : Carbon::now($timezone);
        $this->act = $act;
        $this->amount = $amount;
        $this->symbol = $symbol;
        $this->localCurrency = $localCurrency;
    }
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'act' => $this->act,
            'amount' => (string) $this->amount,
            'symbol' => $this->symbol,
            'localCurrency' => $this->localCurrency
            ];
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getTimestamp(): Carbon
    {
        return $this->timestamp;
    }
    public function getAct(): string
    {
        return $this->act;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function getSymbol(): string
    {
        return $this->symbol;
    }
    public function getLocalCurrency(): string
    {
        return $this->localCurrency;
    }
    public static function getColumns(): array
    {
        return self::TABLE_COLUMNS;
    }
}