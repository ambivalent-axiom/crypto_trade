<?php
namespace Ambax\CryptoTrade\database;
interface Database
{
    public function connect($path);
    public function read();
    public function write(array $data);
}