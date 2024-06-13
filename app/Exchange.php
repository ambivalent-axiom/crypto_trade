<?php
namespace Ambax\CryptoTrade;
use Ambax\CryptoTrade\Api\ApiAdapter;
use Ambax\CryptoTrade\Database\SqLite;
use Carbon\Carbon;

class Exchange {
    private Client $client;
    private SqLite $db;
    private array $latestUpdate; //array of objects
    private array $tableColumns;
    private const DISPLAY_OFFSET = 0;
    private const DISPLAY_LIMIT = 10;

    public function __construct() {
        //client initialization
        $this->db = new SqLite('database.sqlite');
        $this->client = $this->initClient();
        //api initialization
        try {
            $this->exchangeApi = new ApiAdapter($this->client->getCurrency());
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
                if ($currency->getSymbol() == $query) {
                    return new Currency(
                        $currency->getName(),
                        $currency->getSymbol(),
                        $currency->getPrice()
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
        if($list = Client::getClientList($this->db->selectAllUsers()))
        {
            $keys = [];
            foreach ($list as $item) {
                $keys[] = $item['name'];
            }
            $keys[] = 'new';
            $key = Ui::menu('Select the client: ', $keys);
            foreach ($list as $user) {
                if ($user['name'] === $key) {
                    $id = $user['id'];
                }
            }
            if ($key !== 'new') {
                $user = new Client($key, $this->db, $id);
                return $user;
            }
        }
        $user = new Client(readline('Enter your name: '), $this->db);
        $this->db->createUser($user->getId(), $user->getName(), $user->getCurrency());
        $this->db->addToWallet($user->getId(), $user->getCurrency(), Client::getDefaultWallet());
        return $user;
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
                    $item->getName(),
                    $item->getSymbol(),
                    number_format(
                        $item->getPrice(),
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
            $coin->getName(),
            $coin->getSymbol(),
            number_format($coin->getPrice(), 2, '.', ''),
        ];
        Ui::showTable($this->tableColumns, [$coin], "Search By $query");
    }
    public function buy(string $symbol, float $cost): void
    {
        $currency = $this->searchBySymbol($symbol);
        if( ! $currency) {
            throw new \Exception('Could not find symbol ' . $symbol . "\n");
        }
        if($cost > $this->db->selectAmountByCurrency(
            $this->client->getId(), $this->client->getCurrency())) {
            throw new \Exception('Insufficient wallet balance for this transaction!' . "\n");
        }
        if( ! Ui::question("Are you sure you want to proceed with order?")) {
            throw new \Exception('Action aborted ' . $symbol . "\n");
        }
        $boughtAmount = $cost/$currency->getPrice();
        $this->client->takeFromWallet($this->client->getCurrency(), $cost);

        $this->client->addToWallet($currency->getSymbol(), $boughtAmount);
        $this->db->insertTransaction(
            $this->client->getId(),
            Carbon::now($this->client->getDefaultTimezone())->toDateTimeString(),
            'Buy',
            $boughtAmount,
            $symbol,
            $cost
        );
    }
    public function sell(): void
    {
        $options = $this->client->getWalletCurrencies();
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
        $inClientCurrency = $amount * $currency->getPrice();

        $this->client->takeFromWallet($symbol, $amount);


        $this->client->addToWallet($this->client->getCurrency(), $inClientCurrency);
        $this->db->insertTransaction(
            $this->client->getId(),
            Carbon::now($this->client->getDefaultTimezone())->toDateTimeString(),
            'Sell',
            $amount,
            $symbol,
            $inClientCurrency
        );
    }
    public function showClientWalletStatus(): void
    {
        $wallet = $this->db->selectUserWallet($this->client->getId());
        $columns = $this->client->getWalletColumns();
        $keys = [];
        foreach ($wallet as $item) {
            $keys[] = $item['currency'];
        }
        $xtrCount = [];
        foreach ($keys as $key) {
            $xtrCount[] = [$key => count($this->db->selectTransactionsBySymbol($this->client->getId(), $key))];
        }
        $content = [];
        foreach ($wallet as $index => $currency) {
            $key = $keys[$index];
            $sum = $currency['amount'];
            $count = $xtrCount[$index][$key];
            $content[] = [$key, $sum, $count];
        }
        Ui::showTable($columns, $content, $this->client->getName(), $this->client->getCurrency());
    }
    public function showTransactionHistory(): void
    {
        $transactions = $this->db->selectAllTransactions($this->client->getId());
        if (empty($transactions)) {
            throw new \Exception('Could not find transaction history for ' . $this->client->getName() . "\n");
        }
        $columns = [
            'timestamp',
            'act',
            'amount',
            'symbol',
            'localCurrency'
        ];
        $content = array_map(function ($xtr) {
            return [
                $xtr['timestamp'],
                $xtr['act'],
                $xtr['amount'],
                $xtr['symbol'],
                $xtr['localCurrency']
            ];
        }, $transactions);
        Ui::showTable($columns, $content, $this->client->getName(), "Transaction History");
    }
}
