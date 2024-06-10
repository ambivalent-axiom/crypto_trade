<?php
namespace Ambax\CryptoTrade;
use Ambax\CryptoTrade\api\CoinMC;
use Ambax\CryptoTrade\database\JsonDatabase;

class Exchange {
    private Client $client;
    private JsonDatabase $db;
    private CoinMC $exchangeApi;
    private string $latestUpdate;
    private array $tableColumns;
    private const DISPLAY_OFFSET = 0;
    private const DISPLAY_LIMIT = 10;

    public function __construct(Client $client) {
        $this->client = $client;
        $this->db = new JsonDatabase();
        $this->db->connect($this->client->getId());
        try {
            $this->exchangeApi = new CoinMC();
            $this->latestUpdate = $this->exchangeApi->getLatest();
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $this->tableColumns = ['Name', 'Symbol', 'Price ' . $this->client->getCurrency()];
    }
    private function fetchLatestUpdate(): string
    {
        if(isset($this->latestUpdate)) {
            return $this->latestUpdate;
        }
        throw new \Exception("Api Error! Update not found!\n");
    }
    private function searchBySymbol($query): array
    {
        try {
            $latest = json_decode($this->fetchLatestUpdate());
            foreach ($latest->data as $currency) {
                if ($currency->symbol == $query) {
                    return [[
                        'name'   => $currency->name,
                        'symbol' => $currency->symbol,
                        'price'  => $currency->quote->{$this->client->getCurrency()}->price
                    ]];
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return [];
    }
    private function chooseAmount(string $symbol): float
    {
        while(true) {
            $amount = readline("Enter amount or empty for all: ");
            if ($amount == '') {
                return $this->client->getCurrencyAmount($symbol);
            }
            if (is_numeric($amount) && $this->client->getCurrencyAmount($symbol) < $amount) {
                echo "You are unable to cover this transaction!\n";
                continue;
            }
            if (is_numeric($amount)) {
                return $amount;
            }
        }
    }
    public function listTop(): void
    {
        try {
            $latest = json_decode($this->fetchLatestUpdate());
            $limitedData = array_slice($latest->data, self::DISPLAY_OFFSET, self::DISPLAY_LIMIT);
            $rows = array_map(function ($item) {
                return [
                    $item->name,
                    $item->symbol,
                    number_format(
                        $item->quote->{$this->client->getCurrency()}->price,
                        2,
                        '.',
                        ''),
                ];
            }, $limitedData);
            Ui::showTable($this->tableColumns, $rows, "Top Crypto");
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    public function listSearchResults(string $query): void
    {
        $rows = $this->searchBySymbol($query);
        if(empty($rows)) {return;}
        $rows[0]['price'] = number_format($rows[0]['price'],2, '.', '');
        Ui::showTable($this->tableColumns, $rows, "Search By $query");
    }
    public function buy(string $symbol, int $cost): void
    {
        $currency = $this->searchBySymbol($symbol);
        if(empty($currency)) {
            throw new \Exception('Could not find symbol ' . $symbol . "\n");
        }
        if( ! Ui::question("Are you sure you want to proceed with order?")) {
            throw new \Exception('Action aborted ' . $symbol . "\n");
        }
        $symbol = $currency[0]['symbol'];
        $price = $currency[0]['price'];
        $boughtAmount = $cost/$price;
        $this->client->takeFromWallet($this->client->getCurrency(), $cost);
        $this->client->addToWallet($symbol, $boughtAmount);
        $this->client->addTransaction('Buy', $symbol, $boughtAmount, $cost);
        $this->writeDatabase();
    }
    public function sell(): void
    {
        $options = array_map(function ($item) {
            return strtolower($item);
        }, $this->client->getWalletCurrencies());
        try {
            $symbol = strtoupper(Ui::menu('Select currency to sell: ', $options));
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            return;
        }
        echo "In wallet: " . $this->client->getCurrencyAmount($symbol) . "\n";
        $amount = $this->chooseAmount($symbol);
        $currency = $this->searchBySymbol($symbol);
        if(empty($currency)) {return;}
        if( ! Ui::question("Are you sure you want to sell $amount $symbol?")) {
            return;
        }
        $price = $currency[0]['price'];
        $inClientCurrency = $amount * $price;
        $this->client->takeFromWallet($symbol, $amount);
        $this->client->addToWallet($this->client->getCurrency(), $inClientCurrency);
        $this->client->addTransaction('Sell', $symbol, $amount, $inClientCurrency);
        $this->writeDatabase();
    }
    public function showClientWalletStatus(): void
    {
        $columns = $this->client->getWalletColumns();
        $keys = array_keys($this->client->getWallet());
        $content = array_map(function ($key, $item) {
            return [$key, $item];
        }, $keys, $this->client->getWallet());
        Ui::showTable($columns, $content, $this->client->getName(), $this->client->getCurrency());
    }
    public function showTransactionHistory(): void
    {
        if (empty($this->client->getTransactions())) {
            throw new \Exception('Could not find transaction history for ' . $this->client->getName() . "\n");
        }
        $columns = array_keys(get_object_vars($this->client->getTransactions()[0]));
        $content = array_map(function ($xtr) {
            return [
                $xtr->timestamp,
                $xtr->act,
                $xtr->symbol,
                $xtr->amount,
                $xtr->currency,
                $xtr->localCurrency
            ];
        }, $this->client->getTransactions());
        Ui::showTable($columns, $content, $this->client->getName(), "Transaction History");
    }
    public function writeDatabase(): void
    {
        $this->db->write($this->client->jsonSerialize());
    }
}
