<?php
namespace Ambax\CryptoTrade;
use Ambax\CryptoTrade\api\CoinMC;
use Ambax\CryptoTrade\database\JsonDatabase;

class Exchange {
    private Client $client;
    private JsonDatabase $db;
    private CoinMC $exchangeApi;
    private string $latestUpdate;
    private const DISPLAY_OFFSET = 0;
    private const DISPLAY_LIMIT = 10;

    public function __construct(Client $client) {
        $this->client = $client;
        $this->db = new JsonDatabase();
        $this->exchangeApi = new CoinMC();
        $this->latestUpdate = $this->exchangeApi->getLatest();
    }

    public function listTop(): void
    {
        $latest = json_decode($this->latestUpdate);
        $columns = ['Name', 'Symbol', 'Price ' . $this->client->getCurrency()];
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
        Ui::showTable($columns, $rows, "Top Crypto");
    }
    public function searchBySymbol(): void
    {
        $latest = json_decode($this->latestUpdate);
        $columns = ['Name', 'Symbol', 'Price ' . $this->client->getCurrency()];
        $query = strtoupper(readline("Ticking symbol: "));
        foreach ($latest->data as $currency) {
            if ($currency->symbol == $query) {
                $rows = [[
                    $currency->name,
                    $currency->symbol,
                        number_format(
                            $currency->quote->{$this->client->getCurrency()}->price,
                            2,
                            '.',
                            ''),
                    ]];
                break;
            } else {
                $rows = [];
            }
        }
        Ui::showTable($columns, $rows, "Search By $query");
    }
}