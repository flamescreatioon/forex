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
        echo "<h3>Welcome to Forex Simulator API</h3>";
        echo "<ul>
        <li>/routes/web.php?route=chart/data - get Market Data </li>
        <li>/routes/web.php?route=chart/update - Generate Next Tick </li>
        <li>>/routes/web.php?route=trade/open — Open a Trade</li>
        <li>/routes/web.php?route=trade/all — Get All Trades</li>
        </ul>";
        break;
}