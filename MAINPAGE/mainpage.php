<?php
// Start the session to keep track of user sessions
session_start();

// Include the Product class from the configuration file
include_once '../CONFIG/product.php';

// Redirect the user to the login page if they are not logged in
if (!isset($_SESSION['customer_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Create an instance of the Product class and retrieve all products from the database
$productObj = new Product();
$products = $productObj->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Basic HTML meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shiffy Vintage Clothing</title>
    <!-- Page icon and stylesheets -->
    <link rel="icon" href="../Logo.ico" />
    <link rel="stylesheet" href="mainpage.css">
    <!-- Include Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link href="mainpage.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <!-- Navigation bar with a welcome message and logout button -->
        <div class="nav-bar">
            <p><strong>Welcome, <?php echo ucwords(htmlspecialchars($_SESSION['customer_name'])); ?>!</strong></p>
            <button type="button" class="logout-btn" onclick="window.location.href='../LOGIN/login.php';">Logout</button>
        </div>
        
        <!-- Main content heading -->
        <h1>New Arrivals</h1>
        
        <!-- Grid layout for displaying products -->
        <div class="product-grid">
            <?php 
            // Check if there are products to display
            if (!empty($products)) {
                // Loop through each product and display its details
                foreach ($products as $product) {
                    // Create an image tag if the image URL is available, otherwise show a placeholder
                    $image_tag = !empty($product['image_url']) ? "<img src='{$product['image_url']}' alt='" . htmlspecialchars($product['product_name']) . "'>" : "<div>No image</div>";
                    
                    // Display the product item with name, price, and "Add to Cart" button
                    echo "<div class='product-item'>
                            $image_tag
                            <div class='product-name'>" . htmlspecialchars($product['product_name']) . "</div>
                            <div class='product-price'>₱" . number_format($product['price'], 2) . "</div>
                            <a href='add_to_cart.php?product_id=" . urlencode($product['product_id']) . "' class='add-cart-button'>Add to Cart</a>
                          </div>";
                }
            } else {
                // Message if no products are available
                echo "<p>No products available</p>";
            }
            ?>
        </div>
    </div>
</body>

</html>
