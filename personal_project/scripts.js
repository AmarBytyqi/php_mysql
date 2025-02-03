// Function to fetch data for multiple cryptocurrencies
function fetchCryptoData() {
    const cryptos = ["bitcoin", "ethereum"];  // Add more coins here
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

// Function to update the UI for each cryptocurrency
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
    `;
    
    cryptoCard.innerHTML = content;
    cryptoContainer.appendChild(cryptoCard);

    // Optionally, you can update the chart dynamically here as well
    updateChart(crypto, data.market_data.current_price.usd);
}

// Function to update the price chart (you can modify to show price trends)
function updateChart(crypto, currentPrice) {
    const ctx = document.getElementById(`${crypto}-priceChart`).getContext('2d');
    const chartData = {
        labels: ["1", "2", "3", "4", "5"], // Example labels (You can update this with real-time data)
        datasets: [{
            label: `${crypto.toUpperCase()} Price`,
            data: [currentPrice, currentPrice + 10, currentPrice - 5, currentPrice + 3, currentPrice - 2], // Example price data
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1
        }]
    };

    if (window[`${crypto}Chart`]) {
        window[`${crypto}Chart`].destroy();  // Destroy previous chart if it exists
    }

    // Create a new chart for each cryptocurrency
    window[`${crypto}Chart`] = new Chart(ctx, {
        type: 'line',
        data: chartData
    });
}

// Initialize the page and load the data for multiple coins
window.onload = function() {
    fetchCryptoData();
};
