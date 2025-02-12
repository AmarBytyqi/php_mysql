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
$apiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,ripple,litecoin,cardano,polkadot,chainlink,stellar,dogecoin&vs_currencies=usd&include_market_cap=true&include_24hr_change=true';
$response = file_get_contents($apiUrl);

// Check if the API call was successful
if ($response === FALSE) {
    die("Error fetching data from CoinGecko API.");
}

$cryptoData = json_decode($response, true);

// Check if the data was decoded successfully
if ($cryptoData === NULL) {
    die("Error decoding JSON data.");
}

// Insert or update cryptocurrency data in the database
foreach ($cryptoData as $crypto => $data) {
    $price = $data['usd'];
    $marketCap = $data['usd_market_cap'];
    $change = $data['usd_24h_change'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO cryptocurrencies (name, price, market_cap) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE price=?, market_cap=?, price_change=?");
    
    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("sddddd", $crypto, $price, $marketCap, $price, $marketCap, $change);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(180deg, #f0faff, #e6f7ff);
            font-family: 'Arial', sans-serif;
            color: #333;
            overflow-y: scroll; /* Allow vertical scrolling */
        }
        .card {
            margin: 20px 0; /* Add margin for spacing */
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            display: flex; /* Use flexbox for layout */
            flex-direction: row; /* Align items in a row */
            width: 100%; /* Full width */
        }
        .info {
            flex: 1; /* Take up available space */
            padding: 20px; /* Add padding */
        }
        canvas {
            width: 70% !important; /* Set canvas width to 70% */
            height: 200px !important; /* Set canvas height */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center text-primary">Cryptocurrency Prices</h2>
    <div class="row justify-content-center">
        <?php foreach ($cryptos as $crypto): ?>
            <div class="col-12 mb-4"> <!-- Full width column -->
                <div class="card p-4"> <!-- Card structure -->
                    <div class="info"> <!-- Left side for info -->
                        <h5 class="card-title text-center"><?php echo ucfirst($crypto['name']); ?></h5>
                        <p class="card-text">Current Price: $<?php echo number_format($crypto['price'], 2); ?></p>
                        <p class="card-text">Market Cap: $<?php echo number_format($crypto['market_cap'], 2); ?></p>
                        <p class="card-text">24h Change: <?php echo number_format($crypto['price_change'], 2); ?>%</p>
                    </div>
                    <canvas id="chart-<?php echo $crypto['name']; ?>" width="300" height="200"></canvas> <!-- Graph on the right -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const cryptoData = {
        'bitcoin': [40000, 41000, 42000, 43000, 44000],
        'ethereum': [2500, 2600, 2700, 2800, 2900],
        'ripple': [0.5, 0.55, 0.6, 0.58, 0.57],
        'litecoin': [150, 155, 160, 158, 162],
        'cardano': [1.2, 1.25, 1.3, 1.28, 1.35],
        'polkadot': [30, 32, 31, 33, 34],
        'chainlink': [25, 26, 27, 28, 29],
        'stellar': [0.1, 0.11, 0.12, 0.115, 0.13],
        'dogecoin': [0.05, 0.055, 0.06, 0.058, 0.057],
    };

    const colors = {
        'bitcoin': 'rgba(255, 215, 0, 1)', // Gold
        'ethereum': 'rgba(0, 0, 255, 1)', // Blue
        'ripple': 'rgba(0, 150, 136, 1)', // Teal
        'litecoin': 'rgba(200, 200, 200, 1)', // Silver
        'cardano': 'rgba(0, 188, 212, 1)', // Cyan
        'polkadot': 'rgba(255, 82, 82, 1)', // Red
        'chainlink': 'rgba(0, 123, 255, 1)', // Light Blue
        'stellar': 'rgba(255, 193, 7, 1)', // Amber
        'dogecoin': 'rgba(255, 193, 7, 1)', // Amber
    };

    Object.keys(cryptoData).forEach(crypto => {
        const ctx = document.getElementById(`chart-${crypto}`).getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
                datasets: [{
                    label: `${crypto.charAt(0).toUpperCase() + crypto.slice(1)} Price`,
                    data: cryptoData[crypto],
                    borderColor: colors[crypto],
                    backgroundColor: colors[crypto].replace('1)', '0.2)'),
                    borderWidth: 2,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(200, 200, 200, 0.5)',
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(200, 200, 200, 0.5)',
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                    },
                    legend: {
                        display: true,
                        position: 'top',
                    }
                }
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>