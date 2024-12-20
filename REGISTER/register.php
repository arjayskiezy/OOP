<?php
session_start();
include '../CONFIG/db_connect.php'; // Ensure this file sets up $conn for the database connection
include '../CONFIG/user.php';

$userManager = new UserManager();


$db = new Database();
$conn = $db->getConnection(); // Get the connection from the Database class

// Check if $conn is properly initialized
if (!$conn) {
    die("Database connection failed.");
}

$error = ""; // Initialize the error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and collect form data
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phoneNumber = $conn->real_escape_string($_POST['phoneNumber']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Simple server-side validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phoneNumber) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match("/^\+63\d{10}$/", $phoneNumber)) {
        $error = "Invalid phone number format. Use +63 followed by 10 digits.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Securely hash the password

        // Check if the email already exists
        $sql_check = "SELECT * FROM Customers WHERE email = '$email'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            if ($userManager->addUser($first_name, $last_name, $email, $phoneNumber ,$password)) {
                $_SESSION['customer_logged_in'] = true;
                $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
                header('Location: ../MAINPAGE/mainpage.php'); // Redirect to the homepage after registration
                exit();
            } else {
                $error = "Error creating account. Please try again.";
            }
            // Insert the new user into the database
            // $sql = "INSERT INTO Customers (first_name, last_name, email, phone, password) VALUES ('$first_name', '$last_name', '$email', '$phoneNumber', '$hashed_password')";
            // if ($conn->query($sql) === TRUE) {
            //     $_SESSION['customer_logged_in'] = true;
            //     $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
            //     header('Location: ../MAINPAGE/mainpage.php'); // Redirect to the homepage or login page after registration
            //     exit();
            // } else {
            //     $error = "Error creating account. Please try again.";
            // }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="icon" href="../Logo.ico" />
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="register-container">
        <h1>Create Account</h1>
        <?php if (!empty($error)) { ?>
            <p id="error-message" style="color: red;"><?php echo $error; ?></p>
        <?php } ?>
        <p id="error-message" style="color: red;"></p> <!-- Placeholder for client-side error -->
        <form name="registerForm" action="register.php" method="POST" onsubmit="return validateForm()">
            <div class="input-group">
                <span class="icon"><i class="fas fa-user"></i></span>
                <input type="text" name="first_name" placeholder="First" required>
            </div>
            <div class="input-group">
                <span class="icon"><i class="fas fa-user-tag"></i></span>
                <input type="text" name="last_name" placeholder="Last" required>
            </div>
            <div class="input-group">
                <span class="icon"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" placeholder="E-mail" required>
            </div>
            <div class="input-group">
                <span class="icon"><i class="fas fa-phone"></i></span>
                <input type="text" name="phoneNumber" placeholder="Phone +63" required>
            </div>
            <div class="input-group">
                <span class="icon"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="btn-group">
                <input type="submit" value="Sign Up">
                <a href="../LOGIN/login.php">Sign In</a>
            </div>
            <img src="../ASSETS/Logo.png" alt="Logo" class="logo">
        </form>
    </div>
</body>

</html>