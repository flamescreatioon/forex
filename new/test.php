<?php
session_start();

// Initialize demo account data if not exists
if (!isset($_SESSION['account'])) {
    $_SESSION['account'] = [
        'balance' => 10000.00,
        'equity' => 10000.00,
        'margin' => 0.00,
        'free_margin' => 10000.00,
        'margin_level' => 0.00,
        'profit' => 0.00
    ];
}

if (!isset($_SESSION['trades'])) {
    $_SESSION['trades'] = [];
}

if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'theme' => 'dark',
        'default_timeframe' => '1h',
        'default_symbol' => 'EURUSD'
    ];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'open_trade':
            $trade = [
                'id' => uniqid(),
                'symbol' => $_POST['symbol'] ?? 'EURUSD',
                'type' => $_POST['type'] ?? 'buy',
                'lots' => floatval($_POST['lots'] ?? 0.01),
                'open_price' => floatval($_POST['open_price'] ?? 1.0850),
                'sl' => floatval($_POST['sl'] ?? 0),
                'tp' => floatval($_POST['tp'] ?? 0),
                'open_time' => date('Y-m-d H:i:s'),
                'profit' => 0
            ];
            $_SESSION['trades'][] = $trade;
            
            // Update margin
            $margin = $trade['lots'] * 100000 * 0.01; // Simplified margin calculation
            $_SESSION['account']['margin'] += $margin;
            $_SESSION['account']['free_margin'] = $_SESSION['account']['equity'] - $_SESSION['account']['margin'];
            
            echo json_encode(['success' => true, 'trade' => $trade, 'account' => $_SESSION['account']]);
            exit;
            
        case 'close_trade':
            $trade_id = $_POST['trade_id'] ?? '';
            foreach ($_SESSION['trades'] as $key => $trade) {
                if ($trade['id'] === $trade_id) {
                    $close_price = floatval($_POST['close_price'] ?? $trade['open_price']);
                    $trade['close_price'] = $close_price;
                    $trade['close_time'] = date('Y-m-d H:i:s');
                    
                    // Calculate final profit
                    if ($trade['type'] === 'buy') {
                        $profit = ($close_price - $trade['open_price']) * $trade['lots'] * 100000;
                    } else {
                        $profit = ($trade['open_price'] - $close_price) * $trade['lots'] * 100000;
                    }
                    $trade['profit'] = $profit;
                    
                    // Update account
                    $_SESSION['account']['balance'] += $profit;
                    $_SESSION['account']['equity'] = $_SESSION['account']['balance'];
                    $margin = $trade['lots'] * 100000 * 0.01;
                    $_SESSION['account']['margin'] -= $margin;
                    $_SESSION['account']['free_margin'] = $_SESSION['account']['equity'] - $_SESSION['account']['margin'];
                    
                    // Move to history
                    $_SESSION['history'][] = $trade;
                    unset($_SESSION['trades'][$key]);
                    $_SESSION['trades'] = array_values($_SESSION['trades']);
                    
                    echo json_encode(['success' => true, 'account' => $_SESSION['account']]);
                    exit;
                }
            }
            echo json_encode(['success' => false, 'message' => 'Trade not found']);
            exit;
            
        case 'update_trades':
            $current_price = floatval($_POST['price'] ?? 1.0850);
            $total_profit = 0;
            
            foreach ($_SESSION['trades'] as &$trade) {
                if ($trade['type'] === 'buy') {
                    $profit = ($current_price - $trade['open_price']) * $trade['lots'] * 100000;
                } else {
                    $profit = ($trade['open_price'] - $current_price) * $trade['lots'] * 100000;
                }
                $trade['profit'] = $profit;
                $total_profit += $profit;
            }
            
            $_SESSION['account']['profit'] = $total_profit;
            $_SESSION['account']['equity'] = $_SESSION['account']['balance'] + $total_profit;
            if ($_SESSION['account']['margin'] > 0) {
                $_SESSION['account']['margin_level'] = ($_SESSION['account']['equity'] / $_SESSION['account']['margin']) * 100;
            }
            $_SESSION['account']['free_margin'] = $_SESSION['account']['equity'] - $_SESSION['account']['margin'];
            
            echo json_encode(['success' => true, 'trades' => $_SESSION['trades'], 'account' => $_SESSION['account']]);
            exit;
            
        case 'get_data':
            echo json_encode([
                'account' => $_SESSION['account'],
                'trades' => $_SESSION['trades'],
                'history' => $_SESSION['history'],
                'settings' => $_SESSION['settings']
            ]);
            exit;
            
        case 'update_settings':
            $_SESSION['settings']['theme'] = $_POST['theme'] ?? 'dark';
            $_SESSION['settings']['default_timeframe'] = $_POST['timeframe'] ?? '1h';
            $_SESSION['settings']['default_symbol'] = $_POST['symbol'] ?? 'EURUSD';
            echo json_encode(['success' => true, 'settings' => $_SESSION['settings']]);
            exit;
    }
}

