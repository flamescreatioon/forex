<?php

require_once __DIR__.'/../services/marketData.php';


class ChartController{
   private $market;
   public function __construct(){
       $this->market = new MarketData('EUR/USD', 1.10000);
   }

   public function getMarketData(){
       header('Content-Type: application/json');
       echo json_encode($this->market->getMarketData());
   }

   public function updateMarket()
   {
    header('Content-Type: application/json');
    echo json_encode($this->market->updateMarket());
   }
}