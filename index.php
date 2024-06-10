<?php
require_once "vendor/autoload.php";
use Ambax\CryptoTrade\Client;
use Ambax\CryptoTrade\Exchange;
use Ambax\CryptoTrade\Ui;

//init
$art = new Client('Arthur', 'EUR');
$coinMarketCap = new Exchange($art);

while(true) {
    $option = Ui::menu('Main Menu');
    switch ($option) {
        case Ui::getMainMenu()[0]: // list top currencies
            $coinMarketCap->listTop();
            break;
        case Ui::getMainMenu()[1]:  // list search results
            $coinMarketCap->listSearch(strtoupper(readline("Ticking symbol: ")));
            break;
        case Ui::getMainMenu()[2]:  //'buy':
            $coinMarketCap->buy(strtoupper(readline("Symbol: ")), readline("For how many: "));
            break;
        case Ui::getMainMenu()[3]: // sell
            $coinMarketCap->sell();
            break;
        case Ui::getMainMenu()[4]: // show wallet
            $coinMarketCap->showStatus();
            break;
        case Ui::getMainMenu()[5]:  //transaction list
            //TODO add xtr list logic
            break;
        case Ui::getMainMenu()[6]: // exit
            exit;
    }
}


