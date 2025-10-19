<?php

// TradeModel: simple file-backed trade strorage + P/L calculations
// Requires: app/services/MarketData.php

require_once __DIR__.'/../services/MarketData.php';

class TradeModel{
    private $storageFile;
    private $market;
    private static $closedTrades = [];

    public function __construct($storageFile = null){
        $this->storageFile = $storageFile ?? __DIR__.'/../../data/trades.json';
        if(!file_exists($this->storageFile)){
            file_put_contents($this->storageFile, json_encode([]));
        }
        $this->market = new MarketData('EUR/USD', 1.10000);
    }

    private function readAllTrades():array{
        $json = @file_get_contents($this->storageFile);
        $arr = json_decode($json, true);
        if(!is_array(arr)) return[];
        return $arr;
    }

    private function writeAllTrades(array $trades){
        file_put_contents($this->storageFile, json_encode(array_values($trades), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    public function getLastMarketTick():array{
        $data = $this->market->getMarketData();
        if(empty($data)){
            $tick = $this->market->updateMarket();
            $tick['price'] = floatval($tick['price']);
            return $tick;
        }

        $last = end($data);
        $last['price'] = floatval($last['price']);
        return $last;
    }

    public static function closeTrade($tradeId){
        $trade = self::findTradeById($tradeId);
        if($trade){
            $trade['status'] = 'closed';
            $trade['close_time'] = date('Y-m-d H:i:s');
            $trade['profit'] = rand(50,500);
            self::$closedTrades[] = $trade;
            unset(self::$trades[$tradeId]);
        }
    }

    public static function getClosedTrades(){
        return self::$closedTrades;
    }
}