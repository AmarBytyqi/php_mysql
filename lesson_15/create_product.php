<?php
$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "";  // Replace with your database password
$dbname = "products_management"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user inputs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $price = floatval($_POST['price']);  // Ensure the price is a float
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = intval($_POST['stock']);  // Ensure the stock is an integer

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO products (title, price, description, stock) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $title, $price, $description, $stock); // 's' for strings, 'd' for double, 'i' for integer

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "New product created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="css_for_create.css">
</head>
<body>
<div class="container">
    <h1>Add Product</h1>
    <form method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required><br>

        <label for="price">Price:</label>
        <input type="text" name="price" id="price" required><br>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea><br>

        <label for="stock">Stock:</label>
        <input type="number" name="stock" id="stock" required><br>

        <input type="submit" value="Add Product">
    </form>
</div>
</body>
</html>
