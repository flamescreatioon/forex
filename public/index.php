<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= APP_NAME ?> Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
    <h1><?= APP_NAME ?></h1>
    <div>Simulated Forex Dashboard</div>
</header>

<div class="container">
    <div class="chart-container">
        <canvas id="chart" height="250"></canvas>
        <h2>EUR/USD: <span class="price" id="price">--</span></h2>
    </div>

    <canvas id="priceChart" height="400"></canvas>

    <div class="info-panel">
        <h2>Account Summary</h2>
        <p><strong>Balance:</strong> $10,000.00</p>
        <p><strong>Profit:</strong> <span id="profit">$0.00</span></p>

        <div class="trade-list">
            <h2>Open Trades</h2>
            <button id="openTrade">Open New Trade</button>
            <ul id="trades"></ul>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>

<script>
document.getElementById('openTrade').addEventListener('click', async () => {
    const res = await fetch('../routes/web.php?route=trade/open');
    const data = await res.json();
    const li = document.createElement('li');
    li.textContent = `${data.symbol} opened at ${data.entry_price}`;
    document.getElementById('trades').appendChild(li);
});
</script>
</body>
</html>
