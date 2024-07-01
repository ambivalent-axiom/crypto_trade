<?php
namespace Ambax\CryptoTrade\Repositories\Database;
use SQLite3;
class SQLite implements Database
{
    public const DB_DIR = "storage/";
    public static function set(): SQLite3 {

        return new SQLite3(self::DB_DIR . 'database.sqlite');
    }
}