$theme = $_SESSION['settings']['theme'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="<?php echo $theme === 'dark' ? '#1a1a1a' : '#ffffff'; ?>">
    <title>MT5 Trading Demo</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDcuMi1jMDAwIDc5LjU2NmViYzViNCwgMjAyMi8wNS8wOS0wODoyNTo1NSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIzLjQgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RjI3RjExNzQwNzIwMTFFRDk3OTZFMTI0QzY0RjVBQzYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RjI3RjExNzUwNzIwMTFFRDk3OTZFMTI0QzY0RjVBQzYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpGMjdGMTE3MjA3MjAxMUVEOTc5NkUxMjRDNjRGNUFDNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpGMjdGMTE3MzA3MjAxMUVEOTc5NkUxMjRDNjRGNUFDNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PkqGhccAAAKYSURBVHja7JdLaBNBGMd/s5tN0jRt2qZVfBRFRRQVEcGDePAgIh48ePLgSQQP3rx58OJBELx4EhQRQfDgQUGwKIgXUbAqVkSt2lqttmlf2SbZx+h/YAmbTTZpN7sH/fDjm292dv7zzcy3M6OoqgrLsixYli1gBVgO1Kqqqv6nwJeBvPsBngAHgBXATqBZURTFYrFoq6urqzPgFHAIaAYGgDvAOeC5x6sSm1gsZgO2ACeBLqAFaMjlcpb29vZqoF7Ay8A54CKwH+gETgO3gTTg8wI4FQqFdgL7gG6gCYgAWeBZ4SN+A+wEeoFbwDngqhfAViAIRAEV0PL5fMayLCsYDNaAqW65P+AR8BjYDRwHXgHP3AAWi0VVVVUbEAbSgMFisQ3gy+VywVwup5dMJpOhUEivd3Z2BmOxWMBkMtmAB0A38AjQeQF4W/ROKYBer1c1Go0mkynS1dUVlWA+nzeVUu/p6Qnm83kzmUx6gftALzDo9h04CrQBx4A7gM9isdiCwaAqXy6XM4TDYeX/hAIGPwPHgQdegO1yuWwXdxRFsUsAS7JisVhUCRgOhxUA2e12VXomSz5VvhVYA1x2C6wFIsDXUiEMw1AikYj83tHRoXR2dipyvVQul8vJ7xKwqqpK5pfdbv+xOJaeewls8RJ4DXwB9gDnPQJngQ/AR2AKeAssKVUom83+rqfT6UwikTA0r7tqIBD4GwwGjcL9f/4oFosnAoGA8ttutwdkb+Sy+wsGg4GuggVqUf2klgL8Al561aJl1aKuOhCNRhf8T+iqRf+rFlXaolJ+KrWoVIueB84WauNerejJsmrR/9WKvpA2dFkr+kxqxaValL4F2TLLlnEL/BZgAOEaloW9YIr6AAAAAElFTkSuQmCC">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-tertiary: #3d3d3d;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-color: #444;
            --green: #00c853;
            --red: #ff1744;
            --blue: #2196f3;
            --orange: #ff9800;
        }
        
        body.light-theme {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f5;
            --bg-tertiary: #e0e0e0;
            --text-primary: #000000;
            --text-secondary: #666666;
            --border-color: #ddd;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow: hidden;
            height: 100vh;
        }
        
        .app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-weight: bold;
            font-size: 16px;
            color: var(--blue);
        }
        
        .account-info {
            display: flex;
            gap: 15px;
            font-size: 12px;
        }
        
        .account-item {
            display: flex;
            flex-direction: column;
        }
        
        .account-label {
            color: var(--text-secondary);
            font-size: 10px;
        }
        
        .account-value {
            font-weight: bold;
        }
        
        .profit-positive { color: var(--green); }
        .profit-negative { color: var(--red); }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .tabs {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            overflow-x: auto;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            white-space: nowrap;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: var(--blue);
            color: var(--blue);
        }
        
        .tab:hover {
            background: var(--bg-tertiary);
        }
        
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 11px;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
        }
        
        .chart-container {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .symbol-selector {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .symbol-btn {
            padding: 6px 12px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .symbol-btn.active {
            background: var(--blue);
            border-color: var(--blue);
        }
        
        .timeframe-selector {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }
        
        .tf-btn {
            padding: 4px 8px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
        }
        
        .tf-btn.active {
            background: var(--blue);
            border-color: var(--blue);
        }
        
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
        
        .trade-panel {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        
        .trade-panel h3 {
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .trade-form {
            display: grid;
            gap: 12px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .form-group label {
            font-size: 11px;
            color: var(--text-secondary);
        }
        
        .form-group input, .form-group select {
            padding: 8px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 4px;
            font-size: 13px;
        }
        
        .trade-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn {
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-buy {
            background: var(--green);
            color: white;
        }
        
        .btn-sell {
            background: var(--red);
            color: white;
        }
        
        .trades-list {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid var(--border-color);
        }
        
        .trades-list h3 {
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .trade-item {
            background: var(--bg-tertiary);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
        }
        
        .trade-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .trade-symbol {
            font-weight: bold;
            font-size: 14px;
        }
        
        .trade-type {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .trade-type.buy {
            background: var(--green);
            color: white;
        }
        
        .trade-type.sell {
            background: var(--red);
            color: white;
        }
        
        .trade-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            font-size: 11px;
        }
        
        .trade-detail {
            display: flex;
            justify-content: space-between;
        }
        
        .trade-actions {
            margin-top: 10px;
            display: flex;
            gap: 8px;
        }
        
        .btn-close {
            padding: 6px 12px;
            background: var(--red);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            flex: 1;
        }
        
        .settings-section {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
        }
        
        .settings-section h3 {
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-label {
            font-size: 13px;
        }
        
        .theme-toggle {
            padding: 8px 16px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
        
        .price-display {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin: 10px 0;
        }
        
        .price-big {
            font-size: 24px;
            font-weight: bold;
        }
        
        .price-change {
            font-size: 14px;
            padding: 2px 6px;
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .account-info {
                display: none;
            }
            
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .tab {
                padding: 10px 15px;
                font-size: 13px;
            }
        }
        
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body class="<?php echo $theme === 'dark' ? '' : 'light-theme'; ?>">
    <div class="app-container">
        <div class="header">
            <div class="logo">MT5 Demo</div>
            <div class="account-info">
                <div class="account-item">
                    <span class="account-label">Balance</span>
                    <span class="account-value" id="header-balance">$<?php echo number_format($_SESSION['account']['balance'], 2); ?></span>
                </div>
                <div class="account-item">
                    <span class="account-label">Equity</span>
                    <span class="account-value" id="header-equity">$<?php echo number_format($_SESSION['account']['equity'], 2); ?></span>
                </div>
                <div class="account-item">
                    <span class="account-label">Profit</span>
                    <span class="account-value profit-positive" id="header-profit">$<?php echo number_format($_SESSION['account']['profit'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="tabs">
                <div class="tab active" data-tab="dashboard">Dashboard</div>
                <div class="tab" data-tab="quotes">Quotes</div>
                <div class="tab" data-tab="chart">Chart</div>
                <div class="tab" data-tab="trades">Trades</div>
                <div class="tab" data-tab="history">History</div>
                <div class="tab" data-tab="settings">Settings</div>
            </div>
            
            <div class="content">
                <!-- Dashboard Tab -->
                <div id="dashboard-content" class="tab-content">
                    <div class="dashboard-grid">
                        <div class="stat-card">
                            <div class="stat-label">Balance</div>
                            <div class="stat-value" id="dash-balance">$<?php echo number_format($_SESSION['account']['balance'], 2); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Equity</div>
                            <div class="stat-value" id="dash-equity">$<?php echo number_format($_SESSION['account']['equity'], 2); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Margin</div>
                            <div class="stat-value" id="dash-margin">$<?php echo number_format($_SESSION['account']['margin'], 2); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Free Margin</div>
                            <div class="stat-value" id="dash-free-margin">$<?php echo number_format($_SESSION['account']['free_margin'], 2); ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Margin Level</div>
                            <div class="stat-value" id="dash-margin-level"><?php echo number_format($_SESSION['account']['margin_level'], 2); ?>%</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Profit/Loss</div>
                            <div class="stat-value profit-positive" id="dash-profit">$<?php echo number_format($_SESSION['account']['profit'], 2); ?></div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="symbol-selector">
                                <span style="font-weight: bold; font-size: 14px;" id="current-symbol">EURUSD</span>
                                <div class="price-display">
                                    <span class="price-big" id="current-price">1.08500</span>
                                    <span class="price-change profit-positive" id="price-change">+0.00125 (+0.12%)</span>
                                </div>
                            </div>
                            <div class="timeframe-selector">
                                <button class="tf-btn" data-tf="1m">1M</button>
                                <button class="tf-btn" data-tf="5m">5M</button>
                                <button class="tf-btn" data-tf="15m">15M</button>
                                <button class="tf-btn active" data-tf="1h">1H</button>
                                <button class="tf-btn" data-tf="4h">4H</button>
                                <button class="tf-btn" data-tf="1d">1D</button>
                            </div>
                        </div>
                        <canvas id="mini-chart"></canvas>
                    </div>
                    
                    <div class="trade-panel">
                        <h3>Quick Trade</h3>
                        <div class="trade-form">
                            <div class="form-group">
                                <label>Symbol</label>
                                <select id="quick-symbol">
                                    <option value="EURUSD">EURUSD</option>
                                    <option value="GBPUSD">GBPUSD</option>
                                    <option value="USDJPY">USDJPY</option>
                                    <option value="AUDUSD">AUDUSD</option>
                                    <option value="USDCAD">USDCAD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Lot Size</label>
                                <input type="number" id="quick-lots" value="0.01" step="0.01" min="0.01">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div class="form-group">
                                    <label>Stop Loss</label>
                                    <input type="number" id="quick-sl" placeholder="0.00000" step="0.00001">
                                </div>
                                <div class="form-group">
                                    <label>Take Profit</label>
                                    <input type="number" id="quick-tp" placeholder="0.00000" step="0.00001">
                                </div>
                            </div>
                            <div class="trade-buttons">
                                <button class="btn btn-buy" onclick="openTrade('buy')">BUY</button>
                                <button class="btn btn-sell" onclick="openTrade('sell')">SELL</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="trades-list" id="dashboard-trades">
                        <h3>Open Positions</h3>
                        <div id="dashboard-trades-content"></div>
                    </div>
                </div>
                
                <!-- Quotes Tab -->
                <div id="quotes-content" class="tab-content hidden">
                    <div class="trades-list">
                        <h3>Market Watch</h3>
                        <div id="quotes-list"></div>
                    </div>
                </div>
                
                <!-- Chart Tab -->
                <div id="chart-content" class="tab-content hidden">
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="symbol-selector">
                                <button class="symbol-btn active" data-symbol="EURUSD">EURUSD</button>
                                <button class="symbol-btn" data-symbol="GBPUSD">GBPUSD</button>
                                <button class="symbol-btn" data-symbol="USDJPY">USDJPY</button>
                                <button class="symbol-btn" data-symbol="AUDUSD">AUDUSD</button>
                            </div>
                            <div class="timeframe-selector">
                                <button class="tf-btn" data-tf="1m">1M</button>
                                <button class="tf-btn" data-tf="5m">5M</button>
                                <button class="tf-btn" data-tf="15m">15M</button>
                                <button class="tf-btn active" data-tf="1h">1H</button>
                                <button class="tf-btn" data-tf="4h">4H</button>
                                <button class="tf-btn" data-tf="1d">1D</button>
                            </div>
                        </div>
                        <div class="price-display">
                            <span class="price-big" id="chart-price">1.08500</span>
                            <span class="price-change profit-positive" id="chart-change">+0.00125 (+0.12%)</span>
                        </div>
                        <canvas id="main-chart"></canvas>
                    </div>
                    
                    <div class="trade-panel">
                        <h3>New Order</h3>
                        <div class="trade-form">
                            <div class="form-group">
                                <label>Symbol</label>
                                <select id="chart-symbol">
                                    <option value="EURUSD">EURUSD</option>
                                    <option value="GBPUSD">GBPUSD</option>
                                    <option value="USDJPY">USDJPY</option>
                                    <option value="AUDUSD">AUDUSD</option>
                                    <option value="USDCAD">USDCAD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Lot Size</label>
                                <input type="number" id="chart-lots" value="0.01" step="0.01" min="0.01">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div class="form-group">
                                    <label>Stop Loss</label>
                                    <input type="number" id="chart-sl" placeholder="0.00000" step="0.00001">
                                </div>
                                <div class="form-group">
                                    <label>Take Profit</label>
                                    <input type="number" id="chart-tp" placeholder="0.00000" step="0.00001">
                                </div>
                            </div>
                            <div class="trade-buttons">
                                <button class="btn btn-buy" onclick="openTrade('buy', 'chart')">BUY</button>
                                <button class="btn btn-sell" onclick="openTrade('sell', 'chart')">SELL</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trades Tab -->
                <div id="trades-content" class="tab-content hidden">
                    <div class="trades-list">
                        <h3>Open Positions</h3>
                        <div id="trades-list-content"></div>
                    </div>
                </div>
                
                <!-- History Tab -->
                <div id="history-content" class="tab-content hidden">
                    <div class="trades-list">
                        <h3>Trade History</h3>
                        <div id="history-list-content"></div>
                    </div>
                </div>
                
                <!-- Settings Tab -->
                <div id="settings-content" class="tab-content hidden">
                    <div class="settings-section">
                        <h3>Appearance</h3>
                        <div class="setting-item">
                            <span class="setting-label">Theme</span>
                            <button class="theme-toggle" onclick="toggleTheme()">
                                <span id="theme-text"><?php echo ucfirst($theme); ?> Mode</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Trading Defaults</h3>
                        <div class="setting-item">
                            <span class="setting-label">Default Symbol</span>
                            <select id="default-symbol" onchange="saveSettings()" style="padding: 8px; background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 4px;">
                                <option value="EURUSD" <?php echo $_SESSION['settings']['default_symbol'] === 'EURUSD' ? 'selected' : ''; ?>>EURUSD</option>
                                <option value="GBPUSD" <?php echo $_SESSION['settings']['default_symbol'] === 'GBPUSD' ? 'selected' : ''; ?>>GBPUSD</option>
                                <option value="USDJPY" <?php echo $_SESSION['settings']['default_symbol'] === 'USDJPY' ? 'selected' : ''; ?>>USDJPY</option>
                                <option value="AUDUSD" <?php echo $_SESSION['settings']['default_symbol'] === 'AUDUSD' ? 'selected' : ''; ?>>AUDUSD</option>
                            </select>
                        </div>
                        <div class="setting-item">
                            <span class="setting-label">Default Timeframe</span>
                            <select id="default-timeframe" onchange="saveSettings()" style="padding: 8px; background: var(--bg-tertiary); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 4px;">
                                <option value="1m" <?php echo $_SESSION['settings']['default_timeframe'] === '1m' ? 'selected' : ''; ?>>1 Minute</option>
                                <option value="5m" <?php echo $_SESSION['settings']['default_timeframe'] === '5m' ? 'selected' : ''; ?>>5 Minutes</option>
                                <option value="15m" <?php echo $_SESSION['settings']['default_timeframe'] === '15m' ? 'selected' : ''; ?>>15 Minutes</option>
                                <option value="1h" <?php echo $_SESSION['settings']['default_timeframe'] === '1h' ? 'selected' : ''; ?>>1 Hour</option>
                                <option value="4h" <?php echo $_SESSION['settings']['default_timeframe'] === '4h' ? 'selected' : ''; ?>>4 Hours</option>
                                <option value="1d" <?php echo $_SESSION['settings']['default_timeframe'] === '1d' ? 'selected' : ''; ?>>1 Day</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3>Account Information</h3>
                        <div class="setting-item">
                            <span class="setting-label">Account Type</span>
                            <span style="color: var(--text-secondary);">Demo Account</span>
                        </div>
                        <div class="setting-item">
                            <span class="setting-label">Leverage</span>
                            <span style="color: var(--text-secondary);">1:100</span>
                        </div>
                        <div class="setting-item">
                            <span class="setting-label">Currency</span>
                            <span style="color: var(--text-secondary);">USD</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let currentSymbol = 'EURUSD';
        let currentTimeframe = '1h';
        let currentPrice = 1.08500;
        let miniChart, mainChart;
        let priceUpdateInterval;
        let symbolPrices = {
            'EURUSD': 1.08500,
            'GBPUSD': 1.26500,
            'USDJPY': 149.50,
            'AUDUSD': 0.65500,
            'USDCAD': 1.36500
        };
        
        // Wait for Chart.js to load
        function waitForChart() {
            return new Promise((resolve) => {
                if (typeof Chart !== 'undefined') {
                    resolve();
                } else {
                    setTimeout(() => waitForChart().then(resolve), 100);
                }
            });
        }
        
        // Initialize charts
        async function initCharts() {
            await waitForChart();
            
            const miniCanvas = document.getElementById('mini-chart');
            const mainCanvas = document.getElementById('main-chart');
            
            if (!miniCanvas || !mainCanvas) {
                console.error('Canvas elements not found');
                return;
            }
            
            const miniCtx = miniCanvas.getContext('2d');
            const mainCtx = mainCanvas.getContext('2d');
            
            if (!miniCtx || !mainCtx) {
                console.error('Could not get canvas context');
                return;
            }
            
            const isDark = !document.body.classList.contains('light-theme');
            const gridColor = isDark ? 'rgba(128, 128, 128, 0.2)' : 'rgba(128, 128, 128, 0.3)';
            const textColor = isDark ? '#b0b0b0' : '#666666';
            
            const initialData = generateChartData(50);
            
            const chartConfig = {
                type: 'line',
                data: {
                    labels: initialData.labels,
                    datasets: [{
                        label: currentSymbol,
                        data: initialData.data,
                        borderColor: '#2196f3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            labels: {
                                color: textColor,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(45, 45, 45, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: gridColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return currentSymbol + ': ' + context.parsed.y.toFixed(5);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { 
                                color: gridColor,
                                drawBorder: false
                            },
                            ticks: { 
                                color: textColor,
                                font: { size: 10 },
                                maxTicksLimit: 8
                            }
                        },
                        y: {
                            position: 'right',
                            grid: { 
                                color: gridColor,
                                drawBorder: false
                            },
                            ticks: { 
                                color: textColor,
                                font: { size: 10 },
                                callback: function(value) {
                                    return value.toFixed(5);
                                }
                            }
                        }
                    }
                }
            };
            
            try {
                miniChart = new Chart(miniCtx, JSON.parse(JSON.stringify(chartConfig)));
                mainChart = new Chart(mainCtx, JSON.parse(JSON.stringify(chartConfig)));
                console.log('Charts initialized successfully');
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        }
        
        function generateChartData(count) {
            const data = [];
            const labels = [];
            let price = symbolPrices[currentSymbol];
            const now = new Date();
            
            for (let i = count; i >= 0; i--) {
                const time = new Date(now.getTime() - i * 3600000);
                const volatility = price * 0.001;
                const change = (Math.random() - 0.5) * volatility;
                price = price + change;
                
                labels.push(time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }));
                data.push(parseFloat(price.toFixed(5)));
            }
            
            symbolPrices[currentSymbol] = price;
            currentPrice = price;
            
            return { labels, data };
        }
        
        function updateChart(chart) {
            if (!chart) {
                console.error('Chart not initialized');
                return;
            }
            
            try {
                const chartData = generateChartData(50);
                chart.data.labels = chartData.labels;
                chart.data.datasets[0].data = chartData.data;
                chart.data.datasets[0].label = currentSymbol;
                
                // Update colors based on price movement
                const firstPrice = chartData.data[0];
                const lastPrice = chartData.data[chartData.data.length - 1];
                const isPositive = lastPrice >= firstPrice;
                
                chart.data.datasets[0].borderColor = isPositive ? '#00c853' : '#ff1744';
                chart.data.datasets[0].backgroundColor = isPositive ? 'rgba(0, 200, 83, 0.1)' : 'rgba(255, 23, 68, 0.1)';
                
                chart.update('none');
            } catch (error) {
                console.error('Error updating chart:', error);
            }
        }
        
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.dataset.tab;
                
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                document.getElementById(tabName + '-content').classList.remove('hidden');
                
                if (tabName === 'chart' || tabName === 'dashboard') {
                    setTimeout(() => {
                        if (tabName === 'chart') updateChart(mainChart);
                        if (tabName === 'dashboard') updateChart(miniChart);
                    }, 100);
                }
                
                if (tabName === 'trades') loadTrades();
                if (tabName === 'history') loadHistory();
                if (tabName === 'quotes') loadQuotes();
            });
        });
        
        // Timeframe switching
        document.querySelectorAll('.tf-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tf = btn.dataset.tf;
                currentTimeframe = tf;
                
                btn.parentElement.querySelectorAll('.tf-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                updateChart(miniChart);
                updateChart(mainChart);
            });
        });
        
        // Symbol switching
        document.querySelectorAll('.symbol-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const symbol = btn.dataset.symbol;
                currentSymbol = symbol;
                currentPrice = symbolPrices[symbol];
                
                btn.parentElement.querySelectorAll('.symbol-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                document.getElementById('current-symbol').textContent = symbol;
                const chartSymbolSelect = document.getElementById('chart-symbol');
                if (chartSymbolSelect) {
                    chartSymbolSelect.value = symbol;
                }
                
                if (mainChart) {
                    updateChart(mainChart);
                }
                if (miniChart) {
                    updateChart(miniChart);
                }
                updatePriceDisplay();
            });
        });
        
        // Open trade
        async function openTrade(type, source = 'quick') {
            const prefix = source === 'quick' ? 'quick' : 'chart';
            const symbol = document.getElementById(prefix + '-symbol').value;
            const lots = document.getElementById(prefix + '-lots').value;
            const sl = document.getElementById(prefix + '-sl').value;
            const tp = document.getElementById(prefix + '-tp').value;
            
            const formData = new FormData();
            formData.append('action', 'open_trade');
            formData.append('symbol', symbol);
            formData.append('type', type);
            formData.append('lots', lots);
            formData.append('open_price', symbolPrices[symbol]);
            formData.append('sl', sl);
            formData.append('tp', tp);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                updateAccountDisplay(data.account);
                loadTrades();
                loadDashboardTrades();
            }
        }
        
        // Close trade
        async function closeTrade(tradeId) {
            const formData = new FormData();
            formData.append('action', 'close_trade');
            formData.append('trade_id', tradeId);
            formData.append('close_price', currentPrice);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                updateAccountDisplay(data.account);
                loadTrades();
                loadHistory();
                loadDashboardTrades();
            }
        }
        
        // Update prices and trades
        async function updatePricesAndTrades() {
            // Simulate price movement
            Object.keys(symbolPrices).forEach(symbol => {
                const volatility = symbolPrices[symbol] * 0.0001;
                const change = (Math.random() - 0.5) * volatility;
                symbolPrices[symbol] += change;
            });
            
            currentPrice = symbolPrices[currentSymbol];
            updatePriceDisplay();
            
            // Update trades with current prices
            const formData = new FormData();
            formData.append('action', 'update_trades');
            formData.append('price', currentPrice);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                updateAccountDisplay(data.account);
                if (document.querySelector('[data-tab="trades"]').classList.contains('active')) {
                    renderTrades(data.trades, 'trades-list-content');
                }
                if (document.querySelector('[data-tab="dashboard"]').classList.contains('active')) {
                    renderTrades(data.trades, 'dashboard-trades-content');
                }
            }
        }
        
        function updatePriceDisplay() {
            const priceElements = ['current-price', 'chart-price'];
            priceElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = currentPrice.toFixed(5);
            });
            
            const changeElements = ['price-change', 'chart-change'];
            const change = (Math.random() - 0.5) * 0.002;
            const changePercent = (change * 100).toFixed(2);
            changeElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = `${change >= 0 ? '+' : ''}${change.toFixed(5)} (${changePercent}%)`;
                    el.className = 'price-change ' + (change >= 0 ? 'profit-positive' : 'profit-negative');
                }
            });
        }
        
        function updateAccountDisplay(account) {
            document.getElementById('header-balance').textContent = ' + account.balance.toFixed(2)';
            document.getElementById('header-equity').textContent = ' + account.equity.toFixed(2)';
            document.getElementById('header-profit').textContent = ' + account.profit.toFixed(2)';
            document.getElementById('header-profit').className = 'account-value ' + (account.profit >= 0 ? 'profit-positive' : 'profit-negative');

            document.getElementById('dash-balance').textContent = ' + account.balance.toFixed(2)';
            document.getElementById('dash-equity').textContent = ' + account.equity.toFixed(2)';
            document.getElementById('dash-margin').textContent = ' + account.margin.toFixed(2)';
            document.getElementById('dash-free-margin').textContent = ' + account.free_margin.toFixed(2)';
            document.getElementById('dash-margin-level').textContent = account.margin_level.toFixed(2) + '%';
            document.getElementById('dash-profit').textContent = ' + account.profit.toFixed(2)';
            document.getElementById('dash-profit').className = 'stat-value ' + (account.profit >= 0 ? 'profit-positive' : 'profit-negative');
        }
        
        async function loadTrades() {
            const formData = new FormData();
            formData.append('action', 'get_data');
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            renderTrades(data.trades, 'trades-list-content');
        }
        
        async function loadDashboardTrades() {
            const formData = new FormData();
            formData.append('action', 'get_data');
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            renderTrades(data.trades, 'dashboard-trades-content');
        }
        
        function renderTrades(trades, containerId) {
            const container = document.getElementById(containerId);
            
            if (trades.length === 0) {
                container.innerHTML = '<div class="empty-state">No open positions</div>';
                return;
            }
            
            container.innerHTML = trades.map(trade => `
                <div class="trade-item">
                    <div class="trade-header">
                        <span class="trade-symbol">${trade.symbol}</span>
                        <span class="trade-type ${trade.type}">${trade.type.toUpperCase()}</span>
                    </div>
                    <div class="trade-details">
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Lots:</span>
                            <span>${trade.lots}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Open Price:</span>
                            <span>${parseFloat(trade.open_price).toFixed(5)}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">SL:</span>
                            <span>${trade.sl > 0 ? parseFloat(trade.sl).toFixed(5) : '-'}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">TP:</span>
                            <span>${trade.tp > 0 ? parseFloat(trade.tp).toFixed(5) : '-'}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Profit/Loss:</span>
                            <span class="${trade.profit >= 0 ? 'profit-positive' : 'profit-negative'}">${trade.profit.toFixed(2)}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Time:</span>
                            <span>${new Date(trade.open_time).toLocaleTimeString()}</span>
                        </div>
                    </div>
                    <div class="trade-actions">
                        <button class="btn-close" onclick="closeTrade('${trade.id}')">Close Position</button>
                    </div>
                </div>
            `).join('');
        }
        
        async function loadHistory() {
            const formData = new FormData();
            formData.append('action', 'get_data');
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            const container = document.getElementById('history-list-content');
            
            if (data.history.length === 0) {
                container.innerHTML = '<div class="empty-state">No trade history</div>';
                return;
            }
            
            container.innerHTML = data.history.map(trade => `
                <div class="trade-item">
                    <div class="trade-header">
                        <span class="trade-symbol">${trade.symbol}</span>
                        <span class="trade-type ${trade.type}">${trade.type.toUpperCase()}</span>
                    </div>
                    <div class="trade-details">
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Lots:</span>
                            <span>${trade.lots}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Open:</span>
                            <span>${parseFloat(trade.open_price).toFixed(5)}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Close:</span>
                            <span>${parseFloat(trade.close_price).toFixed(5)}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Profit/Loss:</span>
                            <span class="${trade.profit >= 0 ? 'profit-positive' : 'profit-negative'}">${trade.profit.toFixed(2)}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Open Time:</span>
                            <span>${new Date(trade.open_time).toLocaleString()}</span>
                        </div>
                        <div class="trade-detail">
                            <span style="color: var(--text-secondary);">Close Time:</span>
                            <span>${new Date(trade.close_time).toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function loadQuotes() {
            const container = document.getElementById('quotes-list');
            container.innerHTML = Object.keys(symbolPrices).map(symbol => {
                const price = symbolPrices[symbol];
                const spread = price * 0.0001;
                const bid = price - spread / 2;
                const ask = price + spread / 2;
                
                return `
                    <div class="trade-item">
                        <div class="trade-header">
                            <span class="trade-symbol">${symbol}</span>
                        </div>
                        <div class="trade-details">
                            <div class="trade-detail">
                                <span style="color: var(--text-secondary);">Bid:</span>
                                <span class="profit-negative">${bid.toFixed(5)}</span>
                            </div>
                            <div class="trade-detail">
                                <span style="color: var(--text-secondary);">Ask:</span>
                                <span class="profit-positive">${ask.toFixed(5)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        async function toggleTheme() {
            const currentTheme = document.body.classList.contains('light-theme') ? 'light' : 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            const formData = new FormData();
            formData.append('action', 'update_settings');
            formData.append('theme', newTheme);
            formData.append('timeframe', currentTimeframe);
            formData.append('symbol', currentSymbol);
            
            await fetch('', {
                method: 'POST',
                body: formData
            });
            
            document.body.classList.toggle('light-theme');
            document.getElementById('theme-text').textContent = newTheme.charAt(0).toUpperCase() + newTheme.slice(1) + ' Mode';
            document.querySelector('meta[name="theme-color"]').setAttribute('content', newTheme === 'dark' ? '#1a1a1a' : '#ffffff');
        }
        
        async function saveSettings() {
            const symbol = document.getElementById('default-symbol').value;
            const timeframe = document.getElementById('default-timeframe').value;
            const theme = document.body.classList.contains('light-theme') ? 'light' : 'dark';
            
            const formData = new FormData();
            formData.append('action', 'update_settings');
            formData.append('theme', theme);
            formData.append('timeframe', timeframe);
            formData.append('symbol', symbol);
            
            await fetch('', {
                method: 'POST',
                body: formData
            });
        }
        
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed'));
            });
        }
        
        // Initialize app
        document.addEventListener('DOMContentLoaded', async () => {
            console.log('DOM loaded, initializing...');
            
            try {
                await initCharts();
                loadDashboardTrades();
                
                // Update prices every 2 seconds
                priceUpdateInterval = setInterval(() => {
                    updatePricesAndTrades();
                    updatePriceDisplay();
                }, 2000);
                
                // Update charts every 5 seconds
                setInterval(() => {
                    if (document.querySelector('[data-tab="dashboard"]').classList.contains('active')) {
                        updateChart(miniChart);
                    }
                    if (document.querySelector('[data-tab="chart"]').classList.contains('active')) {
                        updateChart(mainChart);
                    }
                }, 5000);
                
                console.log('App initialized successfully');
            } catch (error) {
                console.error('Error initializing app:', error);
            }
        });
        
        // Prevent page unload during active trades
        window.addEventListener('beforeunload', (e) => {
            // Just for demo - in real app you'd check for open trades
        });
    </script>
</body>
</html>