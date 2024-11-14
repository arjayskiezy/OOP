<?php
session_start();
include '../CONFIG/db_connect.php';

$db = new Database();
$conn = $db->getConnection(); // Get the connection from the Database class

if (!$conn) {
    die("Database connection failed.");
}

$error = ""; // Initialize the error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    if ($email == 'admin@gmail.com' && $password == 'admin') {
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_name'] = 'Admin'; // Set admin name or modify as needed
        header('Location: ../ADMIN/adminpage.php');
        exit();
    }

    $sql = "SELECT * FROM Customers WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        if (password_verify($password, $customer['password'])) {
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
            header('Location: ../MAINPAGE/mainpage.php');
            exit();
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="image-container">
        </div>
        <div class="login-container">
            <h1> Login </h1>
            <?php if (!empty($error)) { ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php } ?>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <input type="submit" value="Login">
            </form>
            <p>Don't have an account? <a href="../REGISTER/register.php">Create one</a>.</p>
            <img src="../ASSETS/Logo.png" alt="Logo" class="logo">
        </div>
    </div>
</body>

</html>