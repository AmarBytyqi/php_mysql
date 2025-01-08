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

$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Title</th>
                <th>Price</th>
                <th>Description</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['title'] . "</td>
                <td>" . $row['price'] . "</td>
                <td>" . $row['description'] . "</td>
                <td>" . $row['stock'] . "</td>
                <td>
                    <a href='update_product.php?id=" . $row['id'] . "'>Edit</a> |
                    <a href='delete_product.php?id=" . $row['id'] . "'>Delete</a>
                </td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "No products found";
}

$conn->close();
?>
s