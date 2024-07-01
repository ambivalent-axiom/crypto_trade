<?php
use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Repositories\Api\ApiService;
use Ambax\CryptoTrade\Repositories\Database\SQLite;
use Ambax\CryptoTrade\Services\RepositoryServices\TransactionRepositoryService;
use Ambax\CryptoTrade\Services\RepositoryServices\UserRepositoryService;
use Ambax\CryptoTrade\Services\RepositoryServices\WalletRepositoryService;
use Ambax\CryptoTrade\Services\UserService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('DIconfig');
$logger->pushHandler(new StreamHandler('app.log'));

return function() use ($logger) {
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions([

        Logger::class => new Logger(''),
        StreamHandler::class => new StreamHandler('app.log'),
        UserRepositoryService::class => DI\create(
            UserRepositoryService::class)->constructor(
                DI\get(Logger::class),
                DI\get(StreamHandler::class),
                new SQLite()
        ),
        TransactionRepositoryService::class => DI\create(
            TransactionRepositoryService::class)->constructor(
                DI\get(Logger::class),
                DI\get(StreamHandler::class),
                new SQLite()
        ),
        WalletRepositoryService::class => DI\create(
            WalletRepositoryService::class)->constructor(
            DI\get(Logger::class),
            DI\get(StreamHandler::class),
            new SQLite()
        ),
        UserService::class => DI\create(UserService::class)->constructor(
            'Arthur',
            DI\get(WalletRepositoryService::class),
            DI\get(UserRepositoryService::class),
            '457c48d4-32f1-4b90-8357-251c72f1a607'
        ),
        Api::class => DI\create(ApiService::class)->constructor(
            DI\get(Logger::class),
            DI\get(StreamHandler::class)
        )
    ]);
    return $containerBuilder->build();
};