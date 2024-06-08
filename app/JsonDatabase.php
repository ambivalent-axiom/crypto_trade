<?php
namespace Ambax\CryptoTrade;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class JsonDatabase implements Database
{
    private string $filepath;
    private array $data;
    private Logger $logger;
    public function __construct()
    {
        $this->logger = new Logger('jsonDatabase');
        $this->logger->pushHandler(new StreamHandler('warehouse.log'));
    }
    public function connect($path): void
    {
        $this->filepath = "db/" . $path;
        if (file_exists($this->filepath)) {
            $this->data = json_decode(file_get_contents($this->filepath));
            $this->logger->info('db ' . $this->filepath . ' load success.');
        } else {
            $this->data = [];
            $this->logger->info('db ' . $this->filepath . ' load fail. Empty array returned.');
        }
    }
    public function read(): array
    {
        $this->logger->info('db ' . $this->filepath . ' read success.');
        return $this->data;
    }
    public function write(array $data): void
    {
        file_put_contents($this->filepath, json_encode($data, JSON_PRETTY_PRINT));
        $this->logger->info('db ' . $this->filepath . ' write success.');
    }
}