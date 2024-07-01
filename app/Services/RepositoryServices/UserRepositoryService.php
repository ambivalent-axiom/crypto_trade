<?php
namespace Ambax\CryptoTrade\Services\RepositoryServices;
use Ambax\CryptoTrade\Repositories\Database\Database;
use Ambax\CryptoTrade\Services\UserService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class UserRepositoryService
{
    public function __construct(
        Logger $logger,
        StreamHandler $loggerStreamHandler,
        Database $db

    )
    {
        $this->logger = $logger->withName('Database');
        $this->logger->pushHandler($loggerStreamHandler);
        $this->database = $db::set();
    }
    public function createUser(
        string $id,
        string $name,
        string $currency): void
    {
        $this->logger->info('Create User');
        $query = "INSERT INTO users (id, name, currency)" .
            "VALUES ('$id', '$name', '$currency')";
        $this->database->exec($query);
    }

    public function setUserPass(string $id, string $pass): void
    {
        $query = "UPDATE users SET password = '$pass' WHERE id = '$id'";
        $this->database->exec($query);
    }

    public function selectAllUsers(): array
    {
        $this->logger->info('Select all users');
        $users = [];
        $query = 'SELECT * FROM users';
        $result = $this->database->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $users[] = new UserService(
                $row['name'],
                $this, $row['id'],
                $row['currency'],
                $row['password']
            );
        }
        return $users;
    }
}