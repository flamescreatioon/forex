<?php

require_once __DIR__.'/../config/config.php';
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1><?=APP_NAME ?></h1>
    <div class="account-summary">
        <div class="card">
            <h3>Balance</h3>
            <p id="balance">$10000.00</p>
        </div>
        <div class="card">
            <h3>Profit</h3>
            <p id="profit">$0.00</p>
        </div>
        <div class="card">
            <h3>Equity</h3>
            <p id="equity">$10000.00</p>
        </div>
    </div>

    <canvas id="priceChart" height="400"></canvas>

    <!-- After chart section -->
     <div class="trade-controls">
        <button onclick="openTrade('BUY')">Buy</button>
        <button onclick="openTrade('SELL')">Sell</button>
     </div>
    
     <h2>Open Trades</h2>
    <table id="openTrades">
        <thead>
            <tr>
                <th>Pair</th>
                <th>Type</th>
                <th>Open Price</th>
                <th>Lot</th>
                <th>Time</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>





    <script src="/public/js/chart.js"></script>
    <script src="/public/js/account.js"></script>
</body>
</html>