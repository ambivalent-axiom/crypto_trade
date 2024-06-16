<?php
namespace Ambax\CryptoTrade\Modules;
use Ambax\CryptoTrade\Services\SqLite;
use Ramsey\Uuid\Uuid;

class User
{
    private string $name;
    private string $id;
    private string $currency;
    private const DEFAULT_CURRENCY = 'USD';
    private const DEFAULT_WALLET = 1000;
    private const DEFAULT_TIMEZONE = 'Europe/Riga';
    public function __construct(
        string $name,
        SqLite $sqLite,
        string $id = null,
        string $currency = Null
    )
    {
        $this->name = $name;
        $this->db = $sqLite;
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->currency = $currency ?? self::DEFAULT_CURRENCY;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getCurrency(): string
    {
        return $this->currency;
    }
    public function addToWallet(string $symbol, float $amount): void
    {
        $currentAmount = $this->db->selectAmountByCurrency($this->getId(), $symbol);
        $currencies = $this->getWalletCurrencies();
        $currencies[] = $this->currency;
        if(in_array($symbol, $currencies)) {
            $amount = $currentAmount + $amount;
            $this->db->updateWallet($this->getId(), $symbol, $amount);
        } else {
            $this->db->addToWallet($this->getId(), $symbol, $amount);
        }
    }
    public function takeFromWallet(string $symbol, float $amount): void
    {
        $oldAmount = $this->db->selectAmountByCurrency($this->getId(), $symbol);
        $newAmount = $oldAmount - $amount;
        if($symbol != 'USD' && $newAmount == 0) {
            $this->db->deleteFromWallet($this->getId(), $symbol);
        }
        $this->db->updateWallet($this->getId(), $symbol, $newAmount);
    }
    public function getWalletCurrencies(): array// of keys - strings
    {
        $keys = [];
        foreach ($this->db->selectUserWallet($this->getId()) as $currency) {
            $keys[] = $currency['currency'];
        }
        unset($keys[array_search($this->currency, $keys)]);
        return $keys;
    }
    public function getCurrencyAmount(string $symbol): float
    {
        $wallet = $this->db->selectUserWallet($this->getId());
        foreach ($wallet as $currency) {
            if ($currency['currency'] == $symbol) {
                return $currency['amount'];
            }
        }
        return 0;
    }

    public function getId(): string
    {
        return $this->id;
    }
    public static function getDefaultTimezone(): string
    {
        return self::DEFAULT_TIMEZONE;
    }
    public static function getDefaultWallet(): string
    {
        return self::DEFAULT_WALLET;
    }
}