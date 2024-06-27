<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\Models\Currency;
use Ambax\CryptoTrade\Models\User;
use Ambax\CryptoTrade\Repositories\Paprika;
use Ambax\CryptoTrade\Repositories\CoinMC;
use Ambax\CryptoTrade\Services\SqLite;
use Carbon\Carbon;
use Error;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use TypeError;

class Controller
{
    private User $user;
    private SqLite $db;
    private array $latestUpdate; //array of Currency objects
    public const REQUEST_LIMIT = 100;
    public function __construct() {
        $this->logger = new Logger('Controller');
        $this->logger->pushHandler(new StreamHandler('app.log'));
        $this->db = new SqLite('database.sqlite');
        $this->user = new User('Arthur', $this->db, '457c48d4-32f1-4b90-8357-251c72f1a607');
        try {
            $this->exchangeApi = new CoinMC();
            $this->latestUpdate = $this->exchangeApi->get();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->exchangeApi = new Paprika();
            $this->latestUpdate = $this->exchangeApi->get();
        }
    }
    public function index(): array
    {
        return $this->latestUpdate;
    }
    public function show(): array
    {
        $vars = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
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
    public function buy(): void
    {
        $symbol = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
        $cost = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
        $currency = $this->searchBySymbol($symbol);
        if( ! $currency) {
            throw new Exception('Could not find symbol ' . $symbol . "\n");
        }
        if($cost > $this->db->selectAmountByCurrency(
                $this->user->getId(), $this->user->getCurrency())) {
            throw new Exception('Insufficient wallet balance for this transaction!' . "\n");
        }
        $boughtAmount = $cost/$currency->getPrice();
        try {
            $this->user->takeFromWallet($this->user->getCurrency(), $cost);
        } catch (TypeError $e) {
            throw new Exception('Amount should be numeric and cannot be empty!');
        }


        $this->user->addToWallet($currency->getSymbol(), $boughtAmount);
        $this->db->insertTransaction(
            $this->user->getId(),
            Carbon::now(User::DEFAULT_TIMEZONE)->toDateTimeString(),
            'Buy',
            $boughtAmount,
            $symbol,
            $cost
        );
    }
    public function sell(): void
    {
        $symbol = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
        $amount = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
        $wallet = $this->db->selectUserWallet($this->user->getId());
        $currency = $this->searchBySymbol($symbol);

        if (empty($symbol) || empty($amount)) {
            throw new Exception('Fields cannot be empty!');
        }
        if ($amount <= 0 || ! is_numeric($amount)) {
            throw new Exception('Wrong amount!');
        }
        if ($symbol == 'USD') {
            throw new Exception("Forbidden sell operation with USD!");
        }
        if(empty($currency)) {
            throw new Exception('Could not find symbol ' . $symbol);
        }
        if ( ! in_array($symbol, array_keys($wallet->getPortfolio()))) {
            throw new Exception("You don't have such currency in Your protfolio!");
        }
        if ($wallet->getPortfolio()[$symbol] < $amount) {
            throw new Exception('Insufficient wallet balance for this transaction!');
        }

        $inClientCurrency = $amount * $currency->getPrice();
        $this->user->takeFromWallet($symbol, $amount);
        $this->user->addToWallet($this->user->getCurrency(), $inClientCurrency);
        $this->db->insertTransaction(
            $this->user->getId(),
            Carbon::now(User::DEFAULT_TIMEZONE)->toDateTimeString(),
            'Sell',
            $amount,
            $symbol,
            $inClientCurrency
        );
    }
    private function searchBySymbol($query): ?Currency
    {
        foreach ($this->latestUpdate as $currency) {
            if ($currency->getSymbol() == $query) {
                return new Currency(
                    $currency->getName(),
                    $currency->getSymbol(),
                    $currency->getPrice()
                );
            }
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
