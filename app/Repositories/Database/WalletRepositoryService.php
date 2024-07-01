<?php
namespace Ambax\CryptoTrade\Repositories\Database;
use Ambax\CryptoTrade\Models\Wallet;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class WalletRepositoryService
{
    public function __construct(Logger $logger, Database $db)
    {
        $this->logger = $logger->withName('Database');
        $this->logger->pushHandler(new StreamHandler('app.log'));
        $this->database = $db::set();
    }
    public function addToWallet(
        string $id,
        string $currency,
        float  $amount,
        string $since
    ): void
    {
        $query = "INSERT INTO wallets (id, currency, amount, since)" .
            "VALUES ('$id', '$currency', $amount, '$since')";
        $this->database->exec($query);
        $this->logger->info('Add to wallet exec');
    }
    public function selectAvgPrice(string $id, string $currency, string $since): float
    {
        $query = "SELECT AVG(amount) AS avg_amnt, AVG(localCurrency) AS avg_local " .
            "FROM transactions WHERE id='$id' AND act='Buy' AND symbol='$currency' AND timestamp >= '$since'";
        $result = $this->database->query($query);
        $result = $result->fetchArray(SQLITE3_ASSOC);
        return $result['avg_local'] / $result['avg_amnt'];
    }
    public function selectUserWallet(string $id): Wallet
    {
        $query = "SELECT * FROM wallets WHERE id = '$id'";
        $result = $this->database->query($query);
        $wallet = new Wallet($id);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $wallet->addPortfolio($row['currency'], $row['amount']);
        }
        $this->logger->info('Select user wallet');
        return $wallet;
    }
    public function selectAmountByCurrency(string $id, string $currency): ?float
    {
        $this->logger->info('selectAmountByCurrency');
        $query = "SELECT * FROM wallets WHERE id = '$id' AND currency = '$currency'";
        $result = $this->database->query($query);
        if ($result !== false) {
            $amount = $result->fetchArray(SQLITE3_ASSOC);
            $amount = $amount['amount'] ?? 0;
        } else {
            $errorMsg = $this->database->lastErrorMsg();
            $errorCode = $this->database->lastErrorCode();
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
        $result = $this->database->query($query);
        $since = $result->fetchArray(SQLITE3_ASSOC);
        return $since['since'];
    }
    public function updateWallet(string $id, string $currency, float $amount): void
    {
        $query = "UPDATE wallets SET amount = '$amount' WHERE id = '$id' AND currency = '$currency'";
        $this->database->exec($query);
        $this->logger->info('updateWallet');
    }
    public function deleteFromWallet(string $id, string $currency): void
    {
        $query = "DELETE FROM wallets WHERE id = '$id' AND currency = '$currency'";
        $this->database->exec($query);
        $this->logger->info('deleteFromWallet');
    }
}
