<?php
namespace Ambax\CryptoTrade\Exceptions;
use Exception;
class EmptyFieldsSubmitException extends Exception
{
    public function errorMessage()
    {
        return 'Fields cannot be empty!';
    }
}