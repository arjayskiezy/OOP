<?php

class Database
{
    // Database connection properties
    private $host = "127.0.0.1"; // Hostname of the database server
    private $username = "root"; // Database username
    private $password = ""; // Database password
    private $dbname = "POS"; // Database name
    private $conn; // Variable to store the database connection

    // Constructor to initialize the database connection
    public function __construct()
    {
        $this->connect(); // Call the connect method to establish a connection
    }

    // Private method to establish a database connection
    private function connect()
    {
        // Create a new MySQLi connection instance with specified properties
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        // Check if the connection was successful
        if ($this->conn->connect_error) {
            // Throw an exception with the connection error message if it fails
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    // Method to return the established database connection
    public function getConnection()
    {
        return $this->conn; // Return the MySQLi connection instance
    }
}
