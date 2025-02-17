<?php
// Database Connection
$host = "localhost";
$user = "root";
$password = ""; // Change if you have a password
$dbname = "crypto_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// List of cryptocurrencies (Symbols)
$cryptos = ["BTC", "ETH", "ADA", "BNB", "SOL", "DOT", "LTC", "XRP", "DOGE", "LINK", "SHIB", "UNI", "AVAX"]; // More cryptos added

// Function to fetch data from CryptoCompare API using CURL
function fetchCryptoData($cryptoSymbols) {
    $apiUrl = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=" . implode(",", $cryptoSymbols) . "&tsyms=USD&api_key=abc1fbde6dc26f10e1fb9ae020209b5f8850f6dd3e96d541b77558fc0dc8d5fb";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    
    if (!$response) {
        die("API request failed.");
    }
    
    return json_decode($response, true);
}

// Insert or update data in the database
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['symbol'])) {
    $symbol = $_GET['symbol'];
    $cryptoData = fetchCryptoData([$symbol]);

    if (isset($cryptoData['RAW'][$symbol]['USD'])) {
        $data = $cryptoData['RAW'][$symbol]['USD'];
        $name = strtoupper($symbol); // Capitalize the symbol
        $price = $data['PRICE'];
        $market_cap = $data['MKTCAP'];
        $volume = isset($data['VOLUME24H']) ? $data['VOLUME24H'] : 0;
        $change_percentage = isset($data['CHANGEPCT24HOUR']) ? $data['CHANGEPCT24HOUR'] : 0;

        // Update or insert cryptocurrency data in the database
        $stmt = $conn->prepare("INSERT INTO cryptocurrencies (name, symbol, price, market_cap, volume, change_percentage) 
                                VALUES (?, ?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE price = VALUES(price), market_cap = VALUES(market_cap), 
                                volume = VALUES(volume), change_percentage = VALUES(change_percentage), created_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("ssdddi", $name, $symbol, $price, $market_cap, $volume, $change_percentage);
        $stmt->execute();
        
        // Return updated data as JSON
        echo json_encode([
            'price' => number_format($price, 2),
            'market_cap' => number_format($market_cap),
            'volume' => number_format($volume),
            'change_percentage' => number_format($change_percentage, 2),
        ]);
    }
    exit;
}

// Get stored data from the database
$query = "SELECT * FROM cryptocurrencies";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
            margin-top: 50px;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin: 20px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .card p {
            margin: 10px 0;
            font-size: 18px;
            color: #555;
        }

        .card .update-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            margin: 10px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .card .update-btn:hover {
            background-color: #45a049;
        }

        .card .buy-btn {
            background-color: #ff5722;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            font-size: 16px;
        }

        .card .buy-btn:hover {
            background-color: #e64a19;
        }

        .card canvas {
            width: 100% !important;
            height: 150px !important;
        }
    </style>
</head>
<body>

<h2 style="text-align: center; margin-top: 30px;">Cryptocurrency Prices and Charts</h2>
<div class="container">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="card" id="card-<?php echo $row['symbol']; ?>">
        <h3><?php 
            // Display full name for each symbol
            if ($row['symbol'] == 'BTC') {
                echo "Bitcoin (BTC)";
            } elseif ($row['symbol'] == 'ETH') {
                echo "Ethereum (ETH)";
            } elseif ($row['symbol'] == 'ADA') {
                echo "Cardano (ADA)";
            } elseif ($row['symbol'] == 'BNB') {
                echo "Binance Coin (BNB)";
            } elseif ($row['symbol'] == 'SOL') {
                echo "Solana (SOL)";
            } elseif ($row['symbol'] == 'DOT') {
                echo "Polkadot (DOT)";
            } elseif ($row['symbol'] == 'LTC') {
                echo "Litecoin (LTC)";
            } elseif ($row['symbol'] == 'XRP') {
                echo "Ripple (XRP)";
            } elseif ($row['symbol'] == 'DOGE') {
                echo "Dogecoin (DOGE)";
            } elseif ($row['symbol'] == 'LINK') {
                echo "Chainlink (LINK)";
            } elseif ($row['symbol'] == 'SHIB') {
                echo "Shiba Inu (SHIB)";
            } elseif ($row['symbol'] == 'UNI') {
                echo "Uniswap (UNI)";
            } elseif ($row['symbol'] == 'AVAX') {
                echo "Avalanche (AVAX)";
            }
            ?></h3>
        <p id="price-<?php echo $row['symbol']; ?>">Price: $<?php echo number_format($row['price'], 2); ?></p>
        <p id="market_cap-<?php echo $row['symbol']; ?>">Market Cap: $<?php echo number_format($row['market_cap']); ?></p>
        <p id="volume-<?php echo $row['symbol']; ?>">Volume: $<?php echo number_format($row['volume']); ?></p>
        <p id="change-<?php echo $row['symbol']; ?>">24h Change: <?php echo number_format($row['change_percentage'], 2); ?>%</p>

        <canvas id="chart-<?php echo $row['symbol']; ?>"></canvas>

        <button class="update-btn" data-symbol="<?php echo $row['symbol']; ?>">Update</button>
        <a href="creditcard.php?crypto=<?php echo $row['symbol']; ?>" class="buy-btn">Buy</a>
    </div>

    <script>
        let chartData_<?php echo $row['symbol']; ?> = {
            labels: ["1w", "2w", "3w", "1m"], // 1 month period
            datasets: [{
                label: "<?php echo $row['symbol']; ?> Price ($)",
                data: [<?php echo $row['price'] * 0.95; ?>, <?php echo $row['price'] * 0.98; ?>, <?php echo $row['price'] * 1.02; ?>, <?php echo $row['price']; ?>],
                borderColor: "blue",
                fill: false
            }]
        };

        new Chart(document.getElementById("chart-<?php echo $row['symbol']; ?>"), {
            type: "line",
            data: chartData_<?php echo $row['symbol']; ?>,
            options: {
                responsive: true,
                scales: {
                    x: { 
                        beginAtZero: true 
                    },
                    y: { 
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) { return "$" + value.toFixed(2); } // Format y-axis with $
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return "$" + tooltipItem.raw.toFixed(2); // Tooltip formatting
                            }
                        }
                    }
                }
            }
        });

        document.querySelector(".update-btn[data-symbol='<?php echo $row['symbol']; ?>']").addEventListener("click", function() {
            const symbol = this.getAttribute("data-symbol");
            
            // AJAX request to fetch new data for the cryptocurrency
            fetch("home.php?symbol=" + symbol)
                .then(response => response.json())
                .then(data => {
                    // Update the DOM with the new data
                    document.getElementById("price-" + symbol).textContent = "Price: $" + data.price;
                    document.getElementById("market_cap-" + symbol).textContent = "Market Cap: $" + data.market_cap;
                    document.getElementById("volume-" + symbol).textContent = "Volume: $" + data.volume;
                    document.getElementById("change-" + symbol).textContent = "24h Change: " + data.change_percentage + "%";

                    // Optionally, update the chart data (just to demonstrate dynamic change)
                    let chart = Chart.getChart("chart-" + symbol); // Retrieve chart
                    chart.data.datasets[0].data = [data.price * 0.95, data.price * 0.98, data.price * 1.02, data.price]; // Update chart data
                    chart.update(); // Refresh the chart with new data
                });
        });
    </script>
<?php endwhile; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
