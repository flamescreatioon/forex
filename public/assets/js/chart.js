// public/js/chart.js

let ctx = document.getElementById("priceChart").getContext("2d");

let chartData = {
    labels: [],
    datasets: [{
        label: "EUR/USD",
        borderColor: "#00ff88",
        data: [],
        borderWidth: 2,
        fill: false,
        tension: 0.3
    }]
};

let forexChart = new Chart(ctx, {
    type: 'line',
    data: chartData,
    options: {
        scales: {
            x: { title: { display: true, text: 'Time' }},
            y: { title: { display: true, text: 'Price' }}
        }
    }
});

async function updateChart() {
    const res = await fetch("/chart/live");
    const data = await res.json();

    let time = new Date().toLocaleTimeString();
    chartData.labels.push(time);
    chartData.datasets[0].data.push(data.price);

    if (chartData.labels.length > 30) {
        chartData.labels.shift();
        chartData.datasets[0].data.shift();
    }

    forexChart.update();
}

setInterval(updateChart, 2000);
