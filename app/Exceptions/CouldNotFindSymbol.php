<?php
namespace Ambax\CryptoTrade\Exceptions;
use Exception;
class CouldNotFindSymbol extends Exception
{
    public function errorMessage()
    {
        return "Could not find symbol!";
    }
}