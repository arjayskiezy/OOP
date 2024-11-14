<?php
// Include database connection
include_once 'db_connect.php';

class Product {
    private $conn;

    // Constructor to initialize database connection
    public function __construct() {
        $db = new Database(); // Assumes Database class is defined in db_connect.php
        $this->conn = $db->getConnection(); // Use getConnection() to get the connection

        if (!$this->conn) {
            throw new Exception("Failed to establish database connection.");
        }
    }

    // Method to fetch all products
    public function getAllProducts() {
        $products = [];
        $sql = "SELECT product_id, product_name, category, description, price, stock_quantity, image_url FROM Products"; 
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    // Method to add a new product
    public function addProduct($product_name, $category, $description, $price, $stock_quantity, $status, $image_url) {
        $sql = "INSERT INTO Products (product_name, category, description, price, stock_quantity, status, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdiss", $product_name, $category, $description, $price, $stock_quantity, $status, $image_url);
        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Failed to add product: " . $this->conn->error);
        }
    }

    // Method to delete a product by ID
    public function deleteProduct($product_id) {
        $sql = "DELETE FROM Products WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Failed to delete product: " . $this->conn->error);
        }
    }
    
    public function updateProduct($product_id, $product_name, $category, $description, $price, $stock_quantity, $status, $image_url = null) {
        $sql = "UPDATE Products 
                SET product_name = ?, category = ?, description = ?, price = ?, stock_quantity = ?, status = ?, image_url = COALESCE(?, image_url)
                WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdissi", $product_name, $category, $description, $price, $stock_quantity, $status, $image_url, $product_id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Failed to update product: " . $this->conn->error);
        }
    }
}
?>
