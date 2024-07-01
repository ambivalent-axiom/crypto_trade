<?php
namespace Ambax\CryptoTrade;
class RedirectResponse
{
    private string $url;
    private string $message;
    public function __construct(string $url, string $message)
    {
        $this->url = $url;
        $this->message = $message;
    }
    public function getAddress(): string
    {
        return $this->url;
    }
    public function getMessage(): array
    {
        return ['message' => $this->message, $_SERVER['REQUEST_URI']];
    }
}