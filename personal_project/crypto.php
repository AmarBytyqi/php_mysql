<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crypto_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch data from CoinGecko API
function fetchCryptoData($crypto_id) {
    $url = "https://api.coingecko.com/api/v3/coins/{$crypto_id}";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    
    return [
        'name' => $data['name'],
        'symbol' => $data['symbol'],
        'current_price' => $data['market_data']['current_price']['usd'],
        'market_cap' => $data['market_data']['market_cap']['usd'],
        'volume' => $data['market_data']['total_volume']['usd'],
        'supply' => $data['market_data']['circulating_supply']
    ];
}

// Function to insert data into the database
function saveCryptoData($data) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO cryptocurrencies (name, symbol, current_price, market_cap, volume, supply) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssdddi",
        $data['name'],
        $data['symbol'],
        $data['current_price'],
        $data['market_cap'],
        $data['volume'],
        $data['supply']
    );
    $stmt->execute();
    $stmt->close();
}

// Fetch and save data for Bitcoin and Ethereum
$cryptos = ['bitcoin', 'ethereum'];  // Add more coins here if needed

foreach ($cryptos as $crypto_id) {
    $crypto_data = fetchCryptoData($crypto_id);
    saveCryptoData($crypto_data);
}

// Close the database connection
$conn->close();
?>
