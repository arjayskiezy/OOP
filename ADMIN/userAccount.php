<?php
session_start(); // Start the session for user session management
include_once '../CONFIG/user.php'; // Include the UserManager class

$userManager = new UserManager(); // Instantiate the UserManager class

// Handle user update request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $userId = intval($_POST['update_user_id']);
        $firstName = htmlspecialchars($_POST['first_name']);
        $lastName = htmlspecialchars($_POST['last_name']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

        try {
                // Check if this is an update request
                if (isset($_POST['update_user_id']) && !empty($_POST['update_user_id'])) {
                        $result = $userManager->updateUser($userId, $firstName, $lastName, $email, $password);
                        $_SESSION['success_message'] = $result ? "User updated successfully." : "Failed to update the user.";
                } else {
                        $_SESSION['error_message'] = "Invalid update request.";
                }

                header('Location: userAccount.php');
                exit();
        } catch (Exception $e) {
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                header('Location: userAccount.php');
                exit();
        }
}

// Handle user deletion request
if (isset($_GET['delete_user_id'])) {
        $userId = intval($_GET['delete_user_id']); // Convert user ID to integer

        try {
                if ($userManager->deleteUser($userId)) {
                        $_SESSION['success_message'] = "User deleted successfully.";
                } else {
                        $_SESSION['error_message'] = "Failed to delete the user.";
                }
        } catch (Exception $e) {
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }

        header('Location: userAccount.php'); // Redirect to refresh user list
        exit();
}

// Retrieve all users to display on the page
$users = $userManager->fetchUsers();
?>

<!DOCTYPE html>
<html lang="en">

<head>
        <!-- Metadata and link to stylesheets -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - Manage Users</title>
        <link rel="icon" href="../Logo.ico" />
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="adminpage.css" rel="stylesheet"> <!-- Use shared CSS -->
</head>

<body>
        <div class="container mx-auto p-6">
                <!-- Header section -->
                <div class="flex justify-between items-center space-x-4 border-b-2 border-yellow-400 pb-2 mb-4">
                        <p class="text-2xl font-bold custom-h1">ADMIN</p>
                        <div class="flex space-x-4">
                                <button type="button" class="px-4 py-2 custom-button" onclick="window.location.href='../LOGIN/login.php';"><strong> Logout</strong></button>
                                <button type="button" class="px-4 py-2 custom-button" onclick="window.location.href='adminpage.php';"><strong> Products</strong></button>
                        </div>
                </div>

                <!-- Main content title -->
                <h1 class="text-3xl font-bold mb-6 text-center custom-h1">Manage Users</h1>
                <button class="fixed bottom-4 right-4 bg-yellow-700 text-white text-center px-4 py-2 rounded custom-button shadow-lg" onclick="openForm()">Add User</button>

                <div>
                        <?php if (isset($_SESSION['error_message'])): ?>
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

                <!-- User display section -->
                <div class="container mx-auto p-6 max-w-6xl">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <?php if (!empty($users)) {
                                        // Loop to display each user in a grid
                                        foreach ($users as $user) {
                                                $userData = htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); // Encode user data for JS
                                                echo "<div class='bg-white p-4 rounded shadow'>
                                <div class='flex items-center'>
                                    <svg class='h-20 w-20 text-gray-400 mr-3' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 14c3.866 0 7 1.343 7 3v1H5v-1c0-1.657 3.134-3 7-3zm0-8a4 4 0 110 8 4 4 0 010-8z'></path>
                                    </svg>
                                    <div>
                                        <div class='text-xl font-semibold user-name'>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</div>
                                        <div class='text-lg user-email'>" . htmlspecialchars($user['email']) . "</div>
                                    </div>
                                </div>
                                <div class='flex justify-between mt-4'>
                                    <button onclick='openForm(\"update\", {$userData})' style='background-color: #b08968;box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);' class='bg-green-500 text-white px-2 py-1 rounded'>Update</button>
                                    <button onclick='deleteUser(" . $user['customer_id'] . ")' style='background-color: white; color: #b08968;box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);' class='px-2 py-1 rounded'>Delete</button>
                                </div>
                            </div>";
                                        }
                                } else {
                                        echo "<p class='text-center col-span-full'>No users available</p>";
                                }
                                ?>
                        </div>
                </div>
        </div>

        <!-- Modal for Add/Update User Form -->
        <div id="userFormModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden justify-center items-center">
                <div class="bg-white p-6 rounded shadow-lg w-96">
                        <h2 id="formModalTitle" class="text-2xl font-bold mb-4 custom-h1">Add New User</h2>
                        <form id="userForm" action="userAccount.php" method="POST">
                                <input type="hidden" name="update_user_id" id="update_user_id"> <!-- Hidden field for user ID -->
                                <!-- Form fields for user details -->
                                <div class="mb-4">
                                        <label class="block text-gray-700">First Name</label>
                                        <input type="text" name="first_name" id="first_name" class="w-full border border-gray-300 p-2 rounded" required>
                                </div>
                                <div class="mb-4">
                                        <label class="block text-gray-700">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" class="w-full border border-gray-300 p-2 rounded" required>
                                </div>
                                <div class="mb-4">
                                        <label class="block text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" class="w-full border border-gray-300 p-2 rounded" required>
                                </div>
                                <div class="mb-4">
                                        <label class="block text-gray-700">Password</label>
                                        <input type="password" name="password" id="password" class="w-full border border-gray-300 p-2 rounded" required>
                                </div>
                                <!-- Form buttons -->
                                <div class="flex justify-end">
                                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded mr-2" onclick="closeForm()">Cancel</button>
                                        <button type="submit" id="formSubmitButton" class="custom-button px-4 py-2">Save</button>
                                </div>
                        </form>
                </div>
        </div>

        <!-- JavaScript functions for form handling -->
        <script>
                function openForm(mode, user = null) {
                        const formTitle = document.getElementById('formModalTitle');
                        const submitButton = document.getElementById('formSubmitButton');
                        const userForm = document.getElementById('userForm');
                        userForm.reset(); // Reset form fields
                        document.getElementById('update_user_id').value = ''; // Clear hidden input

                        if (mode === 'add') {
                                formTitle.textContent = 'Add New User';
                                submitButton.textContent = 'Save';
                        } else if (mode === 'update' && user) {
                                formTitle.textContent = 'Update User';
                                submitButton.textContent = 'Save';
                                document.getElementById('update_user_id').value = user.customer_id;
                                document.getElementById('first_name').value = user.first_name;
                                document.getElementById('last_name').value = user.last_name;
                                document.getElementById('email').value = user.email;
                                document.getElementById('password').value = ''; // Leave password field empty for update
                        }

                        // Display the modal
                        document.getElementById('userFormModal').classList.remove('hidden');
                        document.getElementById('userFormModal').classList.add('flex');
                }

                function closeForm() {
                        document.getElementById('userFormModal').classList.remove('flex');
                        document.getElementById('userFormModal').classList.add('hidden');
                }

                function deleteUser(userId) {
                        if (confirm("Are you sure you want to delete this user?")) {
                                window.location.href = 'userAccount.php?delete_user_id=' + userId;
                        }
                }

                function showAlertModal(type, message) {
                        const alertModal = document.getElementById('alertModal');
                        const alertContent = document.getElementById('alertContent');

                        alertContent.innerHTML = `
                <div class="${type === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'} border ${type === 'success' ? 'border-green-400' : 'border-red-400'} px-4 py-3 rounded">
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
        </script>
</body>

</html>