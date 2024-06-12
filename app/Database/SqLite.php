<?php
namespace Ambax\CryptoTrade\Database;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SQLite3;

class SqLite implements Database
{
    public const DB_DIR = "storage/";
    public function __construct(string $filepath)
    {
        $this->sqlite = new SQLite3(self::DB_DIR . $filepath);
        $this->logger = new Logger('SqLite');
        $this->logger->pushHandler(new StreamHandler('app.log'));
    }
    public function connect(string $path)
    {
        // TODO: Implement connect() method.
    }
    public function read()
    {
        // TODO: Implement read() method.
    }
    public function write(array $data)
    {
        // TODO: implement write() method
    }


    public function disconnect()
    {
        return true;
    }
}