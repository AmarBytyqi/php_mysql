<?php
$servername = "localhost";
$username = "username";  // Replace with your database username
$password = "password";  // Replace with your database password
$dbname = "products_management"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];

    $sql = "INSERT INTO products (title, price, description, stock) 
            VALUES ('$title', '$price', '$description', '$stock')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New product created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<form method="post">
    Title: <input type="text" name="title" required><br>
    Price: <input type="text" name="price" required><br>
    Description: <textarea name="description" required></textarea><br>
    Stock: <input type="number" name="stock" required><br>
    <input type="submit" value="Add Product">
</form>
