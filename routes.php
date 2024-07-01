<?php
return [
    ['GET', '/', [Ambax\CryptoTrade\Controllers\CurrencyController::class, 'index']],
    ['GET', '/wallet', [Ambax\CryptoTrade\Controllers\WalletController::class, 'status']],
    ['GET', '/hist', [Ambax\CryptoTrade\Controllers\TransactionController::class, 'history']],
    ['POST', '/show', [Ambax\CryptoTrade\Controllers\CurrencyController::class, 'show']],
    ['POST','/', [Ambax\CryptoTrade\Controllers\TransactionController::class, 'buy']],
    ['POST','/wallet', [Ambax\CryptoTrade\Controllers\TransactionController::class, 'sell']]
];








