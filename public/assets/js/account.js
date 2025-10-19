async function updateAccount(){
    const res = await fetch("/account/live");
    const data = await res.json();

    document.getElementById("balance").innerText = `$${data.balance}`;
    document.getElementById("profit").innerText = `$${data.profit}`;
    document.getElementById("equity").innerText = `$${data.equity}`;
}

setInterval(updateAccount, 3000);