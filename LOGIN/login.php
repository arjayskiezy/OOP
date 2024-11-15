<?php
session_start(); // Start a new or resume an existing session
include '../CONFIG/db_connect.php'; // Include the database connection configuration file

$db = new Database();
$conn = $db->getConnection(); // Get the database connection from the Database class

if (!$conn) { // Check if the connection was successful
    die("Database connection failed."); // Stop execution and display an error message if the connection failed
}

$error = ""; // Initialize the error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Check if the form was submitted via POST
    $email = $conn->real_escape_string($_POST['email']); // Sanitize the email input to prevent SQL injection
    $password = $_POST['password']; // Get the password from the form submission

    // Check if the user is logging in as an admin
    if ($email == 'admin@gmail.com' && $password == 'admin') {
        $_SESSION['customer_logged_in'] = true; // Set session variable to indicate user is logged in
        $_SESSION['customer_name'] = 'Admin'; // Set admin name in session or modify as needed
        header('Location: ../ADMIN/adminpage.php'); // Redirect admin to the admin page
        exit(); // Stop further execution after the redirect
    }

    // Query to find the customer record in the database by email
    $sql = "SELECT * FROM Customers WHERE email = '$email'";
    $result = $conn->query($sql); // Execute the query

    if ($result->num_rows > 0) { // Check if any customer record was found
        $customer = $result->fetch_assoc(); // Fetch the customer data as an associative array
        // Verify the password using password hashing
        if (password_verify($password, $customer['password'])) {
            $_SESSION['customer_logged_in'] = true; // Set session variable to indicate customer is logged in
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name']; // Set customer name in session
            header('Location: ../MAINPAGE/mainpage.php'); // Redirect customer to the main page
            exit(); // Stop further execution after the redirect
        } else {
            $error = "Invalid password. Please try again."; // Set error message for incorrect password
        }
    } else {
        $error = "No account found with that email."; // Set error message if no account was found
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link rel="icon" href="../Logo.ico" />
    <link rel="stylesheet" href="login.css"> <!-- Link to external CSS for styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
</head>

<body>
    <div class="container">
        <div class="image-container">
        </div>
        <div class="login-container">
            <h1> Login </h1>
            <?php if (!empty($error)) { ?> <!-- Check if there is an error message to display -->
                <p style="color: red;"><?php echo $error; ?></p> <!-- Display the error message -->
            <?php } ?>
            <form action="login.php" method="POST"> <!-- Login form that submits to the current page -->
                <div class="input-group">
                    <span class="icon"><i class="fas fa-user"></i></span> <!-- Icon for email input -->
                    <input type="email" name="email" placeholder="Email" required> <!-- Email input field -->
                </div>
                <div class="input-group">
                    <span class="icon"><i class="fas fa-lock"></i></span> <!-- Icon for password input -->
                    <input type="password" name="password" placeholder="Password" required> <!-- Password input field -->
                </div>
                <input type="submit" value="Login"> <!-- Submit button -->
            </form>
            <p>Don't have an account? <a href="../REGISTER/register.php">Create one</a>.</p> <!-- Link to registration page -->
            <img src="../ASSETS/Logo.png" alt="Logo" class="logo"> <!-- Display the logo image -->
        </div>
    </div>
</body>

</html>