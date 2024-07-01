<?php
use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Repositories\Api\CoinMC;
use Ambax\CryptoTrade\Repositories\Api\Paprika;
use Ambax\CryptoTrade\Repositories\Database\UserRepositoryService;
use Ambax\CryptoTrade\Repositories\Database\SQLite;
use Ambax\CryptoTrade\Repositories\Database\TransactionRepositoryService;
use Ambax\CryptoTrade\Repositories\Database\WalletRepositoryService;
use Ambax\CryptoTrade\Services\User;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
$logger = new Logger('DIconfig');
$logger->pushHandler(new StreamHandler('app.log'));

return function() use ($logger) {
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions([

        Logger::class => new Logger(''),

        UserRepositoryService::class => DI\create(
            UserRepositoryService::class)->constructor(
                DI\get(Logger::class),
                new SQLite()
        ),
        TransactionRepositoryService::class => DI\create(
            TransactionRepositoryService::class)->constructor(
                DI\get(Logger::class),
                new SQLite()
        ),
        WalletRepositoryService::class => DI\create(
            WalletRepositoryService::class)->constructor(
            DI\get(Logger::class),
            new SQLite()
        ),
        User::class => DI\create(User::class)->constructor(
            'Arthur',
            DI\get(UserRepositoryService::class),
            DI\get(WalletRepositoryService::class),
            '457c48d4-32f1-4b90-8357-251c72f1a607'
        ),
        Api::class => function() use ($logger) {
            try {
            $exchangeApi = new CoinMC();
            $exchangeApi->get();
        } catch (Exception $e) {
            $logger->error($e->getMessage());
            $exchangeApi = new Paprika();
        }
        return $exchangeApi;
        }

    ]);
    return $containerBuilder->build();
};