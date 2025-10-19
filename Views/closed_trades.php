<div class="container">
    <h2>Closed Trades</h2>
    <table>
        <tr><th>Pair</th><th>Profit</th><th>Closed At</th></tr>
        <?php foreach ($closedTrades as $trade): ?>
            <tr>
                <td><?= $trade['pair'] ?></td>
                <td style="color: <?= $trade['profit']> 0 ? 'green' :'red' ?>;">
                    $<?= $trade['profit'] ?>
            </td>
            <td><?=$trade['close_time'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <h3>Analytics</h3>
    <p>Total Profit: $<?= array_sum(array_column($closedTrades, 'profit')) ?></p>
    <p>Win Rate: 
        <?php 
            $wins = count(array_filter($closedTrades, fn($t) => $t['profit'] > 0));
            $total = count($closedTrades);
            echo $total > 0 ? round(($wins / $total) * 100, 2) . '%' : 'N/A';
        ?>
    </p>

    <canvas id="profitChart"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('profitChart');
    const profits = <?= json_encode(array_column($closedTrades, 'profit')) ?>;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: profits.map((_, i) => 'trade ' + (i + 1)),
            datasets: [{
                label: 'Profit over time',
                data: profits,
                borderWidth: 2,
                fill: false, 
                tension: 0.2,
            }]
        }
    });
</script>
