// Function to fetch historical data for a cryptocurrency (last 30 days)
function fetchHistoricalData(crypto) {
    const url = `https://api.coingecko.com/api/v3/coins/${crypto}/market_chart?vs_currency=usd&days=30`;
    
    return fetch(url)
        .then(response => response.json())
        .then(data => {
            return data.prices.map(price => ({
                time: new Date(price[0]), // Time is in milliseconds
                price: price[1] // Price in USD
            }));
        })
        .catch(error => console.error(`Error fetching historical data for ${crypto}:`, error));
}

// Function to update the chart with real price data
function updateChart(crypto, data) {
    // Fetch historical data for the last 30 days
    fetchHistoricalData(crypto).then(historicalData => {
        const labels = historicalData.map(item => {
            return `${item.time.getDate()}/${item.time.getMonth() + 1}`; // Format date (DD/MM)
        });

        const prices = historicalData.map(item => item.price);

        const ctx = document.getElementById(`${crypto}-priceChart`).getContext('2d');

        // Destroy previous chart if it exists
        if (window[`${crypto}Chart`]) {
            window[`${crypto}Chart`].destroy();
        }

        // Create a new chart with the real price data
        window[`${crypto}Chart`] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: `${crypto.toUpperCase()} Price`,
                    data: prices,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false,  // Do not fill the area under the curve
                    tension: 0.1
                }]
            }
        });
    });
}

// Function to fetch real-time data and update UI for multiple cryptocurrencies
function fetchCryptoData() {
    const cryptos = ["bitcoin", "ethereum"];  // Add more coins if needed
    cryptos.forEach(crypto => {
        fetch(`https://api.coingecko.com/api/v3/coins/${crypto}`)
            .then(response => response.json())
            .then(data => {
                // Update the UI with the fetched data
                updateUI(crypto, data);
            })
            .catch(error => console.error("Error fetching data for", crypto, error));
    });
}

// Function to update the UI with the latest data for each cryptocurrency
function updateUI(crypto, data) {
    const cryptoContainer = document.getElementById("crypto-container");
    const cryptoCard = document.createElement("div");
    cryptoCard.classList.add("crypto-card");
    
    // Build the HTML content for each crypto
    const content = `
        <h2>${data.name} (${data.symbol.toUpperCase()})</h2>
        <p><strong>Current Price:</strong> $${data.market_data.current_price.usd.toFixed(2)}</p>
        <p><strong>Market Cap:</strong> $${data.market_data.market_cap.usd.toFixed(2)}</p>
        <p><strong>24h Volume:</strong> $${data.market_data.total_volume.usd.toFixed(2)}</p>
        <p><strong>Circulating Supply:</strong> ${data.market_data.circulating_supply.toFixed(2)}</p>
        <button onclick="updateChart('${crypto}', ${data.market_data.current_price.usd})">Update Chart</button>
        <canvas id="${crypto}-priceChart" width="400" height="200"></canvas>
    `;
    
    cryptoCard.innerHTML = content;
    cryptoContainer.appendChild(cryptoCard);

    // Optionally, you can call the chart update function immediately to show real-time data
    updateChart(crypto, data.market_data.current_price.usd);
}

// Initialize the page and load the data for multiple coins
window.onload = function() {
    fetchCryptoData();
};
