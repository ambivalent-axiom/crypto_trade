<?php
namespace Ambax\CryptoTrade;
class Response
{
    private array $data;
    private string $address;
    public function __construct(array $data, string $address)
    {
        $this->data = $data;
        $this->address = $address;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function getAddress(): string
    {
        return $this->address;
    }
}