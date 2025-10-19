<?php
 require_once __DIR__.'/../helpers/Utils.php';
 require_once __DIR__.'/../helpers/Format.php';
 require_once __DIR__.'/../models/TradeEngine.php';
 require_once __DIR__.'ChartController.php';
 require_once __DIR__.'/../models/TradeModel.php';

 class TradeController{
     private $tradesFile;
     private $engine;
     private $chartController;


     public function __construct(){

        session_start();
         $this->tradesFile = __DIR__.'/../../data/trades.json';
         if(!file_exists($this->tradesFile)){
             file_put_contents($this->tradesFile, json_encode([]));
         }
         $this->engine = new TradeEngine();
         $this->chartController = new ChartController();
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

     public function open(){
        $pair = $_POST['pair']??'EUR/USD';
        $type = $_POST['type']??'BUY';
        $priceData = $this->chartController->getMarketData();

        $trade =$this->engine->openTrade($pair, $type, $priceData['price']);
        echo json_encode(['success'=>true, 'trade'=>$trade]);
     }

     public function close(){
        $tradeId = $_POST['id']??'';
        $priceData = $this->chartController->getMarketData();

        $trade = $this->engine->closeTrade($tradeId, $priceData['price']);
        echo json_encode(['success' => true, 'trade'=>$trade]);
     }

     public function listOpen(){
        header('Content-Type: application/json');
        $trades = $this->engine->getOpenTrades();
        echo json_encode($trades);
     }

     public function closedTrades(){
        $closedTrades = TradeModel::getClosedTrades();
        require_once __DIR__.'/../views/closed_trades.php';
     }
 }