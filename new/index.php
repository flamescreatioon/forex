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
    <link rel="stylesheet" href="assets/css/styles.css">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="assets/js/app.js"></script>