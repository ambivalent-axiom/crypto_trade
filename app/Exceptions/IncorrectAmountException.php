<?php
namespace Ambax\CryptoTrade\Exceptions;
use Exception;
class IncorrectAmountException extends Exception
{
    public function errorMessage()
    {
        return "Wrong amount!";
    }
}