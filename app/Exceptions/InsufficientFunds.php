<?php
namespace Ambax\CryptoTrade\Exceptions;
use Exception;
class InsufficientFunds extends Exception
{
    public function errorMessage()
    {
        return 'Insufficient wallet balance for this transaction!';
    }
}