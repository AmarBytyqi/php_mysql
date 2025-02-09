<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'crypto_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch cryptocurrency data from CoinGecko API
$apiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,ripple,litecoin,cardano&vs_currencies=usd&include_market_cap=true&include_24hr_change=true';
$response = file_get_contents($apiUrl);
$cryptoData = json_decode($response, true);

// Insert or update cryptocurrency data in the database
foreach ($cryptoData as $crypto => $data) {
    $price = $data['usd'];
    $marketCap = $data['usd_market_cap'];
    $change = $data['usd_24h_change'];

    // Check if the cryptocurrency already exists in the database
    $stmt = $conn->prepare("INSERT INTO cryptocurrencies (name, price, market_cap, change) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE price=?, market_cap=?, change=?");
    $stmt->bind_param("sddddd", $crypto, $price, $marketCap, $change, $price, $marketCap, $change);
    $stmt->execute();
}

$stmt->close();

// Fetch all cryptocurrencies from the database
$result = $conn->query("SELECT * FROM cryptocurrencies");
$cryptos = $result->fetch_all(MYSQLI_ASSOC);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #f0faff, #e6f7ff);
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .card {
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center text-primary">Cryptocurrency Prices</h2>
    <div class="row justify-content-center">
        <?php foreach ($cryptos as $crypto): ?>
            <div class="col-md-4">
                <div class="card p-4">
                    <h5 class="card-title text-center"><?php echo ucfirst($crypto['name']); ?></h5>
                    <p class="card-text">Current Price: $<?php echo number_format($crypto['price'], 2); ?></p>
                    <p class="card-text">Market Cap: $<?php echo number_format($crypto['market_cap'], 2); ?></p>
                    <p class="card-text">24h Change: <?php echo number_format($crypto['change'], 2); ?>%</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>