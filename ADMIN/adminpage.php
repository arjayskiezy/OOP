<?php
session_start(); // Start the session for user session management
include_once '../CONFIG/product.php'; // Include the Product class for product operations

$productObj = new Product(); // Instantiate the Product class

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input from form submission
    $product_name = htmlspecialchars($_POST['product_name']);
    $category = htmlspecialchars($_POST['category']);
    $description = htmlspecialchars($_POST['description']);
    $price = floatval($_POST['price']); // Convert price input to a float
    $stock_quantity = intval($_POST['stock_quantity']); // Convert stock input to an integer
    $status = htmlspecialchars($_POST['status']);
    $image_url = null; // Initialize image URL variable

    // Handle file upload for the product image
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $upload_dir = '../ADMIN/uploads/'; // Directory for uploads
        $filename = uniqid() . '-' . basename($_FILES['product_image']['name']);
        $target_file = $upload_dir . $filename;

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move uploaded file to the target directory
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $image_url = $target_file; // Store file path for database storage
        } else {
            // Handle file upload error
            $_SESSION['error_message'] = "Error uploading the file.";
            header('Location: adminpage.php');
            exit();
        }
    }

    try {

        if ($price <= 0) {
            throw new Exception('Price must be a positive number greater than zero.');
            $_SESSION['error_message'] = "Price must be a positive number greater than zero.";
        }

        if ($stock_quantity < 0) {
            throw new Exception('Stock quantity must be a non-negative integer.');
            $_SESSION['error_message'] = "Stock quantity must be a non-negative integer.";
        }
        // Check if this is an update request
        if (isset($_POST['update_product_id']) && !empty($_POST['update_product_id'])) {
            $product_id = intval($_POST['update_product_id']); // Convert product ID to integer
            $result = $productObj->updateProduct($product_id, $product_name, $category, $description, $price, $stock_quantity, $status, $image_url);
            $_SESSION['success_message'] = $result ? "Product updated successfully." : "Failed to update the product.";
        } else {
            // Handle add request if no update ID is provided
            $result = $productObj->addProduct($product_name, $category, $description, $price, $stock_quantity, $status, $image_url);
            $_SESSION['success_message'] = $result ? "Product added successfully." : "Failed to add the product.";
        }

        header('Location: adminpage.php');
        exit();
    } catch (Exception $e) {
        // Handle any exception that occurs during product operation
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header('Location: adminpage.php');
        exit();
    }
}

