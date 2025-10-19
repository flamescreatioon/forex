async function openTrade(type){
    const formData = new FormData();
    formData.append('pair', 'EUR/USD');
    formData.append('type', type);

    await fetch('/trade/open', {method: 'POST', body: formData});
    loadOpenTrades();
}

async function closeTrade(id){
    const formData = new FormData();
    formData.append('id', id);

    await fetch('/trade/close', {method: 'POST', body: formData});
    loadOpenTrades();
}

async function loadOpenTrades(){
    const res = await fetch('/trade/list');
    const trades = await res.json();

    const tbody = document.querySelector('#openTrades tbody');
    tbody.innerHTML = '';

    trades.forEach(t=>{
        const row = `
        <tr>
        <td>${t.pair}</td>
        <td>${t.type}</td>
        <td>${t.oepn_price.toFixed(5)}</td>
        <td>${t.lot}</td>
        <td>${t.open_time}</td>
        <td><button onclick="closeTrade('${t.id}')">Close</button></td>
        </tr>
        `;
        tbody.innerHTML += row;
    })
}

setInterval(loadOpenTrades, 4000);