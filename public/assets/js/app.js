const priceEl = document.getElementById('price');
const profitEl = document.getElementById('profit');
const chartCtx = document.getElementById('chart').getContext('2d');

let chart;
let prices = [];
let labels = [];

function initChart(){
    chart = new Chart(chartCtx,{
        type: 'line',
        data:{
            labels: labels, 
            datasets:[{
                label: ' EUR/USD',
                data: prices,
                borderColor: '#58a6ff',
                tension: 0.3
             }]
        },
        options: {
            scales: {
                x: {display: false},
                y: {color: '#ccc'}
            },
            plugins: {legend: {labels: {color: '#ccc'}}}
        }
    });
}

async function fetchData(){
    const res = await fetch ('../routes/web.php?route=chart/update');
    const tick = await res.json();

    prices.push(parseFloat(tick.price));
    labels.push(tick.timestamp);

    if(prices.length > 20){
        prices.shift();
        labels.shift();
    }

    chart.update();
    priceEl.textContent = tick.price;

    const profit = (Math.random() * 50).toFixed(2);
    profitEl.textContent = `$${profit}`;
}

window.onload = function(){
    initChart();
    this.setInterval(fetchData, 5000);
}