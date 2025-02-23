<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$cacheFile = 'crypto_cache.json';
$cacheTime = 900; // Cache duration (15 minutes)
$cryptoIds = ['bitcoin', 'ethereum', 'cardano', 'solana', 'dogecoin', 'polkadot', 'chainlink', 'litecoin', 'ripple'];

function fetchCryptoData($cryptoIds) {
    $apiUrl = "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=" . implode(',', $cryptoIds) . "&order=market_cap_desc&per_page=9&page=1&sparkline=true";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: MyCryptoApp/1.0"]);

    $data = curl_exec($ch);
    curl_close($ch);

    return json_decode($data, true);
}

// Load cached data if valid, otherwise fetch from API
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $data = json_decode(file_get_contents($cacheFile), true);
} else {
    $data = fetchCryptoData($cryptoIds);
    if ($data) {
        file_put_contents($cacheFile, json_encode($data));
    } else {
        $data = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cryptocurrency Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(180deg, #f0faff, #e6f7ff);
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
            background: white;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .btn-buy {
            background-color: #0072ff;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            color: white;
            transition: background-color 0.3s;
        }
        .btn-buy:hover {
            background-color: #005cbf;
        }
        .crypto-logo {
            width: 50px;
            height: 50px;
        }
        .change-positive {
            color: green;
        }
        .change-negative {
            color: red;
        }
        .card-header {
            background-color: #0072ff;
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .card-title {
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4">Cryptocurrency Market</h1>

    <?php if (empty($data)): ?>
        <p class="text-center text-danger">Error: Unable to fetch data from CoinGecko API.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($data as $crypto): ?>
                <div class="col-md-4">
                    <div class="card p-3">
                        <div class="card-header d-flex align-items-center">
                            <img src="<?php echo $crypto['image']; ?>" class="crypto-logo me-2">
                            <h5 class="card-title mb-0"><?php echo ucfirst($crypto['name']); ?> (<?php echo strtoupper($crypto['symbol']); ?>)</h5>
                        </div>
                        <p class="card-text">💰 <strong>Price:</strong> $<span id="price-<?php echo $crypto['id']; ?>"><?php echo number_format($crypto['current_price'], 2); ?></span></p>
                        <p class="card-text">📈 <strong>Market Cap:</strong> $<?php echo number_format($crypto['market_cap'], 0); ?></p>
                        <p class="card-text">📊 <strong>24h Change:</strong> 
                            <span class="<?php echo $crypto['price_change_percentage_24h'] >= 0 ? 'change-positive' : 'change-negative'; ?>">
                                <?php echo number_format($crypto['price_change_percentage_24h'], 2); ?>%
                            </span>
                        </p>
                        <p class="card-text">📉 <strong>7d Low:</strong> $<?php echo number_format($crypto['low_24h'], 2); ?></p>
                        <p class="card-text">🚀 <strong>7d High:</strong> $<?php echo number_format($crypto['high_24h'], 2); ?></p>
                        <button class="btn btn-secondary" onclick="updatePrice('<?php echo $crypto['id']; ?>')">Update Price</button>
                        <a href="creditcard.php?crypto_id=<?php echo $crypto['id']; ?>" class="btn btn-buy">Buy</a>
                        <canvas id="chart-<?php echo $crypto['id']; ?>" width="400" height="200"></canvas>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    const historicalData = <?php echo json_encode($data); ?>;
    let charts = {}; // Store all chart instances

    function createChart(cryptoId, data) {
        const ctx = document.getElementById('chart-' + cryptoId).getContext('2d');
        
        if (charts[cryptoId]) {
            charts[cryptoId].destroy(); // Destroy previous chart before creating a new one
        }

        charts[cryptoId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({ length: data.length }, (_, i) => i),
                datasets: [{
                    label: cryptoId.toUpperCase() + " Price (USD)",
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: false }
                }
            }
        });
    }

    // Initialize graphs
    historicalData.forEach(crypto => {
        createChart(crypto.id, crypto.sparkline_in_7d.price);
    });

    function updatePrice(crypto) {
        const button = $(`#price-${crypto}`).closest('.card').find('button'); // Get the button for the current crypto
        button.prop('disabled', true).text('Updating...'); // Disable the button and change text

        $.ajax({
            url: `https://api.coingecko.com/api/v3/simple/price?ids=${crypto}&vs_currencies=usd`,
            method: 'GET',
            headers: { "User-Agent": "MyCryptoApp/1.0" },
            success: function(data) {
                if (data[crypto]) {
                    $("#price-" + crypto).text(data[crypto].usd.toFixed(2));
                }
            },
            error: function() {
                alert('Error fetching data from CoinGecko');
            },
            complete: function() {
                button.prop('disabled', false).text('Update Price'); // Re-enable the button and reset text
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
