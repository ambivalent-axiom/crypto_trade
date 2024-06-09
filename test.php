<?php
require_once "vendor/autoload.php";
use Ambax\CryptoTrade\api\CoinMC;

$api = new CoinMC();

echo $api->getBySymbol();
