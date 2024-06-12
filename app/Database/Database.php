<?php
namespace Ambax\CryptoTrade\Database;
interface Database
{
    public function connect(string $path);
    public function read();
    public function write(array $data);
}