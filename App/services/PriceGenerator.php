<?php
require_once __DIR__.'/../helpers/Utils.php';
require_once __DIR__.'/../helpers/Format.php';

class PriceGenerator{
    private $symbol;
    private $currentPrice;

    public function __construct($symbol = 'EUR/USD', $startPrice = 1.100){
        $this->symbol = $symbol;
        $this->currentPrice = $startPrice;
    }

    //Generate next price tick based on random movement

    public function generateNext(){
        $change = randomFloat(-0.0005, 0.0005, 5);
        $this->currentPrice+=$change;

        if($this->currentPrice < 1.00000) $this->currentPrice = 1.0000;
        if($this->currentPrice > 1.50000) $this->currentPrice = 1.5000;

        return[
            'symbol' => $this->symbol,
            'price' => formatPrice($this->currentPrice),
            'change'=> formatPrice($change),
            'timestamp' => now()
        ];
    }
}