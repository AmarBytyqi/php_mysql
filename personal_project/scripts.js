// Function to fetch data from the API (or your backend PHP)
function fetchCryptoData() {
    fetch("https://api.coingecko.com/api/v3/coins/bitcoin")
        .then(response => response.json())
        .then(data => {
            const currentPrice = data.market_data.current_price.usd;
            const marketCap = data.market_data.market_cap.usd;
            const volume = data.market_data.total_volume.usd;
            const supply = data.market_data.circulating_supply;

            // Update the page with the fetched data
            document.getElementById("current-price").innerText = currentPrice.toFixed(2);
            document.getElementById("market-cap").innerText = marketCap.toFixed(2);
            document.getElementById("volume").innerText = volume.toFixed(2);
            document.getElementById("supply").innerText = supply.toFixed(2);

            // Update the chart
            updateChart(currentPrice);
        })
        .catch(error => console.error("Error fetching data:", error));
}

// Update the chart with new data
let chart;
function updateChart(currentPrice) {
    const ctx = document.getElementById('priceChart').getContext('2d');

    if (chart) {
        chart.destroy();  // Destroy the previous chart before creating a new one
    }

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["1", "2", "3", "4", "5"],  // Example labels
            datasets: [{
                label: 'Bitcoin Price',
                data: [currentPrice, currentPrice + 10, currentPrice - 5, currentPrice + 3, currentPrice - 2], // Example data
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1
            }]
        }
    });
}

// Initialize data on page load
window.onload = function() {
    fetchCryptoData();

    // Update button click event
    document.getElementById("update-btn").addEventListener("click", fetchCryptoData);
};
