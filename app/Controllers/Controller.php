<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\Repositories\ApiAdapter;
use Ambax\CryptoTrade\Models\Currency;
use Ambax\CryptoTrade\Models\Transaction;
use Ambax\CryptoTrade\Models\User;
use Ambax\CryptoTrade\Models\Wallet;
use Ambax\CryptoTrade\Services\SqLite;
use Carbon\Carbon;
use JsonSerializable;

class Controller implements JsonSerializable
{
    private User $user;
    private array $users;
    private SqLite $db;
    private array $latestUpdate; //array of Currency objects

    public function __construct() {
        //client initialization
        $this->db = new SqLite('database.sqlite');
        $this->users = $this->db->selectAllUsers();
        $this->user = $this->initUser();
        //api initialization
        try {
            $this->exchangeApi = new ApiAdapter($this->user->getCurrency());
            $this->latestUpdate = $this->exchangeApi->getLatest();
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
    public function jsonSerialize()
    {
        return [
            'user' => $this->user,
            'latestUpdate' => $this->latestUpdate,
        ];
    }
    public function index(): string
    {
        return json_encode($this->latestUpdate);
    }
    public function show(string $vars): string
    {
        return json_encode($this->searchBySymbol($vars));
    }









    private function initUser(): User
    {
        return new User('Arthur', $this->db, '457c48d4-32f1-4b90-8357-251c72f1a607');
    }
    private function chooseAmount(string $symbol): float
    {
        while(true) {
            $amount = readline("Enter amount or empty for all: ");
            if ($amount == '') {
                return $this->user->getCurrencyAmount($symbol);
            }
            if (is_numeric($amount) && $this->user->getCurrencyAmount($symbol) < $amount) {
                echo "You are unable to cover this transaction!\n";
                continue;
            }
            if (is_numeric($amount)) {
                return $amount;
            }
        }
    }
    private function numberFormat(float $number): string
    {
        $formattedNumber = rtrim(sprintf('%.10f', $number), '0');
        $formattedNumber = rtrim($formattedNumber, '.');
        return $formattedNumber;
    }
    private function searchBySymbol($query): ?Currency
    {
        try {
            foreach ($this->latestUpdate as $currency) {
                if ($currency->getSymbol() == $query) {
                    return new Currency(
                        $currency->getName(),
                        $currency->getSymbol(),
                        $currency->getPrice()
                    );
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return null;
    }




















    public function buy(string $symbol, float $cost): void
    {
        $currency = $this->searchBySymbol($symbol);
        if( ! $currency) {
            throw new \Exception('Could not find symbol ' . $symbol . "\n");
        }
        if($cost > $this->db->selectAmountByCurrency(
            $this->user->getId(), $this->user->getCurrency())) {
            throw new \Exception('Insufficient wallet balance for this transaction!' . "\n");
        }
        if( ! Ui::question("Are you sure you want to proceed with order?")) {
            throw new \Exception('Action aborted ' . $symbol . "\n");
        }
        $boughtAmount = $cost/$currency->getPrice();
        $this->user->takeFromWallet($this->user->getCurrency(), $cost);

        $this->user->addToWallet($currency->getSymbol(), $boughtAmount);
        $this->db->insertTransaction(
            $this->user->getId(),
            Carbon::now($this->user->getDefaultTimezone())->toDateTimeString(),
            'Buy',
            $boughtAmount,
            $symbol,
            $cost
        );
    }
    public function sell(): void
    {
        $options = $this->user->getWalletCurrencies();
        try {
            $symbol = strtoupper(Ui::menu('Select currency to sell: ', $options));
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            return;
        }
        echo "In wallet: " . $this->user->getCurrencyAmount($symbol) . "\n";
        $amount = $this->chooseAmount($symbol);
        $currency = $this->searchBySymbol($symbol);
        if(empty($currency)) {return;}
        if( ! Ui::question(
            "Are you sure you want to sell " .
            $this->numberFormat($amount) . " " .
            $symbol .
            "?")) {
            return;
        }
        $inClientCurrency = $amount * $currency->getPrice();
        $this->user->takeFromWallet($symbol, $amount);
        $this->user->addToWallet($this->user->getCurrency(), $inClientCurrency);
        $this->db->insertTransaction(
            $this->user->getId(),
            Carbon::now($this->user->getDefaultTimezone())->toDateTimeString(),
            'Sell',
            $amount,
            $symbol,
            $inClientCurrency
        );
    }
    public function showClientWalletStatus(): void
    {
        $wallet = $this->db->selectUserWallet($this->user->getId());
        $content = [];
        foreach ($wallet->getPortfolio() as $key => $amount) {
            $content[] = [
                $key,
                $this->numberFormat($amount),
                count($this->db->selectTransactionsBySymbol($this->user->getId(), $key)),
                $key == 'USD' ? "NaN" : number_format($this->calcProfit($key), 2, '.', '') . "%"
            ];
        }
        Ui::showTable(Wallet::getWalletColumns(), $content, $this->user->getName(), $this->user->getCurrency());
    }
    public function showTransactionHistory(): void
    {
        $transactions = $this->db->selectAllTransactions($this->user->getId());
        if (empty($transactions)) {
            throw new \Exception('Could not find transaction history for ' . $this->user->getName() . "\n");
        }
        $content = array_map(function ($xtr) {
            return [
                $xtr->getTimestamp(),
                $xtr->getAct(),
                $this->numberFormat($xtr->getAmount()),
                $xtr->getSymbol(),
                $this->numberFormat($xtr->getLocalCurrency())
            ];
        }, $transactions);
        Ui::showTable(Transaction::getColumns(), $content, $this->user->getName(), "Transaction History");
    }
    public function calcProfit($currency): float
    {
        $averageBuy = $this->db->selectAvgPrice(
            $this->user->getId(),
            $currency,
            $this->db->selectCurrencySince(
                $this->user->getId(),
                $currency
            )
        );
        $currentPrice = $this->searchBySymbol($currency);
        return (($currentPrice->getPrice() - $averageBuy)/$averageBuy) * 100;
    }
}
