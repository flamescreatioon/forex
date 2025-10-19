<?php

class TradeEngine {
    public function __construct(){
        session_start();
        if(!isset($_SESSION['trades'])){
            $_SESSION['trades'] = [];
        }
    }

    public function openTrade($pair, $type, $price, $lot = 0.1){
        $trade = [
            'id' => uniqid(),
            'pair' => $pair,
            'type' => strtoupper($type),
            'open_price' => $price,
            'lot' => $lot,
            'status' => 'open',
            'open_time' => date('H:i:s'),
        ];
        $_SESSION['trades'][] = $trade;
        return $trade;
    }

    public function closeTrade($tradeId, $currentPrice){
        foreach($_SESSION['trades'] as &$trade){
            if($trade['id'] === $tradeId && $trade['status'] === 'open'){
                $trade['close_price'] = $currentPrice;
                $trade['status'] = 'closed';
                $trade['close_time'] = date('H:i:s');
                $trade['profit'] = $this->calculateProfit($trade, $currentPrice);
                return $trade;
            }
        }
        return null;
    }

    public function calculateProfit($trade, $currentPrice){
      $pipDifference = ($currentPrice - $trade['open_price']) * 10000;
      $pipValue = $trade['lot'] * 10;

      $profit = ($trade['type'] === 'BUY')? $pipDifference * $pipValue : -$pipDifference * $pipValue;
      return round($profit, 2);
    }

    public function getOpenTrades(){
        return array_filter($_SESSION['trades'], fn($t) => $t['status'] === 'open');
    }

    public function getClosedTrades(){
        return array_filter($_SESSION['trades'], fn($t) => $t['status'] === 'closed');
    }
}