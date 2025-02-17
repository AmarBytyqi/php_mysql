<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

// Initialize error message
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize input data
    $cardholder_name = trim($_POST['cardholder_name']);
    $card_number = trim($_POST['card_number']);
    $expiration_month = trim($_POST['expiration_month']);
    $expiration_year = trim($_POST['expiration_year']);
    $cvv = trim($_POST['cvv']);
    
    // Simple validation
    if (empty($cardholder_name) || empty($card_number) || empty($expiration_month) || empty($expiration_year) || empty($cvv)) {
        $error_message = "Please fill in all fields.";
    } elseif (!preg_match('/^\d{16}$/', $card_number)) {
        $error_message = "Invalid card number. It should be 16 digits.";
    } elseif (!preg_match('/^\d{3}$/', $cvv)) {
        $error_message = "Invalid CVV. It should be 3 digits.";
    } else {
        // Check if the expiration date is valid
        $current_month = date('n'); // Current month (1-12)
        $current_year = date('y'); // Current year (last two digits)

        if ($expiration_year < $current_year || ($expiration_year == $current_year && $expiration_month < $current_month)) {
            $error_message = "The expiration date is in the past.";
        } else {
            // Here, you can simulate an invalid card number for this example
            $error_message = "This credit card doesn't exist."; // Simulate card does not exist
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Card Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #f0faff, #e6f7ff);
            font-family: 'Arial', sans-serif;
            color: #333;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .error-message {
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4">Enter Credit Card Information</h1>
    <div class="card p-4">
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="creditcard.php" method="POST">
            <div class="mb-3">
                <label for="cardholder_name" class="form-label">Cardholder Name:</label>
                <input type="text" class="form-control" id="cardholder_name" name="cardholder_name" required>
            </div>
            <div class="mb-3">
                <label for="card_number" class="form-label">Card Number:</label>
                <input type="text" class="form-control" id="card_number" name="card_number" required>
            </div>
            <div class="mb-3">
                <label for="expiration_month" class="form-label">Expiration Month:</label>
                <select class="form-select" id="expiration_month" name="expiration_month" required>
                    <option value="">Select Month</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="expiration_year" class="form-label">Expiration Year:</label>
                <select class="form-select" id="expiration_year" name="expiration_year" required>
                    <option value="">Select Year</option>
                    <?php for ($y = date('y'); $y <= date('y') + 10; $y++): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="cvv" class="form-label">CVV:</label>
                <input type="text" class="form-control" id="cvv" name="cvv" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Payment</button>
        </form>
        <div class="mt-3">
            <a href="home.php" class="btn btn-secondary">Go Back to Home</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
