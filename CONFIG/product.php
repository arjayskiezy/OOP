<?php
// Include database connection
include_once 'db_connect.php';

class Product
{
    private $conn; // Database connection variable

    // Constructor to initialize database connection
    public function __construct()
    {
        $db = new Database(); // Create an instance of the Database class (assumes Database class is defined in db_connect.php)
        $this->conn = $db->getConnection(); // Use getConnection() method to get the database connection

        // Check if the connection was successful
        if (!$this->conn) {
            throw new Exception("Failed to establish database connection."); // Throw an exception if connection fails
        }
    }

    // Method to fetch all products
    public function getAllProducts()
    {
        $products = []; // Initialize an empty array to hold product data
        $sql = "SELECT product_id, product_name, category, description, price, stock_quantity, image_url FROM Products"; // SQL query to fetch product details
        $result = $this->conn->query($sql); // Execute the query

        // Check if there are results and if there are rows
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { // Fetch each row as an associative array
                $products[] = $row; // Add each product to the products array
            }
        }

        return $products; // Return the array of products
    }

    // Method to add a new product
    public function addProduct($product_name, $category, $description, $price, $stock_quantity, $status, $image_url)
    {
        // SQL query to insert a new product into the database
        $sql = "INSERT INTO Products (product_name, category, description, price, stock_quantity, status, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql); // Prepare the SQL statement
        $stmt->bind_param("sssdiss", $product_name, $category, $description, $price, $stock_quantity, $status, $image_url); // Bind parameters

        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            return true; // Return true if the product was added successfully
        } else {
            throw new Exception("Failed to add product: " . $this->conn->error); // Throw an exception if an error occurs
        }
    }

    // Method to delete a product by ID
    public function deleteProduct($product_id)
    {
        // SQL query to delete a product from the database by product ID
        $sql = "DELETE FROM Products WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql); // Prepare the SQL statement
        $stmt->bind_param("i", $product_id); // Bind the product ID parameter

        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            return true; // Return true if the product was deleted successfully
        } else {
            throw new Exception("Failed to delete product: " . $this->conn->error); // Throw an exception if an error occurs
        }
    }

    // Method to update an existing product by ID
    public function updateProduct($product_id, $product_name, $category, $description, $price, $stock_quantity, $status, $image_url = null)
    {
        // SQL query to update product details; uses COALESCE to keep the original image_url if a new one isn't provided
        $sql = "UPDATE Products 
                SET product_name = ?, category = ?, description = ?, price = ?, stock_quantity = ?, status = ?, image_url = COALESCE(?, image_url)
                WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql); // Prepare the SQL statement
        $stmt->bind_param("sssdissi", $product_name, $category, $description, $price, $stock_quantity, $status, $image_url, $product_id); // Bind parameters

        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            return true; // Return true if the product was updated successfully
        } else {
            throw new Exception("Failed to update product: " . $this->conn->error); // Throw an exception if an error occurs
        }
    }
}
