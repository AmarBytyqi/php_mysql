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

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];

    $sql = "UPDATE products SET title='$title', price='$price', description='$description', stock='$stock' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Product updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<form method="post">
    Title: <input type="text" name="title" value="<?php echo $product['title']; ?>" required><br>
    Price: <input type="text" name="price" value="<?php echo $product['price']; ?>" required><br>
    Description: <textarea name="description" required><?php echo $product['description']; ?></textarea><br>
    Stock: <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required><br>
    <input type="submit" value="Update Product">
</form>
