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

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="css_for_read.css">
</head>
<style>
    /* General body styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

/* Main container */
.container {
    width: 80%;
    max-width: 1000px;
    margin: 50px auto;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Header styling */
h1 {
    text-align: center;
    color: #444;
    font-size: 2rem;
    margin-bottom: 30px;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Table row hover effect */
tr:hover {
    background-color: #f1f1f1;
}

/* Action links styling (Edit, Delete) */
a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
    margin-right: 10px;
    transition: color 0.3s ease;
}

a:hover {
    color: #0056b3;
}

/* No products message styling */
.no-products {
    text-align: center;
    color: #777;
    font-size: 1.2rem;
}

/* Responsive design for smaller screens */
@media (max-width: 768px) {
    .container {
        width: 90%;
        padding: 20px;
    }

    h1 {
        font-size: 1.5rem;
    }

    table {
        font-size: 0.9rem;
    }

    th, td {
        padding: 10px;
    }
}

    </style>
<body>

<div class="container">
    <h1>Product List</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Title</th>
                <th>Price</th>
                <th>Description</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>

            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td>
                        <a href="update_product.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_product.php?id=<?php echo $row['id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-products">No products found</p>
    <?php endif; ?>

</div>

</body>
</html>