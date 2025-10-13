<?php

require_once __DIR__.'/../app/controllers/ChartController.php';
require_once __DIR__.'/../app/controllers/TradeController.php';

$route = $_GET['route']??'';

switch ($route){
    case 'chart/data':
        (new ChartController())->getMarketData();
        break;
    case 'chart/update':
        (new ChartController())->updateMarket();
        break;

    case 'trade/open':
        (new TradeController())->openTrade();
        break;

    case 'trade/all':
        (new TradeController())->getTrades();
        break;

    default:
       include __DIR__.'../public/index.php';
        break;
}