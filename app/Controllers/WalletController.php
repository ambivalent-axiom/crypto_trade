<?php
namespace Ambax\CryptoTrade\Controllers;
use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Response;
use Ambax\CryptoTrade\Services\RepositoryServices\TransactionRepositoryService;
use Ambax\CryptoTrade\Services\RepositoryServices\WalletRepositoryService;
use Ambax\CryptoTrade\Services\UserService;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class WalletController
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
    public function status(): Response
    {
        $wallet = $this->walletRepository->selectUserWallet($this->user->getId());
        $content = [];
        foreach ($wallet->getPortfolio() as $key => $amount) {
            try {
                $content[] = [
                    'symbol' => $key,
                    'amount' => $amount,
                    'transactions' => count($this->transactionRepository->selectTransactionsBySymbol(
                        $this->user->getId(),
                        $key
                    )),
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
        return new Response(['items' => $content], 'status');
    }
}