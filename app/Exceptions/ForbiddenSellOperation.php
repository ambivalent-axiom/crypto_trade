<?php
namespace Ambax\CryptoTrade\Exceptions;
use Exception;
class ForbiddenSellOperation extends Exception
{
    public function errorMessage()
    {
        return "Forbidden sell operation!";
    }
}