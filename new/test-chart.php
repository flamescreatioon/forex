<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candlestick Chart Test</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #1a1a1a;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .chart-wrapper {
            background: #242424;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        canvas {
            max-height: 400px;
        }
        .status {
            padding: 15px;
            background: #2d2d2d;
            border-radius: 4px;
            margin-top: 20px;
        }
        .status h3 {
            margin-top: 0;
            color: #4caf50;
        }
        .status ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .status li {
            margin: 5px 0;
        }
        .error {
            color: #ff5252;
        }
        .success {
            color: #4caf50;
        }
    </style>
    
    <!-- Load Chart.js and plugins in strict order -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@0.2.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-financial@0.1.1/dist/chartjs-chart-financial.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Candlestick Chart Test</h1>
        
        <div class="chart-wrapper">
            <h2>Test Chart</h2>
            <canvas id="test-chart"></canvas>
        </div>
        
        <div class="status" id="status">
            <h3>Loading...</h3>
        </div>
    </div>

    <script>
        // Status reporting
        const statusDiv = document.getElementById('status');
        const statusItems = [];
        
        function addStatus(message, isError = false) {
            statusItems.push({ message, isError });
            updateStatus();
        }
        
        function updateStatus() {
            const items = statusItems.map(item => 
                `<li class="${item.isError ? 'error' : 'success'}">${item.message}</li>`
            ).join('');
            statusDiv.innerHTML = `<h3>Chart Initialization Status</h3><ul>${items}</ul>`;
        }
        
        // Check what's available
        addStatus('Chart.js loaded: ' + (typeof Chart !== 'undefined' ? 'YES ✓' : 'NO ✗'), typeof Chart === 'undefined');
        
        if (typeof Chart !== 'undefined') {
            addStatus('Chart.js version: ' + Chart.version);
            addStatus('Chart.registry exists: ' + (Chart.registry ? 'YES ✓' : 'NO ✗'), !Chart.registry);
            
            // Check for date library (Luxon)
            const luxonCheck = typeof luxon !== 'undefined';
            addStatus('Luxon library loaded: ' + (luxonCheck ? 'YES ✓' : 'NO ✗'), !luxonCheck);
            if (luxonCheck) addStatus('Luxon version: ' + luxon.VERSION);
            
            // Check for financial plugin
            if (window.Chart && window.Chart.Financial) {
                addStatus('Chart.Financial found: YES ✓');
                addStatus('Available: ' + Object.keys(Chart.Financial).join(', '));
            } else {
                addStatus('Chart.Financial found: NO ✗', true);
            }
            
            // Check if candlestick is registered
            try {
                const controller = Chart.registry.getController('candlestick');
                addStatus('Candlestick controller registered: YES ✓');
            } catch (e) {
                addStatus('Candlestick controller registered: NO ✗ - ' + e.message, true);
            }
        }
        
        // Generate sample OHLC data
        function generateOHLCData(count) {
            const data = [];
            let price = 1.08500;
            const now = new Date();
            
            for (let i = count; i >= 0; i--) {
                const time = new Date(now.getTime() - i * 3600000); // 1 hour intervals
                const open = price;
                const volatility = price * 0.002;
                const change = (Math.random() - 0.5) * volatility;
                const close = open + change;
                const high = Math.max(open, close) + Math.random() * volatility * 0.5;
                const low = Math.min(open, close) - Math.random() * volatility * 0.5;
                
                data.push({
                    x: time,
                    o: parseFloat(open.toFixed(5)),
                    h: parseFloat(high.toFixed(5)),
                    l: parseFloat(low.toFixed(5)),
                    c: parseFloat(close.toFixed(5))
                });
                
                price = close;
            }
            
            return data;
        }
        
        // Initialize chart when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            addStatus('DOM loaded, attempting to create chart...');
            
            try {
                const ctx = document.getElementById('test-chart').getContext('2d');
                
                const chart = new Chart(ctx, {
                    type: 'candlestick',
                    data: {
                        datasets: [{
                            label: 'EURUSD',
                            data: generateOHLCData(50)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'hour'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                },
                                ticks: {
                                    color: '#999'
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                },
                                ticks: {
                                    color: '#999'
                                }
                            }
                        }
                    }
                });
                
                addStatus('✓ Chart created successfully!');
                addStatus('Chart has ' + chart.data.datasets[0].data.length + ' data points');
                
            } catch (error) {
                addStatus('✗ Failed to create chart: ' + error.message, true);
                addStatus('Error stack: ' + error.stack, true);
                console.error('Chart creation error:', error);
            }
        });
    </script>
</body>
</html>
