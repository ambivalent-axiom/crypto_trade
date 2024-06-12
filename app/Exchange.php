<?php
namespace Ambax\CryptoTrade;
use Ambax\CryptoTrade\Api\ApiAdapter;
use Ambax\CryptoTrade\Api\CoinMC;
use Ambax\CryptoTrade\Api\Paprika;
use Ambax\CryptoTrade\Database\Database;
use Ambax\CryptoTrade\Database\JsonDatabase;
use Carbon\Carbon;

class Exchange {
    private Client $client;
    private Database $db;
    private array $latestUpdate; //array of objects
    private array $tableColumns;
    private const DISPLAY_OFFSET = 0;
    private const DISPLAY_LIMIT = 10;

    public function __construct() {
        //client initialization
        $this->client = $this->initClient();
        $this->db = new JsonDatabase($this->client->jsonSerialize());
        $this->db->connect($this->client->getId());
        $this->fillClient($this->db->read()[0]);
        //api initialization
        try {
            $this->exchangeApi = new ApiAdapter(new Paprika(), $this->client->getCurrency());
            $this->latestUpdate = $this->exchangeApi->getLatest();
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        //other stuff
        $this->tableColumns = ['Name', 'Symbol', 'Price ' . $this->client->getCurrency()];
    }
    private function fetchLatestUpdate(): array
    {
        if(isset($this->latestUpdate)) {
            return $this->latestUpdate;
        }
        throw new \Exception("Api Error! Update not found!\n");
    }
    private function searchBySymbol($query): ?Currency
    {
        try {
            foreach ($this->fetchLatestUpdate() as $currency) {
                if ($currency->symbol == $query) {
                    return new Currency(
                        $currency->name,
                        $currency->symbol,
                        $currency->price
                    );
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return null;
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
    private function initClient(): Client
    {
        if($list = Client::getClientList())
        {
            $list['new'] = 0;
            $key = Ui::menu('Select the client: ', array_keys($list));
            if ($key == 'new') {
                return new Client(readline('Enter your name: '));
            }
            return new Client($key, $list[$key]);
        }
        return new Client(readline('Enter your name: '));
    }
    private function fillClient(\stdClass $data): void
    {
        $this->client->setCurrency($data->currency);
        $this->client->setWallet((array) $data->wallet);
        $this->client->setTransactions($data->transactions);
    }
    private function numberFormat(float $number): string
    {
        $formattedNumber = rtrim(sprintf('%.10f', $number), '0');
        $formattedNumber = rtrim($formattedNumber, '.');
        return $formattedNumber;
    }
    public function listTop(): void
    {
        try {
            $limitedData = array_slice($this->fetchLatestUpdate(), self::DISPLAY_OFFSET, self::DISPLAY_LIMIT);
            $rows = array_map(function ($item) {
                return [
                    $item->name,
                    $item->symbol,
                    number_format(
                        $item->price,
                        2,
                        '.',
                        ''),
                ];
            }, $limitedData);
            Ui::showTable($this->tableColumns, $rows, "Top " . self::DISPLAY_LIMIT . " Crypto");
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
    public function listSearchResults(string $query): void
    {
        $coin = $this->searchBySymbol($query);
        if( ! $coin) {return;}
        $coin = [
            $coin->name,
            $coin->symbol,
            number_format($coin->price, 2, '.', ''),
        ];
        Ui::showTable($this->tableColumns, [$coin], "Search By $query");
    }
    public function buy(string $symbol, float $cost): void
    {
        $currency = $this->searchBySymbol($symbol);
        if( ! $currency) {
            throw new \Exception('Could not find symbol ' . $symbol . "\n");
        }
        if($cost > $this->client->getWallet()[$this->client->getCurrency()]) {
            throw new \Exception('Insufficient wallet balance for this transaction!' . "\n");
        }
        if( ! Ui::question("Are you sure you want to proceed with order?")) {
            throw new \Exception('Action aborted ' . $symbol . "\n");
        }
        $boughtAmount = $cost/$currency->price;
        $this->client->takeFromWallet($this->client->getCurrency(), $cost);
        $this->client->addToWallet($currency->symbol, $boughtAmount);
        $this->client->addTransaction(
            'Buy',
            $currency->symbol,
            $this->numberFormat($boughtAmount),
            $this->numberFormat($cost)
        );
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
        if( ! Ui::question(
            "Are you sure you want to sell " .
            $this->numberFormat($amount) . " " .
            $symbol .
            "?")) {
            return;
        }
        $inClientCurrency = $amount * $currency->price;
        $this->client->takeFromWallet($symbol, $amount);
        $this->client->addToWallet($this->client->getCurrency(), $inClientCurrency);
        $this->client->addTransaction(
            'Sell',
            $symbol,
            $this->numberFormat($amount),
            $this->numberFormat($inClientCurrency)
        );
        $this->writeDatabase();
    }
    public function showClientWalletStatus(): void
    {
        $columns = $this->client->getWalletColumns();
        $keys = array_keys($this->client->getWallet());

        $transactions = array_fill_keys($keys, 0);
        foreach ($this->client->getTransactions() as $transaction) {
            if (isset($transactions[$transaction->symbol])) {
                $transactions[$transaction->symbol] += 1;
            }
        }

        $content = array_map(function ($key, $item, $xtrCount): array {
            return [$key, (string) $item, $xtrCount];
        }, $keys, $this->client->getWallet(), $transactions);
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
                Carbon::parse($xtr->timestamp),
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
