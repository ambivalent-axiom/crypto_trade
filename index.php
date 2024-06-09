<?php
require_once "vendor/autoload.php";
use Ambax\CryptoTrade\Client;
use Ambax\CryptoTrade\Exchange;
use Ambax\CryptoTrade\Ui;

const MAIN_MENU = [
    'list Top',
    'find by ticker',
    'buy',
    'sell',
    'wallet',
    'x-actions',
    'exit'
];

//init
$art = new Client('Arthur', 'EUR');
$coinMarketCap = new Exchange($art);

while(true) {
    $option = Ui::menu('Main Menu', MAIN_MENU);
    switch ($option) {
        case MAIN_MENU[0]: // list top currencies
            $coinMarketCap->listTop();
            break;
        case MAIN_MENU[1]:  // list search results
            $coinMarketCap->listSearch(strtoupper(readline("Ticking symbol: ")));
            break;
        case MAIN_MENU[2]:  //'buy':
            $coinMarketCap->buy(strtoupper(readline("Symbol: ")), readline("For how many: "));
            break;
        case MAIN_MENU[3]: // sell
            $coinMarketCap->sell();
            break;
        case MAIN_MENU[4]: // show wallet
            $coinMarketCap->showStatus();
            break;
        case MAIN_MENU[5]:  //transaction list
            //TODO add xtr list logic
            break;
        case MAIN_MENU[6]: // exit
            exit;
    }
}


