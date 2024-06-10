<?php
namespace Ambax\CryptoTrade\database;
interface Database
{
    public function connect(string $path);
    public function read();
    public function write(array $data);
}