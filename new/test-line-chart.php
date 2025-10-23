<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Chart Test - Forex</title>
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
    
    <!-- Load Chart.js and time adapter -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@0.2.1"></script>
</head>
<body>
    <div class="container">
        <h1>📈 Line Chart Test - EURUSD</h1>
        
        <div class="chart-wrapper">
            <h2>Price Chart</h2>
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
            
            const luxonCheck = typeof luxon !== 'undefined';
            addStatus('Luxon library loaded: ' + (luxonCheck ? 'YES ✓' : 'NO ✗'), !luxonCheck);
            if (luxonCheck) addStatus('Luxon version: ' + luxon.VERSION);
        }
        
        // Generate sample price data for line chart
        function generateLineData(count) {
            const data = [];
            let price = 1.08500;
            const now = new Date();
            
            for (let i = count; i >= 0; i--) {
                const time = new Date(now.getTime() - i * 3600000); // 1 hour intervals
                const volatility = price * 0.001;
                const change = (Math.random() - 0.5) * volatility;
                price += change;
                
                data.push({
                    x: time,
                    y: parseFloat(price.toFixed(5))
                });
            }
            
            return data;
        }
        
        // Initialize chart when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            addStatus('DOM loaded, attempting to create line chart...');
            
            try {
                const ctx = document.getElementById('test-chart').getContext('2d');
                
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: 'EURUSD',
                            data: generateLineData(50),
                            borderColor: '#00c853',
                            backgroundColor: 'rgba(0, 200, 83, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1,
                            pointRadius: 0,
                            pointHoverRadius: 5
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
                                    color: '#fff'
                                }
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
                                    unit: 'hour',
                                    displayFormats: {
                                        hour: 'HH:mm'
                                    }
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
                                    color: '#999',
                                    callback: function(value) {
                                        return value.toFixed(5);
                                    }
                                }
                            }
                        }
                    }
                });
                
                addStatus('✓ Line chart created successfully!');
                addStatus('Chart has ' + chart.data.datasets[0].data.length + ' data points');
                
            } catch (error) {
                addStatus('✗ Failed to create chart: ' + error.message, true);
                console.error('Chart creation error:', error);
            }
        });
    </script>
</body>
</html>
