<?php
require_once 'db_connect.php';

class UserManager
{
        private $db;

        public function __construct()
        {
                $database = new Database();
                $this->db = $database->getConnection();
        }

        // Function to fetch all users
        public function fetchUsers()
        {
                $query = "SELECT * FROM Customers";
                $result = $this->db->query($query);

                if ($result->num_rows > 0) {
                        $users = [];
                        while ($row = $result->fetch_assoc()) {
                                $users[] = $row;
                        }
                        return $users;
                } else {
                        return [];
                }
        }

        // Function to delete a user by customer_id
        public function deleteUser($customerId)
        {
                $query = "DELETE FROM Customers WHERE customer_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $customerId);

                if ($stmt->execute()) {
                        return true;
                } else {
                        return false;
                }
        }

        // Function to update a user's details
        public function updateUser($customerId, $firstName, $lastName, $email, $password)
        {
                $query = "UPDATE Customers SET first_name = ?, last_name = ?, email = ?, password = ? WHERE customer_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssssi", $firstName, $lastName, $email, $password, $customerId);

                if ($stmt->execute()) {
                        return true;
                } else {
                        return false;
                }
        }

        // Function to add a new user
        public function addUser($firstName, $lastName, $email, $password)
        {
                $query = "INSERT INTO Customers (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssss", $firstName, $lastName, $email, $password);

                if ($stmt->execute()) {
                        return true;
                } else {
                        return false;
                }
        }
}
