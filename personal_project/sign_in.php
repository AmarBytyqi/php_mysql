<?php
// Start session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'crypto_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Error message variable
$error_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if inputs are empty
    if (empty($username) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        // Prepare query to find user
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username); // Check by username or email
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                // Redirect to home page
                header("Location: home.php");
                exit();
            } else {
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "No user found with that username or email.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Background Gradient */
        body {
            background: linear-gradient(180deg, #f0faff, #e6f7ff);
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        /* Card Styling */
        .card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Card Header */
        .card-header {
            background-color: #ffffff;
            color: #0072ff;
            text-align: center;
            font-size: 26px;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }

        /* Input Fields */
        .form-control {
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ccc;
            background-color: #f9f9f9;
            color: #333;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0072ff;
            box-shadow: 0 0 5px rgba(0, 114, 255, 0.5);
        }

        /* Button Styling */
        .btn-primary {
            background-color: #0072ff;
            border: none;
            border-radius: 25px;
            padding: 12px;
            font-size: 18px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #005cbf;
        }

        /* Error Message Styling */
        .alert-danger {
            background-color: rgba(255, 0, 0, 0.1);
            color: #d32f2f;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
        }

        /* Footer with link */
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #0072ff;
        }

        .footer a {
            color: #0072ff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Animations */
        .card-header, .form-control, .btn-primary {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">Sign In</div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form action="sign_in.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username or Email:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Sign In</button>
    </form>

    <div class="footer">
        <p>Don't have an account? <a href="sign_up.php">Sign Up</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
