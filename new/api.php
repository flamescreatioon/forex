<?php
/**
 * MT5 Demo Trading API Helper
 * This file can be used for additional API endpoints if needed
 */

session_start();
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Initialize session data if not exists
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

// Route handling
switch ($action) {
    case 'get_account':
        echo json_encode([
            'success' => true,
            'data' => $_SESSION['account']
        ]);
        break;
        
    case 'get_trades':
        echo json_encode([
            'success' => true,
            'data' => $_SESSION['trades']
        ]);
        break;
        
    case 'get_history':
        echo json_encode([
            'success' => true,
            'data' => $_SESSION['history']
        ]);
        break;
        
    case 'get_symbols':
        $symbols = [
            'EURUSD' => ['bid' => 1.08450, 'ask' => 1.08500, 'spread' => 5],
            'GBPUSD' => ['bid' => 1.26450, 'ask' => 1.26500, 'spread' => 5],
            'USDJPY' => ['bid' => 149.45, 'ask' => 149.50, 'spread' => 5],
            'AUDUSD' => ['bid' => 0.65450, 'ask' => 0.65500, 'spread' => 5],
            'USDCAD' => ['bid' => 1.36450, 'ask' => 1.36500, 'spread' => 5],
            'NZDUSD' => ['bid' => 0.61450, 'ask' => 0.61500, 'spread' => 5],
            'USDCHF' => ['bid' => 0.87450, 'ask' => 0.87500, 'spread' => 5],
            'EURJPY' => ['bid' => 162.45, 'ask' => 162.50, 'spread' => 5],
            'GBPJPY' => ['bid' => 189.45, 'ask' => 189.50, 'spread' => 5],
            'EURGBP' => ['bid' => 0.85450, 'ask' => 0.85500, 'spread' => 5],
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $symbols
        ]);
        break;
        
    case 'reset_account':
        $_SESSION['account'] = [
            'balance' => 10000.00,
            'equity' => 10000.00,
            'margin' => 0.00,
            'free_margin' => 10000.00,
            'margin_level' => 0.00,
            'profit' => 0.00
        ];
        $_SESSION['trades'] = [];
        $_SESSION['history'] = [];
        
        echo json_encode([
            'success' => true,
            'message' => 'Account reset successfully',
            'data' => $_SESSION['account']
        ]);
        break;
        
    case 'calculate_profit':
        $symbol = $_POST['symbol'] ?? 'EURUSD';
        $type = $_POST['type'] ?? 'buy';
        $lots = floatval($_POST['lots'] ?? 0.01);
        $open_price = floatval($_POST['open_price'] ?? 1.0850);
        $current_price = floatval($_POST['current_price'] ?? 1.0860);
        
        if ($type === 'buy') {
            $profit = ($current_price - $open_price) * $lots * 100000;
        } else {
            $profit = ($open_price - $current_price) * $lots * 100000;
        }
        
        echo json_encode([
            'success' => true,
            'profit' => $profit,
            'pips' => abs($current_price - $open_price) * 10000
        ]);
        break;
        
    case 'get_market_data':
        // Generate random market data for demo purposes
        $symbols = ['EURUSD', 'GBPUSD', 'USDJPY', 'AUDUSD'];
        $data = [];
        
        foreach ($symbols as $symbol) {
            $basePrice = 1.08500;
            if ($symbol === 'GBPUSD') $basePrice = 1.26500;
            if ($symbol === 'USDJPY') $basePrice = 149.50;
            if ($symbol === 'AUDUSD') $basePrice = 0.65500;
            
            $change = (rand(-100, 100) / 100000);
            $price = $basePrice + $change;
            
            $data[] = [
                'symbol' => $symbol,
                'price' => $price,
                'change' => $change,
                'change_percent' => ($change / $basePrice) * 100,
                'timestamp' => time()
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}