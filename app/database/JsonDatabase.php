<?php
namespace Ambax\CryptoTrade\database;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use stdClass;

class JsonDatabase implements Database
{
    private string $filepath;
    public const DB_DIR = "db/";
    private array $data;
    private Logger $logger;
    public function __construct(array $data = null)
    {
        $this->logger = new Logger('jsonDatabase');
        $this->logger->pushHandler(new StreamHandler('app.log'));
        $this->data = $data ?? null;
    }
    public function connect(string $path): void
    {
        $this->filepath = self::DB_DIR . $path;
        if (file_exists($this->filepath)) {
            $this->logger->info('db ' . $this->filepath . ' previous database loaded.');
        } else {
            fopen($this->filepath, 'w');
            file_put_contents($this->filepath, json_encode($this->data, JSON_PRETTY_PRINT));
            $this->logger->info('db ' . $this->filepath . ' new database created.');
        }
        $this->data = json_decode(file_get_contents($this->filepath));
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