<?php
require_once "vendor/autoload.php";
use Ambax\CryptoTrade\Exchange;
use Ambax\CryptoTrade\Ui;

$coinMarketCap = new Exchange();
while(true) {
    $option = Ui::menu('Main Menu');
    switch ($option) {
        case Ui::getMainMenu()[0]: // list top currencies
            $coinMarketCap->listTop();
            break;
        case Ui::getMainMenu()[1]:  // list search results
            $coinMarketCap->listSearchResults(strtoupper(readline("Ticking symbol: ")));
            break;
        case Ui::getMainMenu()[2]:  //'buy':
            try {
                $coinMarketCap->buy(strtoupper(readline("Symbol: ")), readline("For how many: "));
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;
        case Ui::getMainMenu()[3]: // sell
            try {
                $coinMarketCap->sell();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;
        case Ui::getMainMenu()[4]: // show wallet
            $coinMarketCap->showClientWalletStatus();
            break;
        case Ui::getMainMenu()[5]:  //transaction list
            try {
                $coinMarketCap->showTransactionHistory();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;
        case Ui::getMainMenu()[6]: // exit
            exit;
    }
}


