<?php
namespace Ambax\CryptoTrade\Repositories\Database;
use Ambax\CryptoTrade\Models\Transaction;
use Ambax\CryptoTrade\Models\Wallet;
use Ambax\CryptoTrade\Services\User;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SQLite3;

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
        $this->logger->info('Create User');
        $query = "INSERT INTO users (id, name, currency)" .
            "VALUES ('$id', '$name', '$currency')";
        $this->sqlite->exec($query);
    }
    public function setUserPass(string $id, string $pass): void
    {
        $query = "UPDATE users SET password = '$pass' WHERE id = '$id'";
        $this->sqlite->exec($query);
    }
    public function selectAllUsers(): array
    {
        $this->logger->info('Select all users');
        $users = [];
        $query = 'SELECT * FROM users';
        $result = $this->sqlite->query($query);
        while($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $users[] = new User(
                $row['name'],
                $this, $row['id'],
                $row['currency'],
                $row['password']
            );
        }
        return $users;
    }
//WALLET
    public function addToWallet(
        string $id,
        string $currency,
        float $amount,
        string $since
    ): void
    {
        $query = "INSERT INTO wallets (id, currency, amount, since)" .
            "VALUES ('$id', '$currency', $amount, '$since')";
        $this->sqlite->exec($query);
        $this->logger->info('Add to wallet exec');
    }
    public function selectAvgPrice(string $id, string $currency, string $since): float
    {
        $query = "SELECT AVG(amount) AS avg_amnt, AVG(localCurrency) AS avg_local " .
            "FROM transactions WHERE id='$id' AND act='Buy' AND symbol='$currency' AND timestamp >= '$since'";
        $result = $this->sqlite->query($query);
        $result = $result->fetchArray(SQLITE3_ASSOC);
        return $result['avg_local']/$result['avg_amnt'];
    }
    public function selectUserWallet(string $id): Wallet
    {
        $query = "SELECT * FROM wallets WHERE id = '$id'";
        $result = $this->sqlite->query($query);
        $wallet = new Wallet($id);
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $wallet->addPortfolio($row['currency'], $row['amount']);
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
    public function selectCurrencySince(string $id, string $currency): string
    {
        $this->logger->info('selectCurrencySince');
        $query = "SELECT * FROM wallets WHERE id = '$id' AND currency = '$currency'";
        $result = $this->sqlite->query($query);
        $since  = $result->fetchArray(SQLITE3_ASSOC);
        return $since['since'];
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
        $this->logger->info('selectAllTransactions');
        $transactions = [];
        $query = "SELECT * FROM transactions WHERE id = '$id' ORDER BY timestamp DESC";
        $result = $this->sqlite->query($query);
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
        $result = $this->sqlite->query($query);
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