<?php
namespace Ambax\CryptoTrade\Database;
use SQLite3;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SqLite
{
    public const DB_DIR = "storage/";
    public function __construct($dbfile)
    {
        $this->sqlite = new SQLite3(self::DB_DIR . $dbfile);
        $this->logger = new Logger('SqLite');
        $this->logger->pushHandler(new StreamHandler('app.log'));
    }
    public function createUser(
        string $id,
        string $name,
        string $currency): void
    {
        $query = "INSERT INTO users (id, name, currency)" .
            "VALUES ('$id', '$name', '$currency')";
        $this->sqlite->exec($query);
        $this->logger->info('Create USer');
    }
    public function selectAllUsers(): array
    {
        $users = [];
        $query = 'SELECT * FROM users';
        $result = $this->sqlite->query($query);
        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'currency' => $row['currency']
            ];
        }
        $this->logger->info('Select all users');
        return $users;
    }
//WALLET
    public function addToWallet(string $id, string $currency, float $amount): void
    {
        $query = "INSERT INTO wallets (id, currency, amount)" .
            "VALUES ('$id', '$currency', $amount)";
        $this->sqlite->exec($query);
        $this->logger->info('Add to wallet exec');
    }
    public function selectUserWallet(string $id): array
    {
        $wallet = [];
        $query = "SELECT * FROM wallets WHERE id = '$id'";
        $result = $this->sqlite->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $wallet [] = [
                'currency' => $row['currency'],
                'amount' => $row['amount']
            ];
        }
        $this->logger->info('Select user wallet');
        return $wallet;
    }
    public function selectAmountByCurrency(string $id, string $currency): ?float
    {
        $this->logger->info('selectAmountByCurrency');
        $query = "SELECT * FROM wallets WHERE id = '$id' AND currency = '$currency'";
        $result = $this->sqlite->query($query);
        if($result !== false) {
            $amount = $result->fetchArray(SQLITE3_ASSOC);
            $amount = $amount['amount'] ?? 0;
        } else {
            $errorMsg = $this->sqlite->lastErrorMsg();
            $errorCode = $this->sqlite->lastErrorCode();
            $this->logger->error("SQLite query failed: $errorMsg (Code: $errorCode)");
        }
        if ($amount > 0) {
            return $amount;
        }
        return null;
    }
    public function updateWallet(string $id, string $currency, float $amount): void
    {
        $query = "UPDATE wallets SET amount = '$amount' WHERE id = '$id' AND currency = '$currency'";
        $this->sqlite->exec($query);
        $this->logger->info('updateWallet');
    }
    public function deleteFromWallet(string $id, string $currency): void
    {
        $query = "DELETE FROM wallets WHERE id = '$id' AND currency = '$currency'";
        $this->sqlite->exec($query);
        $this->logger->info('deleteFromWallet');
    }
//TRANSACTIONS
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
        $this->sqlite->exec($query);
        $this->logger->info('insertTransaction');
    }
    public function selectAllTransactions(string $id): array
    {
        $transactions = [];
        $query = "SELECT * FROM transactions WHERE id = '$id' ORDER BY timestamp DESC";
        $result = $this->sqlite->query($query);
        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $transactions[] = [
                'timestamp' => $row['timestamp'],
                'act' => $row['act'],
                'amount' => $row['amount'],
                'symbol' => $row['symbol'],
                'localCurrency' => $row['localCurrency']
            ];
        }
        $this->logger->info('selectAllTransactions');
        return $transactions;
    }
    public function selectTransactionsBySymbol(string $id, string $symbol): array
    {
        $transactions = [];
        $query = "SELECT * FROM transactions WHERE id = '$id' AND symbol = '$symbol' ORDER BY timestamp DESC";
        $result = $this->sqlite->query($query);
        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $transactions[] = [
                'timestamp' => $row['timestamp'],
                'act' => $row['act'],
                'amount' => $row['amount'],
                'symbol' => $row['symbol'],
                'localCurrency' => $row['localCurrency']
            ];
        }
        $this->logger->info('selectTransactionsBySymbol');
        return $transactions;
    }
}