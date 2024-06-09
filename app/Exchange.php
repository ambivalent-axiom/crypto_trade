<?php
namespace Ambax\CryptoTrade;
class Exchange {
    private Client $client;
    private JsonDatabase $db;
    private CoinMC $exchangeApi;

    public function __construct(Client $client) {
        $this->client = $client;
        $this->db = new JsonDatabase();
        $this->exchangeApi = new CoinMC();
    }

    public function listTop()
    {
        $latest = json_decode($this->exchangeApi->getLatest());
        $columns = ['Name', 'Symbol', 'Price ' . $this->client->getCurrency()];
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
        }, $latest->data);
        Ui::showTable($columns, $rows, "Top Crypto");
    }



}