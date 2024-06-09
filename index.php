<?php
require_once "vendor/autoload.php";
use Ambax\CryptoTrade\Client;
use Ambax\CryptoTrade\Exchange;
use Ambax\CryptoTrade\Ui;

const MAIN_MENU = [
    'list Top Currencies',
    'search currency by ticker',
    'buy',
    'sell',
    'show wallet',
    'transaction list',
    'exit'
];

//init
$art = new Client('Arthur', 'EUR');
$coinMarketCap = new Exchange($art);

while(true) {
    $option = Ui::menu('Main Menu', MAIN_MENU);
    switch ($option) {
        case 'list Top Currencies':
            $coinMarketCap->listTop();
            break;
        case 'search currency by ticker':
            break;
        case 'buy':
            //TODO add buy logic
            break;
        case 'sell':
            //TODO add sell logic
            break;
        case 'show wallet':
            $art->showStatus();
            break;
        case 'transaction list':
            //TODO add xtr list logic
            break;
        case 'exit':
            exit;
    }
}