// Handle product deletion request
if (isset($_GET['delete_product_id'])) {
    $product_id = intval($_GET['delete_product_id']); // Convert product ID to integer

    try {
        if ($productObj->deleteProduct($product_id)) {
            $_SESSION['success_message'] = "Product deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete the product.";
        }
    } catch (Exception $e) {
        // Handle exception during product deletion
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header('Location: adminpage.php'); // Redirect to refresh product list
    exit();
}

// Retrieve all products to display on the page
$products = $productObj->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Metadata and link to stylesheets -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="adminpage.css" rel="stylesheet">
</head>

<body>
    <div class="container mx-auto p-6">
        <!-- Header section -->
        <div class="flex justify-between items-center border-b-2 border-yellow-400 pb-2 mb-4">
            <p class="text-2xl font-bold custom-h1">ADMIN</p>
            <button type="button" class="px-4 py-2 custom-button" onclick="window.location.href='../LOGIN/login.php';">Logout</button>
        </div>

        <!-- Main content title -->
        <h1 class="text-3xl font-bold mb-6 text-center custom-h1">Manage Products</h1>
        <button class="fixed bottom-4 right-4 bg-yellow-700 text-white text-center px-4 py-2 rounded custom-button shadow-lg" onclick="openForm()">Add Product</button>

        <div><?php if (isset($_SESSION['error_message'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showAlertModal('error', '<?php echo addslashes($_SESSION['error_message']); ?>');
                    });
                </script>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showAlertModal('success', '<?php echo addslashes($_SESSION['success_message']); ?>');
                    });
                </script>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        </div>

        <!-- Product display section -->
        <div class="container mx-auto p-6 max-w-6xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php if (!empty($products)) {
                    // Loop to display each product in a grid
                    foreach ($products as $product) {
                        $productData = htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8'); // Encode product data for JS
                        $image_tag = !empty($product['image_url']) ? "<img class='w-50 h-70 object-cover rounded' src='{$product['image_url']}' alt='" . htmlspecialchars($product['product_name']) . "'>" : "<div class='w-full h-48 bg-gray-200 rounded flex items-center justify-center'>No image</div>";
                        echo "<div class='bg-white p-2 rounded shadow'>
                            $image_tag
                            <div class='text-xl font-semibold mt-2 product-name'>" . htmlspecialchars($product['product_name']) . "</div>
                            <div class='text-lg product-price font-bold'>₱" . number_format($product['price'], 2) . "</div>
                            <div class='flex justify-between mt-4'>
                                <button onclick='openForm(\"update\", {$productData})' class='bg-green-500 text-white px-2 py-1 rounded'>Update</button>
                                <button onclick='deleteProduct(" . $product['product_id'] . ")' class='bg-red-500 text-white px-2 py-1 rounded'>Delete</button>
                            </div>
                        </div>";
                    }
                } else {
                    // Message when no products are available
                    echo "<p class='text-center col-span-full'>No products available</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Update Product Form -->
    <div id="productFormModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden justify-center items-center">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h2 id="formModalTitle" class="text-2xl font-bold mb-4 custom-h1">Add New Product</h2>
            <form id="productForm" action="adminpage.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_product_id" id="update_product_id"> <!-- Hidden field for product ID -->
                <!-- Form fields for product details -->
                <div class="mb-4">
                    <label class="block text-gray-700">Product Name</label>
                    <input type="text" name="product_name" id="product_name" class="w-full border border-gray-300 p-2 rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Category</label>
                    <input type="text" name="category" id="category" class="w-full border border-gray-300 p-2 rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Description</label>
                    <textarea name="description" id="description" class="w-full border border-gray-300 p-2 rounded" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Price</label>
                    <input type="number" name="price" id="price" step="0.01" class="w-full border border-gray-300 p-2 rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="w-full border border-gray-300 p-2 rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Status</label>
                    <select name="status" id="status" class="w-full border border-gray-300 p-2 rounded" required>
                        <option value="Published">Available</option>
                        <option value="Draft">No Stock</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Product Image</label>
                    <input type="file" name="product_image" id="product_image" class="w-full border border-gray-300 p-2 rounded" accept="image/*">
                </div>
                <!-- Form buttons -->
                <div class="flex justify-end">
                    <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded mr-2" onclick="closeForm()">Cancel</button>
                    <button type="submit" id="formSubmitButton" class="custom-button px-4 py-2">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Alert Modal -->
    <div id="alertModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full">
            <div id="alertContent" class="text-center">
                <!-- Content will be dynamically inserted here -->
            </div>
            <div class="mt-4 flex justify-center">
                <button onclick="closeAlertModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Close</button>
            </div>
        </div>
    </div>

    <!-- JavaScript functions for form handling -->
    <script>
        function openForm(mode, product = null) {
            const formTitle = document.getElementById('formModalTitle');
            const submitButton = document.getElementById('formSubmitButton');
            const productForm = document.getElementById('productForm');
            productForm.reset(); // Reset form fields
            document.getElementById('update_product_id').value = ''; // Clear hidden input

            if (mode === 'add') {
                // Set form for adding a new product
                formTitle.textContent = 'Add New Product';
                submitButton.textContent = 'Save';
                document.getElementById('update_product_id').value = ''; // Clear ID field for new product
                productForm.reset(); // Clear all fields
            } else if (mode === 'update' && product) {
                // Populate form with product details for update
                formTitle.textContent = 'Update Product';
                submitButton.textContent = 'Save';
                document.getElementById('update_product_id').value = product.product_id;
                document.getElementById('product_name').value = product.product_name;
                document.getElementById('category').value = product.category;
                document.getElementById('description').value = product.description;
                document.getElementById('price').value = product.price;
                document.getElementById('stock_quantity').value = product.stock_quantity;
                document.getElementById('status').value = product.status;
            }

            // Display the modal
            document.getElementById('productFormModal').classList.remove('hidden');
            document.getElementById('productFormModal').classList.add('flex');
        }

        function closeForm() {
            // Hide the modal and reset the form
            document.getElementById('productFormModal').classList.remove('flex');
            document.getElementById('productFormModal').classList.add('hidden');
        }

        function deleteProduct(productId) {
            // Confirm deletion and redirect to delete the product
            if (confirm("Are you sure you want to delete this product?")) {
                window.location.href = 'adminpage.php?delete_product_id=' + productId;
            }
        }

        function showAlertModal(type, message) {
            const alertModal = document.getElementById('alertModal');
            const alertContent = document.getElementById('alertContent');

            // Set the content and style based on the alert type
            alertContent.innerHTML = `
        <div class="${type === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'} border ${
        type === 'success' ? 'border-green-400' : 'border-red-400'
    } px-4 py-3 rounded">
            <strong class="font-bold">${type === 'success' ? 'Success:' : 'Error:'}</strong>
            <span class="block sm:inline">${message}</span>
        </div>
    `;

            alertModal.classList.remove('hidden');
            alertModal.classList.add('flex');
        }

        function closeAlertModal() {
            const alertModal = document.getElementById('alertModal');
            alertModal.classList.add('hidden');
        }

        function closeAlert(element) {
            element.parentElement.classList.add('hidden');
        }
    </script>
</body>

</html>