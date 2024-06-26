<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\Repositories\ApiAdapter;
use Ambax\CryptoTrade\Models\Currency;
use Ambax\CryptoTrade\Models\User;
use Ambax\CryptoTrade\Services\SqLite;
use Carbon\Carbon;
use Exception;

class Controller
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
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
    public function index(): array
    {
        return $this->latestUpdate;
    }
    public function show(string $vars): array
    {
        return [$this->searchBySymbol($vars)];
    }
    public function status(): array
    {
        $wallet = $this->db->selectUserWallet($this->user->getId());
        $content = [];
        foreach ($wallet->getPortfolio() as $key => $amount) {
            $content[] = [
                'symbol' => $key,
                'amount' => $amount,
                'transactions' => count($this->db->selectTransactionsBySymbol($this->user->getId(), $key)),
                'profit' => $key == 'USD' ? "NaN" : number_format($this->calcProfit($key), 2, '.', '') . "%"
            ];
        }
        return $content;
    }
    public function history(): array
    {
        return $this->db->selectAllTransactions($this->user->getId());
    }
    private function initUser(): User
    {
        return new User('Arthur', $this->db, '457c48d4-32f1-4b90-8357-251c72f1a607');
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
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return null;
    }
    private function calcProfit($currency): float
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
    public function buy(): void
    {
        $symbol = strtoupper($_POST['symbol']);
        $cost = $_POST['amount'];
        $currency = $this->searchBySymbol($symbol);
        if( ! $currency) {
            throw new Exception('Could not find symbol ' . $symbol . "\n");
        }
        if($cost > $this->db->selectAmountByCurrency(
            $this->user->getId(), $this->user->getCurrency())) {
            throw new Exception('Insufficient wallet balance for this transaction!' . "\n");
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
        $symbol = strtoupper($_POST['symbol']);
        $amount = $_POST['amount'];
        $currency = $this->searchBySymbol($symbol);
        if(empty($currency)) {return;}
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
}
