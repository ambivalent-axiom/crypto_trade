<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Repositories\Database\SqLite;
use Ambax\CryptoTrade\Services\Currency;
use Ambax\CryptoTrade\Services\User;
use Carbon\Carbon;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use TypeError;

class Controller
{
    private User $user;
    private SqLite $db;
    private array $latestCurrencyUpdate;
    public const REQUEST_LIMIT = 100;
    public function __construct(Logger $logger, SqLite $sqLite, User $user, Api $api) {
        $this->logger = $logger->withName('Controller');
        $this->logger->pushHandler(new StreamHandler('app.log'));
        $this->db = $sqLite;
        $this->user = $user;
        $this->exchangeApi = $api;
        $this->latestCurrencyUpdate = $this->exchangeApi->get();
    }
    public function index(): array
    {
        return $this->latestCurrencyUpdate;
    }
    public function show(): array
    {
        $vars = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
        return [Currency::searchBySymbol($vars, $this->latestCurrencyUpdate)];
    }
    public function status(): array
    {
        $wallet = $this->db->selectUserWallet($this->user->getId());
        $content = [];
        foreach ($wallet->getPortfolio() as $key => $amount) {
            try {
                $content[] = [
                    'symbol' => $key,
                    'amount' => $amount,
                    'transactions' => count($this->db->selectTransactionsBySymbol($this->user->getId(), $key)),
                    'profit' => $key == 'USD' ? "NaN" : number_format(
                            $this->user->calcProfit($key, $this->latestCurrencyUpdate),
                            2,
                            '.',
                            '') . "%"
                ];
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
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
        $currency = Currency::searchBySymbol($symbol, $this->latestCurrencyUpdate);
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
        $currency = Currency::searchBySymbol($symbol, $this->latestCurrencyUpdate);

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
}
