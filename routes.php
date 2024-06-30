<?php
return [
    ['GET', '/', [Ambax\CryptoTrade\Controllers\Controller::class, 'index']],
    ['GET', '/wallet', [Ambax\CryptoTrade\Controllers\Controller::class, 'status']],
    ['GET', '/hist', [Ambax\CryptoTrade\Controllers\Controller::class, 'history']],
    ['POST', '/show', [Ambax\CryptoTrade\Controllers\Controller::class, 'show']],
    ['POST','/', [Ambax\CryptoTrade\Controllers\Controller::class, 'buy']],
    ['POST','/wallet', [Ambax\CryptoTrade\Controllers\Controller::class, 'sell']]
];








