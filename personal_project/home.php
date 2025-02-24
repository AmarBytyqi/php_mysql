<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

$cacheFile = 'crypto_cache.json';
$cacheTime = 900; // 15 minutes cache
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

function saveCryptoDataToDatabase($data) {
    $mysqli = new mysqli("localhost", "root", "", "crypto_db");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    foreach ($data as $crypto) {
        $name = $mysqli->real_escape_string($crypto['name']);
        $symbol = $mysqli->real_escape_string($crypto['symbol']);
        $price = $crypto['current_price'];
        $market_cap = $crypto['market_cap'];
        $volume = $crypto['total_volume'];
        $change_percentage = $crypto['price_change_percentage_24h'];
        $created_at = date('Y-m-d H:i:s');

        // Construct the SQL query
        $query = "INSERT INTO cryptocurrencies (name, symbol, price, market_cap, volume, change_percentage, created_at) 
                  VALUES ('$name', '$symbol', $price, $market_cap, $volume, $change_percentage, '$created_at') 
                  ON DUPLICATE KEY UPDATE price=$price, market_cap=$market_cap, volume=$volume, change_percentage=$change_percentage";
        
        // Execute the query
        if (!$mysqli->query($query)) {
            echo "Error: " . $mysqli->error; // Output error if there's an issue
        }
    }
    $mysqli->close();
}

// Load cache or fetch new data
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $data = json_decode(file_get_contents($cacheFile), true);
} else {
    $data = fetchCryptoData($cryptoIds);
    if ($data) {
        saveCryptoDataToDatabase($data);
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
        body { background: linear-gradient(180deg, #f0faff, #e6f7ff); font-family: 'Arial', sans-serif; color: #333; }
        .container { max-width: 1200px; margin: auto; }
        .card { display: flex; flex-direction: row; align-items: center; justify-content: space-between; border-radius: 20px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15); background: white; transition: transform 0.3s ease, box-shadow 0.3s ease; margin-bottom: 40px; padding: 25px; min-height: 400px; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); }
        .card-left { flex: 1; padding-right: 20px; }
        .card-header { background-color: white; border-radius: 15px; display: flex; align-items: center; padding: 15px; font-size: 1.3em; font-weight: bold; }
        .crypto-logo { width: 70px; height: 70px; margin-right: 15px; }
        .card-body { text-align: left; }
        .card-text { font-size: 1.2em; margin-bottom: 12px; }
        .change-positive { color: green; font-weight: bold; }
        .change-negative { color: red; font-weight: bold; }
        .btn-container { display: flex; gap: 10px; }
        .btn-more-info, .btn-update { border: none; border-radius: 25px; padding: 10px 20px; font-size: 1em; font-weight: bold; text-align: center; transition: background-color 0.3s ease, transform 0.2s ease; color: white; }
        .btn-more-info { background-color: #0072ff; }
        .btn-more-info:hover { background-color: #005cbf; transform: scale(1.05); }
        .btn-update { background-color: #28a745; }
        .btn-update:hover { background-color: #218838; transform: scale(1.05); }
        .card-right { flex: 1.5; text-align: center; }
        .chart-container { width: 100%; height: 300px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Cryptocurrency Market</h1>
        <?php foreach ($data as $crypto): ?>
            <div class="card">
                <div class="card-left">
                    <div class="card-header">
                        <img src="<?php echo $crypto['image']; ?>" class="crypto-logo">
                        <h5 class="mb-0"><?php echo ucfirst($crypto['name']); ?> (<?php echo strtoupper($crypto['symbol']); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">💰 <strong>Price:</strong> $<span id="price-<?php echo $crypto['id']; ?>"><?php echo number_format($crypto['current_price'], 2); ?></span></p>
                        <p class="card-text">📈 <strong>Market Cap:</strong> $<?php echo number_format($crypto['market_cap'], 0); ?></p>
                        <p class="card-text">📊 <strong>24h Change:</strong> <span class="<?php echo $crypto['price_change_percentage_24h'] >= 0 ? 'change-positive' : 'change-negative'; ?>"> <?php echo number_format($crypto['price_change_percentage_24h'], 2); ?>% </span></p>
                        <div class="btn-container">
                            <a href="<?php echo ucfirst($crypto['id']); ?>.php" class="btn-more-info">More Info</a>
                            <button class="btn-update" onclick="updatePrice('<?php echo $crypto['id']; ?>')">Update Price</button>
                        </div>
                    </div>
                </div>
                <div class="card-right">
                    <canvas id="chart-<?php echo $crypto['id']; ?>"></canvas>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        const historicalData = <?php echo json_encode($data); ?>;
        let charts = {};
        historicalData.forEach(crypto => {
            const ctx = document.getElementById('chart-' + crypto.id).getContext('2d');
            charts[crypto.id] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({ length: crypto.sparkline_in_7d.price.length }, (_, i) => i),
                    datasets: [{
                        label: crypto.id.toUpperCase() + " Price (USD)",
                        data: crypto.sparkline_in_7d.price,
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
        });

        function updatePrice(cryptoId) {
            $.ajax({
                url: `https://api.coingecko.com/api/v3/simple/price?ids=${cryptoId}&vs_currencies=usd`,
                method: 'GET',
                success: function(data) {
                    const newPrice = data[cryptoId].usd;
                    $('#price-' + cryptoId).text(newPrice.toFixed(2));
                },
                error: function() {
                    console.error("Error fetching data from API");
                }
            });
        }
    </script>
</body>
</html>
