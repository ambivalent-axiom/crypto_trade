<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\RedirectResponse;
use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Response;
use Ambax\CryptoTrade\Services\CurrencyService;
use Ambax\CryptoTrade\Services\RepositoryServices\TransactionRepositoryService;
use Ambax\CryptoTrade\Services\RepositoryServices\WalletRepositoryService;
use Ambax\CryptoTrade\Services\UserService;
use Carbon\Carbon;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use TypeError;

class TransactionController
{
    private UserService $user;
    private array $latestCurrencyUpdate;
    public function __construct(
        Logger                       $logger,
        StreamHandler                $loggerStreamHandler,
        TransactionRepositoryService $transactionRepository,
        WalletRepositoryService      $walletRepository,
        UserService                  $user,
        Api                          $api
    )
    {
        $this->logger = $logger->withName('Controller');
        $this->logger->pushHandler($loggerStreamHandler);
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->user = $user;
        $this->exchangeApi = $api;
        $this->latestCurrencyUpdate = $this->exchangeApi->get();
    }
    public function history(): Response
    {
        return new Response(['records' => $this->transactionRepository->selectAllTransactions($this->user->getId())], 'history');
    }
    public function buy(): RedirectResponse
    {
        $symbol = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
        $cost = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
        $currency = CurrencyService::searchBySymbol($symbol, $this->latestCurrencyUpdate);
        if( ! $currency) {
            throw new Exception('Could not find symbol ' . $symbol . "\n");
        }
        if($cost > $this->walletRepository->selectAmountByCurrency(
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
        $this->transactionRepository->insertTransaction(
            $this->user->getId(),
            Carbon::now(UserService::DEFAULT_TIMEZONE)->toDateTimeString(),
            'Buy',
            $boughtAmount,
            $symbol,
            $cost
        );
        return new RedirectResponse('/notify', 'Buy operation completed!');
    }
    public function sell(): RedirectResponse
    {
        $symbol = htmlspecialchars(strtoupper($_POST['symbol']), ENT_QUOTES, 'UTF-8');
        $amount = htmlspecialchars($_POST['amount'], ENT_QUOTES, 'UTF-8');
        $wallet = $this->walletRepository->selectUserWallet($this->user->getId());
        $currency = CurrencyService::searchBySymbol($symbol, $this->latestCurrencyUpdate);

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
        $this->transactionRepository->insertTransaction(
            $this->user->getId(),
            Carbon::now(UserService::DEFAULT_TIMEZONE)->toDateTimeString(),
            'Sell',
            $amount,
            $symbol,
            $inClientCurrency
        );
        return new RedirectResponse('/notify', 'Sell operation completed!');
    }
}
