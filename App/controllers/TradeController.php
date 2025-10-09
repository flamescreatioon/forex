<?php
 require_once __DIR__.'/../helpers/Utils.php';
 require_once __DIR__.'/../helpers/Format.php';

 class TradeController{
     private $tradesFile;

     public function __construct(){
         $this->tradesFile = __DIR__.'/../../data/trades.json';
         if(!file_exists($this->tradesFile)){
             file_put_contents($this->tradesFile, json_encode([]));
         }
     }

     public function openTrade($symbol = "EUR/USD", $lot=1)
     {
       $trade = [
           'id' => uniqid('trade_'),
           'symbol' => $symbol,
           'lot'=>$lot,
           'entry_price'=>randomFloat(1.1000, 1.1500),
           'status'=>'open',
           'timestamp'=>now()
       ];

       $trades = json_decode(file_get_contents($this->tradesFile), true);
       $trades[] = $trade;
       file_get_contents($this->tradesFile, json_encode($trades, JSON_PRETTY_PRINT));

       header('Content-Type: application/json');
       echo json_encode($trade);
     }

     public function getTrades(){
         header('Content-Type: application/json');
         echo file_get_contents($this->tradesFile);
     }
 }