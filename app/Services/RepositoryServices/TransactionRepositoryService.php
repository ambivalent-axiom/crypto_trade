<?php
namespace Ambax\CryptoTrade\Services\RepositoryServices;
use Ambax\CryptoTrade\Models\Transaction;
use Ambax\CryptoTrade\Repositories\Database\Database;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class TransactionRepositoryService
{
    public function __construct(
        Logger $logger,
        StreamHandler $loggerStreamHandler,
        Database $db
    )
    {
        $this->logger = $logger->withName('TransactionRepositoryService');
        $this->logger->pushHandler($loggerStreamHandler);
        $this->database = $db::set();
    }
    public function insertTransaction(
        string $id,
        string $timestamp,
        string $act,
        float $amount,
        string $symbol,
        float $localCurrency
    ): void
    {
        $query = "INSERT INTO transactions (id, timestamp, act, amount, symbol, localCurrency)" .
            "VALUES ('$id', '$timestamp','$act', '$amount', '$symbol', '$localCurrency')";
        $this->database->exec($query);
        $this->logger->info('insertTransaction');
    }
    public function selectAllTransactions(string $id): array
    {
        $this->logger->info('selectAllTransactions');
        $transactions = [];
        $query = "SELECT * FROM transactions WHERE id = '$id' ORDER BY timestamp DESC";
        $result = $this->database->query($query);
        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $transactions[] = new Transaction(
                    $row['act'],
                    $row['symbol'],
                    $row['amount'],
                    $row['localCurrency'],
                    $row['id'],
                    $row['timestamp']
                );
        }
        return $transactions;
    }
    public function selectTransactionsBySymbol(string $id, string $symbol): array
    {
        $transactions = [];
        $query = "SELECT * FROM transactions WHERE id = '$id' AND symbol = '$symbol' ORDER BY timestamp DESC";
        $result = $this->database->query($query);
        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $transactions[] = new Transaction(
                $row['act'],
                $row['symbol'],
                $row['amount'],
                $row['localCurrency'],
                $row['id'],
                $row['timestamp']
            );
        }
        $this->logger->info('selectTransactionsBySymbol');
        return $transactions;
    }
}