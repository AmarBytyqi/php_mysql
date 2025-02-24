<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$cacheFile = 'crypto_cache.json';
$cacheTime = 900; // 15 minutes cache
$cryptoId = 'bitcoin';

function fetchCryptoData($cryptoId) {
    $apiUrl = "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=" . $cryptoId;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: MyCryptoApp/1.0"]);

    $data = curl_exec($ch);
    curl_close($ch);

    return json_decode($data, true);
}

// Fetch historical data for the chart
function fetchHistoricalData($cryptoId) {
    $apiUrl = "https://api.coingecko.com/api/v3/coins/{$cryptoId}/market_chart?vs_currency=usd&days=7";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: MyCryptoApp/1.0"]);

    $data = curl_exec($ch);
    curl_close($ch);

    return json_decode($data, true);
}

$data = fetchCryptoData($cryptoId);
$historicalData = fetchHistoricalData($cryptoId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitcoin - Cryptocurrency Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(180deg, #f0faff, #e6f7ff);
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            background: white;
            padding: 25px;
            margin-bottom: 40px;
        }
        .card-header {
            background-color: #0072ff;
            color: white;
            border-radius: 15px;
            padding: 15px;
            font-size: 1.5em;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .crypto-logo {
            width: 70px;
            height: 70px;
            margin-right: 15px;
        }
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-more-info, .btn-update, .btn-buy {
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1em;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
            color: white;
        }
        .btn-more-info {
            background-color: #0072ff;
        }
        .btn-more-info:hover {
            background-color: #005cbf;
            transform: scale(1.05);
        }
        .btn-update {
            background-color: #28a745;
        }
        .btn-update:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
        .btn-buy {
            background-color: #ff5e57;
        }
        .btn-buy:hover {
            background-color: #ff4a4a;
            transform: scale(1.05);
        }
        .chart-container {
            width: 100%;
            height: 400px;
            margin-top: 20px;
            display: flex;
            justify-content: center; /* Center the chart */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <div class="card-header">
            <img src="<?php echo $data[0]['image']; ?>" class="crypto-logo" alt="Bitcoin Logo">
            <h5 class="mb-0"><?php echo ucfirst($data[0]['name']); ?> (<?php echo strtoupper($data[0]['symbol']); ?>)</h5>
        </div>
        <div class="card-body">
            <p><strong>💰 Price:</strong> $<span id="price"><?php echo number_format($data[0]['current_price'], 2); ?></span></p>
            <p><strong>📈 Market Cap:</strong> $<?php echo number_format($data[0]['market_cap'], 0); ?></p>
            <p><strong>📊 24h Change:</strong>
                <span class="<?php echo $data[0]['price_change_percentage_24h'] >= 0 ? 'change-positive' : 'change-negative'; ?>">
                    <?php echo number_format($data[0]['price_change_percentage_24h'], 2); ?>%
                </span>
            </p>
            <p><strong>📉 24h Low:</strong> $<?php echo number_format($data[0]['low_24h'], 2); ?></p>
            <p><strong>📈 24h High:</strong> $<?php echo number_format($data[0]['high_24h'], 2); ?></p>
            <div class="btn-container">
                <button class="btn-update" onclick="updatePrice('<?php echo $data[0]['id']; ?>')">Update Price</button>
                <a href="creditcard.php?crypto_id=<?php echo $data[0]['id']; ?>" class="btn-buy">Buy</a>
                <a href="home.php" class="btn-more-info">Go Back to Home</a>
            </div>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="chart-bitcoin"></canvas>
    </div>
</div>

<script>
    const historicalData = <?php echo json_encode($historicalData['prices']); ?>;
    const labels = historicalData.map(data => {
        const date = new Date(data[0]);
        return date.toLocaleDateString();
    });
    const prices = historicalData.map(data => data[1]);

    const ctx = document.getElementById('chart-bitcoin').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Bitcoin Price (USD)',
                data: prices,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    function updatePrice(cryptoId) {
        $.ajax({
            url: `https://api.coingecko.com/api/v3/simple/price?ids=${cryptoId}&vs_currencies=usd`,
            method: 'GET',
            success: function (data) {
                const newPrice = data[cryptoId].usd;
                $('#price').text(newPrice.toFixed(2));
            },
            error: function () {
                console.error("Error fetching data from API");
            }
        });
    }
</script>

</body>
</html>
