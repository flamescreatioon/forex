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
        
        // Ensure financial chart controllers are registered (for UMD builds)
        try {
            if (Chart && Chart.Financial) {
                const { CandlestickController, OhlcController, CandlestickElement, OhlcElement } = Chart.Financial;
                Chart.register(CandlestickController, OhlcController, CandlestickElement, OhlcElement);
            }
        } catch (e) {
            console.warn('Financial chart controllers not available yet:', e);
        }

        async function loadFinancialPluginUMD() {
            return new Promise((resolve, reject) => {
                // If already available, resolve immediately
                try {
                    const test = new Chart(document.createElement('canvas').getContext('2d'), { type: 'candlestick', data: { datasets: [] } });
                    test.destroy();
                    return resolve();
                } catch (_) { /* continue to load */ }

                const existing = document.querySelector('script[src*="chartjs-chart-financial"]');
                if (existing) {
                    setTimeout(resolve, 50);
                    return;
                }
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/chartjs-chart-financial@3.3.0/dist/chartjs-chart-financial.min.js';
                s.async = true;
                s.onload = () => resolve();
                s.onerror = () => reject(new Error('Failed to load financial plugin UMD'));
                document.head.appendChild(s);
            });
        }
        
        // Map timeframe to Chart.js time scale unit
        const tfUnit = {
            '1m': 'minute',
            '5m': 'minute',
            '15m': 'minute',
            '1h': 'hour',
            '4h': 'hour',
            '1d': 'day'
        };
        
        // Initialize charts
        function initCharts() {
            const miniCtx = document.getElementById('mini-chart').getContext('2d');
            const mainCtx = document.getElementById('main-chart').getContext('2d');
            
            const baseConfig = () => ({
                type: 'candlestick',
                data: {
                    datasets: [{
                        label: currentSymbol,
                        data: generateCandlestickData(50)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: { unit: tfUnit[currentTimeframe] || 'hour' },
                            grid: { color: 'rgba(128, 128, 128, 0.1)' },
                            ticks: { color: 'var(--text-secondary)' }
                        },
                        y: {
                            grid: { color: 'rgba(128, 128, 128, 0.1)' },
                            ticks: { color: 'var(--text-secondary)' }
                        }
                    }
                }
            });

            miniChart = new Chart(miniCtx, baseConfig());
            mainChart = new Chart(mainCtx, baseConfig());
        }
        
        function generateCandlestickData(count) {
            const data = [];
            let price = symbolPrices[currentSymbol];
            const now = new Date();
            
            for (let i = count; i >= 0; i--) {
                const time = new Date(now.getTime() - i * 3600000);
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
            
            symbolPrices[currentSymbol] = price;
            currentPrice = price;
            return data;
        }
        
        function updateChart(chart) {
            chart.data.datasets[0].data = generateCandlestickData(50);
            chart.data.datasets[0].label = currentSymbol;
            chart.update('none');
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

                // Update x-axis unit on both charts
                [miniChart, mainChart].forEach(ch => {
                    if (!ch) return;
                    ch.options.scales.x.time.unit = tfUnit[tf] || 'hour';
                    ch.update();
                });
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
                document.getElementById('chart-symbol').value = symbol;
                updateChart(mainChart);
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
            document.getElementById('header-balance').textContent = '+ ' + account.balance.toFixed(2);
            document.getElementById('header-equity').textContent = '+ ' + account.equity.toFixed(2);
            document.getElementById('header-profit').textContent = '+ ' + account.profit.toFixed(2);
            document.getElementById('header-profit').className = 'account-value ' + (account.profit >= 0 ? 'profit-positive' : 'profit-negative');
            
            document.getElementById('dash-balance').textContent = '+ ' + account.balance.toFixed(2);
            document.getElementById('dash-equity').textContent = '+ ' + account.equity.toFixed(2);
            document.getElementById('dash-margin').textContent = '+ ' + account.margin.toFixed(2);
            document.getElementById('dash-free-margin').textContent = '+ ' + account.free_margin.toFixed(2);
            document.getElementById('dash-margin-level').textContent = account.margin_level.toFixed(2) + '%';
            document.getElementById('dash-profit').textContent = '+ ' + account.profit.toFixed(2);
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
                navigator.serviceWorker.register('service-worker.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed'));
            });
        }
        
        // Initialize app
        document.addEventListener('DOMContentLoaded', async () => {
            // Try init; if candlestick not registered, dynamically load plugin and retry once
            try {
                initCharts();
            } catch (e) {
                if (String(e).includes('not a registered controller')) {
                    try {
                        await loadFinancialPluginUMD();
                        initCharts();
                    } catch (loadErr) {
                        console.error('Failed to load candlestick controller:', loadErr);
                    }
                } else {
                    console.error(e);
                }
            }
            loadDashboardTrades();
            
            // Update prices every 2 seconds
            priceUpdateInterval = setInterval(() => {
                updatePricesAndTrades();
                
                // Update charts every 10 seconds
                if (Math.random() > 0.8) {
                    updateChart(miniChart);
                    updateChart(mainChart);
                }
            }, 2000);
        });
        
        // Prevent page unload during active trades
        window.addEventListener('beforeunload', (e) => {
            // Just for demo - in real app you'd check for open trades
        });