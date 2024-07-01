<?php
namespace Ambax\CryptoTrade\Exceptions;
use Exception;
class NoCurrencyInPortfolio extends Exception
{
    public function errorMessage()
    {
        return "You don't have such currency in Your protfolio!";
    }
}