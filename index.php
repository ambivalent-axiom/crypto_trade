<?php
require_once "vendor/autoload.php";
use Ambax\CryptoTrade\CoinMC;
use Ambax\CryptoTrade\Client;
use Ambax\CryptoTrade\Exchange;


//$c = new CoinMC();
//$a = json_decode($c->getLatest());
//
//foreach ($a->data as $coin) {
//    echo $coin->quote->EUR->price . "\n";
//}

$art = new Client('Arthur', 'EUR');
$art->showStatus();

$coinMarketCap = new Exchange($art);
$coinMarketCap->listTop();
