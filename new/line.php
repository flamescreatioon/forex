<?php
// index.php - Main Trading Platform Interface
session_start();

// Mock data for demonstration
$account = [
    'balance' => 100000.00,
    'equity' => 99999.89,
    'margin' => 13.03,
    'free_margin' => 99986.86,
    'level' => 767458.866
];

$currency_pairs = [
    ['symbol' => 'AUDCAD', 'bid' => 0.91162, 'ask' => 0.91175, 'change' => 0.41, 'direction' => 'up'],
    ['symbol' => 'AUDCHF', 'bid' => 0.51828, 'ask' => 0.51841, 'change' => 0.41, 'direction' => 'down'],
    ['symbol' => 'AUDDKK', 'bid' => 4.45852, 'ask' => 4.46016, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDHKD', 'bid' => 5.25944, 'ask' => 5.25953, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDHUF', 'bid' => 228.86992, 'ask' => 229.08708, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDJPY', 'bid' => 99.435, 'ask' => 99.448, 'change' => 0.89, 'direction' => 'up'],
    ['symbol' => 'AUDNOK', 'bid' => 6.89340, 'ask' => 6.89630, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDNZD', 'bid' => 1.13246, 'ask' => 1.13273, 'change' => 0.21, 'direction' => 'up'],
    ['symbol' => 'AUDPLN', 'bid' => 2.63122, 'ask' => 2.63225, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDSEK', 'bid' => 6.11812, 'ask' => 6.12610, 'change' => 0.46, 'direction' => 'up'],
];

$positions = [
    ['symbol' => 'AUDCAD', 'type' => 'buy', 'volume' => 0.02, 'open_price' => 0.91182, 'current_price' => 0.91174, 'profit' => -0.11]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forex Trading Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 50px;
            background: #2c3e50;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 0;
        }

        .sidebar-icon {
            width: 30px;
            height: 30px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
            cursor: pointer;
            font-size: 18px;
        }

        .sidebar-icon:hover {
            color: #3498db;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid #e0e0e0;
            flex-wrap: wrap;
        }

        .header-btn {
            padding: 5px 15px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
        }

        .header-btn:hover {
            background: #f0f0f0;
        }

        .header-btn.active {
            background: #3498db;
            color: #fff;
            border-color: #3498db;
        }

        .new-order-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 3px;
            cursor: pointer;
            margin-left: auto;
        }

        /* Trading Area */
        .trading-area {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Chart Section */
        .chart-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .chart-header {
            padding: 10px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            font-weight: 600;
        }

        .chart-container {
            flex: 1;
            padding: 20px;
            background: #fafafa;
            position: relative;
        }

        .chart-placeholder {
            width: 100%;
            height: 100%;
            background: #fff;
            border: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
        }

        /* Symbol List */
        .symbol-list {
            width: 320px;
            background: #fff;
            border-left: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .symbol-search {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .symbol-search input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 13px;
        }

        .symbol-list-items {
            flex: 1;
            overflow-y: auto;
        }

        .symbol-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .symbol-item:hover {
            background: #f8f9fa;
        }

        .symbol-name {
            font-weight: 600;
            font-size: 14px;
        }

        .symbol-prices {
            display: flex;
            gap: 15px;
            font-size: 13px;
        }

        .price-bid {
            color: #e74c3c;
        }

        .price-ask {
            color: #27ae60;
        }

        .symbol-change {
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .symbol-change.up {
            background: #d4edda;
            color: #27ae60;
        }

        .symbol-change.down {
            background: #f8d7da;
            color: #e74c3c;
        }

        /* Bottom Panel */
        .bottom-panel {
            height: 200px;
            background: #fff;
            border-top: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .panel-tabs {
            display: flex;
            gap: 20px;
            padding: 10px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .panel-tab {
            padding: 5px 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #7f8c8d;
        }

        .panel-tab.active {
            color: #3498db;
            border-bottom: 2px solid #3498db;
        }

        .panel-content {
            flex: 1;
            padding: 15px 20px;
            overflow-y: auto;
        }

        .account-info {
            display: flex;
            gap: 30px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            gap: 10px;
        }

        .info-label {
            color: #7f8c8d;
        }

        .info-value {
            font-weight: 600;
        }

        .positions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .positions-table th {
            text-align: left;
            padding: 8px;
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 1px solid #e0e0e0;
        }

        .positions-table td {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .no-positions {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .create-order-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            width: 400px;
            border-radius: 5px;
            overflow: hidden;
        }

        .modal-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-weight: 600;
            font-size: 16px;
        }

        .modal-close {
            cursor: pointer;
            font-size: 20px;
            color: #7f8c8d;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        .price-display {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .price-btn {
            flex: 1;
            padding: 40px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            border-radius: 3px;
        }

        .sell-btn {
            background: #e74c3c;
            color: #fff;
        }

        .buy-btn {
            background: #3498db;
            color: #fff;
        }

        .price-value {
            font-size: 12px;
            margin-top: 5px;
        }

        /* Mobile View */
        .mobile-nav {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .symbol-list {
                display: none;
            }

            .mobile-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: #fff;
                border-top: 1px solid #e0e0e0;
                padding: 10px 0;
                justify-content: space-around;
                z-index: 999;
            }

            .mobile-nav-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 5px;
                font-size: 11px;
                color: #7f8c8d;
                cursor: pointer;
            }

            .mobile-nav-item.active {
                color: #3498db;
            }

            .container {
                padding-bottom: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-icon">☰</div>
            <div class="sidebar-icon">✎</div>
            <div class="sidebar-icon">↗</div>
            <div class="sidebar-icon">⚡</div>
            <div class="sidebar-icon">□</div>
            <div class="sidebar-icon">☰</div>
            <div class="sidebar-icon">T</div>
            <div class="sidebar-icon">👁</div>
            <div class="sidebar-icon">🔒</div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <button class="header-btn">H</button>
                <button class="header-btn">🎵</button>
                <button class="header-btn">~</button>
                <button class="header-btn">≋</button>
                <button class="header-btn">|||</button>
                <button class="header-btn">||||</button>
                <button class="header-btn">M1</button>
                <button class="header-btn">M5</button>
                <button class="header-btn">M15</button>
                <button class="header-btn">M30</button>
                <button class="header-btn active">H1</button>
                <button class="header-btn">H4</button>
                <button class="header-btn">D1</button>
                <button class="header-btn">W1</button>
                <button class="header-btn">MN</button>
                <button class="new-order-btn" onclick="openOrderModal()">+ New Order</button>
            </div>

            <!-- Trading Area -->
            <div class="trading-area">
                <!-- Chart Section -->
                <div class="chart-section">
                    <div class="chart-header">
                        AUDCAD, H1: Australian Dollar vs Canadian Dollar
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">
                            <p>Chart visualization would go here (requires charting library like TradingView or Chart.js)</p>
                        </div>
                    </div>
                </div>

                <!-- Symbol List -->
                <div class="symbol-list">
                    <div class="symbol-search">
                        <input type="text" placeholder="Search symbol" id="symbolSearch">
                    </div>
                    <div class="symbol-list-items">
                        <?php foreach ($currency_pairs as $pair): ?>
                            <div class="symbol-item" onclick="selectSymbol('<?php echo $pair['symbol']; ?>')">
                                <div>
                                    <div class="symbol-name"><?php echo $pair['symbol']; ?></div>
                                    <div class="symbol-prices">
                                        <span class="price-bid"><?php echo number_format($pair['bid'], 5); ?></span>
                                        <span class="price-ask"><?php echo number_format($pair['ask'], 5); ?></span>
                                    </div>
                                </div>
                                <?php if ($pair['change'] != 0): ?>
                                    <span class="symbol-change <?php echo $pair['direction']; ?>">
                                        <?php echo $pair['change']; ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Bottom Panel -->
            <div class="bottom-panel">
                <div class="panel-tabs">
                    <div class="panel-tab active">Positions</div>
                    <div class="panel-tab">Orders</div>
                    <div class="panel-tab">Deals</div>
                </div>
                <div class="panel-content">
                    <div class="account-info">
                        <div class="info-item">
                            <span class="info-label">Balance:</span>
                            <span class="info-value"><?php echo number_format($account['balance'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Equity:</span>
                            <span class="info-value"><?php echo number_format($account['equity'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Margin:</span>
                            <span class="info-value"><?php echo number_format($account['margin'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Free margin:</span>
                            <span class="info-value"><?php echo number_format($account['free_margin'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Level:</span>
                            <span class="info-value"><?php echo number_format($account['level'], 2); ?>%</span>
                        </div>
                    </div>

                    <?php if (count($positions) > 0): ?>
                        <table class="positions-table">
                            <thead>
                                <tr>
                                    <th>Symbol</th>
                                    <th>Type</th>
                                    <th>Volume</th>
                                    <th>Price</th>
                                    <th>S / L</th>
                                    <th>T / P</th>
                                    <th>Current</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($positions as $position): ?>
                                    <tr>
                                        <td><?php echo $position['symbol']; ?></td>
                                        <td><?php echo ucfirst($position['type']); ?></td>
                                        <td><?php echo $position['volume']; ?></td>
                                        <td><?php echo $position['open_price']; ?></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td><?php echo $position['current_price']; ?></td>
                                        <td style="color: <?php echo $position['profit'] < 0 ? '#e74c3c' : '#27ae60'; ?>">
                                            <?php echo number_format($position['profit'], 2); ?> USD
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-positions">
                            <p>You don't have any positions</p>
                            <br>
                            <a href="#" class="create-order-link" onclick="openOrderModal(); return false;">Create New Order</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="mobile-nav-item active">
            <span>📊</span>
            <span>Quotes</span>
        </div>
        <div class="mobile-nav-item">
            <span>📈</span>
            <span>Chart</span>
        </div>
        <div class="mobile-nav-item">
            <span>≡</span>
            <span>Trade</span>
        </div>
        <div class="mobile-nav-item">
            <span>🕐</span>
            <span>History</span>
        </div>
        <div class="mobile-nav-item">
            <span>⚙</span>
            <span>Settings</span>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">AUDCAD</div>
                <div class="modal-close" onclick="closeOrderModal()">×</div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Market Execution</label>
                </div>
                <div class="form-group">
                    <label class="form-label">Volume</label>
                    <input type="number" class="form-input" value="0.01" step="0.01">
                    <small>1 000.00 AUD</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Stop Loss</label>
                    <input type="text" class="form-input" placeholder="-">
                </div>
                <div class="form-group">
                    <label class="form-label">Take Profit</label>
                    <input type="text" class="form-input" placeholder="-">
                </div>
                <div class="form-group">
                    <label class="form-label">Comment</label>
                    <input type="text" class="form-input">
                </div>
                <div class="price-display">
                    <button class="price-btn sell-btn">
                        <div class="price-value">0.91173</div>
                        <div>Sell by Market</div>
                    </button>
                    <button class="price-btn buy-btn">
                        <div class="price-value">0.91185</div>
                        <div>Buy by Market</div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openOrderModal() {
            document.getElementById('orderModal').classList.add('active');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        function selectSymbol(symbol) {
            console.log('Selected symbol:', symbol);
            // Update chart and order modal with selected symbol
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });

        // Symbol search functionality
        document.getElementById('symbolSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.symbol-item');
            
            items.forEach(item => {
                const symbol = item.querySelector('.symbol-name').textContent.toLowerCase();
                if (symbol.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Simulate real-time price updates
        setInterval(() => {
            document.querySelectorAll('.price-bid, .price-ask').forEach(el => {
                const currentPrice = parseFloat(el.textContent);
                const change = (Math.random() - 0.5) * 0.0001;
                const newPrice = currentPrice + change;
                el.textContent = newPrice.toFixed(5);
            });
        }, 3000);
    </script>
</body>
</html>