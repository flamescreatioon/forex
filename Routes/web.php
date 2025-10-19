<?php

require_once __DIR__.'/../app/controllers/ChartController.php';
require_once __DIR__.'/../app/controllers/TradeController.php';
require_once __DIR__.'/../app/controllers/AccountController.php';

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
    
    case 'account/live':
        (new AccountController())->getLiveAccountData();
        break;

    case '/trade/open':
        (new TradeController())->open();
        break;

    case '/trade/close':
        (new TradeController())->close();
        break;

    case '/trade/list':
        (new TradeController())->listOpen();

    case '/closed-trades':
        (new TradeController())->closedTrades();
        break;

    default:
       include __DIR__.'../public/index.php';
        break;
}