<?php
namespace Ambax\CryptoTrade\Services;
use Ambax\CryptoTrade\Services\RepositoryServices\UserRepositoryService;
use Ambax\CryptoTrade\Services\RepositoryServices\WalletRepositoryService;
use Carbon\Carbon;
use Error;
use Exception;
use Ramsey\Uuid\Uuid;

class UserService
{
    private string $name;
    private string $id;
    private string $password;
    private string $currency;
    public const DEFAULT_CURRENCY = 'USD';
    public const DEFAULT_WALLET = 1000;
    public const DEFAULT_TIMEZONE = 'Europe/Riga';
    public function __construct(
        string                  $name,
        WalletRepositoryService $walletRepository,
        UserRepositoryService   $userRepository,
        string                  $id = null,
        string                  $currency = Null,
        string                  $password = null
    )
    {
        $this->name = $name;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->currency = $currency ?? self::DEFAULT_CURRENCY;
        $this->password = $password ?? '';
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
        $currentAmount = $this->walletRepository->selectAmountByCurrency($this->getId(), $symbol);
        $currencies = $this->getWalletCurrencies();
        $currencies[] = $this->currency;
        if(in_array($symbol, $currencies)) {
            $amount = $currentAmount + $amount;
            $this->walletRepository->updateWallet($this->getId(), $symbol, $amount);
        } else {
            $this->walletRepository->addToWallet($this->getId(), $symbol, $amount, Carbon::now()->toDateTimeString());
        }
    }
    public function takeFromWallet(string $symbol, float $amount): void
    {
        $oldAmount = $this->walletRepository->selectAmountByCurrency($this->getId(), $symbol);
        $newAmount = $oldAmount - $amount;
        if($symbol != 'USD' && $newAmount == 0) {
            $this->walletRepository->deleteFromWallet($this->getId(), $symbol);
        }
        $this->walletRepository->updateWallet($this->getId(), $symbol, $newAmount);
    }
    public function getWalletCurrencies(): array
    {
        $currencies = $this->walletRepository->selectUserWallet($this->getId());
        $keys = array_keys($currencies->getPortfolio());
        unset($keys[array_search($this->currency, $keys)]);
        return $keys;
    }
    public function getCurrencyAmount(string $symbol): float
    {
        $wallet = $this->walletRepository->selectUserWallet($this->getId());
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
    public function setPassword(string $password): void
    {
        $this->password = md5($password);
        $this->userRepository->setUserPass($this->getId(), $this->getPassword());
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    public function login($password): bool
    {
        return md5($password) == $this->getPassword();
    }
    public function calcProfit($symbol, $currencies): float
    {
        $averageBuy = $this->walletRepository->selectAvgPrice(
            $this->id,
            $symbol,
            $this->walletRepository->selectCurrencySince(
                $this->id,
                $symbol
            )
        );
        $currentPrice = CurrencyService::searchBySymbol($symbol, $currencies);
        //TODO fix this error
        //if it does not exist in a wallet it will find null
        try {
            $price = $currentPrice->getPrice();
        } catch (Error $e) {
            throw new Exception('Uups! Looks like connection issues...');
        }
        return (($price - $averageBuy)/$averageBuy) * 100;
    }
}