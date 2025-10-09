<?php

require_once __DIR__.'/PriceGenerator.php';

class MarketData{
    private $dataFile;
    private $priceGenerator;

    public function __construct($symbol = 'EUR/USD', $startPrice = 1.1000){
        $this->dataFile = __DIR__.'/../../data/fake_market.json';
        $this->priceGenerator = new PriceGenerator($symbol, $startPrice);
    }

    public function updateMarket(){
        $tick = $this->priceGenerator->generateNext();

        $allData = [];
        if(file_exists($this->dataFile)){
            $allData = json_decode(file_get_contents($this->dataFile), true);
        }
        $allData[] = $tick;
        if(count($allData) > 100){
            array_shift($allData);
        }

        file_put_contents($this->dataFile, json_encode($allData, JSON_PRETTY_PRINT));
        return $tick;
    }

    public function getMarketData(){
        if(!file_exists($this->dataFile)){
            return [];
        }

        return json_decode(file_get_contents($this->dataFile), true);
    }
}