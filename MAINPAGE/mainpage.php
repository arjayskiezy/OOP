<?php
session_start();
include_once '../CONFIG/product.php';

// Redirect if not logged in
if (!isset($_SESSION['customer_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Instantiate Product class and get products
$productObj = new Product();
$products = $productObj->getAllProducts();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shiffy Vintage Clothing</title>
    <link rel="icon" href="../Logo.ico" />
    <link rel="stylesheet" href="mainpage.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link href="mainpage.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="nav-bar">
            <p><strong>Welcome, <?php echo ucwords(htmlspecialchars($_SESSION['customer_name'])); ?>!</strong></p>
            <button type="button" class="logout-btn" onclick="window.location.href='../LOGIN/login.php';">Logout</button>
        </div>
        <h1>New Arrivals</h1>
        <div class="product-grid">
            <?php if (!empty($products)) {
                foreach ($products as $product) {
                    $image_tag = !empty($product['image_url']) ? "<img src='{$product['image_url']}' alt='" . htmlspecialchars($product['product_name']) . "'>" : "<div>No image</div>";
                    echo "<div class='product-item'>
                            $image_tag
                            <div class='product-name'>" . htmlspecialchars($product['product_name']) . "</div>
                            <div class='product-price'>₱" . number_format($product['price'], 2) . "</div>
                            <a href='add_to_cart.php?product_id=" . urlencode($product['product_id']) . "' class='add-cart-button'>Add to Cart</a>
                          </div>";
                }
            } else {
                echo "<p>No products available</p>";
            }
            ?>
        </div>
    </div>
</body>

</html>