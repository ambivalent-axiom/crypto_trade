<?php

use Ambax\CryptoTrade\Repositories\Api\Api;
use Ambax\CryptoTrade\Repositories\Api\CoinMC;
use Ambax\CryptoTrade\Repositories\Api\Paprika;
use Ambax\CryptoTrade\Repositories\Database\SqLite;
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
        SqLite::class => new SqLite('database.sqlite'),
        User::class => DI\create(User::class)->constructor('Arthur', DI\get(SqLite::class), '457c48d4-32f1-4b90-8357-251c72f1a607'),
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