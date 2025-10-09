<?php
require_once __DIR__.'/Config/config.php';
?>

<h2><?= APP_NAME ?></h2>
<p>Welcome to your simulated forex platform!</p>
<a href="Routes/web.php?route=chart/data">🔹 View Market Data (JSON)</a><br>
<a href="Routes/web.php?route=chart/update">🔹 Generate Next Tick</a><br>
<a href="Routes/web.php?route=trade/open">🔹 Open Fake Trade</a><br>
<a href="Routes/web.php?route=trade/all">🔹 View All Trades</a>